<div class="modal fade" id="modalAlteraPorcentagemDocumento" tabindex="-1" aria-labelledby="modalAlteraPorcentagemDocumento" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Carregar Porcentagem Documento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formAlterarPorcent" action="contas_medicas/alteraPorcentagemDocumento/verificar_valores_documento.php" method="POST">
                    <div class="">
                        <label for="prestadorAltPorcentDoc">Prestador *</label>
                        <select id="prestadorAltPorcentDoc" name="codPrest" class="custom-select">
                            <option value="" disabled selected>Prestador</option>
                            <option value="1"></option>
                        </select>
                    </div> 
                    <div class="mb-3 form-floating-label">
                        <input id="numDocPorcent" type="text" class="form-control inputback" name="numDoc" placeholder=" " required maxlength="30">
                        <label for="numDocPorcent">Número de documento *</label>
                    </div>
                    <!-- <div class="mb-3 form-floating-label">
                        <input id="numPacPorcent" type="text" class="form-control inputback" name="numPacote" placeholder=" " required maxlength="30">
                        <label for="numPacPorcent">Pacote *</label>
                    </div> -->

                    <div class="mb-3 form-floating-label">
                        <input id="numPerPercent" type="text" class="form-control inputback" name="periodoRef" placeholder=" " required maxlength="30">
                        <label for="numPerPercent">Número Período Referência *</label>
                    </div>
                    <div class="mb-3 form-floating-label">
                        <input id="anoRefPercent" type="text" class="form-control inputback" name="dtAnoRef" placeholder=" " required maxlength="4">
                        <label for="anoRefPercent">Ano Referência *</label>
                    </div>
                    
                    <div class="text-center">
                        <button type="submit" class="btn btn-success" id="btnCarregar">
                            <span id="btnText">Carregar</span>
                            <div id="btnSpinner" class="spinner-border spinner-border-sm d-none" role="status">
                                <span class="visually-hidden">Carregando...</span>
                            </div>
                        </button>
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
        const btnCarregar = document.getElementById('btnCarregar');
        const btnText = document.getElementById('btnText');
        const btnSpinner = document.getElementById('btnSpinner');

        // Adiciona evento de envio ao formulário
        form.addEventListener('submit', function (event) {
            //! verifica se campos digitados são numericos
            const numericFields = ['numDoc', 'numPacote', 'periodoRef', 'dtAnoRef'];
            let isValid = true;

            numericFields.forEach(fieldName => {
                const field = form.elements[fieldName];
                if (field) { // Verifica se o campo existe
                    const value = field.value.trim();
                    // Verifica se é um número válido (apenas se o campo não estiver vazio)
                    if (value !== '' && !/^\d+$/.test(value)) {
                        isValid = false;
                        field.classList.add('is-invalid'); // Adiciona classe de erro
                        alert(`O campo "${field.getAttribute('name')}" deve conter apenas números.`);
                    } else {
                        field.classList.remove('is-invalid'); // Remove classe de erro
                    }
                }
            });

            if (!isValid) {
                event.preventDefault(); // Impede o envio se houver erro
                return;
            }

            // Exibe o loader global que bloqueia a tela inteira
            msgLoader.innerText = "Carregando dados dos pacotes, aguarde por favor...";
            loadingOverlay.style.display = 'flex';
            
            // Desabilita o botão e mostra spinner
            btnCarregar.disabled = true;
            btnText.textContent = "Carregando...";
            btnSpinner.classList.remove('d-none');

            // Adiciona proteção para impedir fechamento do modal com ESC
            const modalKeyHandler = function(e) {
                if (e.key === 'Escape' || e.keyCode === 27) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            };
            document.addEventListener('keydown', modalKeyHandler);

            // Remove o event listener quando o modal for fechado
            const modal = document.getElementById('modalAlteraPorcentagemDocumento');
            const modalHideHandler = function() {
                document.removeEventListener('keydown', modalKeyHandler);
                modal.removeEventListener('hidden.bs.modal', modalHideHandler);
            };
            modal.addEventListener('hidden.bs.modal', modalHideHandler);

            // Permite que o formulário continue com o envio normal
            // O loading permanecerá até a página recarregar completamente
        });

        //! Limpa o foco quando o modal está prestes a ser fechado
        const modal = document.getElementById('modalAlteraPorcentagemDocumento');
  
        modal.addEventListener('hide.bs.modal', function() {
            if (modal.contains(document.activeElement)) {
                document.activeElement.blur();
            }
            
            // Reseta o estado do botão caso o modal seja fechado durante o carregamento
            // Mas não esconde o loadingOverlay global pois ele pode estar sendo usado por outras operações
            btnCarregar.disabled = false;
            btnText.textContent = "Carregar";
            btnSpinner.classList.add('d-none');
        });

        // Reseta o estado do botão se a página for recarregada
        window.addEventListener('beforeunload', function() {
            btnCarregar.disabled = false;
            btnText.textContent = "Carregar";
            btnSpinner.classList.add('d-none');
        });
    });
</script>