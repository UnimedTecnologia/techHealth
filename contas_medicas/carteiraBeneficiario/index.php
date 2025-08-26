<?php
header('Content-Type: text/html; charset=utf-8');
session_start();

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carteira Beneficiário</title>

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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

    <div id="loadingOverlay" style="display:none">
        <div class="spinner"></div>
        <p id="msgLoader">Carregando</p>
    </div>
    <div class="d-flex align-items-center mt-100 mb-2">
        <div style="position: absolute; left: 10px;">
            <button class="btn" onclick="location.href='../../dashboard.php'" style="background-color: #008e55; color: white; margin-left: 10px;">
                <i class="bi bi-arrow-left"></i> <!-- Ícone de voltar -->
            </button>
        </div>
        <div class="mx-auto text-center">
            <h3>Carteira Beneficiário</h3>                
        </div>
    </div>

    <div class="d-flex justify-content-center">
        <?php
        if (isset($_SESSION['dadosCarteira']) && is_array($_SESSION['dadosCarteira'])) {
            echo '<div class="table-responsive" style="zoom:90%">';
            echo '<table class="table table-striped table-bordered">';

            // Cabeçalhos amigáveis
            $headers = [
                "CD_MODALIDADE" => "Código Modalidade",
                "NR_TER_ADESAO" => "Termo Adesão",
                "CD_USUARIO" => "Código Usuário",
                "CD_SIT_CARTEIRA" => "Situação Carteira",
                "NM_USUARIO" => "Nome Usuário",
                "NR_PROPOSTA" => "Número Proposta",
                "CD_CPF" => "CPF",
                "CARTEIRA_COMPLETA" => "Carteira"
            ];

            // Criar cabeçalhos da tabela
            echo '<thead class="table-dark"><tr>';
            foreach ($headers as $header) {
                echo '<th>' . htmlspecialchars($header) . '</th>';
            }
            echo '<th>Ações</th>'; // Coluna para ações
            echo '</tr></thead>';

            // Preencher os dados
            echo '<tbody>';
            foreach ($_SESSION['dadosCarteira'] as $index => $row) {
                echo '<tr>';
                foreach (array_keys($headers) as $key) {
                    echo '<td>' . htmlspecialchars($row[$key] ?? '-') . '</td>'; // Exibe "-" se o valor não existir
                }
                // Botão de editar com atributos para o modal
                echo '<td>';
                echo '<button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAlterarCarteira"';
                echo ' data-index="' . htmlspecialchars($index) . '"'; // Passa o índice da linha
                foreach ($row as $key => $value) {
                    echo ' data-' . strtolower($key) . '="' . htmlspecialchars($value) . '"';
                }
                echo '>';
                echo '<i class="bi bi-pencil-square"></i> Usar';
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
    
    <div class="text-center">
        <p>
        <?php
        if (isset($_SESSION['retornoUpdateCarteira'])) {
            // Verifica o tipo de retorno (sucesso ou erro)
            $retorno = $_SESSION['retornoUpdateCarteira'];
            $class = 'text-' . $retorno['type']; // 'text-danger' ou 'text-success'
        
            echo "<span id='retorno' class='{$class}'>{$retorno['message']}</span>";

            unset($_SESSION['retornoUpdateCarteira']); // Limpar a sessão após exibir a mensagem
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

    <!-- MODAL ALTERAR CARTEIRA-->
    <div class="modal fade" id="modalAlterarCarteira" tabindex="-1" aria-labelledby="modalAlterarCarteiraLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAlterarCarteiraLabel">Informe os dados</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="verificaCarteiraForm" action="verifica_carteira_beneficiario.php" method="POST">

                        <div class="">
                            <label for="prestadorPrincipal" class="form-label">Prestador*</label>
                            <select id="prestadorPrincipal" name="prestadorPrincipal" class="custom-select" >
                                    <option value="" disabled selected>Prestador </option>
                                <option value="1"></option>
                            </select>
                        </div>
                        <!-- <div class="mb-3 form-floating-label">
                            <input type="text" id="prestadorPrincipal" name="prestadorPrincipal" class="form-control inputback" placeholder=" "  >
                            <label for="prestadorPrincipal" >Prestador <span style="color:red">*</span></label>
                        </div>  -->
                        <div class="mb-3 form-floating-label">
                            <input type="text" id="anoRef" pattern="\d*" maxlength="4" name="anoRef" class="form-control inputback" placeholder=" " required >
                            <label for="anoRef" >Ano Referência <span style="color:red">*</span></label>
                        </div> 
                        <div class="mb-3 form-floating-label">
                            <input type="text" id="periodoRef" pattern="\d*" maxlength="2"  name="periodoRef" class="form-control inputback" placeholder=" " required >
                            <label for="periodoRef" >Período Referência <span style="color:red">*</span></label>
                        </div>
                        <div class="mb-3 form-floating-label">
                            <input type="text" id="nrDocOrig"  name="nrDocOrig" class="form-control inputback" placeholder=" " required >
                            <label for="nrDocOrig" >Num. Doc Original <span style="color:red">*</span></label>
                        </div>
                        <div class="mb-3 form-floating-label">
                            <input type="text" id="codTransacao"  name="codTransacao" class="form-control inputback" placeholder=" "  >
                            <label for="codTransacao" >Código Transação</label>
                        </div>

                        <div class="text-center">
                            <button class="btn btn-success">
                                <i class="bi bi-search"></i> Pesquisar
                            </button>
                        </div>
                    </form>

                    <form id="alteraCartBenef" action="alterar_carteira_beneficiario.php" method="POST">
                        <div class="text-center">
                            <p class="mt-4">Sequência: <span id="codSequencia" ></span></p>
                            <!-- //! inputs ocultos p/ enviar formulario -->
                            <input type="hidden" id="hiddenCodSequencia" name="codSequencia">
                            <input type="hidden" id="hiddenprestadorPrincipal" name="prestadorPrincipal">
                            <input type="hidden" id="hiddenanoRef" name="anoRef">
                            <input type="hidden" id="hiddenperiodoRef" name="periodoRef">
                            <input type="hidden" id="hiddennrDocOrig" name="nrDocOrig">
                            <input type="hidden" id="hiddennrcodTransacao" name="codTransacao">
                        </div>
                        <!-- //!CODIGO UNIDADE -->
                        <div class="mt-3 d-flex align-items-center justify-content-center">
                            <div class="col-md-5">
                                <div class="">
                                    <label for="modalCodUnidade2">Código Unidade:</label>
                                    <input type="text" id="modalCodUnidade2" disabled name="codUnidade2" class="form-control inputback" placeholder=" " readonly>
                                    
                                </div>
                            </div>
                            <div class="mx-1 mt-3 text-center">
                                <p class="fs-3 m-0">→</p> <!-- Ajusta o tamanho e remove margens extras -->
                            </div>
                            <div class="col-md-5">
                                <div class="">
                                    <label for="modalCodUnidade">Código Unidade:</label>
                                    <input type="text" id="modalCodUnidade"  name="codUnidade" class="form-control inputback" placeholder=" " readonly>
                                    
                                </div>
                            </div>
                        </div>

                        <!-- //! CARTEIRA-->
                        <div class="mt-3 d-flex align-items-center justify-content-center">
                            <div class="col-md-5">
                                <div class="">
                                    <label for="modalCarteira2" >Carteira:</label>
                                    <input type="text" id="modalCarteira2" disabled name="carteira2" class="form-control inputback" placeholder=" " readonly >
                                    
                                </div>
                            </div>
                            <div class="mx-1 mt-3 text-center">
                                <p class="fs-3 m-0">→</p> 
                            </div>
                            <div class="col-md-5">
                                <div class="">
                                    <label for="modalCarteira" >Carteira:</label>
                                    <input type="text" id="modalCarteira"  name="carteira" class="form-control inputback" placeholder=" " readonly >
                                    
                                </div>
                            </div>
                        </div>

                        <!--//! CODIGO MODALIDADE-->
                        <div class="mt-3 d-flex align-items-center justify-content-center">
                            <div class="col-md-5">
                                <div class="">
                                    <label for="modalCodModalidade2" >Código Modalidade:</label>
                                    <input type="text" id="modalCodModalidade2" disabled name="codModalidade2" class="form-control inputback" placeholder=" " readonly>
                                    
                                </div>
                            </div>
                            <div class="mx-1 mt-3 text-center">
                                <p class="fs-3 m-0">→</p> 
                            </div>
                            <div class="col-md-5">
                                <div class="">
                                    <label for="modalCodModalidade" >Código Modalidade:</label>
                                    <input type="text" id="modalCodModalidade"  name="codModalidade" class="form-control inputback" placeholder=" " readonly>
                                    
                                </div>
                            </div>
                        </div>

                        <!--//! TERMO -->
                        <div class="mt-3 d-flex align-items-center justify-content-center">
                            <div class="col-md-5">
                                <div class="">
                                    <label for="modalTermo2" >Termo:</label>
                                    <input type="text" id="modalTermo2" disabled name="termo2" class="form-control inputback" readonly>
                                    
                                </div>
                            </div>
                            <div class="mx-1 mt-3 text-center">
                                <p class="fs-3 m-0">→</p> 
                            </div>
                            <div class="col-md-5">
                                <div class="">
                                    <label for="modalTermo" >Termo:</label>
                                    <input type="text" id="modalTermo"  name="termo" class="form-control inputback" readonly>
                                    
                                </div>
                            </div>
                        </div>

                        <!--//! CODIGO USUARIO -->
                        <div class="mt-3 d-flex align-items-center justify-content-center">
                            <div class="col-md-5">
                                <div class="">
                                    <label for="modalCodUsuario2" >Código Usuário:</label>
                                    <input type="text" id="modalCodUsuario2" disabled name="codUsuario2" class="form-control inputback" readonly>
                                    
                                    
                                </div>
                            </div>
                            <div class="mx-1 mt-3 text-center">
                                <p class="fs-3 m-0">→</p> 
                            </div>
                            <div class="col-md-5">
                                <div class="">
                                    <label for="modalCodUsuario" >Código Usuário:</label>
                                    <input type="text" id="modalCodUsuario"  name="codUsuario" class="form-control inputback" readonly>
                                                                    
                                </div>
                            </div>
                        </div>

                        <!--//! CHAR22 -->
                        <div class="mt-3 d-flex align-items-center justify-content-center">
                            <div class="col-md-5">
                                <div class="">
                                    <label for="modalChar222" >Char 22:</label>
                                    <input type="text" id="modalChar222" disabled name="modalChar222" class="form-control inputback" readonly>

                                </div>
                            </div>
                            <div class="mx-1 mt-3 text-center">
                                <p class="fs-3 m-0">→</p> 
                            </div>
                            <div class="col-md-5">
                                <div class="">
                                    <label for="modalChar22" >Char 22:</label>
                                    <input type="text" id="modalChar22"  name="modalChar22" class="form-control inputback" readonly>                            
                                                                    
                                </div>
                            </div>
                        </div>

                        <!-- Outros campos aqui -->
                        <div class="text-center mt-3">
                            <button id="btnAtualizarCarteira" type="submit" class="btn btn-success" disabled>
                                <i class="bi-arrow-clockwise"></i> Atualizar
                            </button>
                        </div>
                        <!-- //* Mensagem de retorno -->
                        <div class="text-center">
                            <p id="retornoUpdate" ></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- //! Modal DADOS CARTEIRA -->
    <!-- //* Somente quando tiver mais de 1 registro -->
    <div class="modal fade" id="dataModal" tabindex="-1" aria-labelledby="dataModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="dataModalLabel">Selecionar Registro</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered table-hover" id="dataTable">
                    <thead>
                        <tr>
                        <th>Carteira</th>
                        <th>Modalidade</th>
                        <th>Unidade</th>
                        <th>Documento Original</th>
                        <th>Sequência</th>
                        <th>Selecionar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Dados serão inseridos dinamicamente aqui -->
                    </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
       
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="index.js"></script>
    <script src="../../prestadores.js"></script>

</body>
</html>
