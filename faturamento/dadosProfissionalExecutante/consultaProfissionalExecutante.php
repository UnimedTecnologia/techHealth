<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
set_time_limit(600); //* Definir o tempo máximo de execução para 600 segundos (10 minutos)
// Dados recebidos via POST
$nr_doc       = $_POST['nr_doc'] ?? null; 
$cd_transacao = $_POST['cd_transacao'] ?? '';
$nr_periodo   = $_POST['nr_periodo'] ?? '';
$ano          = $_POST['ano'] ?? '';

// Configuração e conexões
require_once "../../config/AW00DB.php";
require_once "../../config/oracle.class.php";
require_once "../../config/AW00MD.php";


$sql = "SELECT P.NR_DOC_ORIGINAL , P.CD_PRESTADOR , P.CD_TRANSACAO , P.CHAR_4, P.CHAR_13, P.CHAR_14, P.CHAR_15, P.CHAR_19, P.PROGRESS_RECID 
        FROM gp.MOVIPROC P 
            WHERE P.NR_DOC_ORIGINAL = :nr_doc
                AND P.NR_PERREF = :nr_periodo
                AND P.DT_ANOREF = :ano
                AND P.CD_TRANSACAO = :cd_transacao ";

 $bindings = [
        ':nr_doc'       => $nr_doc,
        ':cd_transacao' => $cd_transacao,
        ':nr_periodo'   => $nr_periodo,
        ':ano'          => $ano
    ];

// Executar SELECT
$selectStmt = oci_parse($conn, $sql);
oci_bind_by_name($selectStmt, ':nr_doc', $nr_doc);
oci_bind_by_name($selectStmt, ':cd_transacao', $cd_transacao);
oci_bind_by_name($selectStmt, ':nr_periodo', $nr_periodo);
oci_bind_by_name($selectStmt, ':ano', $ano);


$result = oci_execute($selectStmt);
if (!$result) {
    $e = oci_error($selectStmt);
    echo json_encode([
        'success' => false,
        'message' => "Erro ao executar a consulta: " . htmlentities($e['message'], ENT_QUOTES),
        'dado' => []
    ]);
    exit;
}

// Buscar os dados
$data = [];
while ($row = oci_fetch_assoc($selectStmt)) {
    $data[] = $row;
}

echo json_encode([
    'success' => true,
    'message' => count($data) > 0 ? 'Dados encontrados' : 'Nenhum dado encontrado',
    'dado' => $data
]);
exit;

?>