<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
ini_set('max_execution_time', 300); // 300 segundos (5 minutos)
set_time_limit(300);


$codUnidade    = $_POST['codUnidade'];
$carteira      = $_POST['carteira'];
$codModalidade = $_POST['codModalidade'];
$termo         = $_POST['termo'];
$codUsuario    = $_POST['codUsuario'];
$modalChar22   = $_POST['modalChar22'];
$codSequencia  = $_POST['codSequencia'];
$prestadorPrincipal = $_POST['prestadorPrincipal'];
$anoRef             = $_POST['anoRef'];
$periodoRef         = $_POST['periodoRef'];
$nrDocOrig          = $_POST['nrDocOrig'];

$id_user = $_SESSION['idusuario'];

require_once "../../config/AW00DB.php";
require_once "../../config/oracle.class.php";
require_once "../../config/AW00MD.php";

$db = new oracle();
$conn = $db->connect($db_host, $db_user, $db_pwd, $db_name, $pconnect);

// oci_set_call_timeout($conn, 30000); // Timeout de 30 segundos

if (!$conn) {
    $e = oci_error();
    echo json_encode(["error" => true, "message" => "Erro de conexão: " . htmlentities($e['message'], ENT_QUOTES)]);
    exit;
}

oci_set_client_info($conn, 'UTF-8');

//! REALIZA UPDATE EM TODAS ESSAS TABELAS
$updates = [
    "gp.docrecon" => "CD_PRESTADOR_PRINCIPAL",
    "gp.moviproc" => "CD_PRESTADOR",
    "gp.mov_insu" => "CD_PRESTADOR",
    "gp.histor_movimen_proced" => "CD_PRESTADOR",
    "gp.histor_movimen_insumo" => "CD_PRESTADOR"
];

$bindings = [
    ':codUnidade'         => (int)$codUnidade,
    ':carteira'           => (int)$carteira,
    ':codModalidade'      => (int)$codModalidade,
    ':termo'              => (int)$termo,
    ':codUsuario'         => (int)$codUsuario,
    ':modalChar22'        => $modalChar22,
    ':prestadorPrincipal' => (int)$prestadorPrincipal,
    ':anoRef'             => (int)$anoRef,
    ':periodoRef'         => (int)$periodoRef,
    ':nrDocOrig'          => (int)$nrDocOrig,
    ':codSequencia'       => (int)$codSequencia,
    ':iduser'             => $id_user
];

if (!empty($_POST['codTransacao'])) {
    $codTransacao   = $_POST['codTransacao'];
    $bindings[':codTransacao'] = $codTransacao;
    $whereClause = "AND CD_TRANSACAO = :codTransacao";
} else {
    $whereClause = "";
}

$error = false;
$errorMsg = "";


//! PASSA POR TODAS AS TABELAS (do array) E REALIZA O UPDATE
foreach ($updates as $table => $prestadorColumn) {
    $sql = "UPDATE $table
            SET CD_UNIDADE_CARTEIRA = :codUnidade, 
                CD_CARTEIRA_USUARIO = :carteira, 
                CD_MODALIDADE = :codModalidade,
                NR_TER_ADESAO = :termo, 
                CD_USUARIO = :codUsuario, 
                CHAR_22 = :modalChar22,
                CD_USERID = :iduser,
                DT_ATUALIZACAO = TO_CHAR(SYSDATE, 'DD/MM/YYYY')
            WHERE $prestadorColumn = :prestadorPrincipal
                AND DT_ANOREF = :anoRef
                AND NR_PERREF = :periodoRef
                AND NR_DOC_ORIGINAL = :nrDocOrig
                AND NR_DOC_SISTEMA = :codSequencia
                $whereClause";

    $stmt = oci_parse($conn, $sql);

    if (!$stmt) {
        $e = oci_error($conn);
        $error = true;
        $errorMsg = htmlentities($e['message'], ENT_QUOTES);
        break;
    }

    foreach ($bindings as $key => $value) {
        oci_bind_by_name($stmt, $key, $bindings[$key]);
    }

    if (!oci_execute($stmt, OCI_COMMIT_ON_SUCCESS)) {
        $e = oci_error($stmt);
        $error = true;
        $errorMsg = htmlentities($e['message'], ENT_QUOTES);
        break;
    }
}

if ($error) {
    oci_rollback($conn);
    echo json_encode(["error" => true, "message" => "Erro ao alterar Carteira: $errorMsg", "type" => "danger"]);
} else {
    oci_commit($conn);
    echo json_encode(["error" => false, "message" => "Carteira alterada com sucesso.", "type" => "success"]);
}

oci_close($conn);
?>
