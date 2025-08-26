<?php
header('Content-Type: text/html; charset=utf-8');
session_start();

$carteirinha = isset($_GET["carteirinha"]) ? $_GET["carteirinha"] : null;

if ($carteirinha === null) {
    die("Carteirinha não informada!");
}

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
    <link href="../../../style.css" rel="stylesheet"/>

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
    .select2-container {
        z-index: 0 !important;
    }

    .select2-dropdown {
        z-index: 0 !important;
    }

</style>



</head>
<body>
    <?php include_once "../../../nav.php"; // CABEÇALHO NAVBAR ?>

    <div class="d-flex align-items-center mt-100 mb-2">
        <div style="position: absolute; left: 10px;">
            <button class="btn" onclick="location.href='../../../dashboard.php'" style="background-color: #008e55; color: white; margin-left: 10px;">
                <i class="bi bi-arrow-left"></i> <!-- Ícone de voltar -->
            </button>
        </div>
        <div class="mx-auto text-center">
            <h3>Carteira Beneficiário - Intercâmbio</h3>                
        </div>
    </div>
    
    <!-- MODAL ALTERAR CARTEIRA-->
    <div class="" style="max-width: 400px; margin: auto;">
        <form id="verificaCarteiraForm" class="d-flex flex-column align-items-center p-3" 
            action="../verifica_carteira_beneficiario.php" method="POST" >

            <select id="prestadorPrincipal" name="prestadorPrincipal" class="form-select w-100" >
                <option value="" disabled selected>Prestador</option>
                <option value="1"></option>
            </select>

            <div class=" form-floating-label w-100">
                <input type="text" id="anoRef" pattern="\d*" maxlength="4" name="anoRef" class="form-control inputback" placeholder=" " required>
                <label for="anoRef">Ano Referência <span style="color:red">*</span></label>
            </div>

            <div class=" form-floating-label w-100">
                <input type="text" id="periodoRef" pattern="\d*" maxlength="2" name="periodoRef" class="form-control inputback" placeholder=" " required>
                <label for="periodoRef">Período Referência <span style="color:red">*</span></label>
            </div>

            <div class=" form-floating-label w-100">
                <input type="text" id="nrDocOrig" name="nrDocOrig" class="form-control inputback" placeholder=" " required>
                <label for="nrDocOrig">Num. Doc Original <span style="color:red">*</span></label>
            </div>

            <div class="mb-3 form-floating-label w-100">
                <input type="text" id="codTransacao" name="codTransacao" class="form-control inputback" placeholder=" ">
                <label for="codTransacao">Código Transação</label>
            </div>

            <div class="text-center w-100">
                <button class="btn btn-success w-100">
                    <i class="bi bi-search"></i> Pesquisar
                </button>
            </div>

        </form>


        <form id="alteraCartBenef" action="alterar_carteira_intercambio.php" method="POST">
            <div class="text-center">
                <p class="mt-4 d-none">Sequência: <span id="codSequencia" ></span></p>
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
                <div class="col-md-5 w-100">
                    <div class="">
                        <label for="modalCodUnidade2">Código Unidade:</label>
                        <input type="text" id="modalCodUnidade2" disabled name="codUnidade2" class="form-control inputback" placeholder=" " readonly>
                        
                    </div>
                </div>
                <div class="mx-1 mt-3 text-center">
                    <p class="fs-3 m-0">→</p> <!-- Ajusta o tamanho e remove margens extras -->
                </div>
                <div class="col-md-5 w-100">
                    <div class="">
                        <label for="modalCodUnidade">Código Unidade:</label>
                        <input type="text" id="modalCodUnidade"  name="codUnidade" class="form-control inputback" placeholder=" " readonly>
                        
                    </div>
                </div>
            </div>

            <!-- //! CARTEIRA-->
            <div class="mt-3 d-flex align-items-center justify-content-center">
                <div class="col-md-5 w-100">
                    <div class="">
                        <label for="modalCarteira2" >Carteira:</label>
                        <input type="text" id="modalCarteira2" disabled name="carteira2" class="form-control inputback" placeholder=" " readonly >
                        
                    </div>
                </div>
                <div class="mx-1 mt-3 text-center">
                    <p class="fs-3 m-0">→</p> 
                </div>
                <div class="col-md-5 w-100">
                    <div class="">
                        <label for="modalCarteira" >Carteira:</label>
                        <input type="text" id="modalCarteira"  name="carteira" class="form-control inputback" placeholder=" " readonly >
                        
                    </div>
                </div>
            </div>

            <!--//! CHAR22 -->
            <div class="mt-3 d-flex align-items-center justify-content-center">
                <div class="col-md-5 w-100">
                    <div class="">
                        <label for="modalChar222" >Char 22:</label>
                        <input type="text" id="modalChar222" disabled name="modalChar222" class="form-control inputback" readonly>

                    </div>
                </div>
                <div class="mx-1 mt-3 text-center">
                    <p class="fs-3 m-0">→</p> 
                </div>
                <div class="col-md-5 w-100">
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
    <!-- <script src="../index.js"></script> -->
    <script src="../../../prestadores.js"></script>

    <script>
        var carteirinha = <?php echo json_encode($carteirinha, JSON_HEX_TAG); ?>;
        // alert(carteirinha);
        if (carteirinha.length === 17) { // Certificando-se de que tem o tamanho esperado
            let codigoUnidade = carteirinha.substring(0, 4); // Pega os 4 primeiros dígitos
            let carteira = carteirinha.substring(4); // Pega do 5º caractere até o final
            let char22 = `;;${codigoUnidade};`+carteira+`;;;`; // Monta a string esperada

            // Atribui os valores aos inputs
            document.getElementById("modalCodUnidade").value = codigoUnidade;
            document.getElementById("modalCarteira").value = carteira;
            document.getElementById("modalChar22").value = char22;
        } else {
            console.error("Erro: A carteirinha não tem 17 caracteres.");
        }

        getPrestador('../../../utils/get_prestador.php', ['#prestadorPrincipal']);

        configSelect('#prestadorPrincipal', 'Selecione um Prestador*');

        function configSelect(selectId, placeholder) {
            if (!$(selectId).hasClass('select2-hidden-accessible')) {
                $(selectId).select2({
                    placeholder: placeholder,
                    width: '100%',
                }).on('select2:open', function() {
                    $('.select2-container').css('z-index', '0');
                    $('.select2-dropdown').css('z-index', '0');
                });
            }
        }

        // function configSelect(selectId, placeholder) {
        //     if (!$(selectId).hasClass('select2-hidden-accessible')) {
        //         $(selectId).select2({
        //             placeholder: placeholder, //* texto para exibir no placeholder
        //             width: '100%',
        //         });
        //     }
        // }
    </script>

    <script>
        document.getElementById("verificaCarteiraForm").addEventListener("submit", function (e) {
            e.preventDefault(); // Impede o envio padrão do formulário
            const formData = new FormData(this);
            getInfoCarteira(formData);        
        });


        function getInfoCarteira(formData){
            //* Limpa campos antes da busca
            $("#modalCodUnidade2, #modalCarteira2, #modalCodModalidade2, #modalTermo2, #modalCodUsuario2, #modalChar222").val('');
            $("#codSequencia").text("");
            //* DESABILITA O BOTÃO DE ATUALIZAR
            $("#btnAtualizarCarteira").prop("disabled", true);

            axios.post('../verifica_carteira_beneficiario.php', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            })
            .then(function (response) {
                console.log(response.data);  // Dados da resposta
                if (response.data.error) {
                    alert(response.data.message);  // alerta se não encontrar dados
                } else {

                    //! Verifica se retornou mais de 1 resultado
                    // console.log(response.data.data.length);
                    if(response.data.data.length > 1){
                        //* Se tiver mais de 1 array, precisa exibir uma opção para o usuario escolher qual deseja usar. Monta uma tabela exibindo e ele escolhe
                        // alert("Foram encontrados mais de 1");
                        populateModal(response.data.data); // Preenche a tabela com os dados
                        const modal = new bootstrap.Modal(document.getElementById("dataModal"));
                        modal.show();

                    }else{

                        const item = response.data.data[0]; // Pega primeiro item do array
                        $("#modalCodUnidade2").val(item.CD_UNIDADE_CARTEIRA);
                        $("#modalCarteira2").val(item.CD_CARTEIRA_USUARIO);
                        $("#modalCodModalidade2").val(item.CD_MODALIDADE);
                        $("#modalTermo2").val(item.NR_TER_ADESAO);
                        $("#modalCodUsuario2").val(item.CD_USUARIO);
                        $("#modalChar222").val(item.CHAR_22);
                        $("#codSequencia").text(item.NR_DOC_SISTEMA);

                        //* HABILITA BOTÃO DE ATUALIZAR
                        $("#btnAtualizarCarteira").prop("disabled", false);
                    }
                }

            })
            .catch(function (error) {
                console.error('Erro na requisição:', error);
            });
        }
    </script>
    
    <script>
        document.getElementById("alteraCartBenef").addEventListener("submit", function (e) {
            e.preventDefault();

            $("#btnAtualizarCarteira").prop("disabled",true);
            // Copia o valor do <span> para o campo oculto
            var valorSequencia = document.getElementById('codSequencia').textContent;
            document.getElementById('hiddenCodSequencia').value = valorSequencia;
            //TODO PEGAR DADOS DO OUTRO FORM
            $("#hiddenprestadorPrincipal").val($("#prestadorPrincipal").val());
            $("#hiddenanoRef").val($("#anoRef").val());
            $("#hiddenperiodoRef").val($("#periodoRef").val());
            $("#hiddennrDocOrig").val($("#nrDocOrig").val());
            const formData = new FormData(this);

            axios.post('alterar_carteira_intercambio.php', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            })
            .then(function (response) {
                console.log(response.data);
                $("#retornoUpdate").text(response.data.message);
                $("#retornoUpdate").addClass("text-"+response.data.type);
                
                setTimeout(function () {
                    var retorno = document.getElementById('retornoUpdate');

                    if (retorno) {
                        retorno.style.display = 'none';
                    }
                }, 4000);

                if(response.data.error){
                    alert(response.data.message);
                }
            })
        })
    </script>


</body>
</html>
