<!-- Modal de busca -->
<div class="modal fade" id="modalAtualizacaostatusguia" tabindex="-1" aria-labelledby="modalAtualizacaostatusguia" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Atualização de status guia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="alteraStatus">
                    <div class="mb-3">
                        <label for="nrGuia" class="form-label">Número Guia</label>
                        <input type="text" id="nrGuia" pattern="\d*" maxlength="10" name="nrGuia" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="anoGuia" class="form-label">Ano Guia</label>
                        <input type="text" id="anoGuia" pattern="\d*" maxlength="4" name="anoGuia" class="form-control" required>
                    </div> 
                    <button type="submit" class="btn btn-success w-100">Buscar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de detalhes -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModal" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalhes da Guia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong>Número da Guia:</strong> <span id="modalNrGuia"></span></p>
                <p><strong>Status Atual:</strong> <span id="modalStatus"></span></p>

                <form id="updateStatus">
                    <div class="mb-3">
                        <label for=""><strong>Novo Status:</strong></label>
                        <select id="statusSelect" name="statusSelect" class="form-select">
                            <option value="1">DIGITADA</option>
                            <option value="2">AUTORIZADA</option>
                            <option value="3">CANCELADA</option>
                            <option value="4">PROCESSADA PELO RC</option>
                            <option value="5">FECHADA</option>
                            <option value="6">ORÇAMENTO</option>
                            <option value="7">FATURADA</option>
                            <option value="8">NEGADA</option>
                            <option value="9">PENDENTE AUDITORIA</option>
                            <option value="10">PENDENTE LIBERAÇÃO</option>
                            <option value="11">PENDENTE LAUDO MÉDICO</option>
                            <option value="12">PENDENTE DE JUSTIFICATIVA MÉDICA</option>
                            <option value="13">PENDENTE DE PERÍCIA</option>
                            <option value="19">EM AUDITORIA</option>
                            <option value="20">EM ATENDIMENTO</option>
                            <option value="23">EM PERÍCIA</option>
                            <option value="30">REEMBOLSO</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="senhaOrigem">Senha origem</label>
                        <input id="senhaOrigem" type="text" pattern="\d*" maxlength="10" class="form-control" name="senhaOrigem" required>
                    </div>
                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-success">Atualizar</button>
                    </div>
                </form>
                
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById("alteraStatus").addEventListener("submit", function (e) {
        e.preventDefault();

        const formData = new FormData(this);

        axios.post('intercambio/get_statusGuia.php', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        })
        .then(function (response) {
            console.log(response.data);
            if (response.data.error) {
                alert(response.data.message);
            } else {
                // Atualizar os elementos do novo modal
                document.getElementById("modalNrGuia").innerText = response.data.data[0].NR_GUIA_ATENDIMENTO;
                document.getElementById("modalStatus").innerText = response.data.data[0].STATUS_GUIA;
                document.getElementById("statusSelect").value = response.data.data[0].IN_LIBERADO_GUIAS;
                
                // ** Remover o foco do botão ativo antes de fechar o modal **
                document.activeElement.blur();

                // ** Fechar o modal atual corretamente **
                var modalStatus = bootstrap.Modal.getInstance(document.getElementById("modalAtualizacaostatusguia"));
                if (modalStatus) {
                    modalStatus.hide();
                }

                // ** Garantir que o modal foi completamente fechado antes de abrir outro **
                document.getElementById("modalAtualizacaostatusguia").addEventListener("hidden.bs.modal", function () {
                    // Remover backdrop residual
                    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());

                    // Garantir que o body não tenha classes de modal ativas
                    document.body.classList.remove('modal-open');

                    // Agora abrir o próximo modal
                    var modal = new bootstrap.Modal(document.getElementById("statusModal"));
                    modal.show();
                }, { once: true });
            }
        });
    });
</script>

<script>
    document.getElementById("updateStatus").addEventListener("submit", function (e) {
        e.preventDefault();

        const loadingOverlay = document.getElementById('loadingOverlay');
        const msgLoader = document.getElementById('msgLoader');

        msgLoader.innerText = "Atualizando dados, aguarde por favor...";
        loadingOverlay.style.display = 'flex';
        
        const formData = new FormData(this);

        let senhaOrigem = document.getElementById("senhaOrigem").value;
        let AA = 0;
        let NR = senhaOrigem; // Por padrão, NR é a senha completa

        if (senhaOrigem.length > 8) {
            let excedente = senhaOrigem.length - 8;
            AA = senhaOrigem.slice(0, excedente); // Pega os primeiros dígitos excedentes
            NR = senhaOrigem.slice(excedente);   // Pega os últimos 8 caracteres
        }

        console.log("AA =", AA);
        console.log("NR =", NR);

        axios.post('intercambio/update_statusGuia.php', {
            AA: AA,
            NR: NR,
            statusSelect: document.getElementById("statusSelect").value,
            nr_guia: document.getElementById("modalNrGuia").innerText,
            ano: document.getElementById("anoGuia").value

        },{
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        })
        .then(function (response) {
            loadingOverlay.style.display = 'none'; //* remove loader
            console.log(response.data); 

            Swal.fire({
                icon: response.data.type,
                title: '',
                text: response.data.message,
                // timer: 2500,
                // timerProgressBar: true,
                showConfirmButton: true
            });
            
        })
        .catch(function (error) {
            console.error("Erro ao enviar os dados:", error);
        });

    })
</script>

<script>
    //! Limpa o foco quando o modal está prestes a ser fechado
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('modalAtualizacaostatusguia');
        
        modal.addEventListener('hide.bs.modal', function() {
            if (modal.contains(document.activeElement)) {
            document.activeElement.blur();
            }
        });
    });
</script>