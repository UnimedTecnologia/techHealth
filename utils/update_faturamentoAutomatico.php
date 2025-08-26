<?php
/* //* ////////////////////////////////////////////////////////////////////////////////////////
Realizar select que gera updates de todos os prestadores do array de prestadores ($prestadores)
//* ///////////////////////////////////////////////////////////////////////////////////////////
*/

$ano = $_POST['anoFatAuto'] ?? null; 
$periodo = $_POST['periodoFatAuto'] ?? null; 

set_time_limit(3600); //* limite de tempo

// Configuração e conexões
require_once "../config/AW00DB.php";
require_once "../config/oracle.class.php";
require_once "../config/AW00MD.php";

// Lista dos prestadores
$prestadores = [14551,14552,25154]; //* teste
// $prestadores = [14546,14551,14552,14553,14565,14568,14570,14666,14719,14856,
//                 14911,14913,15034,15035,15037,15038,22360,23644,23931,200537,
//                 500555,508100,800720,25154];

// Loop para cada prestador
foreach ($prestadores as $cd_prestador) {
    // Monta SELECT para esse prestador
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
          AND M.DT_ANOREF = :ano
          AND M.NR_PERREF = :periodo
    ";

    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ':cd', $cd_prestador);
    oci_bind_by_name($stmt, ':ano', $ano);
    oci_bind_by_name($stmt, ':periodo', $periodo);
    oci_execute($stmt);

    // Executa atualizações para cada resultado
    while ($row = oci_fetch_assoc($stmt)) {
        $nome = $row['NM_DIRETOR_TECNICO'];
        $char21 = $row['CHAR_21'];
        $char23 = $row['CHAR_23'];
        $char22 = $row['CHAR_22'];
        $recidM = $row['RECID_M'];
        $recidHP = $row['RECID_HP'];

        // Gera os updates
        $updateMoviproc = "
            UPDATE GP.MOVIPROC SET 
                CHAR_4 = :nm_diretor, 
                CHAR_13 = :char21, 
                CHAR_14 = :char23, 
                CHAR_15 = :char22, 
                CHAR_19 = :charConcat
            WHERE PROGRESS_RECID = :recidM
        ";

        $updateHistorico = "
            UPDATE GP.HISTOR_MOVIMEN_PROCED SET 
                CHAR_4 = :nm_diretor, 
                CHAR_13 = :char21, 
                CHAR_14 = :char23, 
                CHAR_15 = :char22, 
                CHAR_19 = :charConcat
            WHERE PROGRESS_RECID = :recidHP
        ";

        $concat = $char21 . ';' . $char23 . ';' . $char22;

        // Executa update em MOVIPROC
        $upStmt1 = oci_parse($conn, $updateMoviproc);
        oci_bind_by_name($upStmt1, ':nm_diretor', $nome);
        oci_bind_by_name($upStmt1, ':char21', $char21);
        oci_bind_by_name($upStmt1, ':char23', $char23);
        oci_bind_by_name($upStmt1, ':char22', $char22);
        oci_bind_by_name($upStmt1, ':charConcat', $concat);
        oci_bind_by_name($upStmt1, ':recidM', $recidM);
        // oci_execute($upStmt1);

        // Executa update em HISTOR_MOVIMEN_PROCED
        $upStmt2 = oci_parse($conn, $updateHistorico);
        oci_bind_by_name($upStmt2, ':nm_diretor', $nome);
        oci_bind_by_name($upStmt2, ':char21', $char21);
        oci_bind_by_name($upStmt2, ':char23', $char23);
        oci_bind_by_name($upStmt2, ':char22', $char22);
        oci_bind_by_name($upStmt2, ':charConcat', $concat);
        oci_bind_by_name($upStmt2, ':recidHP', $recidHP);
        // oci_execute($upStmt2);

        echo "Atualizado prestador $cd_prestador - RECID M: $recidM / RECID HP: $recidHP<br>";
    }

    oci_free_statement($stmt);
}

oci_close($conn);
?>
