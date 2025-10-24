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

    //! EXECUTAR UPDATES
    if (!oci_execute($stmt, OCI_COMMIT_ON_SUCCESS)) {
        $e = oci_error($stmt);
        throw new Exception("Erro ao executar update de $entity: " . htmlentities($e['message'], ENT_QUOTES));
    }
    
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
    
    // 1. Buscar como array direto (progress_recid_proc[])
    if (isset($pacote[$campo]) && is_array($pacote[$campo])) {
        $resultados = $pacote[$campo];
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
    }
    
    // Filtrar valores vazios, nulos ou não numéricos
    $resultados = array_values(array_filter($resultados, function($item) {
        return $item !== null && $item !== '' && is_numeric($item);
    }));
    
    return $resultados;
}

// Função para recarregar os dados atualizados do banco - CORRIGIDA
function recarregarDadosAtualizados($codPrest, $numDoc, $periodoRef, $dtAnoRef) {
    // Usar a mesma conexão global para evitar problemas
    global $conn;
    
    if (!$conn) {
        throw new Exception("Conexão com o banco não disponível");
    }
    
    // Usar a mesma query do verificar_valores_documento.php
    $sql = "
    WITH params AS (
      SELECT 
        :cd_prestador AS cd_prestador,
        :nr_perref AS nr_perref,
        :dt_anoref AS dt_anoref,
        :nr_doc AS nr_doc
      FROM dual
    ),
    pacotes AS (
      SELECT DISTINCT 
             NVL(P.CD_PACOTE, I.CD_PACOTE) AS CD_PACOTE
      FROM GP.DOCRECON D
      LEFT JOIN GP.MOVIPROC P
        ON P.NR_DOC_ORIGINAL       = D.NR_DOC_ORIGINAL
       AND P.NR_DOC_SISTEMA        = D.NR_DOC_SISTEMA
       AND P.CD_TRANSACAO          = D.CD_TRANSACAO
       AND P.NR_SERIE_DOC_ORIGINAL = D.NR_SERIE_DOC_ORIGINAL
      LEFT JOIN GP.MOV_INSU I
        ON I.NR_DOC_ORIGINAL       = D.NR_DOC_ORIGINAL
       AND I.NR_DOC_SISTEMA        = D.NR_DOC_SISTEMA
       AND I.CD_TRANSACAO          = D.CD_TRANSACAO
       AND I.NR_SERIE_DOC_ORIGINAL = D.NR_SERIE_DOC_ORIGINAL
      JOIN GP.params prm ON 1=1
      WHERE D.CD_PRESTADOR_PRINCIPAL = prm.cd_prestador
        AND D.NR_PERREF = prm.nr_perref
        AND D.DT_ANOREF = prm.dt_anoref
        AND D.NR_DOC_ORIGINAL = prm.nr_doc
        AND NVL(P.CD_PACOTE, I.CD_PACOTE) <> 0
    ),
    lines AS (
      SELECT 
        D.NR_DOC_ORIGINAL,
        P.CD_PACOTE,
        P.NR_PROCESSO,
        P.NR_SEQ_DIGITACAO,
        P.DT_DIGITACAO,
        P.HR_DIGITACAO,
        P.PROGRESS_RECID,
        HP.PROGRESS_RECID AS PROGRESS_RECID_HIST,
        P.VL_COBRADO,
        P.VL_GLOSADO,
        'P' AS origem,
        TO_DATE(TO_CHAR(P.DT_DIGITACAO, 'YYYY-MM-DD') || ' ' || 
                LPAD(FLOOR(P.HR_DIGITACAO/100),4,'0'), 'YYYY-MM-DD HH24MI') AS DT_HR_DIG,
        FLOOR(P.HR_DIGITACAO/100)*3600 + MOD(P.HR_DIGITACAO,100)*60 AS HR_SEGUNDOS
      FROM GP.DOCRECON D
      LEFT JOIN GP.MOVIPROC P
        ON P.NR_DOC_ORIGINAL       = D.NR_DOC_ORIGINAL
       AND P.NR_DOC_SISTEMA        = D.NR_DOC_SISTEMA
       AND P.CD_TRANSACAO          = D.CD_TRANSACAO
       AND P.NR_SERIE_DOC_ORIGINAL = D.NR_SERIE_DOC_ORIGINAL
      LEFT JOIN GP.HISTOR_MOVIMEN_PROCED HP
        ON HP.NR_DOC_ORIGINAL       = P.NR_DOC_ORIGINAL
       AND HP.NR_DOC_SISTEMA        = P.NR_DOC_SISTEMA
       AND HP.CD_TRANSACAO          = P.CD_TRANSACAO
       AND HP.NR_SERIE_DOC_ORIGINAL = P.NR_SERIE_DOC_ORIGINAL
       AND HP.NR_PROCESSO           = P.NR_PROCESSO
       AND HP.NR_SEQ_DIGITACAO      = P.NR_SEQ_DIGITACAO
      JOIN GP.params prm ON 1 = 1
      JOIN GP.pacotes pc ON pc.CD_PACOTE = P.CD_PACOTE
      WHERE D.CD_PRESTADOR_PRINCIPAL = prm.cd_prestador
        AND D.NR_PERREF = prm.nr_perref
        AND D.DT_ANOREF = prm.dt_anoref
        AND D.NR_DOC_ORIGINAL = prm.nr_doc

      UNION ALL

      SELECT 
        D.NR_DOC_ORIGINAL,
        I.CD_PACOTE,
        I.NR_PROCESSO,
        I.NR_SEQ_DIGITACAO,
        I.DT_DIGITACAO,
        I.HR_DIGITACAO,
        I.PROGRESS_RECID,
        HI.PROGRESS_RECID AS PROGRESS_RECID_HIST,
        I.VL_COBRADO,
        I.VL_GLOSADO,
        'I' AS origem,
        TO_DATE(TO_CHAR(I.DT_DIGITACAO, 'YYYY-MM-DD') || ' ' || 
                LPAD(FLOOR(I.HR_DIGITACAO/100),4,'0'), 'YYYY-MM-DD HH24MI') AS DT_HR_DIG,
        FLOOR(I.HR_DIGITACAO/100)*3600 + MOD(I.HR_DIGITACAO,100)*60 AS HR_SEGUNDOS
      FROM GP.DOCRECON D
      LEFT JOIN GP.MOV_INSU I
        ON I.NR_DOC_ORIGINAL       = D.NR_DOC_ORIGINAL
       AND I.NR_DOC_SISTEMA        = D.NR_DOC_SISTEMA
       AND I.CD_TRANSACAO          = D.CD_TRANSACAO
       AND I.NR_SERIE_DOC_ORIGINAL = D.NR_SERIE_DOC_ORIGINAL
      LEFT JOIN GP.HISTOR_MOVIMEN_INSUMO HI
        ON HI.NR_DOC_ORIGINAL       = I.NR_DOC_ORIGINAL
       AND HI.NR_DOC_SISTEMA        = I.NR_DOC_SISTEMA
       AND HI.CD_TRANSACAO          = I.CD_TRANSACAO
       AND HI.NR_SERIE_DOC_ORIGINAL = I.NR_SERIE_DOC_ORIGINAL
       AND HI.NR_PROCESSO           = I.NR_PROCESSO
       AND HI.NR_SEQ_DIGITACAO      = I.NR_SEQ_DIGITACAO
      JOIN GP.params prm ON 1 = 1
      JOIN GP.pacotes pc ON pc.CD_PACOTE = I.CD_PACOTE
      WHERE D.CD_PRESTADOR_PRINCIPAL = prm.cd_prestador
        AND D.NR_PERREF = prm.nr_perref
        AND D.DT_ANOREF = prm.dt_anoref
        AND D.NR_DOC_ORIGINAL = prm.nr_doc
    ),
    numbered AS (
      SELECT
        l.*,
        DENSE_RANK() OVER (
          PARTITION BY l.NR_DOC_ORIGINAL
          ORDER BY l.CD_PACOTE, l.DT_HR_DIG
        ) AS PACOTE_OCORRENCIA_BASE
      FROM lines l
    ),
    ajustado AS (
      SELECT 
        n.*,
        LAG(n.HR_SEGUNDOS) OVER (PARTITION BY n.CD_PACOTE ORDER BY n.DT_HR_DIG) AS HR_SEG_ANTERIOR,
        LAG(n.PACOTE_OCORRENCIA_BASE) OVER (PARTITION BY n.CD_PACOTE ORDER BY n.DT_HR_DIG) AS PACOTE_OCORRENCIA_ANTERIOR
      FROM numbered n
    ),
    final AS (
      SELECT
        a.*,
        CASE 
          WHEN a.HR_SEG_ANTERIOR IS NULL THEN a.PACOTE_OCORRENCIA_BASE
          WHEN ABS(a.HR_SEGUNDOS - a.HR_SEG_ANTERIOR) <= 50 THEN a.PACOTE_OCORRENCIA_ANTERIOR
          ELSE a.PACOTE_OCORRENCIA_BASE
        END AS PACOTE_OCORRENCIA
      FROM ajustado a
    )
    SELECT
      NR_DOC_ORIGINAL,
      CD_PACOTE,
      TO_CHAR(DT_DIGITACAO, 'DD/MM/YYYY') AS DT_DIGITACAO,
      HR_DIGITACAO,
      NR_PROCESSO,
      NR_SEQ_DIGITACAO,
      origem,
      PROGRESS_RECID,
      PROGRESS_RECID_HIST,
      PACOTE_OCORRENCIA,
      VL_COBRADO,
      VL_GLOSADO
    FROM final
    ORDER BY PACOTE_OCORRENCIA, CD_PACOTE, DT_HR_DIG, origem
    ";
    
    $bindings = [
        ':cd_prestador' => $codPrest,
        ':nr_perref'    => $periodoRef,
        ':dt_anoref'    => $dtAnoRef,
        ':nr_doc'       => $numDoc
    ];
    
    $stmt = oci_parse($conn, $sql);
    if (!$stmt) {
        throw new Exception("Erro ao preparar consulta para recarregar dados");
    }
    
    foreach ($bindings as $key => $val) {
        oci_bind_by_name($stmt, $key, $bindings[$key]);
    }

    if (!oci_execute($stmt)) {
        $e = oci_error($stmt);
        oci_free_statement($stmt);
        throw new Exception("Erro ao executar consulta para recarregar dados: " . $e['message']);
    }

    $data = [];
    while ($row = oci_fetch_assoc($stmt)) {
        $data[] = $row;
    }

    oci_free_statement($stmt);
    
    return $data;
}

try {
    // Verificar se há dados POST
    if (empty($_POST['pacotes'])) {
        throw new Exception("Nenhum dado recebido para atualização.");
    }

    // Obter parâmetros da sessão para recarregar dados depois
    $parametros = $_SESSION['parametros_porcentagem'] ?? [];
    $codPrest = $parametros['codPrest'] ?? '';
    $numDoc = $parametros['numDoc'] ?? '';
    $periodoRef = $parametros['periodoRef'] ?? '';
    $dtAnoRef = $parametros['dtAnoRef'] ?? '';

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
        
        // Validar dados obrigatórios
        $porcentagem = floatval($pacote['porcentagem'] ?? 0);
        $cdPacote = $pacote['cd_pacote'] ?? '';
        $pacoteOcorrencia = $pacote['pacote_ocorrencia'] ?? '';
        
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
            
            // Distribuir os recids encontrados (esta é uma aproximação)
            if (!empty($allProgressRecids)) {
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
            }

            // 2. UPDATE gp.HISTOR_MOVIMEN_PROCED usando progress_recid_hist (Histórico de Procedimentos)
            if (!empty($progressRecidHistProc)) {
                $progressRecidsHist = implode(',', array_map('intval', $progressRecidHistProc));
                
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
            }

            // 3. UPDATE gp.MOV_INSU usando progress_recid (Insumos)
            if (!empty($progressRecidInsu)) {
                $progressRecidsInsu = implode(',', array_map('intval', $progressRecidInsu));
                
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
            }

            // 4. UPDATE gp.HISTOR_MOVIMEN_INSUMO usando progress_recid_hist (Histórico de Insumos)
            if (!empty($progressRecidHistInsu)) {
                $progressRecidsHistInsu = implode(',', array_map('intval', $progressRecidHistInsu));
                
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
            }

            $results['success'][] = "Ocorrência $pacoteOcorrencia (Pacote: $cdPacote) atualizada com $porcentagem%";

        } catch (Exception $e) {
            $results['errors'][] = "Erro na ocorrência $pacoteOcorrencia (Pacote: $cdPacote): " . $e->getMessage();
        }
    }

    // Verificar se houve algum sucesso
    if (empty($results['success']) && !empty($results['errors'])) {
        throw new Exception("Nenhum update foi bem-sucedido. Erros: " . implode('; ', $results['errors']));
    }

    // 🔄 RECARREGAR DADOS ATUALIZADOS DO BANCO - COM TRY/CATCH SEGURO
    $dadosRecarregados = false;
    if (!empty($codPrest) && !empty($numDoc) && !empty($periodoRef) && !empty($dtAnoRef)) {
        try {
            $dadosAtualizados = recarregarDadosAtualizados($codPrest, $numDoc, $periodoRef, $dtAnoRef);
            
            // Atualizar a sessão com os novos dados
            $_SESSION['dadosValoresDoc'] = [
                'error' => false,
                'message' => '',
                'resultados' => $dadosAtualizados
            ];
            
            $dadosRecarregados = true;
            
        } catch (Exception $e) {
            // Não interrompe o processo principal se falhar ao recarregar
            error_log("AVISO: Não foi possível recarregar os dados atualizados: " . $e->getMessage());
        }
    }

    // Preparar resposta
    $response = [
        'error' => false,
        'message' => sprintf(
            "Atualização concluída! %d ocorrências processadas. " .
            "MOVIPROC: %d, HISTOR_PROC: %d, MOV_INSU: %d, HISTOR_INSU: %d" .
            ($dadosRecarregados ? " (Dados recarregados)" : " (Dados não recarregados)"),
            $results['summary']['total_ocorrencias'],
            $results['summary']['atualizados_moviproc'],
            $results['summary']['atualizados_histor_proc'],
            $results['summary']['atualizados_mov_insu'],
            $results['summary']['atualizados_histor_insu']
        ),
        'details' => $results,
        'dados_recarregados' => $dadosRecarregados
    ];

} catch (Exception $e) {
    $response = [
        'error' => true,
        'message' => $e->getMessage()
    ];
}

// Salvar mensagem na sessão para exibir na página
$_SESSION['retornoUpdatePorcentagem'] = [
    'type' => $response['error'] ? 'error' : 'success',
    'message' => $response['message']
];

// Garantir que apenas JSON seja retornado
echo json_encode($response);
exit;