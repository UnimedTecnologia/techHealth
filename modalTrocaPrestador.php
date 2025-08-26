<!-- Modal Troca Carteira beneficiário-->
<div class="modal fade" id="modalAlteraPrestador" tabindex="-1" aria-labelledby="modalTrocaPrestadorLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTrocaPrestadorLabel">Troca de Prestador</h5>
                <button id="closemodalTrocaPrestador" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- <form id="formTrocaPrestador" action="prestador/verifica_prestador.php" method="POST" > -->
                <form id="formTrocaPrestador" action="contas_medicas/prestador/get_prestador_insumo_proced.php" method="POST" >
                    
                    <div class="">
                        <label for="prestadorTroca" class="form-label">Prestador*</label>
                        <select id="prestadorTroca" name="codPrest" class="custom-select" >
                                <option value="" disabled selected>Prestador </option>
                            <option value="1"></option>
                        </select>
                    </div>

                    <div class="mb-3 form-floating-label">
                        <input id="numDocPrest" type="text" class="form-control inputback" name="numDoc" placeholder=" " required >
                        <label for="numDocPrest">Número de documento (separado por virgula) *</label>
                    </div> 
                    <div class="mb-3 form-floating-label">
                        <input id="codTransPrest" type="text" class="form-control inputback" name="codTrans" placeholder=" " required maxlength="30">
                        <label for="codTransPrest">Código transação *</label>
                    </div>
                    <div class="mb-3 form-floating-label">
                        <input id="serieDocPrest" type="text" class="form-control inputback" name="serieDoc" placeholder=" "  maxlength="30">
                        <label for="serieDocPrest">Série documento </label>
                    </div>
                    
                    <!-- NÃO OBRIGATORIO-->
                    <div class="mb-3 form-floating-label">
                        <input id="periodoRefPrest" type="text" class="form-control inputback" name="periodoRef" placeholder=" " maxlength="30">
                        <label for="periodoRefPrest">Número Período Referência</label>
                    </div>
                    <div class="mb-3 form-floating-label">
                        <input id="dtAnoRefPrest" type="text" class="form-control inputback" name="dtAnoRef" placeholder=" " maxlength="4">
                        <label for="dtAnoRefPrest">Ano Referência</label>
                    </div>
                    
                    <div class="text-center">
                        <button id="btnBuscarPrest" type="submit" class="btn btn-success" >Buscar</button>
                    </div>
                    
                </form>
                <div class="text-center">
                <?php
                    // Exibição de erro, se houver
                    if (isset($_SESSION['erroPrestador'])) {
                        echo "<span id='erroPrestador' class='text-danger'>{$_SESSION['erroPrestador']}</span>";
                        ?>
                        <script>
                            document.addEventListener("DOMContentLoaded", function () {
                                setTimeout(function () {
                                    var erroPrestador = document.getElementById('erroPrestador');
                                    if (erroPrestador) {
                                        erroPrestador.style.display = 'none';
                                    }
                                }, 3000);
                            });
                        </script>
                        <?php
                        unset($_SESSION['erroPrestador']); // Limpar a sessão após exibir a mensagem
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('formTrocaPrestador');
        const loadingOverlay = document.getElementById('loadingOverlay');
        const msgLoader = document.getElementById('msgLoader');

        // Adiciona evento de envio ao formulário
        form.addEventListener('submit', function (event) {
            // Exibe overlay com a mensagem de carregamento
            msgLoader.innerText = "Carregando dados, aguarde por favor...";
            loadingOverlay.style.display = 'flex';
        })

        //! Limpa o foco quando o modal está prestes a ser fechado
        const modal = document.getElementById('modalAlteraPrestador');
        modal.addEventListener('hide.bs.modal', function() {
            if (modal.contains(document.activeElement)) {
            document.activeElement.blur();
            }
  });
        
    })
</script>