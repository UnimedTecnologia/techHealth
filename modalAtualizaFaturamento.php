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
<div class="modal fade" id="modalAtualizaFaturamento" tabindex="-1" aria-labelledby="modalAtualizaFaturamentoLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAtualizaFaturamentoLabel">Atualizar Faturamento</h5>
                <!-- <button id="closeModalPrest" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> -->
                 <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar" ></button>
            </div>
            <div class="modal-body">
                <!-- Nav tabs -->
                <ul class="nav nav-tabs" id="modalTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="fat-tab" data-bs-toggle="tab" data-bs-target="#fat" type="button" role="tab" aria-controls="fat" aria-selected="true">
                            Faturamento
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="segundo-tab" data-bs-toggle="tab" data-bs-target="#segundo" type="button" role="tab" aria-controls="segundo" aria-selected="false">
                            Faturamento Automático
                        </button>
                    </li>
                </ul>

                <!-- Tab content -->
                <div class="tab-content mt-3">
                    <!-- Primeira aba: Faturamento -->
                    <div class="tab-pane fade show active" id="fat" role="tabpanel" aria-labelledby="fat-tab">
                        <form id="formFat" method="POST">
                            <div class="mb-3 form-floating-label">
                                <input id="anoRefFat" type="text" pattern="\d*" class="form-control inputback" name="anoRefFat" placeholder=" " required>
                                <label for="anoRefFat">Ano Referência *</label>
                            </div>

                            <div class="mb-3 form-floating-label">
                                <input id="numRefFat" type="text" pattern="\d*" class="form-control inputback" name="numRefFat" placeholder=" " required>
                                <label for="numRefFat">Número Período Referência *</label>
                            </div>

                            <div class="text-center mt-4">
                                <button id="btRelatorioPrestadorFat" type="button" class="btn btn-success" onclick="startUpdates()">Gerar relatório</button>
                            </div>

                            <div class="text-center mt-2">
                                <div id="messageFat" style="margin-top: 10px; color: green;"></div>

                                <div class="progress mt-3" style="display:none;" id="progress-container">
                                    <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%">0%</div>
                                </div>
                            </div>

                            <div id="progressoUpdate" style="display:none">
                                <div class="d-flex justify-content-between">
                                    <p>Pacote com fator multiplicado = 1</p>
                                    <p id="pacoteMult" class="progresso colorGray">Aguardando</p>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <p>Erro 3013</p>
                                    <p id="erro3013" class="progresso colorGray">Aguardando</p>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <p>Erro 3016</p>
                                    <p id="erro3016" class="progresso colorGray">Aguardando</p>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Segunda aba: Faturamento Automatico : (14546,14551,14552,14553,14565,14568,14570,14666,14719,14856,14911,14913,15034,15035,15037,15038,22360,23644,23931,200537,500555,508100,800720,25154) -->
                    <div class="tab-pane fade" id="segundo" role="tabpanel" aria-labelledby="segundo-tab">
                        <!-- <form id="formSegundo" action="utils/update_faturamentoAutomatico.php/" method="POST"> -->
                        <form id="formSegundo" action="utils/faturamentoAutomatico.php/" method="POST">
                            <!-- <div class="mb-3 form-floating-label">
                                <input id="anoFatAuto" type="text" pattern="\d*" class="form-control inputback" name="anoFatAuto" placeholder=" " required>
                                <label for="anoFatAuto">Ano Referência *</label>
                            </div>

                            <div class="mb-3 form-floating-label">
                                <input id="periodoFatAuto" type="text" pattern="\d*" class="form-control inputback" name="periodoFatAuto" placeholder=" " required>
                                <label for="periodoFatAuto">Número Período Referência *</label>
                            </div> -->
                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-primary">Gerar faturamento automático</button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>


<script>
    function startUpdates() {
        console.log("Atualizando faturamento");
        iniciarProgresso();
        
        // Obter valores com trim() para remover espaços em branco
        const anoRefFat = document.getElementById('anoRefFat').value.trim();
        const numRefFat = document.getElementById('numRefFat').value.trim();
        
        // Validação melhorada com feedback visual
        if (numRefFat === '' || anoRefFat === '') {
            // Adiciona classes de erro
            document.getElementById('numRefFat').classList.toggle('is-invalid', numRefFat === '');
            document.getElementById('anoRefFat').classList.toggle('is-invalid', anoRefFat === '');
            
            // Mostra mensagem específica
            alert(`Por favor, preencha ${numRefFat === '' ? 'o Ano Referência' : 'o Número Período Referência'}`);
            return;
        }

        // Remove classes de erro se existirem
        document.getElementById('anoRefFat').classList.remove('is-invalid');
        document.getElementById('numRefFat').classList.remove('is-invalid');

        console.log('Valores capturados:', { anoRefFat, numRefFat }); // Para debug
    
        // Configurar elementos de UI
        document.getElementById('messageFat').style.display = 'block';
        document.getElementById('messageFat').textContent = 'Iniciando atualização...';
        document.getElementById('progress-container').style.display = 'block';
        document.getElementById('btRelatorioPrestadorFat').disabled = true;

        // Usando Fetch API com EventSource para acompanhar o progresso
        const eventSource = new EventSource(`utils/atualizaFaturamento.php?numRefFat=${numRefFat}&anoRefFat=${anoRefFat}`);
        
        eventSource.onmessage = function(e) {
            console.log('Recebido:', e.data); // Debug
            const data = JSON.parse(e.data);
            

            if (data.error) { //! Verifica retorno de erro
                document.getElementById('messageFat').style.color = 'red';
                document.getElementById('messageFat').textContent = 'Erro: ' + data.error;
                eventSource.close();
            } 
            else if (data.complete) { //! Verifica se completou todos os updates
                document.getElementById('messageFat').textContent = 'Atualização concluída com sucesso!';
                document.getElementById('progress-bar').style.width = '100%';
                document.getElementById('progress-bar').textContent = '100%';
                eventSource.close();
                
                document.getElementById('progress-container').style.display = 'none';
                document.getElementById('progress-bar').style.width = '0%';
                document.getElementById('progress-bar').textContent = '0%';
                document.getElementById('messageFat').style.display = 'none';

                
                // Habilitar botão após 2 segundos
                setTimeout(() => {
                    document.getElementById('btRelatorioPrestadorFat').disabled = false;
                }, 2000);
                
            } 
            else {//! Atualizar progresso
                
                document.getElementById('progress-bar').style.width = data.percent + '%';
                document.getElementById('progress-bar').textContent = data.percent + '%';
                document.getElementById('messageFat').textContent = data.message;
                
                document.getElementById('progressoUpdate').style.display = 'block';
                console.log(data.linhas +" linhas atualizadas");
                if(data.progresso != ""){
                    avancarProgresso();
                }
            }
        };

        eventSource.onerror = function() {
            document.getElementById('messageFat').style.color = 'red';
            document.getElementById('messageFat').textContent = 'Erro na conexão com o servidor';
            eventSource.close();
        };
    }


const steps = ['pacoteMult', 'erro3013', 'erro3016'];
let progressoAtualIndex = 0;

function iniciarProgresso() {
    progressoAtualIndex = 0;
    document.getElementById('progressoUpdate').style.display = 'block';
    atualizarProgresso();
}

function atualizarProgresso() {
    steps.forEach((id, index) => {
        const el = document.getElementById(id);
        if (index < progressoAtualIndex) {
            el.textContent = 'Concluído';
            el.style.color = 'green';
        } else if (index === progressoAtualIndex) {
            el.textContent = 'Em execução';
            el.style.color = 'orange';
        } else {
            el.textContent = 'Aguardando';
            el.style.color = 'lightgray';
        }
    });
}

function avancarProgresso() {
    progressoAtualIndex++;
    if (progressoAtualIndex < steps.length) {
        atualizarProgresso();
    } else {
        finalizarProgresso();
    }
}

function finalizarProgresso() {
    steps.forEach((id) => {
        const el = document.getElementById(id);
        el.textContent = 'Concluído';
        el.style.color = 'green';
    });
}

function resetarProgresso() {
    document.getElementById('progressoUpdate').style.display = 'none';
    steps.forEach((id) => {
        const el = document.getElementById(id);
        el.textContent = 'Aguardando';
        el.style.color = 'lightgray';
    });
    progressoAtualIndex = 0;
}


var modal = document.getElementById('modalAtualizaFaturamento');
var form = document.getElementById('formFat');
//* Limpa o formulário ao fechar o modal
modal.addEventListener('hidden.bs.modal', function () {
    resetarProgresso();
    form.reset();
    
    
});


//! Limpa o foco quando o modal está prestes a ser fechado
document.addEventListener('DOMContentLoaded', function() {
  const modal = document.getElementById('modalAtualizaFaturamento');
  
  modal.addEventListener('hide.bs.modal', function() {
    if (modal.contains(document.activeElement)) {
      document.activeElement.blur();
    }
  });
});



</script>