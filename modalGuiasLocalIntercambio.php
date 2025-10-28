<style>
    .tag-input-container {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        padding: 6px;
        border: 1px solid #ced4da;
        border-radius: 8px;
        min-height: 46px;
        background: #fff;
        cursor: text;
    }
    .tag-input-container input {
        border: none;
        outline: none;
        flex: 1;
        min-width: 120px;
    }
    .tag {
        background-color: #198754;
        color: white;
        padding: 4px 10px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.9rem;
    }
    .tag button {
        background: none;
        border: none;
        color: white;
        cursor: pointer;
        font-size: 1rem;
        padding: 0;
    }
    .modal-content {
        max-height: 90vh;
        overflow-y: auto;
    }
</style>

<div class="modal fade" id="modalGuiasLocalIntercambio" tabindex="-1" aria-labelledby="modalGuiasLocalIntercambioLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalGuiasLocalIntercambioLabel">Guias Local/Intercâmbio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <form id="formGuiaLocalIntercambio" action="autorizacoes/relatorioGuiasLocalIntercambio/relatorioGuiasLocalIntercambio.php" method="POST" style="max-width: 600px; margin: 0 auto;">
                    <div class="row justify-content-center">
                        <div class="col-md-5 mb-3">
                            <div class="form-floating-label">
                                <input id="dtGuiaLocalIntercambioIni" type="date" class="form-control" name="dtGuiaLocalIntercambioIni" placeholder=" " required>
                                <label for="dtGuiaLocalIntercambioIni">Data início *</label>
                            </div>
                        </div>

                        <div class="col-md-5 mb-3">
                            <div class="form-floating-label">
                                <input id="dtGuiaLocalIntercambioFim" type="date" class="form-control" name="dtGuiaLocalIntercambioFim" placeholder=" " required>
                                <label for="dtGuiaLocalIntercambioFim">Data fim *</label>
                            </div>
                        </div>

                        <!-- Campo de matrículas com tags -->
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Matrículas *</label>
                            <div class="tag-input-container" id="tagInputContainer">
                                <input type="text" id="tagInput" placeholder="Digite o código e pressione Enter">
                            </div>
                            <input type="hidden" id="matriculaAutorizacao" name="matriculaAutorizacao">
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <button id="btRelatorioGuiasLocalIntercambio" type="submit" class="btn btn-success">Gerar relatório</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    console.log('DOM Carregado - Iniciando script...');
    
    // Verifica se Bootstrap está disponível
    if (typeof bootstrap === 'undefined') {
        console.error('Bootstrap não foi carregado!');
        // Carrega Bootstrap dinamicamente se não estiver disponível
        loadBootstrap();
        return;
    }

    // Inicializa o modal com tratamento de erro
    let modal;
    try {
        const modalElement = document.getElementById('modalGuiasLocalIntercambio');
        if (modalElement) {
            modal = new bootstrap.Modal(modalElement);
            console.log('Modal inicializado com sucesso');
        } else {
            console.error('Elemento modal não encontrado');
        }
    } catch (error) {
        console.error('Erro ao inicializar modal:', error);
    }

    // Seu código original com melhor tratamento
    const container = document.getElementById("tagInputContainer");
    const input = document.getElementById("tagInput");
    const hiddenInput = document.getElementById("matriculaAutorizacao");
    
    if (!container || !input || !hiddenInput) {
        console.error('Elementos necessários não encontrados');
        return;
    }

    let tags = ["114000586", "114000562", "114000595", "114000563", "114000535", "114000602", "114000616"];

    function renderTags() {
        // Remove todas as tags existentes
        const existingTags = container.querySelectorAll(".tag");
        existingTags.forEach(tag => tag.remove());
        
        // Adiciona as novas tags
        tags.forEach((matr, index) => {
            const tagEl = document.createElement("div");
            tagEl.classList.add("tag");
            tagEl.innerHTML = `${matr} <button type="button" data-index="${index}">&times;</button>`;
            container.insertBefore(tagEl, input);
        });
        
        // Atualiza o input hidden
        hiddenInput.value = tags.join(",");
        console.log('Tags atualizadas:', tags);
    }

    container.addEventListener("click", () => input.focus());

    input.addEventListener("keydown", (e) => {
        if (e.key === "Enter" || e.key === "," || e.key === " ") {
            e.preventDefault();
            const value = input.value.trim();
            if (value && !tags.includes(value)) {
                tags.push(value);
                renderTags();
                input.value = "";
            }
        } else if (e.key === "Backspace" && input.value === "") {
            if (tags.length > 0) {
                tags.pop();
                renderTags();
            }
        }
    });

    container.addEventListener("click", (e) => {
        if (e.target.tagName === "BUTTON") {
            const index = parseInt(e.target.dataset.index);
            if (!isNaN(index) && index >= 0 && index < tags.length) {
                tags.splice(index, 1);
                renderTags();
            }
        }
    });

    // Form submit
    const form = document.getElementById("formGuiaLocalIntercambio");
    if (form) {
        form.addEventListener("submit", function (e) {
            e.preventDefault();
            console.log('Formulário submetido');

            const loaderGuia = document.getElementById("loadingOverlay");
            const botaoGuia = document.getElementById("btRelatorioGuiasLocalIntercambio");
            const msgLoader = document.getElementById("msgLoader");
            
            if (msgLoader) msgLoader.innerText = "Gerando relatório de Guias, aguarde por favor...";
            if (loaderGuia) loaderGuia.style.display = "flex";
            if (botaoGuia) botaoGuia.disabled = true;

            const formData = new FormData(this);

            fetch(this.action, { 
                method: "POST", 
                body: formData 
            })
            .then(res => {
                if (!res.ok) {
                    throw new Error(`HTTP error! status: ${res.status}`);
                }
                return res.json();
            })
            .then(data => {
                console.log('Resposta do servidor:', data);
                if (data.success) {
                    if (loaderGuia) loaderGuia.style.display = "none";
                    if (botaoGuia) botaoGuia.disabled = false;
                    window.location.href = data.file;
                } else {
                    alert(data.error || "Erro ao gerar relatório.");
                    if (loaderGuia) loaderGuia.style.display = "none";
                    if (botaoGuia) botaoGuia.disabled = false;
                }
            })
            .catch(err => {
                console.error("Erro na requisição:", err);
                alert("Erro ao gerar relatório: " + err.message);
                if (loaderGuia) loaderGuia.style.display = "none";
                if (botaoGuia) botaoGuia.disabled = false;
            });
        });
    }

    // Renderiza as tags iniciais
    renderTags();
    console.log('Script carregado com sucesso');
});

// Função para carregar Bootstrap dinamicamente se necessário
function loadBootstrap() {
    console.log('Carregando Bootstrap dinamicamente...');
    
    // Carrega CSS
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css';
    document.head.appendChild(link);
    
    // Carrega JS
    const script = document.createElement('script');
    script.src = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js';
    script.onload = function() {
        console.log('Bootstrap carregado dinamicamente');
        // Recarrega a funcionalidade após Bootstrap carregar
        setTimeout(() => window.location.reload(), 100);
    };
    document.head.appendChild(script);
}
</script>
