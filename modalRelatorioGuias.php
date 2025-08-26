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
<!-- Modal Atualiza Faturamento-->
<div class="modal fade" id="modalRelatorioGuias" tabindex="-1" aria-labelledby="modalRelatorioGuiasLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalRelatorioGuiasLabel">Relatório Guias </h5>
                 <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar" ></button>
            </div>
            <div class="modal-body">
                <!-- Nav tabs -->
                <ul class="nav nav-tabs" id="modalTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="primeiro-tab" data-bs-toggle="tab" data-bs-target="#guias11" type="button" role="tab" aria-controls="fat" aria-selected="true">
                            Guias 11 (Consultas)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="segundo-tab" data-bs-toggle="tab" data-bs-target="#guias21" type="button" role="tab" aria-controls="segundo" aria-selected="false">
                              Guias 21 (Exames)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="terceiro-tab" data-bs-toggle="tab" data-bs-target="#guias31" type="button" role="tab" aria-controls="segundo" aria-selected="false">
                              Guias 31 (Internações)
                        </button>
                    </li>
                </ul>

                <!-- Tab content -->
                <div class="tab-content mt-3">
                    <!-- //!ABA GUIAS 11 -->
                    <div class="tab-pane fade show active" id="guias11" role="tabpanel" aria-labelledby="primeiro-tab">
                        <iframe name="iframeDownload" style="display: none;"></iframe>
                        <form id="formPrimeiroGuia" action="autorizacoes/relatorioGuias11.php" method="POST" target="iframeDownload" style="max-width: 600px; margin: 0 auto;">
                            <div class="row justify-content-center" >
                                <div class="col-md-5 mb-3">
                                    <div class="form-floating-label">
                                        <input id="dtEmissaoIni11" type="date" pattern="\d*" class="form-control inputback" name="dtEmissaoIni11" placeholder=" " required>
                                        <label for="dtEmissaoIni11">Início data emissão *</label>
                                    </div>

                                </div>

                                <div class="col-md-5 mb-3">
                                    <div class="form-floating-label">
                                        <input id="dtEmisaoFim11" type="date" pattern="\d*" class="form-control inputback" name="dtEmisaoFim11" placeholder=" " required>
                                        <label for="dtEmisaoFim11">Fim data emissão *</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row justify-content-center " >
                                <div class="col-md-6 mb-3">
                                    <div class="form-floating-label">
                                        <input id="codCart11" type="text" pattern="\d*" class="form-control inputback" name="codCart11" placeholder=" ">
                                        <label for="codCart11">Código Carteira</label>
                                    </div>
                                </div>
                            </div>

                            <div class="text-center mt-4">
                                <button id="btRelatorioGuias11" type="submit" class="btn btn-success">Gerar relatório</button>
                            </div>
                        </form>
                    </div>

                    <!-- //!ABA GUIAS 21 -->
                    <div class="tab-pane fade" id="guias21" role="tabpanel" aria-labelledby="segundo-tab">
                        <form id="formSegundoGuia" action="autorizacoes/relatorioGuias21.php" method="POST" style="max-width: 600px; margin: 0 auto;">
                            <div class="row justify-content-center" >
                                <div class="col-md-5 mb-3">
                                    <div class="form-floating-label">
                                        <input id="dtEmissaoIni21" type="date" pattern="\d*" class="form-control inputback" name="dtEmissaoIni21" placeholder=" " required>
                                        <label for="dtEmissaoIni21">Início data emissão *</label>
                                    </div>
                                </div>

                                <div class="col-md-5 mb-3">
                                    <div class="form-floating-label">
                                        <input id="dtEmisaoFim21" type="date" pattern="\d*" class="form-control inputback" name="dtEmisaoFim21" placeholder=" " required>
                                        <label for="dtEmisaoFim21">Fim data emissão *</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row justify-content-center " >
                                <div class="col-md-6 mb-3">
                                    <div class="form-floating-label">
                                        <input id="codCart21" type="text" pattern="\d*" class="form-control inputback" name="codCart21" placeholder=" ">
                                        <label for="codCart21">Código Carteira</label>
                                    </div>
                                </div>
                            </div>

                            <div class="text-center mt-4">
                                <button id="btRelatorioGuias21" type="submit" class="btn btn-success" >Gerar relatório</button>
                            </div>
                        </form>
                    </div>

                    <!-- //! ABA GUIAS 31 -->
                    <div class="tab-pane fade" id="guias31" role="tabpanel" aria-labelledby="terceiro-tab">
                        <form id="formTerceiroGuia" action="autorizacoes/relatorioGuias31.php" method="POST" style="max-width: 600px; margin: 0 auto;">
                            <div class="row justify-content-center" >
                                <div class="col-md-5 mb-3">
                                    <div class="form-floating-label">
                                        <input id="dtEmissaoIni31" type="date" pattern="\d*" class="form-control inputback" name="dtEmissaoIni31" placeholder=" " required>
                                        <label for="dtEmissaoIni31">Início data emissão *</label>
                                    </div>
                                </div>

                                <div class="col-md-5 mb-3">
                                    <div class="form-floating-label">
                                        <input id="dtEmisaoFim31" type="date" pattern="\d*" class="form-control inputback" name="dtEmisaoFim31" placeholder=" " required>
                                        <label for="dtEmisaoFim31">Fim data emissão *</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row justify-content-center " >
                                <div class="col-md-6 mb-3">
                                    <div class="form-floating-label">
                                        <input id="codCart31" type="text" pattern="\d*" class="form-control inputback" name="codCart31" placeholder=" ">
                                        <label for="codCart31">Código Carteira</label>
                                    </div>
                                </div>
                            </div>

                            <div class="text-center mt-4">
                                <button id="btRelatorioGuias31" type="submit" class="btn btn-success" >Gerar relatório</button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>


<script>
document.getElementById("formPrimeiroGuia").addEventListener("submit", function (e) {
    e.preventDefault(); // Impede envio padrão

    const loaderGuia = document.getElementById("loadingOverlay");
    const botaoGuia = document.getElementById("btRelatorioGuias11");
    const msgLoader = document.getElementById("msgLoader");
    msgLoader.innerText = "Gerando relatório Excel, aguarde por favor...";
    loaderGuia.style.display = "flex";
    botaoGuia.disabled = true;

    const form = e.target;
    const formData = new FormData(form);

    fetch("autorizacoes/relatorioGuias11.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            loaderGuia.style.display = "none";
            botaoGuia.disabled = false;
            window.location.href = data.file; // inicia o download
        } else {
            alert("Erro ao gerar relatório.");
            loaderGuia.style.display = "none";
            botaoGuia.disabled = false;
        }
    })
    .catch(err => {
        console.error("Erro:", err);
        alert("Erro ao gerar relatório.");
        loaderGuia.style.display = "none";
        botaoGuia.disabled = false;
    });

});
</script>


<script>
document.getElementById("formSegundoGuia").addEventListener("submit", function (e) {
    e.preventDefault(); // Impede envio padrão

    const loaderGuia = document.getElementById("loadingOverlay");
    const botaoGuia = document.getElementById("btRelatorioGuias21");
    const msgLoader = document.getElementById("msgLoader");
    msgLoader.innerText = "Gerando relatório Excel, aguarde por favor...";
    loaderGuia.style.display = "flex";
    botaoGuia.disabled = true;

    const form = e.target;
    const formData = new FormData(form);

    fetch("autorizacoes/relatorioGuias21.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            loaderGuia.style.display = "none";
            botaoGuia.disabled = false;
            window.location.href = data.file; // inicia o download
        } else {
            alert("Erro ao gerar relatório.");
            loaderGuia.style.display = "none";
            botaoGuia.disabled = false;
        }
    })
    .catch(err => {
        console.error("Erro:", err);
        alert("Erro ao gerar relatório.");
        loaderGuia.style.display = "none";
        botaoGuia.disabled = false;
    });

});
</script>


<script>
document.getElementById("formTerceiroGuia").addEventListener("submit", function (e) {
    e.preventDefault(); // Impede envio padrão

    const loaderGuia = document.getElementById("loadingOverlay");
    const botaoGuia = document.getElementById("btRelatorioGuias31");
    const msgLoader = document.getElementById("msgLoader");
    msgLoader.innerText = "Gerando relatório Excel, aguarde por favor...";
    loaderGuia.style.display = "flex";
    botaoGuia.disabled = true;

    const form = e.target;
    const formData = new FormData(form);

    fetch("autorizacoes/relatorioGuias31.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {       
        if (data.success) {
            loaderGuia.style.display = "none";
            botaoGuia.disabled = false;
            window.location.href = data.file; // inicia o download
        } else {
            alert("Erro ao gerar relatório.");
            loaderGuia.style.display = "none";
            botaoGuia.disabled = false;
        }
    })
    .catch(err => {
        console.error("Erro:", err);
        alert("Erro ao gerar relatório.");
        loaderGuia.style.display = "none";
        botaoGuia.disabled = false;
    });

});
</script>
