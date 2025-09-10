<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

require_once "config/AW00DB.php"; 
require_once "config/oracle.class.php";
require_once "config/AW00MD.php";

$idusuario = $_POST['id'] ?? null;

if (!$idusuario) {
    echo json_encode([
        "status" => "erro",
        "mensagem" => "ID do usuário não informado."
    ]);
    exit;
}

// Conexão Oracle
$db = new oracle();
$conn = $db->connect($db_host, $db_user, $db_pwd, $db_name, $pconnect);

// Senha padrão do sistema
$novaSenha = "unimed";

// Criptografa a senha antes de salvar
// Exemplo usando SHA256 (ajuste se já usa outra forma de hash)
$hash = password_hash($novaSenha, PASSWORD_DEFAULT);

$sql = "UPDATE gp.th_usuario 
           SET senha = :senha
         WHERE id = :id";

$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ":senha", $hash);
oci_bind_by_name($stmt, ":id", $idusuario);

if (oci_execute($stmt)) {
    echo json_encode([
        "status" => "ok",
        "mensagem" => "Senha resetada para: $novaSenha"
    ]);
} else {
    $e = oci_error($stmt);
    echo json_encode([
        "status" => "erro",
        "mensagem" => "Erro ao resetar senha: " . $e['message']
    ]);
}

oci_free_statement($stmt);
oci_close($conn);
