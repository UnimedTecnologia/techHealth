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


$sql = "SELECT D.CD_PRESTADOR_PRINCIPAL, M.NR_PERREF , M.DT_ANOREF , M.NR_DOC_ORIGINAL , M.NR_DOC_SISTEMA , M.CD_TRANSACAO , 
M.NR_SERIE_DOC_ORIGINAL , M.CD_MODULO , M.PROGRESS_RECID 
FROM gp.MOVIPROC M 
	INNER JOIN gp.DOCRECON D ON D.NR_DOC_ORIGINAL 		= M.NR_DOC_ORIGINAL 
	                     AND D.NR_DOC_SISTEMA 			= M.NR_DOC_SISTEMA 
	                     AND D.NR_SERIE_DOC_ORIGINAL 	= M.NR_SERIE_DOC_ORIGINAL 
	                     AND D.CD_TRANSACAO 			= M.CD_TRANSACAO 
	WHERE M.NR_DOC_ORIGINAL = :nr_doc
	  AND M.CD_TRANSACAO = :cd_transacao
	  AND M.NR_PERREF = :nr_periodo
	  AND M.DT_ANOREF = :ano ";

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