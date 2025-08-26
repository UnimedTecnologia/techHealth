<?php
header('Content-Type: text/html; charset=utf-8');
session_start();

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Situação Cadastral</title>

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="../style.css" rel="stylesheet"/>

    <style>
    table {
        word-wrap: break-word;
        table-layout: auto;
    }

    th, td {
        text-align: center;
        vertical-align: middle;
    }

    .table-responsive {
        margin: auto;
        max-width: 100%;
    }
</style>



</head>
<body>
    <?php include_once "../nav.php"; // CABEÇALHO NAVBAR ?>

    <div class="d-flex align-items-center mt-100 mb-2">
        <div style="position: absolute; left: 10px;">
            <button class="btn" onclick="location.href='../dashboard.php'" style="background-color: #008e55; color: white; margin-left: 10px;">
                <i class="bi bi-arrow-left"></i> <!-- Ícone de voltar -->
            </button>
        </div>
        <div class="mx-auto text-center">
            <h3>Alterar Situação Cadastral</h3>                
        </div>
    </div>

    <div class="d-flex justify-content-center">
        <?php
        if (isset($_SESSION['dadosSituacaoCadastral']) && is_array($_SESSION['dadosSituacaoCadastral'])) {
            echo '<div class="table-responsive">';
            echo '<table class="table table-striped table-bordered">';

            //* Cabeçalhos
            $headers = [
                "NM_USUARIO" => "Nome Usuário",
                "CD_SIT_CADASTRO" => "Código Situação Cadastro",
                "CD_SIT_USUARIO" => "Código Situação Usuário",
                "CD_USUARIO" => "Código Usuário",
            ];

            //* Criar cabeçalhos da tabela
            echo '<thead class="table-dark"><tr>';
            foreach ($headers as $header) {
                echo '<th>' . htmlspecialchars($header) . '</th>';
            }
            echo '<th>Ações</th>'; //* Coluna para ações
            echo '</tr></thead>';

            //* Preencher os dados
            echo '<tbody>';
            foreach ($_SESSION['dadosSituacaoCadastral'] as $index => $row) {
                echo '<tr>';
                foreach (array_keys($headers) as $key) {
                    $value = $row[$key] ?? '-';

                    //* Alterar valor de CD_SIT_CADASTRO para 'Ativo' ou 'Inativo'
                    if ($key === "CD_SIT_CADASTRO") {
                        $value = $value === '1' ? 'Ativo' : ($value === '0' ? 'Inativo' : $value);
                    }

                    echo '<td>' . htmlspecialchars($value) . '</td>'; //* Exibe o valor ajustado
                }
                //* Botão de editar com atributos para o modal
                echo '<td>';
                echo '<button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAlterarSitCadastral"';
                echo ' onclick="preencherFormulario(' . htmlspecialchars(json_encode($row)) . ')"';
                echo '>';
                echo '<i class="bi bi-pencil-square"></i> Editar';
                echo '</button>';
                echo '</td>';
                echo '</tr>';
            }
            echo '</tbody>';

            echo '</table>';
            echo '</div>';

        } else {
            echo '<p class="text-center">Nenhum dado encontrado na sessão.</p>';
        }

        ?>

    </div>

    <script>
        function preencherFormulario(dados) {
            // Atualiza os campos visíveis do formulário
            document.getElementById('sitUsuario').value = dados.CD_SIT_USUARIO;

            // Preenche inputs hidden com outros dados
            let form = document.getElementById('alteraSituacaoUsuario');

            // Cria ou atualiza inputs hidden para os outros valores
            ['CD_MODALIDADE', 'NR_PROPOSTA', 'CD_USUARIO'].forEach(campo => {
                let input = form.querySelector(`input[name="${campo.toLowerCase()}"]`);
                if (!input) {
                    // Cria o input hidden se não existir
                    input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = campo.toLowerCase();
                    form.appendChild(input);
                }
                // Atualiza o valor
                input.value = dados[campo] || '';
            });
        }
</script>

    

    <!-- //! MODAL ALTERAR CODIGO SITUAÇÃO USUÁRIO -->
    <div class="modal fade" id="modalAlterarSitCadastral" tabindex="-1" aria-labelledby="modalAlterarSitCadastralLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAlterarSitCadastralLabel">Informe o código da situação do usuário</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="alteraSituacaoUsuario" action="alterarSituacaoCadastral.php" method="POST">

                        <div class="mb-3 form-floating-label">
                            <input type="text" id="sitUsuario" pattern="\d*" maxlength="4" name="sitUsuario" class="form-control inputback" placeholder=" " required >
                            <label for="sitUsuario" >Código Situação Usuário </label>
                        </div> 
                        <div class="text-center">
                            <div>
                                <button class="btn btn-success"> Alterar </button>
                            </div>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>

    <!-- //! retorno update -->
    <div class="text-center">
        <p>
            <?php
                if (isset($_SESSION['retornoAtualizacaoSituacao']) && is_array($_SESSION['retornoAtualizacaoSituacao'])) {
                    $retorno = $_SESSION['retornoAtualizacaoSituacao'];
                    $class = 'text-' . $retorno['type']; // 'text-danger' ou 'text-success'
                
                    echo "<span id='retorno' class='{$class}'>{$retorno['message']}</span>";
                    unset($_SESSION['retornoAtualizacaoSituacao']); // Limpar a sessão após exibir a mensagem
                    ?>
                    <!--//! EXIBE A MENSAGEM E OCULTA APOS 3s -->
                    <script>
                        document.addEventListener("DOMContentLoaded", function () {
        
                            setTimeout(function () {
                                var retorno = document.getElementById('retorno');
                                if (retorno) {
                                    retorno.style.display = 'none';
                                }
                            }, 3000);
                        });
                    </script>
                    <?php
                }
            ?>
        </p>
    </div>

    <p class="text-center">
        <?php
        if (isset($_SESSION['erroSituacaoCadastral'])) {
            echo "<span id='erroSituacaoCadastral' class='text-danger'>{$_SESSION['erroSituacaoCadastral']}</span>";
            ?>
            <script>
                document.addEventListener("DOMContentLoaded", function () {
                    setTimeout(function () {
                        var erroSituacaoCadastral = document.getElementById('erroSituacaoCadastral');
                        if (erroSituacaoCadastral) {
                            erroSituacaoCadastral.style.display = 'none';
                        }
                    }, 3000);
                });
            </script>
            <?php
            unset($_SESSION['erroSituacaoCadastral']); // Limpar a sessão após exibir a mensagem
        }
        ?>
    </p>


    <!-- //! ADICIONA NOVA OPÇÃO PARA FILTRAR -->
    <div class="container d-flex justify-content-center align-items-center flex-column mt-5" >
        <h4 class="text-center">Nova consulta</h4>
        <form id="formSituacaoCadastral"  action="getSituacaoCadastral.php" method="POST" class="w-100 p-3" style="max-width: 500px; border: solid 1px lightgray">
            <div class="mb-3 form-floating-label">
                <input id="cdModalidade" type="text" class="form-control inputback" name="cdModalidade" placeholder=" " maxlength="6" 
                oninput="this.value = this.value.replace(/[^0-9]/g, '')" >
                <label for="cdModalidade">Modalidade</label>
            </div>
            <div class="mb-3 form-floating-label">
                <input id="nrProposta" type="text" class="form-control inputback" name="nrProposta" placeholder=" " maxlength="6" 
                oninput="this.value = this.value.replace(/[^0-9]/g, '')" >
                <label for="nrProposta">Proposta</label>
            </div>
            <div class="mb-3 form-floating-label">
                <input id="cdUsuario" type="text" class="form-control inputback" name="cdUsuario" placeholder=" " maxlength="6" 
                oninput="this.value = this.value.replace(/[^0-9]/g, '')" >
                <label for="cdUsuario">Código Usuário</label>
            </div>
            <input name="novaConsulta" value="true" style="display:none">
            <div class="text-center mt-4">
                <button id="btnBuscarSituacao" type="submit" class="btn btn-success w-100">Pesquisar</button>
            </div>
        </form>
    </div>




           
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- <script src="index.js"></script> -->

</body>
</html>
