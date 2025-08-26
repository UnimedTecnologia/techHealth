<?php
header('Content-Type: text/html; charset=utf-8');
session_start();

// $cdModalidade = (int) $_POST['cdModalidade'];
// $nrProposta = (int) $_POST['nrProposta'];
// $cdUsuario = (int) $_POST['cdUsuario'];
// $novaConsulta = $_POST['novaConsulta'];


require_once "../config/AW00DB.php";
require_once "../config/oracle.class.php";
require_once "../config/AW00MD.php";

$db = new oracle();
$conn = $db->connect($db_host, $db_user, $db_pwd, $db_name, $pconnect);

// Verificar conexão
if (!$conn) {
    $e = oci_error();
    echo "Erro de conexão: " . htmlentities($e['message'], ENT_QUOTES);
    exit;
}

// Configurar codificação
oci_set_client_info($conn, 'UTF-8');

$cdModalidade = $_POST['cdModalidade'] ?? null;
$nrProposta = $_POST['nrProposta'] ?? null;
$cdUsuario = $_POST['cdUsuario'] ?? null;
$novaConsulta = $_POST['novaConsulta'] ?? false;

// Consulta SQL inicial
$sql = "select u.nm_usuario, u.cd_sit_cadastro, u.cd_sit_usuario, u.cd_modalidade, u.nr_proposta, u.cd_usuario
       from gp.usuario u 
       where 
       u.cd_modalidade IN (:cdModalidade) 
       and u.nr_proposta IN (:nrProposta) 
       and u.cd_usuario in (:cdUsuario) 
       "; //--and u.cd_sit_usuario = 7

// Bind de parâmetros
$bindings = [
    ':cdModalidade' => $cdModalidade,
    ':nrProposta' => $nrProposta,
    ':cdUsuario' => $cdUsuario,

];

// Adicionar condições opcionais
// if (!empty($_POST['sitUsuario'])) {
//     $sitUsuario = (int) $_POST['sitUsuario'];
//     $sql .= " and u.cd_sit_usuario = :sitUsuario";
//     $bindings[':sitUsuario'] = $sitUsuario;
// }


// Preparar consulta
$stmt = oci_parse($conn, $sql);

if (!$stmt) {
    $e = oci_error($conn);
    echo "Erro ao preparar a consulta: " . htmlentities($e['message'], ENT_QUOTES);
    exit;
}

// Associar os parâmetros
foreach ($bindings as $key => $value) {
    oci_bind_by_name($stmt, $key, $bindings[$key]);
}

// Executar consulta
$result = oci_execute($stmt);

if (!$result) {
    $e = oci_error($stmt);
    echo "Erro ao executar a consulta: " . htmlentities($e['message'], ENT_QUOTES);
    exit;
}

// Inicializar array para armazenar os resultados
$data = [];

while ($row = oci_fetch_assoc($stmt)) {
    $data[] = $row; // Adiciona cada linha ao array $data
}

if(!$novaConsulta){ //* PRIMEIRA BUSCA
    // Verificar resultados
    if (empty($data)) {
        $_SESSION['erroSituacaoCadastral'] = "Dados não encontrados";
        header("Location: ../dashboard.php");
        exit;
    } else {
        unset($_SESSION['erroSituacaoCadastral']); // Limpa sessão
        $_SESSION['dadosSituacaoCadastral'] = $data;
        header("Location: ./");
        exit;
    }
}else if($novaConsulta){ //* Pagina da situação cadastrar (nova busca)
    if (empty($data)) {
        $_SESSION['erroSituacaoCadastral'] = "Dados não encontrados";
        header("Location: ./");
        exit;
    } else {
        unset($_SESSION['erroSituacaoCadastral']); // Limpa sessão
        $_SESSION['dadosSituacaoCadastral'] = $data;
        header("Location: ./");
        exit;
    }
}

// Liberar recursos
oci_free_statement($stmt);
oci_close($conn);
