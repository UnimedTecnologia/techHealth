<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
set_time_limit(600);

$nr_doc       = $_POST['nr_doc'] ?? null; 
$cd_transacao = $_POST['cd_transacao'] ?? '';
$nr_periodo   = $_POST['nr_periodo'] ?? '';
$ano          = $_POST['ano'] ?? '';

require_once "../../config/AW00DB.php";
require_once "../../config/oracle.class.php";
require_once "../../config/AW00MD.php";

/**
 * Consulta MOVIPROC
 */
$sql_proc = "SELECT D.CD_PRESTADOR_PRINCIPAL, M.NR_PERREF , M.DT_ANOREF , M.NR_DOC_ORIGINAL , 
M.NR_DOC_SISTEMA , M.CD_TRANSACAO , M.NR_SERIE_DOC_ORIGINAL , M.CD_MODULO , M.PROGRESS_RECID 
FROM gp.MOVIPROC M 
INNER JOIN gp.DOCRECON D ON D.NR_DOC_ORIGINAL = M.NR_DOC_ORIGINAL 
    AND D.NR_DOC_SISTEMA = M.NR_DOC_SISTEMA 
    AND D.NR_SERIE_DOC_ORIGINAL = M.NR_SERIE_DOC_ORIGINAL 
    AND D.CD_TRANSACAO = M.CD_TRANSACAO 
WHERE M.NR_DOC_ORIGINAL = :nr_doc
  AND M.CD_TRANSACAO = :cd_transacao
  AND M.NR_PERREF = :nr_periodo
  AND M.DT_ANOREF = :ano";

$stmt_proc = oci_parse($conn, $sql_proc);
oci_bind_by_name($stmt_proc, ':nr_doc', $nr_doc);
oci_bind_by_name($stmt_proc, ':cd_transacao', $cd_transacao);
oci_bind_by_name($stmt_proc, ':nr_periodo', $nr_periodo);
oci_bind_by_name($stmt_proc, ':ano', $ano);

oci_execute($stmt_proc);

$data_proc = [];
while ($row = oci_fetch_assoc($stmt_proc)) {
    $data_proc[] = $row;
}

/**
 * Consulta MOV_INSU
 */
$sql_insu = "SELECT D.CD_PRESTADOR_PRINCIPAL, M.NR_PERREF , M.DT_ANOREF , 
M.NR_DOC_ORIGINAL , M.NR_DOC_SISTEMA , M.CD_TRANSACAO , M.NR_SERIE_DOC_ORIGINAL ,
M.CD_MODULO , M.PROGRESS_RECID  
FROM gp.mov_insu M
INNER JOIN gp.DOCRECON D ON D.NR_DOC_ORIGINAL = M.NR_DOC_ORIGINAL 
    AND D.NR_DOC_SISTEMA = M.NR_DOC_SISTEMA 
    AND D.NR_SERIE_DOC_ORIGINAL = M.NR_SERIE_DOC_ORIGINAL 
    AND D.CD_TRANSACAO = M.CD_TRANSACAO 
WHERE D.NR_DOC_ORIGINAL = :nr_doc
  AND D.CD_TRANSACAO = :cd_transacao
  AND D.DT_ANOREF = :ano
  AND D.NR_PERREF = :nr_periodo";

$stmt_insu = oci_parse($conn, $sql_insu);
oci_bind_by_name($stmt_insu, ':nr_doc', $nr_doc);
oci_bind_by_name($stmt_insu, ':cd_transacao', $cd_transacao);
oci_bind_by_name($stmt_insu, ':nr_periodo', $nr_periodo);
oci_bind_by_name($stmt_insu, ':ano', $ano);

oci_execute($stmt_insu);

$data_insu = [];
while ($row = oci_fetch_assoc($stmt_insu)) {
    $data_insu[] = $row;
}

// Retorno final
echo json_encode([
    'success' => true,
    'message' => 'Consulta realizada',
    'dado_moviproc' => $data_proc,
    'dado_movinsu' => $data_insu
]);
exit;
