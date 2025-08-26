<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

$AA         = $_POST['AA'];
$NR         = $_POST['NR']; 
$statusGuia = $_POST['statusSelect']; 
$nr_guia    = $_POST['nr_guia'];
$ano        = $_POST['ano'];

require_once "../config/AW00DB.php";
require_once "../config/oracle.class.php";
require_once "../config/AW00MD.php";

$sql = "UPDATE GP.GUIAUTOR SET AA_GUIA_ATEND_ORIGEM = :AA, NR_GUIA_ATEND_ORIGEM = :NR, IN_LIBERADO_GUIAS = :statusGuia 
WHERE NR_GUIA_ATENDIMENTO = :nrGuia AND AA_GUIA_ATENDIMENTO = :ano ";

$bindings = [
    ':AA'           => $AA,
    ':NR'           => $NR,
    ':statusGuia'   => $statusGuia,
    ':nrGuia'       => $nr_guia,
    ':ano'          => $ano,

];

$stmt = oci_parse($conn, $sql);
$error = false;

if (!$stmt) {
    $e = oci_error($conn);
    $error = true;
    $errorMsg = htmlentities($e['message'], ENT_QUOTES);
}

foreach ($bindings as $key => $value) {
    oci_bind_by_name($stmt, $key, $bindings[$key]);
}

if (!oci_execute($stmt, OCI_COMMIT_ON_SUCCESS)) {
    $e = oci_error($stmt);
    $error = true;
    $errorMsg = htmlentities($e['message'], ENT_QUOTES);
}

if ($error) {
    oci_rollback($conn);
    echo json_encode(["error" => true, "message" => "Erro ao atualizar status da guia: $errorMsg", "type" => "error"]);
} else {
    oci_commit($conn);
    echo json_encode(["error" => false, "message" => "Guia atualizada com sucesso", "type" => "success"]);
}

oci_close($conn);

?>
