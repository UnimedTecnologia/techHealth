<?php
header('Content-Type: text/html; charset=utf-8');
session_start();

// Obter dados do formulário
$usuario = $_POST['usuario'];
$password = $_POST['senha'];

require_once "../config/AW00DB.php";
require_once "../config/oracle.class.php";
require_once "../config/AW00MD.php";

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

// Verificar se o usuário existe no banco de dados
$sql = "SELECT * FROM gp.th_usuario u WHERE usuario = :usuario";

$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':usuario', $usuario);
oci_execute($stmt);

$row = oci_fetch_assoc($stmt);

// Verificar se o usuário foi encontrado
if ($row) {
    // Se o usuário existe, comparar as senhas
    $storedPassword = $row['SENHA']; // Senha armazenada no banco (deve ser criptografada)
    
    // Verificar se a senha fornecida corresponde à senha armazenada
    if (password_verify($password, $storedPassword)) {
        // Senha correta, login bem-sucedido
        $_SESSION['usuario']   = $row['USUARIO'];
        $_SESSION['idusuario'] = $row['ID'];
        $_SESSION['nomeuser']  = $row['NOME'];
        $_SESSION['adm']       = $row['ADM'];

        if($password === 'unimed'){
            //!PRIMEIRO ACESSO - solicitar alteração de senha
            $_SESSION['primeiroAcessoTH'] = true;
            header("Location: ../index.php");
            exit();
            
        }else{

            // Redirecionar para o dashboard ou outra página
            header("Location: ../dashboard.php");
            exit();
        }
    } else {
        // Senha incorreta
        $_SESSION['erroLogin'] = 'Usuário ou senha incorretos!';
        header("Location: ../index.php");
        exit();
    }
} else {
    // Usuário não encontrado
    $_SESSION['erroLogin'] = 'Usuário ou senha incorretos!';
    header("Location: ../index.php");
    exit();
}

// Liberar recursos
oci_free_statement($stmt);
oci_close($conn);
?>
