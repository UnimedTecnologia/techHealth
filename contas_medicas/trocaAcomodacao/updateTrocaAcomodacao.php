<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
set_time_limit(600);

$nr_doc       = $_POST['nr_doc'] ?? null; 
$cd_transacao = $_POST['cd_transacao'] ?? '';
$nr_periodo   = $_POST['nr_periodo'] ?? '';
$ano          = $_POST['ano'] ?? '';
$modulo       = 6; // Apartamento

require_once "../../config/AW00DB.php";
require_once "../../config/oracle.class.php";
require_once "../../config/AW00MD.php";

// =========================
// UPDATE MOVIPROC
// =========================
$sql_proc = "UPDATE gp.MOVIPROC SET CD_MODULO = :modulo 
WHERE ( CD_PRESTADOR, NR_PERREF , DT_ANOREF, NR_DOC_ORIGINAL , NR_DOC_SISTEMA ,
 CD_TRANSACAO , NR_SERIE_DOC_ORIGINAL , CD_MODULO , PROGRESS_RECID ) IN (
    SELECT M.CD_PRESTADOR, M.NR_PERREF , M.DT_ANOREF , M.NR_DOC_ORIGINAL , M.NR_DOC_SISTEMA , M.CD_TRANSACAO , 
           M.NR_SERIE_DOC_ORIGINAL , M.CD_MODULO , M.PROGRESS_RECID 
    FROM gp.MOVIPROC M 
    INNER JOIN gp.DOCRECON D ON D.NR_DOC_ORIGINAL = M.NR_DOC_ORIGINAL 
         AND D.NR_DOC_SISTEMA = M.NR_DOC_SISTEMA 
         AND D.NR_SERIE_DOC_ORIGINAL = M.NR_SERIE_DOC_ORIGINAL 
         AND D.CD_TRANSACAO = M.CD_TRANSACAO 
    WHERE M.NR_DOC_ORIGINAL = :nr_doc
      AND M.CD_TRANSACAO = :cd_transacao
      AND M.NR_PERREF = :nr_periodo
      AND M.DT_ANOREF = :ano
)";

$stmt_proc = oci_parse($conn, $sql_proc);
oci_bind_by_name($stmt_proc, ':modulo', $modulo);
oci_bind_by_name($stmt_proc, ':nr_doc', $nr_doc);
oci_bind_by_name($stmt_proc, ':cd_transacao', $cd_transacao);
oci_bind_by_name($stmt_proc, ':nr_periodo', $nr_periodo);
oci_bind_by_name($stmt_proc, ':ano', $ano);

if (!oci_execute($stmt_proc, OCI_NO_AUTO_COMMIT)) {
    $e = oci_error($stmt_proc);
    echo json_encode(['success' => false, 'message' => "Erro ao atualizar MOVIPROC: " . htmlentities($e['message'], ENT_QUOTES)]);
    exit;
}

// =========================
// UPDATE MOV_INSU
// =========================
$sql_insu = "UPDATE gp.MOV_INSU SET CD_MODULO = :modulo 
WHERE ( CD_PRESTADOR, NR_PERREF , DT_ANOREF, NR_DOC_ORIGINAL , NR_DOC_SISTEMA ,
 CD_TRANSACAO , NR_SERIE_DOC_ORIGINAL , CD_MODULO , PROGRESS_RECID ) IN (
    SELECT M.CD_PRESTADOR, M.NR_PERREF , M.DT_ANOREF , M.NR_DOC_ORIGINAL , M.NR_DOC_SISTEMA , M.CD_TRANSACAO , 
           M.NR_SERIE_DOC_ORIGINAL , M.CD_MODULO , M.PROGRESS_RECID 
    FROM gp.MOV_INSU M 
    INNER JOIN gp.DOCRECON D ON D.NR_DOC_ORIGINAL = M.NR_DOC_ORIGINAL 
         AND D.NR_DOC_SISTEMA = M.NR_DOC_SISTEMA 
         AND D.NR_SERIE_DOC_ORIGINAL = M.NR_SERIE_DOC_ORIGINAL 
         AND D.CD_TRANSACAO = M.CD_TRANSACAO 
    WHERE M.NR_DOC_ORIGINAL = :nr_doc
      AND M.CD_TRANSACAO = :cd_transacao
      AND M.NR_PERREF = :nr_periodo
      AND M.DT_ANOREF = :ano
)";

$stmt_insu = oci_parse($conn, $sql_insu);
oci_bind_by_name($stmt_insu, ':modulo', $modulo);
oci_bind_by_name($stmt_insu, ':nr_doc', $nr_doc);
oci_bind_by_name($stmt_insu, ':cd_transacao', $cd_transacao);
oci_bind_by_name($stmt_insu, ':nr_periodo', $nr_periodo);
oci_bind_by_name($stmt_insu, ':ano', $ano);

if (!oci_execute($stmt_insu, OCI_NO_AUTO_COMMIT)) {
    $e = oci_error($stmt_insu);
    oci_rollback($conn);
    echo json_encode(['success' => false, 'message' => "Erro ao atualizar MOV_INSU: " . htmlentities($e['message'], ENT_QUOTES)]);
    exit;
}

// =========================
// UPDATE HISTOR_MOVIMEN_PROCED
// =========================
$sql_hist_proc = "UPDATE gp.HISTOR_MOVIMEN_PROCED
                  SET CD_MODULO = :modulo
                  WHERE NR_DOC_ORIGINAL = :nr_doc
                    AND CD_TRANSACAO = :cd_transacao
                    AND NR_PERREF = :nr_periodo
                    AND DT_ANOREF = :ano";

$stmt_hist_proc = oci_parse($conn, $sql_hist_proc);
oci_bind_by_name($stmt_hist_proc, ':modulo', $modulo);
oci_bind_by_name($stmt_hist_proc, ':nr_doc', $nr_doc);
oci_bind_by_name($stmt_hist_proc, ':cd_transacao', $cd_transacao);
oci_bind_by_name($stmt_hist_proc, ':nr_periodo', $nr_periodo);
oci_bind_by_name($stmt_hist_proc, ':ano', $ano);

if (!oci_execute($stmt_hist_proc, OCI_NO_AUTO_COMMIT)) {
    $e = oci_error($stmt_hist_proc);
    oci_rollback($conn);
    echo json_encode(['success' => false, 'message' => "Erro ao atualizar HISTOR_MOVIMEN_PROCED: " . htmlentities($e['message'], ENT_QUOTES)]);
    exit;
}

// =========================
// UPDATE HISTOR_MOVIMEN_INSUMO
// =========================
$sql_hist_insu = "UPDATE gp.HISTOR_MOVIMEN_INSUMO
                  SET CD_MODULO = :modulo
                  WHERE NR_DOC_ORIGINAL = :nr_doc
                    AND CD_TRANSACAO = :cd_transacao
                    AND NR_PERREF = :nr_periodo
                    AND DT_ANOREF = :ano";

$stmt_hist_insu = oci_parse($conn, $sql_hist_insu);
oci_bind_by_name($stmt_hist_insu, ':modulo', $modulo);
oci_bind_by_name($stmt_hist_insu, ':nr_doc', $nr_doc);
oci_bind_by_name($stmt_hist_insu, ':cd_transacao', $cd_transacao);
oci_bind_by_name($stmt_hist_insu, ':nr_periodo', $nr_periodo);
oci_bind_by_name($stmt_hist_insu, ':ano', $ano);

if (!oci_execute($stmt_hist_insu, OCI_NO_AUTO_COMMIT)) {
    $e = oci_error($stmt_hist_insu);
    oci_rollback($conn);
    echo json_encode(['success' => false, 'message' => "Erro ao atualizar HISTOR_MOVIMEN_INSUMO: " . htmlentities($e['message'], ENT_QUOTES)]);
    exit;
}

// =========================
// COMMIT FINAL
// =========================
oci_commit($conn);

echo json_encode([
    'success' => true,
    'message' => "Acomodação atualizada em MOVIPROC, MOV_INSU e históricos!",
    'type'    => 'success'
]);
exit;
