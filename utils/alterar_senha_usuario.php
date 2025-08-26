<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

// Verifica se a requisição é POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verifica se o usuário está logado
    $idusuario = isset($_SESSION['idusuario']) ? $_SESSION['idusuario'] : 0;

    // Verifica se a nova senha foi enviada
    $novaSenha = isset($_POST['novaSenha']) ? $_POST['novaSenha'] : '';
    if (empty($idusuario) || empty($novaSenha)) {
        echo json_encode(['success' => false, 'message' => 'Dados insuficientes para alterar a senha.']);
        exit;
    }

    // Criptografando a nova senha
    $senha_cripto = password_hash($novaSenha, PASSWORD_DEFAULT);

    // Inclui as configurações e conexões necessárias
    require_once "../config/AW00DB.php";
    require_once "../config/oracle.class.php";
    require_once "../config/AW00MD.php";

    // SQL para atualização da senha
    $sql = 'UPDATE gp.th_usuario SET senha = :senha WHERE id = :idusuario';
    $stmt = oci_parse($conn, $sql);

    // Vincula os parâmetros da query
    oci_bind_by_name($stmt, ':senha', $senha_cripto);
    oci_bind_by_name($stmt, ':idusuario', $idusuario);

    // Executa a query
    if (oci_execute($stmt, OCI_COMMIT_ON_SUCCESS)) {
        oci_commit($conn);
        echo json_encode(['success' => true, 'message' => 'Senha alterada com sucesso!']);
    } else {
        $error = oci_error($stmt);
        echo json_encode(['success' => false, 'message' => 'Erro ao alterar a senha: ' . $error['message']]);
    }

    // Fecha a conexão com o banco
    oci_free_statement($stmt);
    oci_close($conn);
} else {
    echo json_encode(['success' => false, 'message' => 'Método inválido.']);
}
?>
