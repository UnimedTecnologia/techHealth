<!-- Modal Autorização -->
<div class="modal fade" id="modalDadosdeautorizacao" tabindex="-1" aria-labelledby="modalAutorizacaoLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAutorizacaoLabel">Dados de Autorização</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formAutorizacao" action="contas_medicas/autorizacao/verificar_autorizacao.php" method="POST">
                    <div class="">
                    <label for="prestadorAutorizacao">Prestador *</label>
                        <select id="prestadorAutorizacao" name="codPrest" class="custom-select" >
                                <option value="" disabled selected>Prestador </option>
                            <option value="1"></option>
                        </select>
                    </div> 
                    <div class="mb-3 form-floating-label">
                        <input id="numDocAutor" type="text" class="form-control inputback" name="numDoc" placeholder=" " required maxlength="30">
                        <label for="numDocAutor">Número de documento *</label>
                    </div>
                    <div class="mb-3 form-floating-label">
                        <input id="codTransAutor" type="text" class="form-control inputback" name="codTrans" placeholder=" " required maxlength="30">
                        <label for="codTransAutor">Código transação *</label>
                    </div>
                    <div class="mb-3 form-floating-label">
                        <input id="serieDocAutor" type="text" class="form-control inputback" name="serieDoc" placeholder=" " required maxlength="30">
                        <label for="serieDocAutor">Série documento *</label>
                    </div>
                    
                    <!-- NÃO OBRIGATORIO-->
                    <div class="mb-3 form-floating-label">
                        <input id="periodoRefAutor" type="text" class="form-control inputback" name="periodoRef" placeholder=" " maxlength="30">
                        <label for="periodoRefAutor">Número Período Referência</label>
                    </div>
                    <div class="mb-3 form-floating-label">
                        <input id="dtAnoRefAutor" type="text" class="form-control inputback" name="dtAnoRef" placeholder=" " maxlength="4">
                        <label for="dtAnoRefAutor">Ano Referência</label>
                    </div>
                    
                    <div class="text-center">
                        <button type="submit" class="btn btn-success">Carregar</button>
                    </div>
                    <p>
                        <?php
                        if (isset($_SESSION['erroAutorizacao'])) {
                            echo "<div class='text-center'><span id='erroAutorizacao' class='text-danger'>{$_SESSION['erroAutorizacao']}</span></div>";
                            ?>
                            <script>
                                document.addEventListener("DOMContentLoaded", function () {
                                    var modalAutorizacao = new bootstrap.Modal(document.getElementById('modalDadosdeautorizacao'));
                                    modalAutorizacao.show();

                                    setTimeout(function () {
                                        var erroAutorizacao = document.getElementById('erroAutorizacao');
                                        if (erroAutorizacao) {
                                            erroAutorizacao.style.display = 'none';
                                        }
                                    }, 3000);
                                });
                            </script>
                            <?php
                            unset($_SESSION['erroAutorizacao']); // Limpar a sessão após exibir a mensagem
                        }
                        ?>
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('formAutorizacao');
        const loadingOverlay = document.getElementById('loadingOverlay');
        const msgLoader = document.getElementById('msgLoader');

        // Adiciona evento de envio ao formulário
        form.addEventListener('submit', function (event) {
            // Exibe overlay com a mensagem de carregamento
            msgLoader.innerText = "Carregando dados, aguarde por favor...";
            loadingOverlay.style.display = 'flex';
        })


        //! Limpa o foco quando o modal está prestes a ser fechado
        const modal = document.getElementById('modalDadosdeautorizacao');
        modal.addEventListener('hide.bs.modal', function() {
            if (modal.contains(document.activeElement)) {
            document.activeElement.blur();
            }
        });
        


        
    })
</script>