<?php
// Arquivo: update_faturamentoAutomatico.php

// Define ano e período automaticamente
// $ano = date('Y');        // Ex: 2025
// $periodo = date('m');    // Ex: 06

//* Data atual
$dataAtual = new DateTime();
$ano1 = $dataAtual->format('Y');
$periodo1 = $dataAtual->format('m');

//* Data do mês anterior (leva em conta virada de ano)
$dataAnterior = (clone $dataAtual)->modify('-1 month');
$ano2 = $dataAnterior->format('Y');
$periodo2 = $dataAnterior->format('m');





set_time_limit(3600); // 1 hora de execução

// Configuração e conexões
require_once "../config/AW00DB.php";
require_once "../config/oracle.class.php";
require_once "../config/AW00MD.php";

// Lista dos prestadores
$prestadores = [14546,14551,14552,14553,14565,14568,14570,14666,14719,14856,
                14911,14913,15034,15035,15037,15038,22360,23644,23931,200537,
                500555,508100,800720,25154];

// Log
$logFile = __DIR__ . '/log_faturamento_' . date('Ymd_His') . '.log';
$log = fopen($logFile, 'w');

foreach ($prestadores as $cd_prestador) {
    $sql = "
    SELECT DISTINCT P.CD_PRESTADOR, P.NM_DIRETOR_TECNICO, P.CHAR_21, P.CHAR_23, P.CHAR_22, 
           M.PROGRESS_RECID AS RECID_M, 
           HP.PROGRESS_RECID AS RECID_HP
    FROM GP.MOVIPROC M
    INNER JOIN GP.PRESERV P ON P.CD_PRESTADOR = M.CD_PRESTADOR
    INNER JOIN GP.HISTOR_MOVIMEN_PROCED HP ON M.CD_UNIDADE = HP.CD_UNIDADE
        AND M.CD_UNIDADE_PRESTADORA = HP.CD_UNIDADE_PRESTADORA
        AND M.CD_TRANSACAO = HP.CD_TRANSACAO
        AND M.NR_SERIE_DOC_ORIGINAL = HP.NR_SERIE_DOC_ORIGINAL
        AND M.NR_DOC_ORIGINAL = HP.NR_DOC_ORIGINAL
        AND M.NR_DOC_SISTEMA = HP.NR_DOC_SISTEMA
        AND M.NR_PROCESSO = HP.NR_PROCESSO
        AND M.NR_SEQ_DIGITACAO = HP.NR_SEQ_DIGITACAO
    WHERE M.CD_PRESTADOR = :cd
      AND ((M.CHAR_14 = '0' OR M.CHAR_14 IS NULL) 
           OR (M.CHAR_13 = 'OUT' OR M.CHAR_13 = ' ' OR M.CHAR_13 IS NULL))
      AND M.CD_PRESTADOR NOT IN (14592, 200537)
      AND M.CD_UNIDADE_CARTEIRA <> 540
      AND (
          (M.DT_ANOREF = :ano1 AND M.NR_PERREF = :periodo1)
       OR (M.DT_ANOREF = :ano2 AND M.NR_PERREF = :periodo2)
      )

";


    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ':cd', $cd_prestador);
    oci_bind_by_name($stmt, ':ano1', $ano1);
    oci_bind_by_name($stmt, ':periodo1', $periodo1);
    oci_bind_by_name($stmt, ':ano2', $ano2);
    oci_bind_by_name($stmt, ':periodo2', $periodo2);

    oci_execute($stmt);

    while ($row = oci_fetch_assoc($stmt)) {
        $nome = $row['NM_DIRETOR_TECNICO'];
        $char21 = $row['CHAR_21'];
        $char23 = $row['CHAR_23'];
        $char22 = $row['CHAR_22'];
        $recidM = $row['RECID_M'];
        $recidHP = $row['RECID_HP'];
        $concat = $char21 . ';' . $char23 . ';' . $char22;

        $updateMoviproc = "
            UPDATE GP.MOVIPROC SET 
                CHAR_4 = :nm_diretor, 
                CHAR_13 = :char21, 
                CHAR_14 = :char23, 
                CHAR_15 = :char22, 
                CHAR_19 = :charConcat
            WHERE PROGRESS_RECID = :recidM
        ";

        $upStmt1 = oci_parse($conn, $updateMoviproc);
        oci_bind_by_name($upStmt1, ':nm_diretor', $nome);
        oci_bind_by_name($upStmt1, ':char21', $char21);
        oci_bind_by_name($upStmt1, ':char23', $char23);
        oci_bind_by_name($upStmt1, ':char22', $char22);
        oci_bind_by_name($upStmt1, ':charConcat', $concat);
        oci_bind_by_name($upStmt1, ':recidM', $recidM);
        // oci_execute($upStmt1);

        $updateHistorico = "
            UPDATE GP.HISTOR_MOVIMEN_PROCED SET 
                CHAR_4 = :nm_diretor, 
                CHAR_13 = :char21, 
                CHAR_14 = :char23, 
                CHAR_15 = :char22, 
                CHAR_19 = :charConcat
            WHERE PROGRESS_RECID = :recidHP
        ";

        $upStmt2 = oci_parse($conn, $updateHistorico);
        oci_bind_by_name($upStmt2, ':nm_diretor', $nome);
        oci_bind_by_name($upStmt2, ':char21', $char21);
        oci_bind_by_name($upStmt2, ':char23', $char23);
        oci_bind_by_name($upStmt2, ':char22', $char22);
        oci_bind_by_name($upStmt2, ':charConcat', $concat);
        oci_bind_by_name($upStmt2, ':recidHP', $recidHP);
        // oci_execute($upStmt2);

        fwrite($log, "Atualizado prestador $cd_prestador - RECID M: $recidM / RECID HP: $recidHP\n $updateMoviproc\n\n --------------------------------------");
        echo "Atualizado prestador $cd_prestador - RECID M: $recidM / RECID HP: $recidHP<br> $updateMoviproc <br><br>";
    }

    oci_free_statement($stmt);
}

oci_close($conn);
fclose($log);
?>
