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
<div class="modal fade" id="modalDadosProfissionalExecutante" tabindex="-1" aria-labelledby="modalDadosProfissionalExecutanteLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDadosProfissionalExecutanteLabel">Dados profissional executante</h5>
                 <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar" ></button>
            </div>
            <div class="modal-body">
                <div class="tab-content mt-3">
                    <div class="tab-pane fade show active" id="guias11" role="tabpanel" aria-labelledby="primeiro-tab">
                        <form id="formDadosProfExec" method="POST" style="max-width: 600px; margin: 0 auto;">
                            <div class="row justify-content-center" >
   
                                <div class="col-md-5 mb-3">
                                    <div class="form-floating-label">
                                        <input id="anoDadosProExec" type="number" class="form-control inputback" name="ano" required>
                                        <label for="anoDadosProExec">Ano</label>
                                    </div>
                                </div>

                                <div class="col-md-5 mb-3">
                                    <div class="form-floating-label">
                                        <input id="periodoDadosProExec" type="number" class="form-control inputback" name="nr_periodo" required>
                                        <label for="periodoDadosProExec">Período</label>
                                    </div>
                                </div>

                                <div class="col-md-5 mb-3">
                                    <div class="form-floating-label">
                                        <input id="nr_docDadosProExec" type="text" class="form-control inputback" name="nr_doc" placeholder=" " required>
                                        <label for="nr_docDadosProExec">Numero do documento</label>
                                    </div>
                                </div>
                                
                                <div class="col-md-5 mb-3">
                                    <div class="form-floating-label">
                                        <input id="cd_transacaoDadosProExec" type="text" class="form-control inputback" name="cd_transacao" placeholder=" " required>
                                        <label for="cd_transacaoDadosProExec">Transação</label>
                                    </div>
                                </div>

                            </div>

                            <div class="text-center mt-4">
                                <button id="btnConsultarDadosProExec" type="submit" class="btn btn-success">Consultar</button>
                            </div>
                        </form>
                        <div id="resultadoDadosProfExec" class="mt-4" ></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- //!LIMPAR MODAL -->
<script>
document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("modalDadosProfissionalExecutante");
    const form  = document.getElementById("formDadosProfExec");
    const resultado = document.getElementById("resultadoDadosProfExec");
    
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
async function carregarProfissionalExecutante(formData) {
    const loaderGuia = document.getElementById("loadingOverlay");
    const botaoConsultaDocs = document.getElementById("btConsultarDocs");
    const msgLoader = document.getElementById("msgLoader");

    // Configuração do loader
    msgLoader.innerText = "Consultando documentos, aguarde por favor...";
    loaderGuia.style.display = "flex";
    botaoConsultaDocs.disabled = true;

    try {
        const response = await fetch("faturamento/dadosProfissionalExecutante/consultaProfissionalExecutante.php", {
            method: "POST",
            body: formData
        });

        if (!response.ok) throw new Error(`Erro HTTP: ${response.status}`);

        const data = await response.json();

        const container = document.getElementById("resultadoDadosProfExec");
        container.innerHTML = "";

        if (!data.success || data.dado.length === 0) {
            loaderGuia.style.display = "none";
            botaoConsultaDocs.disabled = false;
            container.innerHTML = "<p>Nenhum registro encontrado.</p>";
            return;
        }

        // Mapa de renomeação
const aliasColunas = {
    "CHAR_4":  "NOME_PROF",
    "CHAR_13": "TIPO_CONSELHO",
    "CHAR_14": "NR_CONSELHO",
    "CHAR_15": "UF_CONSELHO",
};

const colunas = Object.keys(data.dado[0]);

let tabela = `<div style="max-height:400px; overflow:auto">
    <table class="table table-striped table-bordered mt-3">
        <thead>
            <tr>
                ${colunas.map(col => `<th>${aliasColunas[col] || col}</th>`).join("")}
            </tr>
        </thead>
        <tbody>
`;

data.dado.forEach(row => {
    tabela += `<tr>${colunas.map(col => `<td>${row[col]}</td>`).join("")}</tr>`;
});

tabela += "</tbody></table></div>";

        //! CHAR_4  = Nome profissional
        //! CHAR_13 = tipo do conselho (CRM)
        //! CHAR_14 = numero conselho (161802)
        //! CHAR_15 = uf conselho (SP)
        //! CHAR_19 = Concatenar (Char13;Char_14;Char_15;+CPF)

        // Botão para executar o update
        tabela += `
            <form id="formUpdateProfExec" action="faturamento/dadosProfissionalExecutante/updateProfissionalExecutante.php" method="POST" style="max-width: 600px; margin: 0 auto;">
                <div class="row justify-content-center" >
                    <div class="col-md-12 mb-3">
                        <div class="form-floating-label">
                            <input id="nomeProfExec" type="text" class="form-control inputback" autocomplete="off"
                                    name="nomeProfExec" placeholder=" " required list="listaProfissionais">
                            <label for="nomeProfExec">Nome do Profissional</label>
                            <datalist id="listaProfissionais"></datalist>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="form-floating-label">
                            <input id="tipoConselho" type="text" class="form-control inputback" name="tipoConselho" placeholder=" " required>
                            <label for="tipoConselho">Tipo Conselho</label>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="form-floating-label">
                            <input id="nrConselho" type="text" class="form-control inputback" name="nrConselho" placeholder=" " required>
                            <label for="nrConselho">Número Conselho</label>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="form-floating-label">
                            <input id="ufConselho" type="text" class="form-control inputback" name="ufConselho" placeholder=" " required>
                            <label for="ufConselho">UF Conselho</label>
                        </div>
                    </div>
                    <input id="dadosProfChar_19" type="hidden" class="form-control inputback" name="char_19" placeholder=" " >
                    
                    <input id="ano2DadosProExec" type="hidden" class="form-control inputback" name="ano" required>
                    <input id="periodo2DadosProExec" type="hidden" class="form-control inputback" name="nr_periodo" required>
                    <input id="nr_doc2DadosProExec" type="hidden" class="form-control inputback" name="nr_doc" placeholder=" " required>
                    <input id="cd_transacao2DadosProExec" type="hidden" class="form-control inputback" name="cd_transacao" placeholder=" " required>
                </div>

                <div class="mt-3">
                    <button id="btnUpdateProfExec" type="button" class="btn btn-primary">Atualizar Profissional Executante</button>
                </div>
            </form>`;

        container.innerHTML = tabela;

        // Preenche os hidden inputs do form de update com os valores do form de consulta
        document.getElementById("ano2DadosProExec").value = 
            document.getElementById("anoDadosProExec").value;

        document.getElementById("periodo2DadosProExec").value = 
            document.getElementById("periodoDadosProExec").value;

        document.getElementById("nr_doc2DadosProExec").value = 
            document.getElementById("nr_docDadosProExec").value;

        document.getElementById("cd_transacao2DadosProExec").value = 
            document.getElementById("cd_transacaoDadosProExec").value;


            
        const nomeProfExec = document.getElementById("nomeProfExec");
        const listaProfissionais = document.getElementById("listaProfissionais");

        // Busca enquanto digita
        nomeProfExec.addEventListener("input", async () => {
            const nome = nomeProfExec.value.trim();
            if (nome.length < 3) return; // só busca se tiver pelo menos 3 letras

            try {
                const formData = new FormData();
                formData.append("nome", nome);

                const resp = await fetch("faturamento/dadosProfissionalExecutante/buscarProfissional.php", {
                    method: "POST",
                    body: formData
                });

                const data = await resp.json();
                if (!data.success) return;

                listaProfissionais.innerHTML = "";

                data.dado.forEach(prof => {
                    const opt = document.createElement("option");
                    opt.value = prof.NOM_PROFIS; // mostra nome no input
                    opt.dataset.conselho = prof.COD_CONS_MEDIC;
                    opt.dataset.uf = prof.COD_UF_CONS;
                    opt.dataset.registro = prof.COD_REGISTRO;
                    opt.dataset.cpf = prof.COD_CPF;
                    listaProfissionais.appendChild(opt);
                });

            } catch (err) {
                console.error("Erro busca profissional:", err);
            }
        });

        // Preenche automaticamente os campos ao sair do input
        nomeProfExec.addEventListener("change", () => {
            const opt = [...listaProfissionais.options].find(o => o.value === nomeProfExec.value);
            if (opt) {
                document.getElementById("tipoConselho").value = opt.dataset.conselho || "";
                document.getElementById("nrConselho").value   = opt.dataset.registro || "";
                document.getElementById("ufConselho").value   = opt.dataset.uf || "";
                let cpfProfissional                           = opt.dataset.cpf || "";

            }
            let char_19 =  opt.dataset.conselho+";"+opt.dataset.registro+";"+opt.dataset.uf+";"+opt.dataset.cpf;
            document.getElementById("dadosProfChar_19").value = char_19;
        });


        // Evento do botão update
        document.getElementById("btnUpdateProfExec").addEventListener("click", async () => {
            msgLoader.innerText = "Atualizando, aguarde por favor...";
            loaderGuia.style.display = "flex";
            botaoConsultaDocs.disabled = true;

            try {
                // Pega o form de update
                const formUpdate = document.getElementById("formUpdateProfExec");
                const updateFormData = new FormData(formUpdate);

                const updateResponse = await fetch("faturamento/dadosProfissionalExecutante/updateProfissionalExecutante.php", {
                    method: "POST",
                    body: updateFormData
                });

                const updateData = await updateResponse.json();

                if (updateData.success) {
                    Swal.fire("Sucesso!", updateData.message || "Atualizado com sucesso!", "success");
                    // Recarregar dados usando o form original da consulta
                    const consultaForm = document.getElementById("formDadosProfExec");
                    const consultaData = new FormData(consultaForm);
                    carregarProfissionalExecutante(consultaData);
                } else {
                    Swal.fire("Erro!", updateData.message || "Falha ao atualizar.", "error");
                }
            } catch (err) {
                Swal.fire("Erro!", "Erro ao executar update: " + err.message, "error");
            }
            loaderGuia.style.display = "none";
            botaoConsultaDocs.disabled = false;
        });

        

    } catch (err) {
        console.error("Erro:", err);
        Swal.fire("Erro!", "Erro ao carregar dados: " + err.message, "error");
    }
    loaderGuia.style.display = "none";
    botaoConsultaDocs.disabled = false;
}

//!enviar formulario - buscar profissional executante
document.getElementById("formDadosProfExec").addEventListener("submit", function (e) {
    e.preventDefault();
    const formData = new FormData(this);
    carregarProfissionalExecutante(formData);
});    
</script>

