<?php
header('Content-Type: text/html; charset=utf-8');
session_start();

$cdModalidade  = $_POST['cd_modalidade'];
$nrProposta    = $_POST['nr_proposta'];
$cdUsuario     = $_POST['cd_usuario'];
$sitUsuario    = $_POST['sitUsuario'];


require_once "../config/AW00DB.php";
require_once "../config/oracle.class.php";
require_once "../config/AW00MD.php";

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
$sql = "update gp.usuario u set CD_SIT_USUARIO = :cdSituacaoUsuario where 
       u.cd_modalidade = :cdModalidade  
       and u.nr_proposta = :nrProposta
       and u.cd_usuario = :cdUsuario ";

// Bind de parâmetros
$bindings = [
    ':cdSituacaoUsuario'  => $sitUsuario,
    ':cdModalidade'       => $cdModalidade,
    ':nrProposta'         => $nrProposta,
    ':cdUsuario'          => $cdUsuario,

];

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

if (!oci_execute($stmt, OCI_COMMIT_ON_SUCCESS)) {
    $e = oci_error($stmt);
    $response = [
        'error' => true,
        'message' => 'Erro ao alterar Código da Situação do Usuário: ' . htmlentities($e['message'], ENT_QUOTES),
        'type' => 'danger'
    ];
} else {
    $response = [
        'error' => false,
        'message' => 'Código Situação do Usuário atualizado com sucesso.',
        'type' => 'success'
    ];
}

$_SESSION['retornoAtualizacaoSituacao'] = $response;

//! AO FAZER UPDATE - GET NOVOS VALORES
$_POST['cdModalidade'] =  $cdModalidade;
$_POST['nrProposta']   =  $nrProposta;
$_POST['cdUsuario']    =  $cdUsuario;
// $_POST['update_prestador'] = true;
//* arquivo que irá usar os parâmetros do $_POST 
include 'getSituacaoCadastral.php';
