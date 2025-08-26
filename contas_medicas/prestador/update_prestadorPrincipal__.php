<?php
header('Content-Type: text/html; charset=utf-8');
session_start();

$newPrestador  = $_POST['newPrestador'];
$oldPrestadorPrincipal  = $_POST['oldPrestadorPrincipal'];
$nrDocOriginal          = $_POST['nrDocOriginal'];
$transacao              = $_POST['transacao'];
$serieDoc               = $_POST['serieDoc'];

require_once "../../config/AW00DB.php";
require_once "../../config/oracle.class.php";
require_once "../../config/AW00MD.php";

$db = new oracle();
$conn = $db->connect($db_host, $db_user, $db_pwd, $db_name, $pconnect);

//* Verificar conexão
if (!$conn) {
    $e = oci_error();
    echo "Erro de conexão: " . htmlentities($e['message'], ENT_QUOTES);
    exit;
}

// Configurar codificação
oci_set_client_info($conn, 'UTF-8');

//* UPDATE PRINCIPAL
if (isset($_SESSION['dadosPrestador']) && is_array($_SESSION['dadosPrestador']) && !empty($_SESSION['dadosPrestador'])) {
    $sql = "UPDATE gp.DOCRECON D SET D.CD_PRESTADOR_PRINCIPAL = :newPrestador WHERE 
        D.CD_PRESTADOR_PRINCIPAL = :oldPrestadorPrincipal AND
        D.NR_DOC_ORIGINAL in ( :nrDocOriginal ) AND
        D.CD_TRANSACAO = :transacao AND
        D.NR_SERIE_DOC_ORIGINAL = :nrSerie";
}

//* UPDATE INSUMOS
if (isset($_SESSION['dadosInsumos']) && is_array($_SESSION['dadosInsumos']) && !empty($_SESSION['dadosInsumos'])) {
    $sql2 = "UPDATE gp.MOV_INSU D SET D.CD_PRESTADOR = :newPrestador, D.CD_PRESTADOR_PAGAMENTO = :newPrestador where
            D.CD_PRESTADOR = :oldPrestadorPrincipal AND
            D.NR_DOC_ORIGINAL in ( :nrDocOriginal ) AND
            D.CD_TRANSACAO = :transacao AND
            D.NR_SERIE_DOC_ORIGINAL = :nrSerie";
}

//* UPDATE PROCEDIMENTO
if (isset($_SESSION['dadosProcedimento']) && is_array($_SESSION['dadosProcedimento']) && !empty($_SESSION['dadosProcedimento'])) {
    $sql3 = "UPDATE gp.MOVIPROC D SET D.CD_PRESTADOR = :newPrestador, D.CD_PRESTADOR_PAGAMENTO = :newPrestador where
            D.CD_PRESTADOR = :oldPrestadorPrincipal AND
            D.NR_DOC_ORIGINAL in ( :nrDocOriginal ) AND
            D.CD_TRANSACAO = :transacao AND
            D.NR_SERIE_DOC_ORIGINAL = :nrSerie";
}

//* Bind de parâmetros
$bindings = [
    ':newPrestador'  => (int)$newPrestador,
    ':oldPrestadorPrincipal'  => (int)$oldPrestadorPrincipal,
    ':nrDocOriginal'          => (int)$nrDocOriginal,
    ':transacao'              => (int)$transacao,
    ':serieDoc'               => $serieDoc,
];

if (!empty($_POST['nrPeriodo'])) {
    $nrPeriodo   = $_POST['nrPeriodo'];
    $sql .= "AND D.NR_PERREF = :nrPeriodo";
    $sql2 .= "AND D.NR_PERREF = :nrPeriodo";
    $sql3 .= "AND D.NR_PERREF = :nrPeriodo";
    $bindings[':nrPeriodo'] = $nrPeriodo;
}
if (!empty($_POST['anoRef'])) {
    $anoRef   = $_POST['anoRef'];
    $sql .= "AND D.DT_ANOREF = :anoRef";
    $sql2 .= "AND D.DT_ANOREF = :anoRef";
    $sql3 .= "AND D.DT_ANOREF = :anoRef";
    $bindings[':anoRef'] = $anoRef;
}


//* Preparar consulta
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
        'message' => 'Prestador principal alterado com sucesso.',
        'type' => 'success',
    ];
    echo json_encode($response);
    
} else {

    $response = [
        'error' => true,
        'message' => 'Erro ao alterar prestador principal.',
        'type' => 'danger'

    ];
    echo json_encode($response);
}

$dataAtual = date("d/m/Y"); // Formato: dia/mês/ano

// se atualizar a tabela de Insumo faz update na historico de Insumos
//* UPDATE HISTORICO INSUMOS
$sqlHistInsu = "UPDATE gp.HISTOR_MOVIMEN_INSUMO 
    SET cd_prestador = :newPrestador,
    dt_atualizacao = $dataAtual, 
    cd_userid = ".$_SESSION['user_id']." ";

//se atualizar a tabela de Procedimento faz update na historico de Procedimento
//* UPDATE HISOTICO PROCEDIMENTO
$sqlHistProced = "UPDATE gp.HISTOR_MOVIMEN_PROCED
    SET cd_prestador = :newPrestador,
    dt_atualizacao = $dataAtual, 
    cd_userid = ".$_SESSION['user_id']." ";
