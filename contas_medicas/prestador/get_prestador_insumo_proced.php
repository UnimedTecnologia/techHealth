<?php
header('Content-Type: text/html; charset=utf-8');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

unset($_SESSION['dadosPrestador']);
unset($_SESSION['dadosInsumos']);
unset($_SESSION['dadosProcedimento']);

$numero_documento = $_POST['numDoc'];
$codigo_prestador = (int) $_POST['codPrest'];
$codigo_transacao = (int) $_POST['codTrans'];
// $serie_documento = $_POST['serieDoc'];
$serie_documento = isset($_POST['serieDoc']) ? $_POST['serieDoc'] : null;

// Dividir a string em uma lista de valores
$numero_documentos = array_map('trim', explode(',', $numero_documento)); // ['123', '456', '789']

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

// Parâmetros comuns
// $bindings = [
//     // ':numero_documento' => $numero_documento,
//     ':codigo_prestador' => $codigo_prestador,
//     ':codigo_transacao' => $codigo_transacao,
//     ':serie_documento'  => $serie_documento,
// ];
// Parâmetros comuns
$bindings = [
    ':codigo_prestador' => $codigo_prestador,
    ':codigo_transacao' => $codigo_transacao,
];

// Adicionar série do documento apenas se foi fornecido
if (!empty($serie_documento)) {
    $bindings[':serie_documento'] = $serie_documento;
}

// Criar placeholders dinâmicos para o bind
$placeholders = [];
foreach ($numero_documentos as $key => $doc) {
    $placeholder = ":numero_documento_" . $key;
    $placeholders[] = $placeholder;
    $bindings[$placeholder] = $doc;
}

// Condições adicionais
$additionalConditions = [
    [
        'query' => 'A.nr_perref = :periodo_referencia',
        'bindKey' => ':periodo_referencia',
        'value' => !empty($_POST['periodoRef']) ? $_POST['periodoRef'] : null,
    ],
    [
        'query' => 'A.dt_anoref = :ano_referencia',
        'bindKey' => ':ano_referencia',
        'value' => !empty($_POST['dtAnoRef']) ? $_POST['dtAnoRef'] : null,
    ],
];

// ** Primeira consulta PRINCIPAL **
// $baseQuery1 = "
//     SELECT A.NR_PERREF, A.DT_ANOREF, A.NR_DOC_ORIGINAL, A.NR_DOC_SISTEMA, 
//            A.CD_PRESTADOR_PRINCIPAL, A.CD_TRANSACAO, A.NR_SERIE_DOC_ORIGINAL
//     FROM gp.docrecon A
//     WHERE A.nr_doc_original IN (" . implode(',', $placeholders) . ")
//       AND A.cd_prestador_principal = :codigo_prestador
//       AND A.cd_transacao = :codigo_transacao
//       AND A.nr_serie_doc_original = :serie_documento ";

// list($sql1, $bindings1) = buildQuery($baseQuery1, $bindings, $additionalConditions);
$baseQuery1 = "
    SELECT A.NR_PERREF, A.DT_ANOREF, A.NR_DOC_ORIGINAL, A.NR_DOC_SISTEMA, 
           A.CD_PRESTADOR_PRINCIPAL, A.CD_TRANSACAO, A.NR_SERIE_DOC_ORIGINAL
    FROM gp.docrecon A
    WHERE A.nr_doc_original IN (" . implode(',', $placeholders) . ")
      AND A.cd_prestador_principal = :codigo_prestador
      AND A.cd_transacao = :codigo_transacao";

// Adicionar condição da série do documento apenas se foi fornecido
if (!empty($serie_documento)) {
    $baseQuery1 .= " AND A.nr_serie_doc_original = :serie_documento";
}

list($sql1, $bindings1) = buildQuery($baseQuery1, $bindings, $additionalConditions);


// ** Segunda consulta INSUMOS **
// $baseQuery2 = "
//     SELECT A.CD_TRANSACAO, A.NR_SERIE_DOC_ORIGINAL, A.NR_DOC_ORIGINAL, A.NR_DOC_SISTEMA, A.CD_TIPO_VINCULO, A.CD_PRESTADOR, 
//     A.CD_PRESTADOR_PAGAMENTO , A.NR_PERREF, A.DT_ANOREF, to_char (CD_INSUMO) as PROC_INSU
//     FROM gp.MOV_INSU A 
//     WHERE A.nr_doc_original IN (" . implode(',', $placeholders) . ")
//       AND A.cd_prestador = :codigo_prestador
//       AND A.cd_transacao = :codigo_transacao
//       AND A.nr_serie_doc_original = :serie_documento ";

// list($sql2, $bindings2) = buildQuery($baseQuery2, $bindings, $additionalConditions);
$baseQuery2 = "
    SELECT A.CD_TRANSACAO, A.NR_SERIE_DOC_ORIGINAL, A.NR_DOC_ORIGINAL, A.NR_DOC_SISTEMA, A.CD_TIPO_VINCULO, A.CD_PRESTADOR, 
    A.CD_PRESTADOR_PAGAMENTO , A.NR_PERREF, A.DT_ANOREF, to_char (CD_INSUMO) as PROC_INSU
    FROM gp.MOV_INSU A 
    WHERE A.nr_doc_original IN (" . implode(',', $placeholders) . ")
      AND A.cd_prestador = :codigo_prestador
      AND A.cd_transacao = :codigo_transacao";

// Adicionar condição da série do documento apenas se foi fornecido
if (!empty($serie_documento)) {
    $baseQuery2 .= " AND A.nr_serie_doc_original = :serie_documento";
}

list($sql2, $bindings2) = buildQuery($baseQuery2, $bindings, $additionalConditions);

// ** Terceira consulta PROCEDIMENTO **
// $baseQuery3 = "
//     SELECT A.CD_TRANSACAO, A.NR_SERIE_DOC_ORIGINAL, A.NR_DOC_ORIGINAL, A.NR_DOC_SISTEMA,
//            A.CD_TIPO_VINCULO, A.CD_PRESTADOR, A.CD_PRESTADOR_PAGAMENTO, A.NR_PERREF, 
//            A.DT_ANOREF, A.CD_PRESTADOR_VALIDA, 
//            A.CD_ESP_AMB || A.CD_GRUPO_PROC_AMB || A.CD_PROCEDIMENTO || A.DV_PROCEDIMENTO AS PROCEDIMENTO
//     FROM gp.MOVIPROC A
//     WHERE A.nr_doc_original IN (" . implode(',', $placeholders) . ")
//       AND A.cd_prestador = :codigo_prestador
//       AND A.cd_transacao = :codigo_transacao
//       AND A.nr_serie_doc_original = :serie_documento ";

// list($sql3, $bindings3) = buildQuery($baseQuery3, $bindings, $additionalConditions);
$baseQuery3 = "
    SELECT A.CD_TRANSACAO, A.NR_SERIE_DOC_ORIGINAL, A.NR_DOC_ORIGINAL, A.NR_DOC_SISTEMA,
           A.CD_TIPO_VINCULO, A.CD_PRESTADOR, A.CD_PRESTADOR_PAGAMENTO, A.NR_PERREF, 
           A.DT_ANOREF, A.CD_PRESTADOR_VALIDA, 
           A.CD_ESP_AMB || A.CD_GRUPO_PROC_AMB || A.CD_PROCEDIMENTO || A.DV_PROCEDIMENTO AS PROCEDIMENTO
    FROM gp.MOVIPROC A
    WHERE A.nr_doc_original IN (" . implode(',', $placeholders) . ")
      AND A.cd_prestador = :codigo_prestador
      AND A.cd_transacao = :codigo_transacao";

// Adicionar condição da série do documento apenas se foi fornecido
if (!empty($serie_documento)) {
    $baseQuery3 .= " AND A.nr_serie_doc_original = :serie_documento";
}

list($sql3, $bindings3) = buildQuery($baseQuery3, $bindings, $additionalConditions);

try {
    $dataPrincipal    = executeQuery($conn, $sql1, $bindings1);
    $dataInsumos      = executeQuery($conn, $sql2, $bindings2);
    $dataProcedimento = executeQuery($conn, $sql3, $bindings3);

    if (empty($dataPrincipal)) {
        $_SESSION['erroPrestador'] = "Dados não encontrados";
        header("Location: ../../dashboard.php");
        exit;
    }

    unset($_SESSION['erroPrestador']);
    $_SESSION['dadosPrestador'] = $dataPrincipal;
    $_SESSION['dadosInsumos'] = $dataInsumos;
    $_SESSION['dadosProcedimento'] = $dataProcedimento;
    //!SE NÃO ESTIVER SETADO em update_prestadorPrincipal.php
    if(!isset($_POST['update_prestador'])){
        header("Location: ./");
        exit;
    }
    
} catch (Exception $e) {
    echo $e->getMessage();
    exit;
}


//! FUNÇÕES
function buildQuery($baseQuery, $bindings, $additionalConditions = []) {
    // Adicionar condições opcionais
    foreach ($additionalConditions as $key => $condition) {
        if (!empty($condition['value'])) {
            $baseQuery .= " AND {$condition['query']}";
            $bindings[$condition['bindKey']] = $condition['value'];
        }
    }

    return [$baseQuery, $bindings];
}


function executeQuery($conn, $sql, $bindings) {
    $stmt = oci_parse($conn, $sql);

    if (!$stmt) {
        $e = oci_error($conn);
        throw new Exception("Erro ao preparar a consulta: " . htmlentities($e['message'], ENT_QUOTES));
    }

    foreach ($bindings as $key => $value) {
        oci_bind_by_name($stmt, $key, $bindings[$key]);
    }

    $result = oci_execute($stmt);

    if (!$result) {
        $e = oci_error($stmt);
        throw new Exception("Erro ao executar a consulta: " . htmlentities($e['message'], ENT_QUOTES));
    }

    $data = [];
    while ($row = oci_fetch_assoc($stmt)) {
        $data[] = $row;
    }

    oci_free_statement($stmt);
    return $data;
}

