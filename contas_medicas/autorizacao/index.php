<?php
header('Content-Type: text/html; charset=utf-8');
session_start();

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autorizacao</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../style.css" rel="stylesheet"/>

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
    <?php include_once "../../nav.php"; // CABEÇALHO NAVBAR ?>

    <div class="d-flex align-items-center mt-100 mb-2" >
        <div style="position: absolute; left: 10px;">
            <button class="btn" onclick="location.href='../../dashboard.php'" style="background-color: #008e55; color: white; margin-left: 10px;">
                <i class="bi bi-arrow-left"></i> <!-- Ícone de voltar -->
            </button>
        </div>
        <div class="mx-auto text-center">
            <h3>Autorização</h3>                
        </div>
    </div>

    <div class="d-flex justify-content-center">
        <?php
        if (isset($_SESSION['dadosAutorizacao']) && is_array($_SESSION['dadosAutorizacao'])) {
            echo '<div class="table-responsive">';
            echo '<table class="table table-striped table-bordered">';

            // Cabeçalhos amigáveis
            $headers = [
                "CD_PRESTADOR_PRINCIPAL" => "Código Prestador",
                "CD_TRANSACAO" => "Código Transação",
                "NR_SERIE_DOC_ORIGINAL" => "Número Série",
                "NR_DOC_ORIGINAL" => "Número Documento",
                "NR_PERREF" => "Número Período Ref.",
                "DT_ANOREF" => "Data Ano Ref.",
                "AA_GUIA_ATENDIMENTO" => "AA Guia",
                "NR_GUIA_ATENDIMENTO" => "Nr Guia"
            ];

            // Criar cabeçalhos da tabela
            echo '<thead class="table-dark"><tr>';
            foreach ($headers as $header) {
                echo '<th>' . htmlspecialchars($header) . '</th>';
            }
            echo '</tr></thead>';

            // Preencher os dados
            echo '<tbody>';
            foreach ($_SESSION['dadosAutorizacao'] as $row) {
                echo '<tr>';
                foreach (array_keys($headers) as $key) {
                    echo '<td>' . htmlspecialchars($row[$key] ?? '-') . '</td>'; // Exibe "-" se o valor não existir
                }
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
    
    <!-- BOTÃO ALTERAR SENHA -->
    <div class="text-center mt-3">
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalSenha">Alterar Senha</button>
    </div>
    <div class="text-center">
        <p>
        <?php
        if (isset($_SESSION['retornoAutorizacao'])) {
            // Verifica o tipo de retorno (sucesso ou erro)
            $retorno = $_SESSION['retornoAutorizacao'];
            $class = 'text-' . $retorno['type']; // 'text-danger' ou 'text-success'
    
            echo "<span id='retorno' class='{$class}'>{$retorno['message']}</span>";

            unset($_SESSION['retornoAutorizacao']); // Limpar a sessão após exibir a mensagem
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
    <!-- MODAL ALTERAR SENHA-->
    <div class="modal fade" id="modalSenha" tabindex="-1" aria-labelledby="modalSenhaLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalSenhaLabel">Informe os dados</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="alterar_senha_autorizacao.php" method="POST">
                        <div class="align-items-center">
                            <div class="mb-3 form-floating-label">
                                <input id="anoguia" type="text" pattern="\d*" class="form-control inputback" name="anoguia" placeholder=" " required >
                                <label for="anoguia">Ano guia</label>
                            </div>
                        
                            <div class="mb-3 form-floating-label">
                                <input id="newpassword" type="text" pattern="\d*" inputmode="numeric" class="form-control inputback" name="newpassword" placeholder=" " required title="A senha deve ser formada por números">
                                <label for="newpassword">Senha</label>
                            </div>
                        
                            <div class="mb-3 form-floating-label">
                                <input id="confpassword" type="text" pattern="\d*" inputmode="numeric" class="form-control inputback" name="confpassword" placeholder=" " required title="A senha deve ser formada por números">
                                <label for="confpassword">Confirmar Senha</label>
                            </div>
                        </div>
                         
                         <!-- Passando dados da sessão para as labels -->
                         <div class="mb-3">
                            <label name="codPrest"><b>Código Prestador:</b> <?php echo htmlspecialchars($_SESSION['dadosAutorizacao'][0]['CD_PRESTADOR_PRINCIPAL'] ?? ''); ?></label> 
                         </div>
                         <div class="mb-3">
                            <label name="numDoc"><b>Número Documento:</b> <?php echo htmlspecialchars($_SESSION['dadosAutorizacao'][0]['NR_DOC_ORIGINAL'] ?? ''); ?></label>
                         </div>
                         <div class="mb-3">
                            <label name="codTrans"><b>Código Transação:</b> <?php echo htmlspecialchars($_SESSION['dadosAutorizacao'][0]['CD_TRANSACAO'] ?? ''); ?></label>  
                         </div>
                         <div class="mb-3">
                            <label name="serieDoc"><b>Série Documento:</b> <?php echo htmlspecialchars($_SESSION['dadosAutorizacao'][0]['NR_SERIE_DOC_ORIGINAL'] ?? ''); ?></label> 
                         </div>
                       

                        <!-- Dados ocultos para enviar ao PHP -->
                        <input type="hidden" name="numDoc" value="<?php echo htmlspecialchars($_SESSION['dadosAutorizacao'][0]['NR_DOC_ORIGINAL'] ?? ''); ?>">
                        <input type="hidden" name="codTrans" value="<?php echo htmlspecialchars($_SESSION['dadosAutorizacao'][0]['CD_TRANSACAO'] ?? ''); ?>">
                        <input type="hidden" name="serieDoc" value="<?php echo htmlspecialchars($_SESSION['dadosAutorizacao'][0]['NR_SERIE_DOC_ORIGINAL'] ?? ''); ?>">
                        <input type="hidden" name="codPrest" value="<?php echo htmlspecialchars($_SESSION['dadosAutorizacao'][0]['CD_PRESTADOR_PRINCIPAL'] ?? ''); ?>">

                        <div class="text-center">
                            <button type="submit" class="btn btn-success">Alterar Senha</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
        
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
   
    
</body>
</html>
