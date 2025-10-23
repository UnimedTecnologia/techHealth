
<div class="modal fade" id="modalAlteraPorcentagemDocumento" tabindex="-1" aria-labelledby="modalAlteraPorcentagemDocumento" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" >Carregar Porcentagem Documento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formAlterarPorcent" action="contas_medicas/alteraPorcentagemDocumento/verificar_valores_documento.php" method="POST">
                    <div class="">
                    <label for="prestadorAltPorcentDoc">Prestador *</label>
                        <select id="prestadorAltPorcentDoc" name="codPrest" class="custom-select" >
                                <option value="" disabled selected>Prestador </option>
                            <option value="1"></option>
                        </select>
                    </div> 
                    <div class="mb-3 form-floating-label">
                        <input id="numDocPorcent" type="text" class="form-control inputback" name="numDoc" placeholder=" " required maxlength="30">
                        <label for="numDocPorcent">Número de documento *</label>
                    </div>
                    <div class="mb-3 form-floating-label">
                        <input id="numPacPorcent" type="text" class="form-control inputback" name="numPacote" placeholder=" " required maxlength="30">
                        <label for="numPacPorcent" >Pacote *</label>
                    </div>

                    <div class="mb-3 form-floating-label">
                        <input id="numPerPercent" type="text" class="form-control inputback" name="periodoRef" placeholder=" " required maxlength="30">
                        <label for="numPerPercent">Número Período Referência *</label>
                    </div>
                    <div class="mb-3 form-floating-label">
                        <input id="anoRefPercent" type="text" class="form-control inputback" name="dtAnoRef" placeholder=" " required maxlength="4">
                        <label for="anoRefPercent">Ano Referência *</label>
                    </div>
                    
                    <div class="text-center">
                        <button type="submit" class="btn btn-success">Carregar</button>
                    </div>
                   
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('formAlterarPorcent');
        const loadingOverlay = document.getElementById('loadingOverlay');
        const msgLoader = document.getElementById('msgLoader');

        // Adiciona evento de envio ao formulário
        form.addEventListener('submit', function (event) {
            //! verifica se campos digitados são numericos
            const numericFields = ['numDoc', 'numPacote', 'periodoRef', 'dtAnoRef'];
            let isValid = true;

            numericFields.forEach(fieldName => {
                const field = form.elements[fieldName];
                const value = field.value.trim();

                // Verifica se é um número válido
                if (!/^\d+$/.test(value)) {
                    isValid = false;
                    field.classList.add('is-invalid'); // Adiciona classe de erro
                    alert(`O campo "${fieldName}" deve conter apenas números.`);
                } else {
                    field.classList.remove('is-invalid'); // Remove classe de erro
                }
            });

            if (!isValid) {
                event.preventDefault(); // Impede o envio se houver erro
                return;
            }

            // Exibe overlay com a mensagem de carregamento
            msgLoader.innerText = "Carregando dados, aguarde por favor...";
            loadingOverlay.style.display = 'flex';

            // Adiciona proteção para impedir fechamento do modal com ESC
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' || e.keyCode === 27) {
                    e.preventDefault();
                }
            });

            // Aguarda alguns segundos antes de continuar o envio (simulação)
            setTimeout(() => {
                form.submit(); // Realiza o envio após exibir o overlay
            }, 1000); // Ajuste o tempo, se necessário
        });

        //! Limpa o foco quando o modal está prestes a ser fechado
        const modal = document.getElementById('modalAlteraPorcentagemDocumento');
  
        modal.addEventListener('hide.bs.modal', function() {
            if (modal.contains(document.activeElement)) {
            document.activeElement.blur();
            }
        });
    });
</script>
