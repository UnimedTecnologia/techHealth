<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
set_time_limit(600);

require_once "../../config/AW00DB.php";
require_once "../../config/oracle.class.php";
require_once "../../config/AW00MD.php";

oci_set_client_info($conn, 'UTF-8');

// Função genérica para executar update
function executeUpdate($conn, $sql, $bindings, $entity) {
    $stmt = oci_parse($conn, $sql);
    if (!$stmt) {
        $e = oci_error($conn);
        throw new Exception("Erro ao preparar a consulta de $entity: " . htmlentities($e['message'], ENT_QUOTES));
    }

    foreach ($bindings as $key => &$value) {
        oci_bind_by_name($stmt, $key, $value);
    }

    // if (!oci_execute($stmt, OCI_COMMIT_ON_SUCCESS)) {
    //     $e = oci_error($stmt);
    //     throw new Exception("Erro ao executar update de $entity: " . htmlentities($e['message'], ENT_QUOTES));
    // }
    
    $rows = oci_num_rows($stmt);
    oci_free_statement($stmt);
    
    return $rows;
}

// Função para calcular o fator multiplicador baseado na porcentagem
function calcularFatorMultiplicador($porcentagem) {
    return $porcentagem / 100;
}

// Função para extrair progress_recids de forma robusta
function getProgressRecids($pacote, $campo) {
    $resultados = [];
    
    // Debug: verificar estrutura do pacote
    error_log("Buscando campo: $campo");
    error_log("Estrutura do pacote: " . print_r($pacote, true));
    
    // 1. Buscar como array direto (progress_recid_proc[])
    if (isset($pacote[$campo]) && is_array($pacote[$campo])) {
        $resultados = $pacote[$campo];
        error_log("Encontrado como array direto: " . count($resultados) . " itens");
    }
    // 2. Buscar como campos indexados (progress_recid_proc[0], progress_recid_proc[1], etc.)
    else {
        foreach ($pacote as $key => $value) {
            // Verificar se a chave corresponde ao padrão do campo
            if (preg_match('/^' . preg_quote($campo, '/') . '(\[\d+\])?$/', $key)) {
                if (is_array($value)) {
                    $resultados = array_merge($resultados, $value);
                } else {
                    $resultados[] = $value;
                }
            }
        }
    }
    
    // 3. Buscar como valor único (quando há apenas um item)
    if (empty($resultados) && isset($pacote[$campo]) && !is_array($pacote[$campo]) && $pacote[$campo] !== '') {
        $resultados = [$pacote[$campo]];
        error_log("Encontrado como valor único");
    }
    
    // Filtrar valores vazios, nulos ou não numéricos
    $resultados = array_values(array_filter($resultados, function($item) {
        return $item !== null && $item !== '' && is_numeric($item);
    }));
    
    error_log("Resultados finais para $campo: " . count($resultados) . " - " . implode(', ', $resultados));
    
    return $resultados;
}

// Função alternativa para debug detalhado
function debugProgressRecids($pacote, $prefixo) {
    $encontrados = [];
    foreach ($pacote as $key => $value) {
        if (strpos($key, $prefixo) === 0) {
            $encontrados[$key] = $value;
        }
    }
    error_log("Campos encontrados com prefixo '$prefixo': " . print_r($encontrados, true));
    return $encontrados;
}

try {
    // Verificar se há dados POST
    if (empty($_POST['pacotes'])) {
        throw new Exception("Nenhum dado recebido para atualização.");
    }

    // Debug: verificar estrutura completa do POST
    error_log("=== ESTRUTURA COMPLETA DO POST ===");
    error_log(print_r($_POST, true));
    error_log("=================================");

    // Obter ID do usuário logado
    $userId = $_SESSION['idusuario'] ?? 'sistema';
    $dataAtual = date('d/m/Y');
    
    $results = [
        'success' => [],
        'errors' => [],
        'summary' => [
            'total_ocorrencias' => 0,
            'atualizados_moviproc' => 0,
            'atualizados_histor_proc' => 0,
            'atualizados_mov_insu' => 0,
            'atualizados_histor_insu' => 0
        ]
    ];

    // Processar cada ocorrência de pacote
    foreach ($_POST['pacotes'] as $idx => $pacote) {
        $results['summary']['total_ocorrencias']++;
        
        // Debug detalhado para esta ocorrência
        error_log("=== PROCESSANDO OCORRÊNCIA $idx ===");
        error_log("Dados do pacote: " . print_r($pacote, true));
        
        // Validar dados obrigatórios
        $porcentagem = floatval($pacote['porcentagem'] ?? 0);
        $cdPacote = $pacote['cd_pacote'] ?? '';
        $pacoteOcorrencia = $pacote['pacote_ocorrencia'] ?? '';
        
        error_log("Porcentagem: $porcentagem, CD_PACOTE: $cdPacote, OCORRENCIA: $pacoteOcorrencia");
        
        // Debug detalhado dos campos de progress_recid
        debugProgressRecids($pacote, 'progress_recid_proc');
        debugProgressRecids($pacote, 'progress_recid_hist_proc');
        debugProgressRecids($pacote, 'progress_recid_insu');
        debugProgressRecids($pacote, 'progress_recid_hist_insu');
        
        // Buscar progress_recids
        $progressRecidProc = getProgressRecids($pacote, 'progress_recid_proc');
        $progressRecidHistProc = getProgressRecids($pacote, 'progress_recid_hist_proc');
        $progressRecidInsu = getProgressRecids($pacote, 'progress_recid_insu');
        $progressRecidHistInsu = getProgressRecids($pacote, 'progress_recid_hist_insu');

        // Validar dados
        if (empty($cdPacote) || empty($pacoteOcorrencia)) {
            $results['errors'][] = "Ocorrência $idx: Dados incompletos para atualização";
            continue;
        }

        if ($porcentagem <= 0 || $porcentagem > 100) {
            $results['errors'][] = "Ocorrência $pacoteOcorrencia (Pacote: $cdPacote): Porcentagem inválida ($porcentagem%)";
            continue;
        }

        // Se não encontrou nenhum progress_recid, tentar abordagem alternativa
        if (empty($progressRecidProc) && empty($progressRecidHistProc) && 
            empty($progressRecidInsu) && empty($progressRecidHistInsu)) {
            
            error_log("Nenhum progress_recid encontrado, tentando busca alternativa...");
            
            // Buscar qualquer campo que contenha "progress_recid"
            $allProgressRecids = [];
            foreach ($pacote as $key => $value) {
                if (strpos($key, 'progress_recid') !== false) {
                    if (is_array($value)) {
                        $allProgressRecids = array_merge($allProgressRecids, $value);
                    } else {
                        $allProgressRecids[] = $value;
                    }
                }
            }
            
            $allProgressRecids = array_values(array_filter($allProgressRecids, function($item) {
                return $item !== null && $item !== '' && is_numeric($item);
            }));
            
            error_log("Progress RECIDs encontrados na busca alternativa: " . count($allProgressRecids));
            
            // Distribuir os recids encontrados (esta é uma aproximação)
            if (!empty($allProgressRecids)) {
                // Como não sabemos a origem, distribuímos igualmente ou usamos alguma lógica
                $progressRecidProc = $allProgressRecids;
                $progressRecidHistProc = $allProgressRecids;
                $progressRecidInsu = $allProgressRecids;
                $progressRecidHistInsu = $allProgressRecids;
            }
        }

        $fatorMultiplicador = calcularFatorMultiplicador($porcentagem);
        $fatorMultiplicadorBind = number_format($fatorMultiplicador, 4, '.', '');

        try {
            $rowsMoviproc = 0;
            $rowsHistorProc = 0;
            $rowsMovInsu = 0;
            $rowsHistorInsu = 0;

            // 1. UPDATE gp.MOVIPROC usando progress_recid (Procedimentos)
            if (!empty($progressRecidProc)) {
                $progressRecids = implode(',', array_map('intval', $progressRecidProc));
                
                error_log("Executando UPDATE MOVIPROC com RECIDs: $progressRecids");
                
                $sqlMoviproc = "
                    UPDATE gp.MOVIPROC P
                    SET VL_PRINCIPAL = 0,
                        P.PC_APLICADO = 100, 
                        P.DEC_11 = :porcentagem, 
                        P.DEC_12 = :porcentagem, 
                        P.DEC_13 = :porcentagem,
                        P.VL_AUXILIAR = (SELECT PP.VL_PROCEDIMENTO * :fatormultiplicador
                            FROM gp.PACPROCE PP
                            WHERE PP.CD_PACOTE = P.CD_PACOTE
                            AND PP.DT_LIMITE > TRUNC(SYSDATE)),
                        P.VL_COBRADO = (SELECT PP.VL_PROCEDIMENTO * :fatormultiplicador
                            FROM gp.PACPROCE PP
                            WHERE PP.CD_PACOTE = P.CD_PACOTE
                                AND PP.DT_LIMITE > TRUNC(SYSDATE))
                    WHERE P.PROGRESS_RECID IN ($progressRecids)
                ";

                $bindingsMoviproc = [
                    ':porcentagem' => $porcentagem,
                    ':fatormultiplicador' => $fatorMultiplicadorBind
                ];

                $rowsMoviproc = executeUpdate($conn, $sqlMoviproc, $bindingsMoviproc, "MOVIPROC");
                $results['summary']['atualizados_moviproc'] += $rowsMoviproc;
                
                error_log("UPDATE MOVIPROC: $rowsMoviproc linhas afetadas");
            }

            // 2. UPDATE gp.HISTOR_MOVIMEN_PROCED usando progress_recid_hist (Histórico de Procedimentos)
            if (!empty($progressRecidHistProc)) {
                $progressRecidsHist = implode(',', array_map('intval', $progressRecidHistProc));
                
                error_log("Executando UPDATE HISTOR_MOVIMEN_PROCED com RECIDs: $progressRecidsHist");
                
                $sqlHistorProc = "
                    UPDATE gp.HISTOR_MOVIMEN_PROCED P
                    SET VL_PRINCIPAL = 0,
                        P.PC_APLICADO = 100, 
                        P.DEC_11 = :porcentagem, 
                        P.DEC_12 = :porcentagem, 
                        P.DEC_13 = :porcentagem,
                        P.VL_AUXILIAR = (SELECT PP.VL_PROCEDIMENTO * :fatormultiplicador
                            FROM gp.PACPROCE PP
                            WHERE PP.CD_PACOTE = P.CD_PACOTE
                            AND PP.DT_LIMITE > TRUNC(SYSDATE)),
                        P.VL_COBRADO = (SELECT PP.VL_PROCEDIMENTO * :fatormultiplicador
                            FROM gp.PACPROCE PP
                            WHERE PP.CD_PACOTE = P.CD_PACOTE
                                AND PP.DT_LIMITE > TRUNC(SYSDATE)),
                        P.DT_ATUALIZACAO = TO_DATE(:data_atual, 'DD/MM/YYYY'), 
                        P.CD_USERID = :user_id
                    WHERE P.PROGRESS_RECID IN ($progressRecidsHist)
                ";

                $bindingsHistorProc = [
                    ':porcentagem' => $porcentagem,
                    ':fatormultiplicador' => $fatorMultiplicadorBind,
                    ':data_atual' => $dataAtual,
                    ':user_id' => $userId
                ];

                $rowsHistorProc = executeUpdate($conn, $sqlHistorProc, $bindingsHistorProc, "HISTOR_MOVIMEN_PROCED");
                $results['summary']['atualizados_histor_proc'] += $rowsHistorProc;
                
                error_log("UPDATE HISTOR_MOVIMEN_PROCED: $rowsHistorProc linhas afetadas");
            }

            // 3. UPDATE gp.MOV_INSU usando progress_recid (Insumos)
            if (!empty($progressRecidInsu)) {
                $progressRecidsInsu = implode(',', array_map('intval', $progressRecidInsu));
                
                error_log("Executando UPDATE MOV_INSU com RECIDs: $progressRecidsInsu");
                
                $sqlMovInsu = "
                    UPDATE gp.MOV_INSU P
                    SET P.PC_APLICADO = 100,
                        P.VL_INSUMO = (SELECT PP.VL_INSUMO * :fatormultiplicador
                            FROM gp.PACINSUM PP
                            WHERE PP.CD_PACOTE = P.CD_PACOTE
                                AND PP.CD_INSUMO = P.CD_INSUMO
                                AND PP.DT_LIMITE > TRUNC(SYSDATE)),
                        P.VL_COBRADO = (SELECT PP.VL_INSUMO * :fatormultiplicador
                            FROM gp.PACINSUM PP
                            WHERE PP.CD_PACOTE = P.CD_PACOTE
                                AND PP.CD_INSUMO = P.CD_INSUMO
                                AND PP.DT_LIMITE > TRUNC(SYSDATE))
                    WHERE P.PROGRESS_RECID IN ($progressRecidsInsu)
                ";

                $bindingsMovInsu = [
                    ':fatormultiplicador' => $fatorMultiplicadorBind
                ];

                $rowsMovInsu = executeUpdate($conn, $sqlMovInsu, $bindingsMovInsu, "MOV_INSU");
                $results['summary']['atualizados_mov_insu'] += $rowsMovInsu;
                
                error_log("UPDATE MOV_INSU: $rowsMovInsu linhas afetadas");
            }

            // 4. UPDATE gp.HISTOR_MOVIMEN_INSUMO usando progress_recid_hist (Histórico de Insumos)
            if (!empty($progressRecidHistInsu)) {
                $progressRecidsHistInsu = implode(',', array_map('intval', $progressRecidHistInsu));
                
                error_log("Executando UPDATE HISTOR_MOVIMEN_INSUMO com RECIDs: $progressRecidsHistInsu");
                
                $sqlHistorInsu = "
                    UPDATE gp.HISTOR_MOVIMEN_INSUMO I
                    SET I.PC_APLICADO = 100,
                        I.VL_INSUMO = (SELECT PP.VL_INSUMO * :fatormultiplicador
                            FROM gp.PACINSUM PP
                            WHERE PP.CD_PACOTE = I.CD_PACOTE
                                AND PP.CD_INSUMO = I.CD_INSUMO
                                AND PP.DT_LIMITE > TRUNC(SYSDATE)),
                        I.VL_COBRADO = (SELECT PP.VL_INSUMO * :fatormultiplicador 
                            FROM gp.PACINSUM PP
                            WHERE PP.CD_PACOTE = I.CD_PACOTE
                                AND PP.CD_INSUMO = I.CD_INSUMO
                                AND PP.DT_LIMITE > TRUNC(SYSDATE)),
                        I.DT_ATUALIZACAO = TO_DATE(:data_atual, 'DD/MM/YYYY'), 
                        I.CD_USERID = :user_id
                    WHERE I.PROGRESS_RECID IN ($progressRecidsHistInsu)
                ";

                $bindingsHistorInsu = [
                    ':fatormultiplicador' => $fatorMultiplicadorBind,
                    ':data_atual' => $dataAtual,
                    ':user_id' => $userId
                ];

                $rowsHistorInsu = executeUpdate($conn, $sqlHistorInsu, $bindingsHistorInsu, "HISTOR_MOVIMEN_INSUMO");
                $results['summary']['atualizados_histor_insu'] += $rowsHistorInsu;
                
                error_log("UPDATE HISTOR_MOVIMEN_INSUMO: $rowsHistorInsu linhas afetadas");
            }

            $results['success'][] = "Ocorrência $pacoteOcorrencia (Pacote: $cdPacote) atualizada com $porcentagem% - " .
                                   "PROC: " . count($progressRecidProc) . " recids, " .
                                   "INSU: " . count($progressRecidInsu) . " recids, " .
                                   "MOVIPROC: $rowsMoviproc, HISTOR_PROC: $rowsHistorProc, " .
                                   "MOV_INSU: $rowsMovInsu, HISTOR_INSU: $rowsHistorInsu";

        } catch (Exception $e) {
            $results['errors'][] = "Erro na ocorrência $pacoteOcorrencia (Pacote: $cdPacote): " . $e->getMessage();
            error_log("ERRO: " . $e->getMessage());
        }
        
        error_log("=== FIM OCORRÊNCIA $idx ===");
    }

    // Verificar se houve algum sucesso
    if (empty($results['success']) && !empty($results['errors'])) {
        throw new Exception("Nenhum update foi bem-sucedido. Erros: " . implode('; ', $results['errors']));
    }

    // Preparar resposta
    $response = [
        'error' => false,
        'message' => sprintf(
            "Atualização concluída! %d ocorrências processadas. " .
            "MOVIPROC: %d, HISTOR_PROC: %d, MOV_INSU: %d, HISTOR_INSU: %d",
            $results['summary']['total_ocorrencias'],
            $results['summary']['atualizados_moviproc'],
            $results['summary']['atualizados_histor_proc'],
            $results['summary']['atualizados_mov_insu'],
            $results['summary']['atualizados_histor_insu']
        ),
        'details' => $results
    ];

} catch (Exception $e) {
    $response = [
        'error' => true,
        'message' => $e->getMessage()
    ];
    error_log("ERRO GERAL: " . $e->getMessage());
}

// Salvar mensagem na sessão para exibir na página
$_SESSION['retornoUpdatePorcentagem'] = [
    'type' => $response['error'] ? 'error' : 'success',
    'message' => $response['message']
];

echo json_encode($response);
?>