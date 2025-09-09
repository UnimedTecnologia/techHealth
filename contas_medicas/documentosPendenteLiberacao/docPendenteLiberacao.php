<?php

header('Content-Type: application/json; charset=utf-8');
session_start();

set_time_limit(1200); // tempo em segundos (10 minutos)
date_default_timezone_set('America/Sao_Paulo');

$ano     = $_POST['ano'] ?? '';
$periodo = $_POST['periodo'] ?? '';
$grupo   = $_POST['grupo'] ?? '';

if (!empty($grupo)) {
    // permite apenas números e vírgulas
    if (!preg_match('/^[0-9,\s]+$/', $grupo)) {
        echo json_encode([
            'success' => false,
            'message' => "O campo 'Grupo' só pode conter números separados por vírgula.",
            'dado' => []
        ]);
        exit;
    }
}


// Conexão com Oracle
require_once "../../config/AW00DB.php";
require_once "../../config/oracle.class.php";
require_once "../../config/AW00MD.php";

$db = new oracle();
$conn = $db->connect($db_host, $db_user, $db_pwd, $db_name, $pconnect);

if (!$conn) {
    $e = oci_error();
    echo json_encode([
        'success' => false,
        'message' => 'Erro de conexão: ' . htmlentities($e['message'], ENT_QUOTES)
    ]);
    exit;
}

// Prepara SQL base
$sql = "SELECT 
    D.CD_PRESTADOR_PRINCIPAL, 
    P.NM_PRESTADOR,
    SUM(CASE WHEN D.CHAR_20 = 1 THEN 1 ELSE 0 END) AS PEND_GLOSA,
    SUM(CASE WHEN D.CHAR_20 = 2 THEN 1 ELSE 0 END) AS PEND_LIBERACAO,
    P.CD_GRUPO_PRESTADOR

FROM gp.DOCRECON D
INNER JOIN gp.PRESERV P 
    ON P.CD_UNIDADE = D.CD_UNIDADE 
    AND P.CD_PRESTADOR = D.CD_PRESTADOR_PRINCIPAL
WHERE D.DT_ANOREF = :ano
  AND D.NR_PERREF = :periodo
  AND D.CHAR_20 <= 3";

// Ajusta grupos (se informado)
$binds = [];
if (!empty($grupo)) {
    $gruposArray = array_filter(array_map('trim', explode(',', $grupo))); // separa vírgula
    $placeholders = [];

    foreach ($gruposArray as $idx => $g) {
        $ph = ":grupo$idx";
        $placeholders[] = $ph;
        $binds[$ph] = $g;
    }

    if (!empty($placeholders)) {
        $sql .= " AND P.CD_GRUPO_PRESTADOR IN (" . implode(',', $placeholders) . ")";
    }
}

$sql .= " GROUP BY D.CD_PRESTADOR_PRINCIPAL, P.NM_PRESTADOR, P.CD_GRUPO_PRESTADOR
    HAVING SUM(CASE WHEN D.CHAR_20 = 1 THEN 1 ELSE 0 END) > 0
    OR SUM(CASE WHEN D.CHAR_20 = 2 THEN 1 ELSE 0 END) > 0
    ORDER BY D.CD_PRESTADOR_PRINCIPAL";

$selectStmt = oci_parse($conn, $sql);
oci_bind_by_name($selectStmt, ':ano', $ano);
oci_bind_by_name($selectStmt, ':periodo', $periodo);

// Faz bind dos grupos
foreach ($binds as $ph => $val) {
    oci_bind_by_name($selectStmt, $ph, $binds[$ph]);
}

// Executar SELECT
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
