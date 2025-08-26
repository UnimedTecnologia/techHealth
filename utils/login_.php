<?php
header('Content-Type: text/html; charset=utf-8');
session_start();

// Obter dados do formulário
$matricula = $_POST['matricula'];
$password = $_POST['password'];

//! verifica se é diferente de 123 (TESTE)
if ($matricula != 123 && $matricula != 12345) {

    require_once "../config/AW00DB.php";
    require_once "../config/oracle.class.php";
    require_once "../config/AW00MD.php";

    //require_once __DIR__ . '/../vendor/autoload.php';
    //use Laminas\Crypt\Password\Bcrypt;

    // Configurar a conexão com o Oracle
    $db = new oracle();
    $conn = $db->connect($db_host, $db_user, $db_pwd, $db_name, $pconnect);

    oci_set_client_identifier($conn, 'UTF-8'); // Garante codificação UTF-8

    // Consulta SQL para autenticação
    $sql = "SELECT U.COD_USUARIO, U.NOM_USUARIO, U.COD_SENHA 
        FROM EMSFND.USUAR_MESTRE U 
        WHERE U.DAT_FIM_VALID > TRUNC(SYSDATE) 
          AND U.COD_USUARIO NOT LIKE ('adm%') 
          AND U.COD_USUARIO = :matricula ";
    //AND U.COD_SENHA = '{$password}' "; //AND U.COD_SENHA = :senha


    $stmt = oci_parse($conn, $sql);

    // Associar os parâmetros
    oci_bind_by_name($stmt, ':matricula', $matricula);
    //oci_bind_by_name($stmt, ':senha', $password);

    if (!oci_execute($stmt)) {
        $e = oci_error($stmt);
        die("Erro ao executar a consulta: " . $e['message']);
    }


    // Executar a consulta
    // if (oci_execute($stmt, OCI_DEFAULT)) {
    if (oci_execute($stmt)) {
        $user = oci_fetch_assoc($stmt);

        if ($user) {

            $key = '123'; // Trocar pela chave usada na criptografia
            $iv = str_repeat("\0", 16); // Substitua pelo IV se aplicável
            $hash3 = base64_encode(openssl_encrypt($password, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv));
            $tam1 = strlen($hash3);
            $tam2 = strlen($user['COD_SENHA']);

            if (password_verify($password, $user['COD_SENHA'])) {
            // if (password_verify($hash3, $user['COD_SENHA'])) {
                die("Deu certo");
            }

            if ($password == $user['COD_SENHA']) {
                // Armazenar nome e código na sessão
                $_SESSION['user_name'] = $user['NOM_USUARIO'];
                $_SESSION['user_id'] = $user['COD_USUARIO'];

                //echo json_encode(["status" => "success", "message" => "Login bem-sucedido!"]);
                header("Location: ../dashboard.php");
                exit();

            } else {
                // Senha inválida
                $_SESSION['error'] = "Senha inválida";
                //echo json_encode(["status" => "error", "message" => "Credenciais inválidas"]);
                header("Location: ../index.php");
                exit();
            }
        } else {
            // Usuário não encontrado
            $_SESSION['error'] = "Credenciais inválidas";
            echo json_encode(["status" => "error", "message" => "Credenciais inválidas"]);
            header("Location: ../index.php");
            exit();
        }
    } else {
        // Erro ao executar a consulta
        $_SESSION['error'] = "Erro ao verificar as credenciais";
        //echo json_encode(["status" => "error", "message" => "Erro ao verificar as credenciais"]);
        header("Location: ../index.php");
        exit();
    }

    // Liberar recursos
    oci_free_statement($stmt);
    oci_close($conn);

} else { // Validação fictícia 

    
    if ($matricula === '123' && $password === '123') {//! EXEMPLO 1
        $_SESSION['user_id'] = 1; // Exemplo de ID do usuário
        $_SESSION['user_name'] = "Pedro Rodrigues"; // nome usuario
        header("Location: ../dashboard.php");
        exit();
    } else if ($matricula === '12345' && $password === '123'){ //! EXEMPLO 2
        $_SESSION['user_id'] = 2; // Exemplo de ID do usuário
        $_SESSION['user_name'] = "Gabriel Freitas"; // nome usuario
        header("Location: ../dashboard.php");
        exit();
        
    }else {
        // Login falhou
        $_SESSION['error'] = "Credenciais inválidas.";
        header("Location: ../index.php");
        exit();
        
    }
    
}