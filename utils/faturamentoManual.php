<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

// Desabilitar buffering de saída
while (ob_get_level() > 0) ob_end_clean();
ini_set('output_buffering', 'off');
ini_set('zlib.output_compression', false);
ob_implicit_flush(true);

// Função para enviar mensagens SSE
function sendSseMessage($data) {
    if (is_array($data)) {
        $data = json_encode($data);
    }
    echo "data: $data\n\n";
    ob_flush();
    flush();
}

// Mensagem inicial
sendSseMessage([
    'percent' => 5,
    'progresso' => '',
    'linhas' => '',
    'message' => 'Iniciando processo de atualização...'
]);

// Inclui configurações
ob_start();
require_once "../config/AW00DB.php";
require_once "../config/oracle.class.php";
require_once "../config/AW00MD.php";
$buffer = ob_get_clean();

// Obter parâmetros via POST
$anoRef         = $_GET['anoFatManual'] ?? null;
$numRef         = $_GET['periodoFatManual'] ?? null;
$cdPrest        = $_GET['cd_prestadorManual'] ?? null;
$nrDocOriginal  = $_GET['nr_docOriginalManual'] ?? null;
$cdprocedimento = $_GET['cdProcedManual'] ?? null;

if (!$anoRef || !$numRef || !$cdPrest || !$nrDocOriginal || !$cdprocedimento) {
    sendSseMessage(['error' => 'Parâmetros inválidos']);
    exit;
}

// Explodir cdProcedManual em array
$procedimentos = array_map('trim', explode(',', $cdprocedimento));

// Criar placeholders dinâmicos para IN (:proc0, :proc1, ...)
$placeholders = [];
foreach ($procedimentos as $i => $proc) {
    $placeholders[] = ":proc$i";
}
$inClause = implode(',', $placeholders);

try {
    // Definir os updates a serem executados
    $updates = [
        [
            // ERRO 3016
            'name' => 'MOVIPROC',
            'progresso' => 'moviproc',
            'sql' => "UPDATE GP.MOVIPROC 
                        SET LOG_11 = 0
                        WHERE (CD_PRESTADOR, NR_DOC_ORIGINAL, NR_DOC_SISTEMA, CD_TRANSACAO, NR_SERIE_DOC_ORIGINAL, LOG_11, 
                                LG_URGENCIA, LG_ADICIONAL_URGENCIA, QT_REPASSE, QT_REPASSE_COB, NR_PERREF, DT_ANOREF, PROGRESS_RECID)
                        IN (
                            SELECT M.CD_PRESTADOR, M.NR_DOC_ORIGINAL, M.NR_DOC_SISTEMA, M.CD_TRANSACAO, M.NR_SERIE_DOC_ORIGINAL, 
                                    M.LOG_11, M.LG_URGENCIA, M.LG_ADICIONAL_URGENCIA, M.QT_REPASSE, M.QT_REPASSE_COB, 
                                    M.NR_PERREF, M.DT_ANOREF, M.PROGRESS_RECID
                                FROM GP.MOVIPROC M
                                INNER JOIN GP.AMBPROCE A 
                                ON A.CD_ESP_AMB = M.CD_ESP_AMB
                                AND A.CD_GRUPO_PROC_AMB = M.CD_GRUPO_PROC_AMB
                                AND A.CD_PROCEDIMENTO = M.CD_PROCEDIMENTO
                                AND A.DV_PROCEDIMENTO = M.DV_PROCEDIMENTO
                                WHERE A.CDPROCEDIMENTOCOMPLETO IN ($inClause)
                                AND M.CD_PRESTADOR = :cdPrest
                                AND M.NR_DOC_ORIGINAL = :nrDocOriginal
                                AND M.NR_PERREF = :numRef
                                AND M.DT_ANOREF = :anoRef
                                AND M.LOG_11 = 1
                        )"
        ],
        [
            // HISTORICO
            'name' => 'HISTORICO MOVIPROC',
            'progresso' => 'histmoviproc',
            'sql' => "UPDATE GP.HISTOR_MOVIMEN_PROCED H
                        SET LOG_11 = 0
                        WHERE (H.CD_PRESTADOR,
                                H.NR_DOC_ORIGINAL,
                                H.NR_PERREF,
                                H.DT_ANOREF,
                                H.LOG_11,
                                H.CD_ESP_AMB,
                                H.CD_GRUPO_PROC_AMB,
                                H.CD_PROCEDIMENTO,
                                H.DV_PROCEDIMENTO)
                            IN (
                                SELECT H2.CD_PRESTADOR,
                                    H2.NR_DOC_ORIGINAL,
                                    H2.NR_PERREF,
                                    H2.DT_ANOREF,
                                    H2.LOG_11,
                                    H2.CD_ESP_AMB,
                                    H2.CD_GRUPO_PROC_AMB,
                                    H2.CD_PROCEDIMENTO,
                                    H2.DV_PROCEDIMENTO
                                FROM GP.HISTOR_MOVIMEN_PROCED H2
                                WHERE H2.CD_PRESTADOR   = :cdPrest
                                AND H2.NR_DOC_ORIGINAL = :nrDocOriginal
                                AND H2.NR_PERREF       = :numRef
                                AND H2.DT_ANOREF       = :anoRef
                                AND H2.LOG_11          = 1
                                AND EXISTS (
                                        SELECT 1
                                        FROM GP.AMBPROCE A
                                        WHERE A.CD_ESP_AMB          = H2.CD_ESP_AMB
                                            AND A.CD_GRUPO_PROC_AMB   = H2.CD_GRUPO_PROC_AMB
                                            AND A.CD_PROCEDIMENTO     = H2.CD_PROCEDIMENTO
                                            AND A.DV_PROCEDIMENTO     = H2.DV_PROCEDIMENTO
                                            AND A.CDPROCEDIMENTOCOMPLETO IN ($inClause)
                                    )
                        )"
        ],
    ];

    $totalUpdates = count($updates);
    $completed = 0;

    foreach ($updates as $update) {
        try {
            $stmt = oci_parse($conn, $update['sql']);

            // Bind fixos
            oci_bind_by_name($stmt, ":cdPrest", $cdPrest);
            oci_bind_by_name($stmt, ":nrDocOriginal", $nrDocOriginal);
            oci_bind_by_name($stmt, ":numRef", $numRef);
            oci_bind_by_name($stmt, ":anoRef", $anoRef);

            // Bind dos procedimentos
            foreach ($procedimentos as $i => $proc) {
                oci_bind_by_name($stmt, ":proc$i", $procedimentos[$i]);
            }

            // Executa a query
            if (oci_execute($stmt, OCI_COMMIT_ON_SUCCESS)) {
                $linhas_afetadas = oci_num_rows($stmt);
                oci_commit($conn);
            } else {
                $error = oci_error($stmt);
                sendSseMessage(['percent' => 0, 'message' => "Erro: {$update['name']} - {$error['message']}"]);
                continue;
            }

            // Calcular progresso
            $completed++;
            $percent = intval(($completed / $totalUpdates) * 100);

            // Enviar progresso atualizado
            sendSseMessage([
                'percent' => $percent,
                'progresso' => $update['progresso'],
                'linhas' => $linhas_afetadas,
                'message' => "Executando: {$update['name']} ($completed/$totalUpdates)"
            ]);

            usleep(500000); // 0.5s

        } catch (Exception $e) {
            sendSseMessage([
                'error' => "Erro ao executar '{$update['name']}': " . $e->getMessage()
            ]);
            exit();
        }
    }

    // Finalizar
    sendSseMessage(['complete' => true, 'percent' => 100, 'message' => 'Processo concluído']);

} catch (Exception $e) {
    sendSseMessage(['error' => $e->getMessage()]);
}
?>
