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
<!-- Modal Produção Clinicas-->
<div class="modal fade" id="modalProducaoClinicas" tabindex="-1" aria-labelledby="modalProducaoClinicasLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalProducaoClinicasLabel">Relatório Produção Clínicas </h5>
                 <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar" ></button>
            </div>
            <div class="modal-body">
                <div class="tab-content mt-3">
                    <div class="tab-pane fade show active" id="guias11" role="tabpanel" aria-labelledby="primeiro-tab">
                        <iframe name="iframeDownload" style="display: none;"></iframe>
                        <form id="formProdClin" action="redeCredenciada/relatorioProducaoClinica.php" method="POST" target="iframeDownload" style="max-width: 600px; margin: 0 auto;">
                            <div class="row justify-content-center" >
   
                                <div class="col-md-5 mb-3">
                                    <div class="form-floating-label">
                                        <input id="dtInicioProdClin" type="month" class="form-control inputback" name="dtInicioProdClin" required>
                                        <label for="dtInicioProdClin">Mês início *</label>
                                    </div>
                                </div>

                                <div class="col-md-5 mb-3">
                                    <div class="form-floating-label">
                                        <input id="dtFimProdClin" type="month" class="form-control inputback" name="dtFimProdClin" required>
                                        <label for="dtFimProdClin">Mês fim *</label>
                                    </div>
                                </div>

                                <div class="col-md-10 mb-3">
                                    <div class="form-floating-label">
                                        <input id="grupoPres" type="text" class="form-control inputback" name="gruposPres" placeholder=" " required>
                                        <label for="grupoPres">Grupo de prestadores (separados por vírgula)</label>
                                    </div>
                                </div>

                            </div>

                            <div class="text-center mt-4">
                                <button id="btRelatorioProdClin" type="submit" class="btn btn-success">Gerar relatório</button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById("grupoPres").addEventListener("input", function () {
    this.value = this.value.replace(/[^0-9,]/g, '');
});

</script>



<!-- <script>
    document.getElementById("formProdClin").addEventListener("submit", function (e) {
    e.preventDefault(); // Impede envio padrão

    const inicio = document.getElementById("dtInicioProdClin").value; // formato YYYY-MM
    const fim = document.getElementById("dtFimProdClin").value;

    if (!inicio || !fim) {
        alert("Por favor, selecione o mês de início e fim.");
        return;
    }

    const anoInicio = parseInt(inicio.split("-")[0]);
    const mesInicio = parseInt(inicio.split("-")[1]);

    const anoFim = parseInt(fim.split("-")[0]);
    const mesFim = parseInt(fim.split("-")[1]);

    // 🔴 Verifica se anos são diferentes
    if (anoInicio !== anoFim) {
        alert("Selecione um intervalo dentro do mesmo ano.");
        return;
    }

    // 🔴 Verifica se mês/ano de início é depois do fim
    if (anoInicio === anoFim && mesInicio > mesFim) {
        alert("O mês de início não pode ser maior que o mês de fim.");
        return;
    }

    // ✅ Passou validações, segue com envio
    const loaderGuia = document.getElementById("loadingOverlay");
    const botaoGuia = document.getElementById("btRelatorioProdClin");
    const msgLoader = document.getElementById("msgLoader");
    msgLoader.innerText = "Gerando relatório Excel, aguarde por favor...";
    loaderGuia.style.display = "flex";
    botaoGuia.disabled = true;

    // Após 1 minuto, muda a mensagem
    setTimeout(() => {
        msgLoader.innerText = "Esse relatório pode levar algum tempo para ser gerado... Aguarde por favor";
    }, 60000); // 60.000ms = 1 minuto


    const form = e.target;
    const formData = new FormData(form);

    fetch("redeCredenciada/relatorioProducaoClinica.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        console.log(data);
        loaderGuia.style.display = "none";
        botaoGuia.disabled = false;

        if (data.success) {
            window.location.href = data.file;
        } else {
            alert(data.message || "Erro ao gerar relatório.");
        }
    })
    .catch(err => {
        console.error("Erro:", err);
        alert("Erro ao gerar relatório.");
        loaderGuia.style.display = "none";
        botaoGuia.disabled = false;
    });
});

</script> -->



<script>
    document.getElementById("formProdClin").addEventListener("submit", async function (e) {
    e.preventDefault();

    // Elementos UI
    const loaderGuia = document.getElementById("loadingOverlay");
    const botaoGuia = document.getElementById("btRelatorioProdClin");
    const msgLoader = document.getElementById("msgLoader");
    
    // Validações
    const inicio = document.getElementById("dtInicioProdClin").value;
    const fim = document.getElementById("dtFimProdClin").value;

    if (!inicio || !fim) {
        alert("Por favor, selecione o mês de início e fim.");
        return;
    }

    const anoInicio = parseInt(inicio.split("-")[0]);
    const mesInicio = parseInt(inicio.split("-")[1]);
    const anoFim = parseInt(fim.split("-")[0]);
    const mesFim = parseInt(fim.split("-")[1]);

    if (anoInicio !== anoFim) {
        alert("Selecione um intervalo dentro do mesmo ano.");
        return;
    }

    if (anoInicio === anoFim && mesInicio > mesFim) {
        alert("O mês de início não pode ser maior que o mês de fim.");
        return;
    }

    // Configuração do loader
    msgLoader.innerText = "Gerando relatório Excel, aguarde por favor...";
    loaderGuia.style.display = "flex";
    botaoGuia.disabled = true;

    // Atualiza mensagem após 1 minuto
    const timeoutId = setTimeout(() => {
        msgLoader.innerText = "Esse relatório pode levar algum tempo para ser gerado... Aguarde por favor";
    }, 60000);

    try {
        const formData = new FormData(this);
        
        const response = await fetch("redeCredenciada/relatorioProducaoClinica.php", {
            method: "POST",
            body: formData
        });

        clearTimeout(timeoutId); // Cancela o timeout se a requisição completar antes

        if (!response.ok) {
            throw new Error(`Erro HTTP: ${response.status}`);
        }

        const data = await response.json();

        if (data.success) {
            // Cria link temporário para download
            const link = document.createElement('a');
            link.href = data.file;
            link.download = data.file.split('/').pop();
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        } else {
            alert(data.message || "Erro ao gerar relatório.");
        }
    } catch (err) {
        console.error("Erro:", err);
        alert("Erro ao gerar relatório: " + err.message);
    } finally {
        // Sempre esconde o loader, independente de sucesso ou erro
        loaderGuia.style.display = "none";
        botaoGuia.disabled = false;
    }
});
</script>