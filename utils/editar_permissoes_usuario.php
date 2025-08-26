<?php
header('Content-Type: text/html; charset=utf-8');
session_start();

// Validar entrada
$idusuario = isset($_POST['usuarios']) ? $_POST['usuarios'] : null;
$idtelas = isset($_POST['telas']) ? $_POST['telas'] : [];

if (is_array($idusuario)) {
    // Caso `usuarios` seja um array, pegue o primeiro valor
    $idusuario = reset($idusuario);
}

// Garantir que `$idusuario` é numérico
if (!is_numeric($idusuario)) {
    $response = [
        'error' => true,
        'message' => 'ID do usuário inválido. Selecione um usuário',
        'type' => 'error'
    ];
    $_SESSION['retornoEditarPermissoes'] = $response;
    header("Location: ../dashboard.php");
    exit;
}

// Restante do código
require_once "../config/AW00DB.php";
require_once "../config/oracle.class.php";
require_once "../config/AW00MD.php";

$db = new oracle();
$conn = $db->connect($db_host, $db_user, $db_pwd, $db_name, $pconnect);

oci_set_call_timeout($conn, 30000); // 30 segundos de timeout

if (!$conn) {
    $e = oci_error();
    // echo "Erro de conexão: " . htmlentities($e['message'], ENT_QUOTES);
    $response = [
        'error' => true,
        'message' => "Erro de conexão: " . htmlentities($e['message'], ENT_QUOTES),
        'type' => 'error'
    ];
    $_SESSION['retornoEditarPermissoes'] = $response;
    header("Location: ../dashboard.php");
    exit;
}

try {
    // Iniciar transação
    oci_execute(oci_parse($conn, 'SAVEPOINT before_delete'), OCI_NO_AUTO_COMMIT);

    //* 1. Deletar permissões do usuário
    $sqlDelete = "DELETE FROM gp.th_permissoes WHERE idusuario = :idusuario";
    $stmtDelete = oci_parse($conn, $sqlDelete);

    if (!$stmtDelete) {
        throw new Exception("Erro ao preparar consulta de exclusão: " . htmlentities(oci_error($conn)['message'], ENT_QUOTES));
    }

    oci_bind_by_name($stmtDelete, ':idusuario', $idusuario, SQLT_INT);
    if (!oci_execute($stmtDelete, OCI_NO_AUTO_COMMIT)) {
        throw new Exception("Erro ao executar exclusão: " . htmlentities(oci_error($stmtDelete)['message'], ENT_QUOTES));
    }

    //* 2. Inserir novas permissões
    if (!empty($idtelas) && is_array($idtelas)) {
        $sqlInsert = "INSERT INTO gp.th_permissoes (idusuario, idtelas) VALUES (:idusuario, :idtelas)";
        $stmtInsert = oci_parse($conn, $sqlInsert);

        if (!$stmtInsert) {
            throw new Exception("Erro ao preparar consulta de inserção: " . htmlentities(oci_error($conn)['message'], ENT_QUOTES));
        }

        foreach ($idtelas as $idtela) {
            if (!is_numeric($idtela)) {
                throw new Exception("ID de tela inválido: " . htmlentities($idtela, ENT_QUOTES));
            }

            oci_bind_by_name($stmtInsert, ':idusuario', $idusuario, SQLT_INT);
            oci_bind_by_name($stmtInsert, ':idtelas', $idtela, SQLT_INT);
            if (!oci_execute($stmtInsert, OCI_NO_AUTO_COMMIT)) {
                throw new Exception("Erro ao inserir permissões: " . htmlentities(oci_error($stmtInsert)['message'], ENT_QUOTES));
            }
        }
    }

    // Commitar alterações
    oci_commit($conn);

    $response = [
        'error' => false,
        'message' => 'Permissões atualizadas com sucesso.',
        'type' => 'success'
    ];
} catch (Exception $e) {
    // Rollback em caso de erro
    oci_rollback($conn);

    $response = [
        'error' => true,
        'message' => $e->getMessage(),
        'type' => 'error'
    ];
} finally {
    // Liberar recursos
    if (isset($stmtDelete)) oci_free_statement($stmtDelete);
    if (isset($stmtInsert)) oci_free_statement($stmtInsert);
    oci_close($conn);
}

    // echo json_encode($response);
    $_SESSION['retornoEditarPermissoes'] = $response;
    header("Location: ../dashboard.php");
    exit;


