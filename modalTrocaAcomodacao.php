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
    .modal-content {
        max-height: 90vh;
        overflow-y: auto;
    }

</style>
<!-- Modal Troca Acomodacao-->
<div class="modal fade" id="modalTrocadeAcomodacao" tabindex="-1" aria-labelledby="modalTrocadeAcomodacaoLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTrocadeAcomodacaoLabel">Troca de acomodação </h5>
                 <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar" ></button>
            </div>
            <div class="modal-body">
                <div class="tab-content mt-3">
                    <div class="tab-pane fade show active" id="guias11" role="tabpanel" aria-labelledby="primeiro-tab">
                        <!-- <form id="formTrocaAcomodacao" action="contas_medicas/trocaAcomodacao/updateTrocaAcomodacao.php" method="POST" style="max-width: 600px; margin: 0 auto;"> -->
                        <form id="formTrocaAcomodacao" action="contas_medicas/trocaAcomodacao/consultaAcomodacao.php" method="POST" style="max-width: 600px; margin: 0 auto;">
                            <div class="row justify-content-center" >
   
                                <div class="col-md-5 mb-3">
                                    <div class="form-floating-label">
                                        <input id="anoTrocaAcomodacao" type="number" class="form-control inputback" name="ano" required>
                                        <label for="anoTrocaAcomodacao">Ano</label>
                                    </div>
                                </div>

                                <div class="col-md-5 mb-3">
                                    <div class="form-floating-label">
                                        <input id="periodoTrocaAcomodacao" type="number" class="form-control inputback" name="nr_periodo" required>
                                        <label for="periodoTrocaAcomodacao">Período</label>
                                    </div>
                                </div>

                                <div class="col-md-5 mb-3">
                                    <div class="form-floating-label">
                                        <input id="nr_docTrocaAcomodacao" type="text" class="form-control inputback" name="nr_doc" placeholder=" " required>
                                        <label for="nr_docTrocaAcomodacao">Numero do documento</label>
                                    </div>
                                </div>
                                
                                <div class="col-md-5 mb-3">
                                    <div class="form-floating-label">
                                        <input id="cd_transacaoTrocaAcomodacao" type="text" class="form-control inputback" name="cd_transacao" placeholder=" " required>
                                        <label for="cd_transacaoTrocaAcomodacao">Transação</label>
                                    </div>
                                </div>

                            </div>

                            <div class="text-center mt-4">
                                <button id="btnConsultarAcomodacao" type="submit" class="btn btn-success">Consultar</button>
                            </div>
                        </form>
                        <div id="resultadoTrocaAcomodacao" class="mt-4"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- //!LIMPAR MODAL -->
<script>
document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("modalTrocadeAcomodacao");
    const form  = document.getElementById("formTrocaAcomodacao");
    const resultado = document.getElementById("resultadoTrocaAcomodacao");
    
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
// Função para carregar os dados e montar a tabela
async function carregarAcomodacao(formData) {
    const loaderGuia = document.getElementById("loadingOverlay");
    const botaoConsultaDocs = document.getElementById("btConsultarDocs");
    const msgLoader = document.getElementById("msgLoader");

    // Configuração do loader
    msgLoader.innerText = "Consultando documentos, aguarde por favor...";
    loaderGuia.style.display = "flex";
    botaoConsultaDocs.disabled = true;

    try {
        const response = await fetch("contas_medicas/trocaAcomodacao/consultaAcomodacao.php", {
            method: "POST",
            body: formData
        });

        if (!response.ok) throw new Error(`Erro HTTP: ${response.status}`);

        const data = await response.json();

        const container = document.getElementById("resultadoTrocaAcomodacao");
        container.innerHTML = "";

        if (!data.success || data.dado.length === 0) {
            loaderGuia.style.display = "none";
            botaoConsultaDocs.disabled = false;
            container.innerHTML = "<p>Nenhum registro encontrado.</p>";
            return;
        }

        const colunas = Object.keys(data.dado[0]);
        let tabela = `<div style="max-height:400px; overflow:auto">
            <table class="table table-striped table-bordered mt-3">
                <thead>
                    <tr>${colunas.map(col => `<th>${col}</th>`).join("")}</tr>
                </thead>
                <tbody>
        `;

        data.dado.forEach(row => {
            tabela += `<tr>${colunas.map(col => `<td>${row[col]}</td>`).join("")}</tr>`;
        });

        tabela += "</tbody></table></div>";

        // Botão para executar o update
        tabela += `
            <div class="mt-3">
                <button id="btnUpdateAcomodacao" class="btn btn-primary">Atualizar Acomodação</button>
            </div>
        `;

        container.innerHTML = tabela;

        // Evento do botão update
        document.getElementById("btnUpdateAcomodacao").addEventListener("click", async () => {
            
            msgLoader.innerText = "Atualizando, aguarde por favor...";
            loaderGuia.style.display = "flex";
            botaoConsultaDocs.disabled = true;

            try {
                const updateResponse = await fetch("contas_medicas/trocaAcomodacao/updateTrocaAcomodacao.php", {
                    method: "POST",
                    body: formData
                });

                const updateData = await updateResponse.json();

                if (updateData.success) {
                    Swal.fire("Sucesso!", updateData.message || "Atualizado com sucesso!", "success");
                    // Recarregar dados
                    carregarAcomodacao(formData);
                } else {
                    Swal.fire("Erro!", updateData.message || "Falha ao atualizar.", "error");
                }
            } catch (err) {
                Swal.fire("Erro!", "Erro ao executar update: " + err.message, "error");
            }
        });
        

    } catch (err) {
        console.error("Erro:", err);
        Swal.fire("Erro!", "Erro ao carregar dados: " + err.message, "error");
    }
    loaderGuia.style.display = "none";
    botaoConsultaDocs.disabled = false;
}


// Listener do formulário principal
document.getElementById("formTrocaAcomodacao").addEventListener("submit", function (e) {
    e.preventDefault();
    const formData = new FormData(this);
    carregarAcomodacao(formData);
});

</script>
