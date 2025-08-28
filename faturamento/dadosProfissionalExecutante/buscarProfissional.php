<?php
header('Content-Type: application/json; charset=utf-8');
require_once "../../config/AW00DB.php";
require_once "../../config/oracle.class.php";
require_once "../../config/AW00MD.php";

$nome = $_POST['nome'] ?? '';

if (!$nome) {
    echo json_encode(['success' => false, 'dado' => []]);
    exit;
}

$sql = "SELECT IDI_PROFIS, COD_CPF, NOM_PROFIS, COD_CONS_MEDIC, COD_UF_CONS, COD_REGISTRO
          FROM gp.DADOS_PROFIS dp
         WHERE UPPER(dp.NOM_PROFIS) LIKE UPPER(:nome)";

$stmt = oci_parse($conn, $sql);
$like = "%$nome%";
oci_bind_by_name($stmt, ":nome", $like);

if (!oci_execute($stmt)) {
    $e = oci_error($stmt);
    echo json_encode(['success' => false, 'message' => htmlentities($e['message'], ENT_QUOTES)]);
    exit;
}

$dados = [];
while ($row = oci_fetch_assoc($stmt)) {
    $dados[] = $row;
}

echo json_encode(['success' => true, 'dado' => $dados]);
