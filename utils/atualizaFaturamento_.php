<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');


// Desabilitar buffering de saída
while (ob_get_level() > 0) ob_end_clean();
ini_set('output_buffering', 'off');
ini_set('zlib.output_compression', false);
ob_implicit_flush(true);

//! inicia processo de mensagem (precisa ter)
sendSseMessage(['percent' => 5,'progresso' => '', 'linhas' => '' , 'message' => 'Iniciando processo de atualizacao...']);

ob_start();
// Inclui as configurações necessárias
require_once "../config/AW00DB.php";
require_once "../config/oracle.class.php";
require_once "../config/AW00MD.php";
$buffer = ob_get_clean();

// Função para enviar mensagens SSE formatadas
function sendSseMessage($data) {
    if (is_array($data)) {
        $data = json_encode($data);
    }
    echo "data: $data\n\n";
    ob_flush();
    flush();
}

// sendSseMessage(['message' => 'Entrando no loop principal...']);

// Obter parâmetros
$anoRef = $_GET['anoRefFat'] ?? null;
$numRef = $_GET['numRefFat'] ?? null;

if (!$anoRef || !$numRef) {
    sendSseMessage(['error' => 'Parâmetros inválidos'], true);
    exit;
}


try {
    // Definir os updates a serem executados
    $updates = [
        [
            'name' => 'PACOTE COBRANÇA SEMPRE 1 (MOVIPROC)',
            'progresso' => '', //* Id do <p>
            'sql' => "UPDATE GP.MOVIPROC SET QT_REPASSE_COB = 1, U_CHAR_1 = '114000267 Pacote ' || TO_CHAR(SYSDATE, 'DD/MM/YYYY') || TO_CHAR(SYSDATE, ' HH24:MI')
                     WHERE (cd_unidade_carteira, cd_carteira_usuario, cd_prestador, nr_doc_original, nr_serie_doc_original, cd_transacao, nr_perref, dt_anoref, CD_PACOTE, QT_REPASSE_COB, INLIBERADOFATURAMENTO, U_CHAR_1, PROGRESS_RECID ) IN 
                     (SELECT D.cd_unidade_carteira, D.cd_carteira_usuario, d.cd_prestador, D.nr_doc_original, D.nr_serie_doc_original, D.cd_transacao, D.nr_perref, D.dt_anoref, D.CD_PACOTE, D.QT_REPASSE_COB, D.INLIBERADOFATURAMENTO, D.U_CHAR_1, D.PROGRESS_RECID
                             FROM GP.MOVIPROC d 
                             WHERE D.NR_PERREF = :numRef AND D.DT_ANOREF = :anoRef
                               AND D.CD_UNIDADE_CARTEIRA <> 540
                               AND (D.CD_PACOTE IS NOT NULL AND D.CD_PACOTE <> 0)
                               AND D.QT_REPASSE_COB <> 1 
                               AND D.INLIBERADOFATURAMENTO = 0)"
        ],
        [
            'name' => 'HISTORICO MOVIPROC',
            'progresso' => 'pacoteMult', //* Id do <p>
            'sql' => "UPDATE GP.HISTOR_MOVIMEN_PROCED SET QT_REPASSE_COB = 1, U_CHAR_1 = '114000267 Pacote ' || TO_CHAR(SYSDATE, 'DD/MM/YYYY') || TO_CHAR(SYSDATE, ' HH24:MI')
                WHERE (cd_unidade_carteira, cd_carteira_usuario, nr_doc_original, nr_serie_doc_original, cd_transacao, nr_perref, dt_anoref, CD_PACOTE, QT_REPASSE_COB, INLIBERADOFATURAMENTO, U_CHAR_1, PROGRESS_RECID) IN 
                (SELECT D.cd_unidade_carteira, D.cd_carteira_usuario, D.nr_doc_original, D.nr_serie_doc_original, D.cd_transacao, D.nr_perref, D.dt_anoref, D.CD_PACOTE, D.QT_REPASSE_COB, D.INLIBERADOFATURAMENTO, D.U_CHAR_1, D.PROGRESS_RECID
                    FROM GP.HISTOR_MOVIMEN_PROCED d 
                    WHERE D.NR_PERREF IN ( :numRef ) AND D.DT_ANOREF = :anoRef
                        AND D.CD_UNIDADE_CARTEIRA <> 540
                        AND (D.CD_PACOTE IS NOT NULL AND D.CD_PACOTE <> 0)
                        AND D.QT_REPASSE_COB <> 1 
                        AND D.INLIBERADOFATURAMENTO = 0)"
        ],
        [
            'name' => 'ERRO 3016 ANESTESISTA 10104020 10102019 10103015',
            'progresso' => 'erro3013', //* Id do <p>
             'sql' => "UPDATE GP.MOVIPROC SET LOG_11 = 0 WHERE (CD_PRESTADOR, NR_DOC_ORIGINAL, NR_DOC_SISTEMA, CD_TRANSACAO, NR_SERIE_DOC_ORIGINAL, LOG_11, LG_URGENCIA, LG_ADICIONAL_URGENCIA, QT_REPASSE, QT_REPASSE_COB, NR_PERREF, DT_ANOREF, PROGRESS_RECID) IN (
                    SELECT M.CD_PRESTADOR, M.NR_DOC_ORIGINAL, M.NR_DOC_SISTEMA, M.CD_TRANSACAO, M.NR_SERIE_DOC_ORIGINAL, M.LOG_11, M.LG_URGENCIA, M.LG_ADICIONAL_URGENCIA, M.QT_REPASSE, M.QT_REPASSE_COB, M.NR_PERREF, M.DT_ANOREF, M.PROGRESS_RECID
                        FROM GP.MOVIPROC M
                        INNER JOIN GP.AMBPROCE A ON A.CD_ESP_AMB          = M.CD_ESP_AMB
                                                AND A.CD_GRUPO_PROC_AMB   = M.CD_GRUPO_PROC_AMB
                                                AND A.CD_PROCEDIMENTO     = M.CD_PROCEDIMENTO
                                                AND A.DV_PROCEDIMENTO     = M.DV_PROCEDIMENTO
                        WHERE A.CDPROCEDIMENTOCOMPLETO IN (10104020, 10102019, 10103015, 41001095, 40325024, 40403505)
                            AND M.NR_PERREF              IN ( :numRef )
                            AND M.DT_ANOREF              = :anoRef
                            AND M.LOG_11                 = 1
                    )"
            // 'sql' => "UPDATE GP.MOVIPROC SET LOG_11 = 0 WHERE (CD_PRESTADOR, NR_DOC_ORIGINAL, NR_DOC_SISTEMA, CD_TRANSACAO, NR_SERIE_DOC_ORIGINAL, LOG_11, LG_URGENCIA, LG_ADICIONAL_URGENCIA, QT_REPASSE, QT_REPASSE_COB, NR_PERREF, DT_ANOREF, PROGRESS_RECID) IN (
            //         SELECT M.CD_PRESTADOR, M.NR_DOC_ORIGINAL, M.NR_DOC_SISTEMA, M.CD_TRANSACAO, M.NR_SERIE_DOC_ORIGINAL, M.LOG_11, M.LG_URGENCIA, M.LG_ADICIONAL_URGENCIA, M.QT_REPASSE, M.QT_REPASSE_COB, M.NR_PERREF, M.DT_ANOREF, M.PROGRESS_RECID
            //             FROM GP.MOVIPROC M
            //             WHERE M.CD_ESP_AMB             = 10
            //                 AND M.CD_GRUPO_PROC_AMB      = 10
            //                 AND ((M.CD_PROCEDIMENTO      = 402 AND M.DV_PROCEDIMENTO       = 0) 
            //                 OR (M.CD_PROCEDIMENTO       = 201 AND M.DV_PROCEDIMENTO       = 9)
            //                 OR (M.CD_PROCEDIMENTO       = 301 AND M.DV_PROCEDIMENTO       = 5))
            //                 AND M.NR_PERREF              IN ( :numRef )
            //                 AND M.DT_ANOREF              = :anoRef
            //                 AND M.LOG_11                 = 1
            //         )"
        ],
        [
            'name' => 'ERRO CONSULTA 3013 - PROCEDIMENTO 10101012',
            'progresso' => 'erro3016', //* Id do <p>
            'sql' => "UPDATE GP.DOCTO_REVIS_CTAS_COMP SET COD_LIVRE_5 = 1 WHERE (CDN_UNID_PRESTDRA, CDN_TRANS, COD_SER_DOCTO_ORIGIN, NUM_DOCTO_ORIGIN, NUM_DOCTO_SIST, COD_LIVRE_5, PROGRESS_RECID) IN (
                    SELECT R.CDN_UNID_PRESTDRA, R.CDN_TRANS, R.COD_SER_DOCTO_ORIGIN, R.NUM_DOCTO_ORIGIN, R.NUM_DOCTO_SIST, R.COD_LIVRE_5, R.PROGRESS_RECID
                    FROM GP.DOCTO_REVIS_CTAS_COMP R 
                    WHERE R.NUM_DOCTO_ORIGIN IN ( SELECT DISTINCT(P.NR_DOC_ORIGINAL) 
                        FROM GP.MOVIPROC P 
                        INNER JOIN GP.AMBPROCE A ON A.CD_ESP_AMB          = P.CD_ESP_AMB
                                                AND A.CD_GRUPO_PROC_AMB   = P.CD_GRUPO_PROC_AMB
                                                AND A.CD_PROCEDIMENTO     = P.CD_PROCEDIMENTO
                                                AND A.DV_PROCEDIMENTO     = P.DV_PROCEDIMENTO
                        WHERE P.NR_PERREF              IN ( :numRef ) 
                            AND P.DT_ANOREF              = :anoRef 
                            AND A.CDPROCEDIMENTOCOMPLETO = 10101012
                            )
                            AND R.COD_LIVRE_5 = '2'
                    )"
        ],
    ];

    $totalUpdates = count($updates);
    $completed = 0;

    // sendSseMessage(['percent' => 0, 'message' => 'Iniciando processo de atualização...']);


    foreach ($updates as $update) {
    try {
        // sendSseMessage(['percent' => 25, 'message' => "Executando: {$update['name']}"]);
        ob_flush(); flush();

        $stmt = oci_parse($conn, $update['sql']);

        // Vincula os parâmetros da query
        oci_bind_by_name($stmt, ':numRef', $numRef);
        oci_bind_by_name($stmt, ':anoRef', $anoRef);

        // Executa a query
        if (oci_execute($stmt, OCI_COMMIT_ON_SUCCESS)) {
            $linhas_afetadas = oci_num_rows($stmt);
            oci_commit($conn); 
        } else {
            $error = oci_error($stmt);
            sendSseMessage(['percent' => 0, 'message' => "Erro: {$update['name']}"]);
        }

        // Calcular progresso
        $completed++;
        $percent = intval(($completed / $totalUpdates) * 100);

        // Enviar progresso atualizado
        sendSseMessage([
            'percent' => $percent,
            'progresso' =>$update['progresso'],
            'linhas' => $linhas_afetadas,
            'message' => "Executando: {$update['name']} ($completed/$totalUpdates)"
        ]);

        // Flush opcional
        if (ob_get_level() > 0) ob_flush();
        flush();
        usleep(500000); // 0.5s para visualização

    } catch (Exception $e) {
        // Enviar erro ao cliente via SSE e encerrar
        sendSseMessage([
            'error' => "Erro ao executar '{$update['name']}': " . $e->getMessage()
        ]);
        if (ob_get_level() > 0) ob_flush();
        flush();
        exit(); // Encerra o loop e o script se der erro
    }
}

    // Finalizar
    sendSseMessage(['complete' => true, 'percent' => 100, 'message' => 'Processo concluido'], true);
    
} catch (Exception $e) {
    sendSseMessage(['error' => $e->getMessage()], true);
}


?>