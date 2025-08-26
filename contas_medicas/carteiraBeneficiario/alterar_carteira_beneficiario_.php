<?php
header('Content-Type: text/html; charset=utf-8');
session_start();

$codUnidade    = $_POST['codUnidade'];
$carteira      = $_POST['carteira'];
$codModalidade = $_POST['codModalidade'];
$termo         = $_POST['termo'];
$codUsuario    = $_POST['codUsuario'];
$modalChar22   = $_POST['modalChar22'];
$codSequencia  = $_POST['codSequencia'];
//* where
$prestadorPrincipal = $_POST['prestadorPrincipal'];
$anoRef             = $_POST['anoRef'];
$periodoRef         = $_POST['periodoRef'];
$nrDocOrig          = $_POST['nrDocOrig'];

require_once "../../config/AW00DB.php";
require_once "../../config/oracle.class.php";
require_once "../../config/AW00MD.php";

$db = new oracle();
$conn = $db->connect($db_host, $db_user, $db_pwd, $db_name, $pconnect);

oci_set_call_timeout($conn, 30000); // 30 segundos de timeout

// Verificar conexão
if (!$conn) {
    $e = oci_error();
    echo "Erro de conexão: " . htmlentities($e['message'], ENT_QUOTES);
    exit;
}

// Configurar codificação
oci_set_client_info($conn, 'UTF-8');

//* UPDATE SQL
$sql = "UPDATE gp.docrecon
        SET CD_UNIDADE_CARTEIRA = :codUnidade, CD_CARTEIRA_USUARIO = :carteira, CD_MODALIDADE = :codModalidade,
        NR_TER_ADESAO = :termo, CD_USUARIO = :codUsuario, CHAR_22 = :modalChar22 
        WHERE
            CD_PRESTADOR_PRINCIPAL = :prestadorPrincipal
            AND DT_ANOREF    = :anoRef
            AND NR_PERREF    = :periodoRef
            AND NR_DOC_ORIGINAL = :nrDocOrig
            AND NR_DOC_SISTEMA = :codSequencia
";

// Bind de parâmetros
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
    ':codSequencia'       => (int)$codSequencia

];

if (!empty($_POST['codTransacao'])) {
    $codTransacao   = $_POST['codTransacao'];
    $sql .= "AND D.CD_TRANSACAO = :codTransacao";
    $bindings[':codTransacao'] = $codTransacao;
}


// Preparar consulta
$stmt = oci_parse($conn, $sql);

if (!$stmt) {
    $e = oci_error($conn);
    echo "Erro ao preparar a consulta: " . htmlentities($e['message'], ENT_QUOTES);
    exit;
}

foreach ($bindings as $key => $value) {
    oci_bind_by_name($stmt, $key, $bindings[$key]);
}

if (oci_execute($stmt, OCI_COMMIT_ON_SUCCESS)) {

    //* Commitar alterações
    oci_commit($conn);
    $response = [
        'error' => false,
        'message' => 'Carteira alterada com sucesso.',
        'type' => 'success',
    ];
    echo json_encode($response);
    
} else {

    $response = [
        'error' => true,
        'message' => 'Erro ao alterar Carteira.',
        'type' => 'danger'

    ];
    echo json_encode($response);
}

