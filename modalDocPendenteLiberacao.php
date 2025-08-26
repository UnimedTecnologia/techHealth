<style>
    .is-invalid {
        border-color: #dc3545 !important;
        background-color: #fff8f8 !important;
    }
    .is-invalid:focus {
        box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25) !important;
    }
    .form-floating-label .is-invalid ~ label {
        color: #dc3545 !important;
    }
    .colorGray{
        color: lightgray;
    }
    .modal-content {
        max-height: 90vh;
        overflow-y: auto;
    }

</style>
<!-- Modal Documentos Pendentes de Liberação-->
<div class="modal fade" id="modalDocumentosPendenteLiberacao" tabindex="-1" aria-labelledby="modalDocumentosPendenteLiberacaoLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDocumentosPendenteLiberacaoLabel">Consulta documentos pendentes de liberação </h5>
                 <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar" ></button>
            </div>
            <div class="modal-body">
                <div class="tab-content mt-3">
                    <div class="tab-pane fade show active" id="guias11" role="tabpanel" aria-labelledby="primeiro-tab">
                        <form id="formDocPendLib" action="contas_medicas/documentosPendenteLiberacao/docPendenteLiberacao.php" method="POST" style="max-width: 600px; margin: 0 auto;">
                            <div class="row justify-content-center" >
   
                                <div class="col-md-5 mb-3">
                                    <div class="form-floating-label">
                                        <input id="anoDocPend" type="number" class="form-control inputback" name="ano" required>
                                        <label for="anoDocPend">Ano</label>
                                    </div>
                                </div>

                                <div class="col-md-5 mb-3">
                                    <div class="form-floating-label">
                                        <input id="periodoDocPend" type="number" class="form-control inputback" name="periodo" required>
                                        <label for="periodoDocPend">Período</label>
                                    </div>
                                </div>

                                <div class="col-md-10 mb-3">
                                    <div class="form-floating-label">
                                        <input id="grupoDocPend" type="text" class="form-control inputback" name="grupo" placeholder=" " >
                                        <label for="grupoDocPend">Grupo de prestadores (separados por vírgula)</label>
                                    </div>
                                </div>

                            </div>

                            <div class="text-center mt-4">
                                <button id="btConsultarDocs" type="submit" class="btn btn-success">Consultar</button>
                            </div>
                        </form>
                        <div id="resultadoDocPend" class="mt-4"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- //!LIMPAR MODAL -->
<script>
document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("modalDocumentosPendenteLiberacao");
    const form  = document.getElementById("formDocPendLib");
    const resultado = document.getElementById("resultadoDocPend");

    // Quando o modal for fechado → limpa tudo
    modal.addEventListener("hidden.bs.modal", () => {
        form.reset();            // limpa campos
        resultado.innerHTML = ""; // limpa resultados
    });

    // (Opcional) Quando o modal for aberto → já garante que está limpo
    modal.addEventListener("show.bs.modal", () => {
        form.reset();
        resultado.innerHTML = "";
    });
});
</script>


<script>
    document.getElementById("formDocPendLib").addEventListener("submit", async function (e) {
    e.preventDefault();

    // Elementos UI
    const loaderGuia = document.getElementById("loadingOverlay");
    const botaoConsultaDocs = document.getElementById("btConsultarDocs");
    const msgLoader = document.getElementById("msgLoader");

    // Configuração do loader
    msgLoader.innerText = "Consultando documentos, aguarde por favor...";
    loaderGuia.style.display = "flex";
    botaoConsultaDocs.disabled = true;

    try {
        const formData = new FormData(this);
        const response = await fetch("contas_medicas/documentosPendenteLiberacao/docPendenteLiberacao.php", {
            method: "POST",
            body: formData
        });

        if (!response.ok) {
            throw new Error(`Erro HTTP: ${response.status}`);
        }

        const data = await response.json();

        if (data.success) {
            //!CARREGA DADOS NA TELA
            if (data.success) {
            const container = document.getElementById("resultadoDocPend");

            // Limpa resultados anteriores
            container.innerHTML = "";

            // Cria tabela
            let tabela = `
                <table class="table table-striped table-bordered mt-3">
                    <thead>
                        <tr>
                            <th>Código Prestador</th>
                            <th>Nome Prestador</th>
                            <th>Pend. Glosa</th>
                            <th>Pend. Liberação</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            data.dado.forEach(row => {
                tabela += `
                    <tr>
                        <td>${row.CD_PRESTADOR_PRINCIPAL}</td>
                        <td>${row.NM_PRESTADOR}</td>
                        <td>${row.PEND_GLOSA}</td>
                        <td>${row.PEND_LIBERACAO}</td>
                    </tr>
                `;
            });

            tabela += "</tbody></table>";

            container.innerHTML = tabela;
        }


        } else {
            alert(data.message || "Erro ao consultar dados.");
        }
    } catch (err) {
        console.error("Erro:", err);
        alert("Erro ao consultar documentos - " + err.message);
    } finally {
        // Sempre esconde o loader, independente de sucesso ou erro
        loaderGuia.style.display = "none";
        botaoConsultaDocs.disabled = false;
    }
});
</script>