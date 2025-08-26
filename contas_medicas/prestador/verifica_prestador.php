<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
unset($_SESSION['dadosPrestador']);

$numero_documento = (int) $_POST['numDoc'];
$codigo_prestador = (int) $_POST['codPrest'];
$codigo_transacao = (int) $_POST['codTrans'];
$serie_documento = $_POST['serieDoc'];

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
$sql = "SELECT D.NR_PERREF, D.DT_ANOREF, D.NR_DOC_ORIGINAL, D.NR_DOC_SISTEMA, 
D.CD_PRESTADOR_PRINCIPAL , D.CD_TRANSACAO, D.NR_SERIE_DOC_ORIGINAL
        FROM gp.docrecon d 
        WHERE d.nr_doc_original = :numero_documento
          AND d.cd_prestador_principal = :codigo_prestador
          AND d.cd_transacao = :codigo_transacao
          AND d.nr_serie_doc_original = :serie_documento";

// Bind de parâmetros
$bindings = [
    ':numero_documento' => $numero_documento,
    ':codigo_prestador' => $codigo_prestador,
    ':codigo_transacao' => $codigo_transacao,
    ':serie_documento' => $serie_documento,
];

// Adicionar condições opcionais
if (!empty($_POST['periodoRef'])) {
    $periodo_referencia = (int) $_POST['periodoRef'];
    $sql .= " AND d.nr_perref = :periodo_referencia";
    $bindings[':periodo_referencia'] = $periodo_referencia;
}
if (!empty($_POST['dtAnoRef'])) {
    $ano_referencia = (int) $_POST['dtAnoRef'];
    $sql .= " AND d.dt_anoref = :ano_referencia";
    $bindings[':ano_referencia'] = $ano_referencia;
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
$dataPrincipal = [];

while ($row = oci_fetch_assoc($stmt)) {
    $dataPrincipal[] = $row; // Adiciona cada linha ao array $dataPrincipal
}

// Verificar resultados
if (empty($dataPrincipal)) {
    $_SESSION['erroPrestador'] = "Dados não encontrados";
    header("Location: ../../dashboard.php");
    exit;
} else {
    unset($_SESSION['erroPrestador']); // Limpa sessão
    $_SESSION['dadosPrestador'] = $dataPrincipal;
    header("Location: ./");
    exit;
}

// Liberar recursos
oci_free_statement($stmt);
oci_close($conn);
