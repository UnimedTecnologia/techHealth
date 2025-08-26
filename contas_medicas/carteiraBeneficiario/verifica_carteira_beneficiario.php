<?php
header('Content-Type: text/html; charset=utf-8');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtém os valores do formulário
    $prestador = isset($_POST['prestadorPrincipal']) ? (int) $_POST["prestadorPrincipal"] : null;
    $anoRef = isset($_POST['anoRef']) ? (int) $_POST['anoRef'] : null;
    $periodoRef = isset($_POST['periodoRef']) ? (int) $_POST['periodoRef'] : null;
    $nrDocOrig = isset($_POST['nrDocOrig']) ? (int) $_POST['nrDocOrig'] : null;
    $cod_transacao = isset($_POST['codTransacao']) && $_POST['codTransacao'] !== '' ? (int) $_POST['codTransacao'] : null;


}

require_once "../../config/AW00DB.php";
require_once "../../config/oracle.class.php";
require_once "../../config/AW00MD.php";

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

// Consulta SQL inicial
$sql = "SELECT D.CD_UNIDADE_CARTEIRA, D.CD_CARTEIRA_USUARIO, 
        D.CD_MODALIDADE, D.NR_TER_ADESAO, D.CD_USUARIO, D.CHAR_22, D.NR_DOC_ORIGINAL, D.NR_DOC_SISTEMA
        FROM GP.DOCRECON D 
     WHERE D.CD_PRESTADOR_PRINCIPAL = :prestador 
   AND D.DT_ANOREF    = :anoRef 
   AND D.NR_PERREF    = :periodoRef 
   AND D.NR_DOC_ORIGINAL = :nrDocOrig 
   
   "; //--AND D.CD_TRANSACAO = 3020

// Bind de parâmetros
$bindings = [
    ':prestador' => $prestador,
    ':anoRef' => $anoRef,
    ':periodoRef' => $periodoRef,
    ':nrDocOrig' => $nrDocOrig,
];
if ($cod_transacao !== null) {
    $sql .= " AND D.CD_TRANSACAO = :cod_transacao";
    $bindings[':cod_transacao'] = $cod_transacao;
}

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

if ($result) {

    while ($row = oci_fetch_assoc($stmt)) {
        $data[] = $row; // Adiciona cada linha ao array $data
    }

    if (!empty($data)) {
        // Retornar os dados encontrados no formato JSON
        $response = [
            'error' => false,
            'message' => '',
            'data' => $data
        ];

    }else {

        $response = [
            'error' => true,
            'message' =>'Dados não encontrados',
            'data' => []
        ];
    }
}else {
        // Se ocorrer erro ao executar a consulta
        $error = oci_error($stmt);
        $response = [
            'error' => true,
            'message' => 'Erro ao executar a consulta: ' . $error['message'],
            'data' => []
        ];
    }

// Liberar recursos
oci_free_statement($stmt);
oci_close($conn);

echo json_encode($response);


