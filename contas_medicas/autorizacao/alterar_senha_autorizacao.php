<?php
header('Content-Type: text/html; charset=utf-8');
session_start();

$newpassword = $_POST['newpassword'];
$confpassword = $_POST['confpassword'];

// Validação básica
if ($newpassword !== $confpassword) {
    $_SESSION['retornoAutorizacao'] = ['type' => 'danger', 'message' => 'As senhas não conferem.'];
    header("Location: ./");
    exit();
}

$anoguia = (int) $_POST['anoguia'];
$numero_documento = (int) $_POST['numDoc'];
$codigo_transacao = (int) $_POST['codTrans'];
$codigo_prestador = (int) $_POST['codPrest'];
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

// UPDATE SQL
$sql = "UPDATE gp.docrecon 
        SET aa_guia_atendimento = :anoguia, nr_guia_atendimento = :newpassword
        WHERE nr_doc_original = :numero_documento 
        AND cd_transacao = :codigo_transacao
        AND nr_serie_doc_original = :serie_documento";


// Preparar consulta
$stmt = oci_parse($conn, $sql);

if (!$stmt) {
    $e = oci_error($conn);
    echo "Erro ao preparar a consulta: " . htmlentities($e['message'], ENT_QUOTES);
    exit;
}

// Bind das variáveis
oci_bind_by_name($stmt, ':newpassword', $newpassword);
oci_bind_by_name($stmt, ':anoguia', $anoguia);
oci_bind_by_name($stmt, ':numero_documento', $numero_documento);
oci_bind_by_name($stmt, ':codigo_transacao', $codigo_transacao);
oci_bind_by_name($stmt, ':serie_documento', $serie_documento);

if (oci_execute($stmt)) {
    $_SESSION['retornoAutorizacao'] = ['type' => 'success', 'message' => 'Senha alterada com sucesso.'];

    // Atualizar dados da sessão com os novos valores
    $selectSql = "SELECT char_22, cd_prestador_principal, cd_transacao, nr_serie_doc_original, nr_doc_original, nr_perref, 
               dt_anoref, aa_guia_atendimento, nr_guia_atendimento 
        FROM gp.docrecon d 
        WHERE d.nr_doc_original = :numero_documento
          AND d.cd_prestador_principal = :codigo_prestador
          AND d.cd_transacao = :codigo_transacao
          AND d.nr_serie_doc_original = :serie_documento";

    $selectStmt = oci_parse($conn, $selectSql);
    if (!$selectStmt) {
        $e = oci_error($conn);
        echo "Erro ao preparar a consulta: " . htmlentities($e['message'], ENT_QUOTES);
        exit;
    }
    oci_bind_by_name($selectStmt, ':numero_documento', $numero_documento);
    oci_bind_by_name($selectStmt, ':codigo_prestador', $codigo_prestador);
    oci_bind_by_name($selectStmt, ':codigo_transacao', $codigo_transacao);
    oci_bind_by_name($selectStmt, ':serie_documento', $serie_documento);
    // Executar consulta
    $result = oci_execute($selectStmt);

    if (!$result) {
        $e = oci_error($selectStmt);
        echo "Erro ao executar a consulta: " . htmlentities($e['message'], ENT_QUOTES);
        exit;
    }

    $data = [];
    while ($row = oci_fetch_assoc($selectStmt)) {
        $data[] = $row;
    }
    $_SESSION['dadosAutorizacao'] = $data;


    header("Location: ./");
} else {
    $_SESSION['retornoAutorizacao'] = ['type' => 'danger', 'message' => 'Erro ao alterar senha.'];
    header("Location: ./");
}
exit();