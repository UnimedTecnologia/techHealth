<?php
header('Content-Type: text/html; charset=utf-8');
session_start();

$nome       = $_POST['nome'];
$usuario    = $_POST['usuario'];
$adm        = isset($_POST['adm']) ? 'S' : 'N'; // Definindo 'S' ou 'N' dependendo da checkbox
$telas      = isset($_POST['telas']) ? $_POST['telas'] : []; // Pegando as telas selecionadas
$senha      = 'unimed'; // Senha padrão
$idadm      = isset($_SESSION['idusuario']) ? $_SESSION['idusuario'] : 0;

// Criptografando a senha
$senha_cripto = password_hash($senha, PASSWORD_DEFAULT);

require_once "../config/AW00DB.php";
require_once "../config/oracle.class.php";
require_once "../config/AW00MD.php";

//!VERIFICA SE USUARIO JÁ FOI CADASTRADO
$sql = 'SELECT id FROM gp.th_usuario WHERE usuario = :usuario';
$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':usuario', $usuario);
$result = oci_execute($stmt);

if ($result) {
    // Verifica se a consulta retornou algum resultado
    if ($row = oci_fetch_assoc($stmt)) {
        // Usuário já cadastrado
        $response = [
            'error' => true,
            'message' => 'O usuário informado já está cadastrado no sistema.',
            'type' => 'warning',
        ];
        // echo json_encode($response);
        $_SESSION['retornoCadUser'] = $response;
        header("Location: ../dashboard.php");
        exit; // Encerra o script para evitar a inserção do mesmo usuário
    } 
} else {
    // Erro ao executar a consulta
    $error = oci_error($stmt);
    $response = [
        'error' => true,
        'message' => 'Erro ao verificar usuário no banco de dados: ' . $error['message'],
        'type' => 'danger',
    ];
    // echo json_encode($response);
    $_SESSION['retornoCadUser'] = $response;
    header("Location: ../dashboard.php");
    exit;
}

//! caso seja um novo usuario
// Liberar o statement
oci_free_statement($stmt);

$db = new oracle();

// Configurar codificação
oci_set_client_info($conn, 'UTF-8');

// Inserir o usuário na tabela th_usuario
$sql_usuario = "INSERT INTO gp.th_usuario (nome, usuario, senha, adm, idadm) VALUES (:nome, :usuario, :senha, :adm, :idadm)";
$stmt_usuario = oci_parse($conn, $sql_usuario);

if (!$stmt_usuario) {
    $e = oci_error($conn);
    echo "Erro ao preparar a consulta de inserção do usuário: " . htmlentities($e['message'], ENT_QUOTES);
    exit;
}

// Associando os parâmetros
oci_bind_by_name($stmt_usuario, ':nome', $nome);
oci_bind_by_name($stmt_usuario, ':usuario', $usuario);
oci_bind_by_name($stmt_usuario, ':senha', $senha_cripto);
oci_bind_by_name($stmt_usuario, ':adm', $adm);
oci_bind_by_name($stmt_usuario, ':idadm', $idadm);

// Executar o insert
if (oci_execute($stmt_usuario, OCI_COMMIT_ON_SUCCESS)) {
    // Pegar o ID do usuário recém inserido
    $sql_id_usuario = "SELECT MAX(id) AS idusuario FROM gp.th_usuario WHERE usuario = :usuario";
    $stmt_id_usuario = oci_parse($conn, $sql_id_usuario);

    oci_bind_by_name($stmt_id_usuario, ':usuario', $usuario);
    oci_execute($stmt_id_usuario);
    $row = oci_fetch_assoc($stmt_id_usuario);
    $idusuario = $row['IDUSUARIO'];

    // Agora inserir as permissões de telas na tabela th_permissoes
    if (!empty($telas)) {
        foreach ($telas as $idtela) {
            $sql_permissao = "INSERT INTO gp.th_permissoes (idusuario, idtelas) VALUES (:idusuario, :idtela)";
            $stmt_permissao = oci_parse($conn, $sql_permissao);

            oci_bind_by_name($stmt_permissao, ':idusuario', $idusuario);
            oci_bind_by_name($stmt_permissao, ':idtela', $idtela);

            // Executar o insert para cada tela
            oci_execute($stmt_permissao);
        }
    }

    // Commitar as alterações
    oci_commit($conn);

    // Resposta de sucesso
    $response = [
        'error' => false,
        'message' => 'Usuário cadastrado com sucesso.',
        'type' => 'success',
    ];
    echo json_encode($response);
} else {
    $error = oci_error($stmt_usuario);
    // Em caso de erro
    $response = [
        'error' => true,
        'message' => 'Erro ao incluir Usuário: ' . $error['message'],
        'code' => $error['code'],
        'sql' => $error['sqltext'],
        'type' => 'danger',
    ];
    echo json_encode($response);
}

// Liberar recursos
oci_free_statement($stmt_usuario);
if (isset($stmt_id_usuario)) {
    oci_free_statement($stmt_id_usuario);
}
if (isset($stmt_permissao)) {
    oci_free_statement($stmt_permissao);
}

// Fechar a conexão
oci_close($conn);


$_SESSION['retornoCadUser'] = $response;
header("Location: ../dashboard.php");
exit();
?>
