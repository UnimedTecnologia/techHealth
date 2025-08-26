<!-- Modal Situação Cadastral -->
<div class="modal fade" id="modalTrocaPeriodoCooperado" tabindex="-1" aria-labelledby="modalTrocaPeriodoCooperado" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTrocaPeriodoCooperadoLabel">Situação Cadastral</h5>
                <button id="closeModalPrest" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formTrocaPeriodoCoop" action="contas_medicas/trocaPeriodoCooperado/trocaPeriodoCoop.php" method="POST" >
                    
                    <!-- <div class="mb-3 form-floating-label">
                        <input id="cdModalidade2" type="text" class="form-control inputback" name="cdModalidade2" placeholder=" " maxlength="6" 
                        oninput="this.value = this.value.replace(/[^0-9]/g, '')" >
                        <label for="cdModalidade2">Modalidade</label>
                    </div> -->

                

                    <p class="text-center">
                            <?php
                            if (isset($_SESSION['erroSituacaoCadastral'])) {
                                echo "<span id='erroSituacaoCadastral' class='text-danger'>{$_SESSION['erroSituacaoCadastral']}</span>";
                                ?>
                                <script>
                                    document.addEventListener("DOMContentLoaded", function () {
                                        var modalSituacaoCadastral = new bootstrap.Modal(document.getElementById('modalSituacaoCadastral'));
                                        modalSituacaoCadastral.show();

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

                    
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('formSituacaoCadastral');
        const loadingOverlay = document.getElementById('loadingOverlay');
        const msgLoader = document.getElementById('msgLoader');

        // Adiciona evento de envio ao formulário
        form.addEventListener('submit', function (event) {
            // Exibe overlay com a mensagem de carregamento
            msgLoader.innerText = "Carregando dados, aguarde por favor...";
            loadingOverlay.style.display = 'flex';
        })

        //! Limpa o foco quando o modal está prestes a ser fechado
        const modal = document.getElementById('modalSituacaoCadastral');
        
        modal.addEventListener('hide.bs.modal', function() {
            if (modal.contains(document.activeElement)) {
            document.activeElement.blur();
            }
        });


        
    })
</script>
