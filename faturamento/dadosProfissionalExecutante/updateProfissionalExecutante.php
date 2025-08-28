<?php
header('Content-Type: application/json; charset=utf-8');
require_once "../../config/AW00DB.php";
require_once "../../config/oracle.class.php";
require_once "../../config/AW00MD.php";

try {
    // Pegando os dados do POST
    $nomeProfExec  = $_POST['nomeProfExec']  ?? '';
    $tipoConselho  = $_POST['tipoConselho']  ?? '';
    $nrConselho    = $_POST['nrConselho']    ?? '';
    $ufConselho    = $_POST['ufConselho']    ?? '';
    $char19        = $_POST['char_19']       ?? '';

    // Campos da guia (vêm do formDadosProfExec)
    $nr_doc       = $_POST['nr_doc'] ?? null; 
    $cd_transacao = $_POST['cd_transacao'] ?? '';
    $nr_periodo   = $_POST['nr_periodo'] ?? '';
    $ano          = $_POST['ano'] ?? '';

    if (!$nomeProfExec || !$tipoConselho || !$nrConselho || !$ufConselho || !$nr_doc || !$cd_transacao || !$nr_periodo || !$ano) {
        // throw new Exception("Parâmetros insuficientes para atualizar o profissional.");
        echo json_encode([
            "success" => false,
            "message" => "Parâmetros insuficientes para atualizar o profissional"
        ]);
        exit;
    }

    // Monta o UPDATE
    $sql = "
        UPDATE gp.MOVIPROC SET
            CHAR_4  = :nome,
            CHAR_13 = :tipoConselho,
            CHAR_14 = :nrConselho,
            CHAR_15 = :ufConselho,
            CHAR_19 = :char19
        WHERE (NR_DOC_ORIGINAL , CD_PRESTADOR , CD_TRANSACAO, CHAR_4, CHAR_13, CHAR_14, CHAR_15, CHAR_19, PROGRESS_RECID) IN (
            SELECT P.NR_DOC_ORIGINAL , P.CD_PRESTADOR , P.CD_TRANSACAO , P.CHAR_4, P.CHAR_13, P.CHAR_14, P.CHAR_15, P.CHAR_19, P.PROGRESS_RECID 
            FROM gp.MOVIPROC P 
            WHERE P.NR_DOC_ORIGINAL = :nrDocOriginal
              AND P.NR_PERREF = :nrPerref
              AND P.DT_ANOREF = :dtAnoref
              AND P.CD_TRANSACAO = :cdTransacao
        )";

    $stmt = oci_parse($conn, $sql);

    oci_bind_by_name($stmt, ":nome",         $nomeProfExec);
    oci_bind_by_name($stmt, ":tipoConselho", $tipoConselho);
    oci_bind_by_name($stmt, ":nrConselho",   $nrConselho);
    oci_bind_by_name($stmt, ":ufConselho",   $ufConselho);
    oci_bind_by_name($stmt, ":char19",       $char19);

    oci_bind_by_name($stmt, ":nrDocOriginal",$nr_doc);
    oci_bind_by_name($stmt, ":nrPerref",     $nr_periodo);
    oci_bind_by_name($stmt, ":dtAnoref",     $ano);
    oci_bind_by_name($stmt, ":cdTransacao",  $cd_transacao);

    $ok = oci_execute($stmt, OCI_COMMIT_ON_SUCCESS);

    if ($ok) {
        echo json_encode([
            "success" => true,
            "message" => "Profissional executante atualizado com sucesso!"
        ]);
        exit;
    } else {
        $e = oci_error($stmt);
        echo json_encode([
            "success" => false,
            "message" => "Erro ao atualizar profissional: " . $e['message']
        ]);
    }

    oci_free_statement($stmt);
    oci_close($conn);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}