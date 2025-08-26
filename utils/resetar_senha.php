<?php

$senha      = 'unimed'; // Senha padrão

// Criptografando a senha
$senha_cripto = password_hash($senha, PASSWORD_DEFAULT);

echo $senha_cripto;
?>
