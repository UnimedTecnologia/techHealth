<div class="modal fade" id="modalAlterarStatusGuia" tabindex="-1" aria-labelledby="modalAlterarStatusGuiaLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Atualização de status guia - REGULAÇÃO</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="consultarStatus">
                    <div class="mb-3">
                        <label for="nrGuiaReg" class="form-label">Número Guia</label>
                        <input type="text" id="nrGuiaReg" pattern="\d*" maxlength="10" name="nrGuiaReg" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="anoGuiaReg" class="form-label">Ano Guia</label>
                        <input type="text" id="anoGuiaReg" pattern="\d*" maxlength="4" name="anoGuiaReg" class="form-control" required>
                    </div> 
                    <button type="submit" class="btn btn-success w-100">Buscar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de detalhes guia -->
<div class="modal fade" id="modalStatusGuia" tabindex="-1" aria-labelledby="modalStatusGuiaLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalhes da Guia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Número da Guia:</strong> <span id="modalNrGuiaReg"></span></p>
                <p><strong>Status Atual:</strong> <span id="modalStatusReg"></span></p>

                <form id="updateStatusReg">
                    <div class="mb-3">
                        <label for=""><strong>Novo Status:</strong></label>
                        <select id="statusSelectReg" name="statusSelectReg" class="form-select">
                            <option value="2">AUTORIZADA</option>
                            <option value="3">CANCELADA</option>
                            <option value="8">NEGADA</option>
                            <option value="9">PENDENTE AUDITORIA</option>
                            <option value="10">PENDENTE LIBERAÇÃO</option>
                        </select>
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
// Variável para controlar o evento
let modalTransitionHandler = null;

document.getElementById("consultarStatus").addEventListener("submit", function (e) {
    e.preventDefault();

    const formData = new FormData(this);

    axios.post('regulacao/get_statusGuiaRegulacao.php', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
    })
    .then(function (response) {
        if (response.data.error) {
            alert(response.data.message);
        } else {
            // Preencher dados
            document.getElementById("modalNrGuiaReg").innerText = response.data.data[0].NR_GUIA_ATENDIMENTO;
            document.getElementById("modalStatusReg").innerText = response.data.data[0].STATUS_GUIA;
            document.getElementById("statusSelectReg").value = response.data.data[0].IN_LIBERADO_GUIAS;

            // Fecha o modal de busca
            const modalBusca = bootstrap.Modal.getOrCreateInstance(document.getElementById("modalAlterarStatusGuia"));
            modalBusca.hide();

            // Quando ele terminar de fechar, abre o de detalhes
            document.getElementById("modalAlterarStatusGuia").addEventListener("hidden.bs.modal", () => {
                const modalDetalhes = bootstrap.Modal.getOrCreateInstance(document.getElementById("modalStatusGuia"));
                modalDetalhes.show();
            }, { once: true });
        }
    })
    .catch(function (error) {
        console.error("Erro na consulta:", error);
        alert("Erro ao buscar dados da guia.");
    });
});


// UPDATE
document.getElementById("updateStatusReg").addEventListener("submit", function (e) {
    e.preventDefault();

    const loadingOverlay = document.getElementById('loadingOverlay');
    const msgLoader = document.getElementById('msgLoader');

    if (loadingOverlay && msgLoader) {
        msgLoader.innerText = "Atualizando dados, aguarde por favor...";
        loadingOverlay.style.display = 'flex';
    }
    
    const formData = new FormData();
    formData.append('statusSelectReg', document.getElementById("statusSelectReg").value);
    formData.append('nr_guiaReg', document.getElementById("modalNrGuiaReg").innerText);
    formData.append('ano', document.getElementById("anoGuiaReg").value);
    
    axios.post('regulacao/update_statusGuiaReg.php', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
    })
    .then(function (response) {
        if (loadingOverlay) loadingOverlay.style.display = 'none';
        
        Swal.fire({
            icon: response.data.type,
            title: response.data.type === 'success' ? 'Sucesso!' : 'Erro!',
            text: response.data.message,
            showConfirmButton: true
        }).then((result) => {
            if (result.isConfirmed && response.data.type === 'success') {
                const modalDetalhes = bootstrap.Modal.getOrCreateInstance(document.getElementById("modalStatusGuia"));
                modalDetalhes.hide();
                document.getElementById("consultarStatus").reset();
            }
        });
    })
    .catch(function (error) {
        if (loadingOverlay) loadingOverlay.style.display = 'none';
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: 'Erro ao atualizar status da guia.'
        });
    });
});


// 🔑 FIX EXTRA: sempre que qualquer modal fechar, garantir que não fica backdrop preso
document.querySelectorAll('.modal').forEach(modalEl => {
    modalEl.addEventListener("hidden.bs.modal", () => {
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(b => b.remove());
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
    });
});


// Cleanup para garantir que não haja backdrops residuais
document.addEventListener('DOMContentLoaded', function() {
    // Limpar backdrops ao carregar a página
    const existingBackdrops = document.querySelectorAll('.modal-backdrop');
    existingBackdrops.forEach(backdrop => {
        backdrop.parentNode.removeChild(backdrop);
    });
    
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
});
</script>