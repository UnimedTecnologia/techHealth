<style>
    .switch {
        position: relative;
        display: inline-block;
        width: 40px;
        height: 24px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 34px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 16px;
        width: 16px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }

    input:checked + .slider {
        background-color: #198754;
    }

    input:checked + .slider:before {
        transform: translateX(16px);
    }
</style>
<!-- Modal Troca Carteira beneficiário-->
<div class="modal fade" id="modalAlteraCarteiraBeneficiario" tabindex="-1" aria-labelledby="modalCarteiraBenefLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCarteiraBenefLabel">Troca Carteira Beneficiário</h5>
                <button id="closeModalCarteiraBenef" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- //! BUSCA LOCAL ou INTERCAMBIO-->
                <form id="formCarteiraBenef" action="contas_medicas/carteiraBeneficiario/get_carteira_beneficiario.php" method="POST" >
                    <div class="d-flex justify-content-start">
                        <label class="switch">
                            <input type="checkbox" id="toggleSwitch" checked>
                            <span class="slider"></span>
                        </label>
                        <span id="status" style="margin-left: 10px">Local</span>
                    </div>

                    <div class="mb-3 form-floating-label">
                        <input id="cpfBenef" type="text" class="form-control inputback" name="cpfNome" placeholder=" " required >
                        <label id="spanLocalInter" for="cpfBenef">CPF ou Nome do Beneficiário *</label>
                    </div>
                    
                    <div class="text-center">
                        <button id="btnCarteira" type="submit" class="btn btn-success" >Carregar Carteira</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<script>
   document.addEventListener('DOMContentLoaded', function () {
        const toggleSwitch = document.getElementById("toggleSwitch");
        const statusText = document.getElementById("status");
        const spanLocalInter = document.getElementById("spanLocalInter");
        const form = document.getElementById('formCarteiraBenef');
        const cpfBenef = document.getElementById("cpfBenef");
        const loadingOverlay = document.getElementById('loadingOverlay');
        const msgLoader = document.getElementById('msgLoader');

        toggleSwitch.addEventListener("change", function () {
            statusText.textContent = this.checked ? "Local" : "Intercâmbio";
            spanLocalInter.textContent = this.checked ? "CPF ou Nome do Beneficiário *" : "Nova Carteirinha *";
            cpfBenef.value = ""; // Limpa o campo ao alternar o switch

            if (!this.checked) {
                //! INTERCAMBIO)
                cpfBenef.setAttribute("type", "text"); // Garante que o input aceite números corretamente
                cpfBenef.setAttribute("inputmode", "numeric"); // Habilita o teclado numérico em dispositivos móveis
                cpfBenef.setAttribute("pattern", "[0-9]*"); // Garante apenas números
            } else {
                //! LOCAL
                cpfBenef.setAttribute("type", "text"); // Retorna ao modo texto normal
                cpfBenef.removeAttribute("inputmode");
                cpfBenef.removeAttribute("pattern");
            }
        });

        // Restringe entrada de caracteres quando está no modo "Intercâmbio"
        cpfBenef.addEventListener("input", function () {
            if (!toggleSwitch.checked) {
                this.value = this.value.replace(/\D/g, ""); // Remove qualquer caractere não numérico
            }
        });

        // Adiciona evento de envio ao formulário
        form.addEventListener('submit', function (event) {
            // Verifica o estado do switch e altera a ação do formulário
            
            if(!toggleSwitch.checked){
                if(cpfBenef.value.length < 17){
                    event.preventDefault(); // Impede o envio do formulário
                    alert("A carteirinha deve ter exatamente 17 caracteres!");
                    return;
                }
            }
            if (!toggleSwitch.checked) {
                let carteirinha = cpfBenef.value.trim();
                event.preventDefault(); // Impede o envio padrão
                window.location.href = "contas_medicas/carteiraBeneficiario/intercambio/index.php?carteirinha=" + encodeURIComponent(carteirinha);
                // window.location.href = "contas_medicas/carteiraBeneficiario/intercambio/index.php?carteirinha="+cpfBenef.value+"";
            } else {
                // Exibe overlay com a mensagem de carregamento para requisições locais
                msgLoader.innerText = "Carregando dados, aguarde por favor...";
                loadingOverlay.style.display = 'flex';
            }
        });

        //! Limpa o foco quando o modal está prestes a ser fechado
        const modal = document.getElementById('modalAlteraCarteiraBeneficiario');
        modal.addEventListener('hide.bs.modal', function() {
            if (modal.contains(document.activeElement)) {
            document.activeElement.blur();
            }
        });
    });

</script>