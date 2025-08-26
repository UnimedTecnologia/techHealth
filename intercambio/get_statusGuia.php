<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

// Verificação se os parâmetros foram passados corretamente
if (!isset($_POST['nrGuia'], $_POST['anoGuia']) || empty($_POST['nrGuia']) || empty($_POST['anoGuia'])) {
    echo json_encode([
        'error' => true,
        'message' => 'Parâmetros inválidos',
        'type' => 'danger',
        'data' => ''
    ]);
    exit;
}

$nrGuia = $_POST['nrGuia'];
$anoGuia = (int) $_POST['anoGuia']; // Garante que é um número inteiro

require_once "../config/AW00DB.php";
require_once "../config/oracle.class.php";
require_once "../config/AW00MD.php";

// Define UTF-8 corretamente
putenv('NLS_LANG=AMERICAN_AMERICA.UTF8');

// Conectar ao banco
$db = new oracle();
$conn = $db->connect($db_host, $db_user, $db_pwd, $db_name, $pconnect);

if (!$conn) {
    echo json_encode([
        'error' => true,
        'message' => 'Erro de conexão com o banco de dados',
        'type' => 'danger',
        'data' => ''
    ]);
    exit;
}

// Divide a string dos números da guia e cria placeholders para o Oracle
$guias = explode(',', $nrGuia);
$placeholders = [];
$bindings = [':anoGuia' => $anoGuia];

foreach ($guias as $index => $guia) {
    $param = ":guia{$index}";
    $placeholders[] = $param;
    $bindings[$param] = (int) trim($guia);
}

// Monta a consulta com os placeholders
$sql = "SELECT 
    G.NR_GUIA_ATENDIMENTO, 
    G.AA_GUIA_ATEND_ORIGEM, 
    G.NR_GUIA_ATEND_ORIGEM, 
    G.IN_LIBERADO_GUIAS,
    CASE 
        WHEN G.IN_LIBERADO_GUIAS =  1  THEN 'DIGITADA'
        WHEN G.IN_LIBERADO_GUIAS =  2  THEN 'AUTORIZADA'
        WHEN G.IN_LIBERADO_GUIAS =  3  THEN 'CANCELADA'
        WHEN G.IN_LIBERADO_GUIAS =  4  THEN 'PROCESSADA PELO RC'
        WHEN G.IN_LIBERADO_GUIAS =  5  THEN 'FECHADA'
        WHEN G.IN_LIBERADO_GUIAS =  6  THEN 'ORÇAMENTO'
        WHEN G.IN_LIBERADO_GUIAS =  7  THEN 'FATURADA'
        WHEN G.IN_LIBERADO_GUIAS =  8  THEN 'NEGADA'
        WHEN G.IN_LIBERADO_GUIAS =  9  THEN 'PENDENTE AUDITORIA'
        WHEN G.IN_LIBERADO_GUIAS = 10  THEN 'PENDENTE LIBERAÇÃO'
        WHEN G.IN_LIBERADO_GUIAS = 11  THEN 'PENDENTE LAUDO MÉDICO'
        WHEN G.IN_LIBERADO_GUIAS = 12  THEN 'PENDENTE DE JUSTIFICATIVA MÉDICA'
        WHEN G.IN_LIBERADO_GUIAS = 13  THEN 'PENDENTE DE PERÍCIA'
        WHEN G.IN_LIBERADO_GUIAS = 19  THEN 'EM AUDITORIA'
        WHEN G.IN_LIBERADO_GUIAS = 20  THEN 'EM ATENDIMENTO'
        WHEN G.IN_LIBERADO_GUIAS = 23  THEN 'EM PERÍCIA'
        WHEN G.IN_LIBERADO_GUIAS = 30  THEN 'REEMBOLSO'
        ELSE 'STATUS DESCONHECIDO'
    END AS STATUS_GUIA
FROM GP.GUIAUTOR G 
WHERE G.AA_GUIA_ATENDIMENTO = :anoGuia 
AND G.NR_GUIA_ATENDIMENTO IN (" . implode(',', $placeholders) . ")";

$stmt = oci_parse($conn, $sql);

if (!$stmt) {
    echo json_encode([
        'error' => true,
        'message' => 'Erro ao preparar a consulta',
        'type' => 'danger',
        'data' => ''
    ]);
    exit;
}

// Associar os parâmetros corretamente
foreach ($bindings as $key => $value) {
    oci_bind_by_name($stmt, $key, $bindings[$key]);
}

// Executar consulta
$result = oci_execute($stmt);

if (!$result) {
    $e = oci_error($stmt);
    echo json_encode([
        'error' => true,
        'message' => 'Erro ao executar a consulta: ' . htmlentities($e['message'], ENT_QUOTES),
        'type' => 'danger',
        'data' => ''
    ]);
    exit;
}

// Coleta os dados
$data = [];
while ($row = oci_fetch_assoc($stmt)) {
    $data[] = $row;
}

// Resposta JSON
if (empty($data)) {
    echo json_encode([
        'error' => true,
        'message' => 'Nenhuma guia encontrada',
        'type' => 'warning',
        'data' => ''
    ]);
} else {
    echo json_encode([
        'error' => false,
        'message' => 'Consulta realizada com sucesso',
        'type' => 'success',
        'data' => $data
    ]);
}

// Liberar recursos
oci_free_statement($stmt);
oci_close($conn);
