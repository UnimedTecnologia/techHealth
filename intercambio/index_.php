<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atualização de status guia</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" >
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.24/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.24/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <link href="../style.css" rel="stylesheet" />
    
</head>
<body>
    <!--//! loader  -->
    <div id="loadingOverlay" style="display:none">
        <div class="spinner"></div>
        <p id="msgLoader">Carregando</p>
    </div>

    <div class="container d-flex flex-column align-items-center justify-content-center min-vh-100">
        <h5 class="text-center mb-4">Atualização de status guia</h5>
        <form id="alteraStatus" action="get_statusGuia" method="POST">
            <div class="col-12 col-md-12">
                <div class="mb-3 form-floating-label">
                    <input type="text" id="nrGuia" pattern="\d*" maxlength="10" name="nrGuia" class="form-control inputback" placeholder=" " required>
                    <label for="nrGuia">Número Guia</label>
                </div> 
                <div class="mb-3 form-floating-label">
                    <input type="text" id="anoGuia" pattern="\d*" maxlength="4" name="anoGuia" class="form-control inputback" placeholder=" " required>
                    <label for="nrGuia">Ano Guia</label>
                </div> 
                <button class="btn btn-success w-100">Buscar</button>
            </div>
        </form>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1">
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
                        <label for="status"><strong>Novo Status:</strong></label>
                        <select id="status" name="status" style="border-radius:10px; height: 40px;">
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
                        <div id="divProc" class="mb-3 form-floating-label" >
                            <input id="senhaOrigem" type="text" pattern="\d*" maxlength="10" class="form-control inputback" name="senhaOrigem" placeholder=" " required >
                            <label for="senhaOrigem">Senha origem</label>
                        </div>
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-success">Atualizar</button>
                        </div>
                    </form>
                    
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.getElementById("alteraStatus").addEventListener("submit", function (e) {
            e.preventDefault();

            const formData = new FormData(this);

            axios.post('get_statusGuia.php', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            })
                .then(function (response) {
                    console.log(response.data);
                    if(response.data.error){
                        alert(response.data.message);
                    }else{
                        // console.log(response.data.data[0].NR_GUIA_ATENDIMENTO);
                        document.getElementById("modalNrGuia").innerText = response.data.data[0].NR_GUIA_ATENDIMENTO;
                        document.getElementById("modalStatus").innerText = response.data.data[0].STATUS_GUIA;
                        document.getElementById("status").value = response.data.data[0].IN_LIBERADO_GUIAS;

                        // Abrir modal
                        var modal = new bootstrap.Modal(document.getElementById("statusModal"));
                        modal.show();
                    }
                })
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

            axios.post('update_statusGuia.php', {
                AA: AA,
                NR: NR,
                status: document.getElementById("status").value,
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

</body>
</html>