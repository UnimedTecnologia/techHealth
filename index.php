<?php
header('Content-Type: text/html; charset=utf-8');

session_start();
if (isset($_SESSION['idusuario']) && !isset($_SESSION['primeiroAcessoTH'])) {
    header("Location: dashboard.php"); // Redireciona para o painel
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="images/techealth.png" type="image/png">
    <title>Login</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="style.css" rel="stylesheet" />

</head>
<body >
<div class=" d-flex align-items-center" style="height: 100vh; background:#f5f5f5">
    <!-- Lado Esquerdo: Formulário -->
    <div class="col-12 col-md-6 d-flex justify-content-center align-items-center" >
        
        <div class="w-75">
            <!-- //! logo apenas mobile -->
            <div class="d-flex justify-content-center">
                <img src="images/techealth.png" class="img-fluid only-mobile" style="width: 160px; height: 90px">
            </div>
            <div class="mb-3 text-center" style="border: solid 1px white; background: #008E55; border-radius: 5px;">
                <h3 class="text-white">Login</h3>
            </div>
            <!-- <form action="utils/logar_adm.php" method="POST"> -->
            <form action="utils/login.php" method="POST">
                <div class="mb-3">
                    <label for="usuario" class="form-label">Usuário</label>
                    <input type="text" class="form-control" name="usuario" required>
                </div>
                <div class="mb-3">
                    <label for="senha" class="form-label">Senha</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password" name="senha" required>
                        <button type="button" id="togglePassword" class="btn btn-outline-secondary">
                            <i id="icon" class="bi bi-eye-slash"></i>
                        </button>
                    </div>
                </div>
                <?php if (isset($_SESSION['erroLogin'])): ?>
                    <p id="erroLogin" style="color: red; text-align: center;">
                        <?= htmlspecialchars($_SESSION['erroLogin']) ?>
                    </p>
                    <script>
                        document.addEventListener("DOMContentLoaded", function () {
                            setTimeout(() => {
                                var erroLogin = document.getElementById('erroLogin');
                                if (erroLogin) erroLogin.style.display = 'none';
                            }, 3000);
                        });
                    </script>
                <?php unset($_SESSION['erroLogin']); endif; ?>
                
                <!-- //! Verifica se é primeiro acesso -->
                <?php if (isset($_SESSION['primeiroAcessoTH'])) { ?>
                    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
                    <script>
                        document.addEventListener("DOMContentLoaded", function () {
                            Swal.fire({
                                title: 'Alterar Senha: Primeiro Acesso',
                                html: `
                                    <form>
                                        <div class="mb-3 text-start">
                                            <label for="novaSenha" class="form-label">Nova Senha:</label>
                                            <div class="input-group">
                                                <input type="password" id="novaSenha" class="form-control" placeholder="Digite a nova senha">
                                                <button id="btnVerNovaSenha" type="button" class="btn btn-outline-secondary toggle-password" data-target="novaSenha">
                                                    <i id="icoVerNovaSenha" class="bi bi-eye-slash"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="mb-3 text-start">
                                            <label for="confirmarSenha" class="form-label">Confirmar Senha:</label>
                                            <div class="input-group">
                                                <input type="password" id="confirmarSenha" class="form-control" placeholder="Confirme a nova senha">
                                                <button id="btnVerConfSenha" type="button" class="btn btn-outline-secondary toggle-password" data-target="confirmarSenha">
                                                    <i id="icoVerConfSenha" class="bi bi-eye-slash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                `,
                                showCancelButton: true,
                                confirmButtonText: 'Alterar',
                                cancelButtonText: 'Cancelar',
                                preConfirm: () => {
                                    const novaSenha = document.getElementById('novaSenha').value;
                                    const confirmarSenha = document.getElementById('confirmarSenha').value;

                                    if (!novaSenha || !confirmarSenha) {
                                        Swal.showValidationMessage('Por favor, preencha todos os campos.');
                                        return false;
                                    }

                                    if (novaSenha !== confirmarSenha) {
                                        Swal.showValidationMessage('As senhas não conferem.');
                                        return false;
                                    }

                                     // Verifica se a senha atende aos critérios de força
                                    const senhaForteRegex = /^(?=.*[!@#$%^&*(),.?":{}|<>])[A-Za-z\d!@#$%^&*(),.?":{}|<>]{6,}$/;
                                    if (!senhaForteRegex.test(novaSenha)) {
                                        Swal.showValidationMessage('A senha deve ter no mínimo 6 caracteres e pelo menos 1 caractere especial.');
                                        return false;
                                    }

                                    return { novaSenha };
                                }
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    const novaSenha = result.value.novaSenha;

                                    // Faz a requisição ao PHP para alterar a senha
                                    fetch('utils/alterar_senha_usuario.php', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/x-www-form-urlencoded',
                                        },
                                        body: `novaSenha=${encodeURIComponent(novaSenha)}`
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success) {
                                            // Swal.fire('Sucesso', 'Senha alterada com sucesso!', 'success');
                                            window.location.href = 'dashboard.php';
                                        } else {
                                            Swal.fire('Erro', data.message || 'Ocorreu um erro ao alterar a senha.', 'error');
                                        }
                                    })
                                    .catch(() => {
                                        Swal.fire('Erro', 'Não foi possível comunicar com o servidor.', 'error');
                                    });
                                }
                            });
                            
                            //* Alternar a visualização das senhas
                            setupTogglePassword('btnVerNovaSenha', 'novaSenha', 'icoVerNovaSenha');
                            setupTogglePassword('btnVerConfSenha', 'confirmarSenha', 'icoVerConfSenha');
                            
                        });
                    </script>
                    <?php //unset($_SESSION['primeiroAcessoTH']); 
                } ?>

                <button type="submit" class="btn btn-success w-100">Entrar</button>
               
            </form>

            <p class="cursor-pointer text-center mt-5">Esqueci a senha</p>

        </div>
    </div>

    <!-- Lado Direito: Imagem -->
    <div class="col-12 col-md-6 d-none d-md-block imagemLogin" style="" ></div>
</div>

    <script>
        //* Função para alternar a visualização da senha
        function setupTogglePassword(toggleId, inputId, iconId) {
            const toggleButton = document.getElementById(toggleId);
            const passwordField = document.getElementById(inputId);
            const icon = document.getElementById(iconId);

            toggleButton.addEventListener('click', function () {
                if (passwordField.type === 'password') {
                    passwordField.type = 'text';
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                } else {
                    passwordField.type = 'password';
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                }
            });
        }

        setupTogglePassword('togglePassword', 'password', 'icon');
    </script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

    
</body>
</html>
