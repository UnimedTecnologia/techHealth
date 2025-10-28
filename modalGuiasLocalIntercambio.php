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
    const container = document.getElementById("tagInputContainer");
    const input = document.getElementById("tagInput");
    const hiddenInput = document.getElementById("matriculaAutorizacao");
    let tags = ["114000586", "114000562", "114000595", "114000563", "114000535"]; // valores padrão

    function renderTags() {
        container.querySelectorAll(".tag").forEach(tag => tag.remove());
        tags.forEach((matr, index) => {
            const tagEl = document.createElement("div");
            tagEl.classList.add("tag");
            tagEl.innerHTML = `${matr} <button type="button" data-index="${index}">&times;</button>`;
            container.insertBefore(tagEl, input);
        });
        hiddenInput.value = tags.join(",");
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
            tags.pop();
            renderTags();
        }
    });

    container.addEventListener("click", (e) => {
        if (e.target.tagName === "BUTTON") {
            const index = e.target.dataset.index;
            tags.splice(index, 1);
            renderTags();
        }
    });

    renderTags();

    document.getElementById("formGuiaLocalIntercambio").addEventListener("submit", function (e) {
        e.preventDefault();

        const loaderGuia = document.getElementById("loadingOverlay");
        const botaoGuia = document.getElementById("btRelatorioGuiasLocalIntercambio");
        const msgLoader = document.getElementById("msgLoader");
        if (msgLoader) msgLoader.innerText = "Gerando relatório de Guias, aguarde por favor...";
        if (loaderGuia) loaderGuia.style.display = "flex";
        botaoGuia.disabled = true;

        const form = e.target;
        const formData = new FormData(form);

        fetch(form.action, { method: "POST", body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (loaderGuia) loaderGuia.style.display = "none";
                botaoGuia.disabled = false;
                window.location.href = data.file;
            } else {
                alert(data.error || "Erro ao gerar relatório.");
                if (loaderGuia) loaderGuia.style.display = "none";
                botaoGuia.disabled = false;
            }
        })
        .catch(err => {
            console.error("Erro:", err);
            alert("Erro ao gerar relatório.");
            if (loaderGuia) loaderGuia.style.display = "none";
            botaoGuia.disabled = false;
        });
    });
});
</script>
