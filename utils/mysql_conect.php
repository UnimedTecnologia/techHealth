<?php
ini_set("display_errors", 1);
error_reporting(E_ALL);

// $host = "192.168.0.21"; 
// $user = "admin";
// $pass = "2666328ddconn";
$host = "localhost"; 
$user = "root";
$pass = "";
$db = "intranet";

$conexao = mysqli_connect($host, $user, $pass);
if (!$conexao) {
    $_SESSION['error'] = "Erro ao conectar ao servidor: ". mysqli_connect_error();
    header("Location: ../index.php");
    exit();
    // die("Erro ao conectar: " . mysqli_connect_error());
}

$banco = mysqli_select_db($conexao, $db);
if (!$banco) {
    $_SESSION['error'] = "Erro ao conectar ao servidor: ". mysqli_error($conexao);
    header("Location: ../index.php");
    exit();
    // die("Erro ao selecionar o banco de dados: " . mysqli_error($conexao));
}

mysqli_set_charset($conexao, 'utf8');
// echo "Conexão bem-sucedida!";
?>
