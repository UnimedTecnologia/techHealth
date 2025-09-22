document.addEventListener("DOMContentLoaded", function () { 
    document.getElementById('loadingOverlay').style.display = 'none';
    
    //* Dados de autorização
    abrirModal($('#modalDadosdeautorizacao'), $("#prestadorAutorizacao"));
    //* Relatorio Prestador
    abrirModal($('#modalRelatorioPrestador'), $("#prestador"));
    //* Correção Prestador
    abrirModal($('#modalCorrecaoPrestador'), $("#correcaoPrestador"));
    //* Altera Prestador
    abrirModal($('#modalAlteraPrestador'), $("#prestadorTroca"));
    //* Altera Porcentagem Documento
    abrirModal($('#modalAlteraPorcentagemDocumento'), $("#prestadorAltPorcentDoc"));
    //* Relatório Titulo Prestador
    abrirModal($('#modalRelatorioTituloPrestador'), $("#prestadorTitulo"));
    
    //*Configura select no modal (prestadores.js)
    initSelect2('#prestadorAutorizacao', '#modalDadosdeautorizacao', 'Selecione um Prestador*');
    initSelect2('#prestador', '#modalRelatorioPrestador', 'Selecione um Prestador*');
    initSelect2('#correcaoPrestador', '#modalCorrecaoPrestador', 'Selecione um Prestador*');
    initSelect2('#grupoPrest', '#modalRelatorioPrestador', 'Selecione um Grupo de Prestadores*');
    initSelect2('#transacao', '#modalRelatorioPrestador', 'Selecione uma Transação');
    initSelect2('#prestadorTroca', '#modalAlteraPrestador', 'Selecione um Prestador*');
    initSelect2('#telas', '#modalCadastrar', ''); //* Input seleção de telas - permissão(Cadastro)
    initSelect2('#prestadorAltPorcentDoc', '#modalAlteraPorcentagemDocumento', 'Selecione um Prestador*');
    initSelect2('#prestadorTitulo', '#modalRelatorioTituloPrestador', 'Selecione um Prestador*');

    //*GET PRESTADOR E PREENCHE INPUT SELECT (prestadores.js) 
    getPrestador('utils/get_prestador.php', ['#prestador', '#prestadorTitulo', '#correcaoPrestador', '#prestadorAutorizacao', '#prestadorTroca', '#prestadorAltPorcentDoc']);
    
    //*GET GRUPO PRESTADORES
    getGrupoPrestador(['#grupoPrest']);

    //*PREENCHE INPUT DE TRANSAÇÕES
    getTransacoes();
    //!carrega telas liberadas do usuário
    getPermissoesTela();

    //* LIMPEZA DOS MODAIS
    //! MODAL AUTORIZAÇÃO
    var modal = document.getElementById('modalDadosdeautorizacao');
    var form = document.getElementById('formAutorizacao');
    //* Limpa o formulário ao fechar o modal
    modal.addEventListener('hidden.bs.modal', function () {
        form.reset();
    });
    
    //! MODAL RELATORIO PRESTADOR
    var modalPrest = document.getElementById('modalRelatorioPrestador');
    var formPrest = document.getElementById('formPrestador');
    modalPrest.addEventListener('hidden.bs.modal', function () {
        //* Código atual de limpeza
        const selecaoTransacaoDiv = document.getElementById('selecaoTransacao');
        selecaoTransacaoDiv.innerHTML = '';
        $('#prestador').val(null).trigger('change');
        $('#grupoPrest').val(null).trigger('change');
        const selecaoListaPrest = document.getElementById('listaPrestadores');
        selecaoListaPrest.innerHTML = '';
        formPrest.reset();
    
        //* Mover o foco para um elemento visível
        const triggerButton = document.querySelector('[data-bs-target="#modalRelatorioPrestador"]');
        if (triggerButton) {
            triggerButton.focus();
        }
    });

    //!MODAL TROCA PRESTADOR
    var modalTrocaPrest = document.getElementById('modalAlteraPrestador');
    var formTrocaPrest = document.getElementById('formTrocaPrestador');
     //* Limpa o formulário ao fechar o modal
     modalTrocaPrest.addEventListener('hidden.bs.modal', function () {
        formTrocaPrest.reset();
    });

    //!MODAL SITUAÇÃO CADASTRAL
    var modalSituacaoCadastral = document.getElementById('modalSituacaoCadastral');
    var formSituacaoCadastral = document.getElementById('formSituacaoCadastral');
     //* Limpa o formulário ao fechar o modal
     modalSituacaoCadastral.addEventListener('hidden.bs.modal', function () {
        formSituacaoCadastral.reset();
    });

    //! MODAL CADASTRAR USUARIO
    var modalCadUser = document.getElementById('modalCadastrar');
    var formCadUser = document.getElementById('formCadastrar');
    var formCadUserEdit = document.getElementById('formEditar');
    //* Limpa o formulário ao fechar o modal
    modalCadUser.addEventListener('hidden.bs.modal', function () {
        formCadUser.reset();
        formCadUserEdit.reset();
        // Seleciona as abas
        var abaCadastrar = document.getElementById('cadastrar');
        var abaEditar = document.getElementById('editar');
        var tabCadastrar = document.getElementById('tabCadastrar');
        var tabEditar = document.getElementById('tabEditar');

        // Remove as classes "active" e "show" da aba "Editar"
        abaEditar.classList.remove('active', 'show');
        tabEditar.classList.remove('active');

        // Adiciona as classes "active" e "show" à aba "Cadastrar"
        abaCadastrar.classList.add('active', 'show');
        tabCadastrar.classList.add('active');
    });

});

//!ABRIR MODAL
function abrirModal(idModal, idSelect){    
    idModal.on('show.bs.modal', function () {
        // console.log('Modal aberto:', idModal.attr('id'));
        //! Exibe apenas a div de prestadores e esconde a div de grupo de prestadores
        $("#divPrestadores").css("display","block");
        $("#divGrupo").css("display","none");
        // console.log(idSelect);

        $(document).on('keydown.preventEsc', function (e) {
            if (e.key === 'Escape' || e.keyCode === 27) {
                e.preventDefault(); //* Impede o fechamento do modal ao pressionar ESC
                return false; //* Impede que o evento continue e feche o modal
            }
        });
    });
}

//! GRUPO PRESTADORES
async function getGrupoPrestador(selectIds) {
    await axios({
        method: 'post',
        url: 'utils/get_grupo_prestador.php',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        data: {}
    })
    .then(function (response) {
        // console.log(response.data);
        if (response.data.error) {
            alert(response.data.message);
        } else {
            const prestadores = response.data.data;
            selectIds.forEach(selectId => {
                preencheInput(prestadores, $(selectId));
            });
        }
    })
    .catch(function (error) {
        console.error('Erro na requisição:', error);
    });
}

function preencheInput(prestadores, $select){
    $select.empty();
    $select.append('<option value="" selected>Grupo Prestador</option>');
    prestadores.forEach(prestador => {
        const Prest = `${prestador.CD_GRUPO_PRESTADOR} - ${prestador.DS_GRUPO_PRESTADOR}`;
        $select.append(`<option value="${prestador.CD_GRUPO_PRESTADOR}">${Prest}</option>`);
    });
}

//! TRANSAÇÕES
async function getTransacoes() {
    await axios({
        method: 'post',
        url: 'utils/get_transacao.php',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        data: {}
    })
    .then(function (response) {
        //console.log(response.data.data); 
        if (response.data.error) {
            alert(response.data.message); 
        } else {
            //! preenche select
            const transacoes = response.data.data; 
            const $select = $('#transacao');
            //* Limpa o select antes de adicionar as novas opções (caso necessário)
            $select.empty();
            //* Adiciona uma opção padrão novamente
            $select.append('<option value="" selected>Transacao</option>');
            //* Itera sobre os resultados e adiciona opções ao select
            transacoes.forEach(transacao => {
                var Transac = transacao.CD_TRANSACAO + " - " + transacao.DS_TRANSACAO;
                $select.append(`<option value="${transacao.CD_TRANSACAO}">${Transac}</option>`);
            });
        }
    })
    .catch(function (error) {
        console.error('Erro na requisição:', error);
    });
}

//! adicionar item na lista (prestadores ou transações)
function handleSelection(selectId, targetDivId) {

    const selectElement = document.getElementById(selectId);
    const selecaoTransacaoDiv = document.getElementById(targetDivId);

    const selectedValue = selectElement.value; //* Obtém o valor selecionado

    //* Verifica se o valor já está na div
    if (document.querySelector(`[data-value="${selectedValue}"]`)) {
        selectElement.selectedIndex = 0; //? remove o item selecionado do select
        return;
    }

    //* Cria um elemento para exibir a transação
   const itemDiv = document.createElement('div');
    itemDiv.className = 'lista-item'; //* Usa a classe CSS
    itemDiv.setAttribute('data-value', selectedValue);

    // *Adiciona o texto e o botão de remoção
    itemDiv.innerHTML = `
        <span>${selectedValue}</span>
        <button class="btnTrash "> <i class="bi bi-trash"></i></button>
    `; 

    //* Adiciona evento ao botão para remover o item
    itemDiv.querySelector('button').addEventListener('click', function () {
        itemDiv.remove();
    });

    //* Adiciona o item à div de seleção
    selecaoTransacaoDiv.appendChild(itemDiv);

    selectElement.selectedIndex = 0; //? remove o item selecionado do select
}

//! selecionar um prestador
$("#prestador").off("change").on("change", function () {
    handleSelection('prestador', 'listaPrestadores');
});
//! prestador titulo
// $("#prestadorTitulo").off("change").on("change", function () {
//     handleSelection('prestadorTitulo', 'listaPrestadoresTitulo');
// });

$("#transacao").off("change").on("change", function () {
    handleSelection('transacao', 'selecaoTransacao');
});

$("#grupoPrest").off("change").on("change", function () {
    handleSelection('grupoPrest', 'listaGrupo');
});


function exibirLoader(event) {
    //* Previne o envio padrão para controlar o comportamento via JavaScript
    event.preventDefault();
    const loadingOverlay = document.getElementById('loadingOverlay');
    const msgLoader = document.getElementById('msgLoader');

    //! VERIFICA SE SELECIONOU ALGUMA TRANSAÇÃO - NOTIFICA QUE PODE LEVAR MAIS TEMPO PARA GERAR
    if ($("#selecaoTransacao .lista-item").length === 0 && !relatorioGrupo.checked) {
        //* Exibe o SweetAlert2 com opção de continuar ou selecionar
        Swal.fire({
            title: 'Nenhuma Transação Selecionada!',
            text: 'Você não selecionou nenhuma transação. O relatório pode levar mais tempo para ser gerado. Deseja continuar?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sim, Continuar',
            cancelButtonText: 'Não, Selecionar Transação',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                //? confirmar
                //*exibe loader
                
                msgLoader.innerText = "Carregando dados, aguarde por favor...";
                loadingOverlay.style.display = 'flex';

                const timeoutId = setTimeout(() => {
                    msgLoader.innerText = "Esse processo pode levar um tempo...";
                }, 5000);
        
                //* Função gerar relatório
                relatorioPrestador(event);
                // relatorioPrestador(event)
                //     .then(() => {
                //         clearTimeout(timeoutId); // Cancela mensagem adicional se o processo terminar antes de 5s
                //         loadingOverlay.style.display = 'none';
                //     })
                //     .catch(error => {
                //         clearTimeout(timeoutId); // Cancela mensagem adicional em caso de erro
                //         msgLoader.innerText = "Erro ao processar a solicitação.";
                //         console.error(error);
                //         loadingOverlay.style.display = 'none';
                //         resetaStatus();
                //     });

            } else {
                //? recusar
                $("#transacao").select2('open'); //ToDo abre o select automaticamente
                //* Garante o foco no campo de pesquisa
                setTimeout(function () {
                    const searchField2 = document.querySelector('.select2-search__field');
                    if (searchField2) {
                        searchField2.focus(); //* Foca no campo de pesquisa

                        //* Monitorar e manter o foco no campo
                        searchField2.addEventListener('blur', function () {
                            setTimeout(() => {
                                searchField2.focus(); //* Reaplica o foco ao perder
                            }, 10); //* Pequeno delay para reaplicar o foco
                        });
                    }
                }, 200); //* Aguarda a renderização do Select2
    

               
            }
        });
    } else {
        //* Adiciona loader e carrega relatório
        msgLoader.innerText = "Carregando dados, aguarde por favor...";
        loadingOverlay.style.display = 'flex';

        //* Troca mensagem apos 5 segundos
        const timeoutId = setTimeout(() => {
            msgLoader.innerText = "Esse processo pode levar um tempo...";
        }, 5000);

        //* Função gerar relatório 
        relatorioPrestador(event);
        // relatorioPrestador(event)
        //     .then(() => {
        //         clearTimeout(timeoutId); // Cancela mensagem adicional se o processo terminar antes de 5s
        //         loadingOverlay.style.display = 'none';
        //     })
        //     .catch(error => {
        //         clearTimeout(timeoutId); // Cancela mensagem adicional em caso de erro
        //         msgLoader.innerText = "Erro ao processar a solicitação.";
        //         console.error(error);
        //         loadingOverlay.style.display = 'none';
        //         resetaStatus();
        //     });
    }
}

async function relatorioPrestador(event) {
    event.preventDefault();
    $("#btRelatorioPrestador").prop("disabled", true);
    $("#closeModalPrest").prop("disabled", true);

    //* Obtém os elementos do texto e do loader
    const message = document.getElementById('message');
    const loader = document.getElementById('loader');

    //* Exibe o texto e o loader
    message.style.display = 'inline-block';
    loader.style.display = 'inline-block';

    //! Coleta os valores das transações
    const transacoes = [];
    document.querySelectorAll('.div-selecao .lista-item').forEach(item => {
        transacoes.push(item.getAttribute('data-value'));
    });

    //* Adiciona os valores como um campo oculto no formulário
    let transacoesInput = document.querySelector('#hiddenTransacoes');
    if (!transacoesInput) {
        transacoesInput = document.createElement('input');
        transacoesInput.type = 'hidden';
        transacoesInput.name = 'transacoes'; //* Nome que será enviado ao servidor
        transacoesInput.id = 'hiddenTransacoes';
        event.target.appendChild(transacoesInput);
    }
    transacoesInput.value = transacoes.join(','); //* Adiciona os valores como uma string separada por vírgulas
   

    //? VERIFICA SE RADIO BUTTON ESTÁ MARCADO
    const relatorioPrestador = document.getElementById('relatorioPrestador');
    const relatorioGrupo = document.getElementById('relatorioGrupo');

    //* Verificar se o radio button está checado
    if (relatorioPrestador.checked){
        //*limpa select do relatorioGrupo (para não enviar)
        $('#grupoPrest').val(null).trigger('change');
        //! Coleta os valores dos Prestadores
        const prestadores = [];
        document.querySelectorAll('.div-selecaoPrest .lista-item').forEach(item => {
            prestadores.push(item.getAttribute('data-value'));
        });

         //* Verifica se selecionou prestador
         if(prestadores.length == 0){
            alert("Selecione pelo menos um prestador!");
            resetaStatus();
            
            return;
        }
        //* Adiciona os valores como um campo oculto no formulário
        let prestadoresInput = document.querySelector('#hiddenPrestador');
        if (!prestadoresInput) {
            prestadoresInput = document.createElement('input');
            prestadoresInput.type = 'hidden';
            prestadoresInput.name = 'prestador_principal'; //* Nome que será enviado ao servidor
            prestadoresInput.id = 'hiddenPrestador';
            event.target.appendChild(prestadoresInput);
        }
        prestadoresInput.value = prestadores.join(','); //* Adiciona os valores como uma string separada por vírgulas
       
    }else if(relatorioGrupo.checked){
        //* limpa select(div) do relatorioPrestador (para não enviar)
        $('#prestador').val(null).trigger('change');
        const grupo = [];
        document.querySelectorAll('.div-selecaoGrupo .lista-item').forEach(item => {
            grupo.push(item.getAttribute('data-value'));
        });

        if(grupo.length == 0){
            alert("Selecione pelo menos um grupo!");
            resetaStatus();
            
            return;
        }

        //* Adiciona os valores como um campo oculto no formulário
        let grupoInput = document.querySelector('#hiddenGrupo');
        if (!grupoInput) {
            grupoInput = document.createElement('input');
            grupoInput.type = 'hidden';
            grupoInput.name = 'grupo_prestadores'; //* Nome que será enviado ao servidor
            grupoInput.id = 'hiddenGrupo';
            event.target.appendChild(grupoInput);
        }
        grupoInput.value = grupo.join(',');

    }

    // const formData = new FormData(event.target); // Captura os dados do formulário
    // formData.append('transacoes', transacoes.join(',')); // Adiciona transações manualmente

    // const response = await fetch(event.target.action, {
    //     method: "POST",
    //     body: formData
    // });

    // if (!response.ok) {
    //     throw new Error("Erro ao processar o relatório.");
    // }

    // const result = await response.json(); // Supondo que a resposta seja JSON
    // return result; // Retorna o resultado para ser usado com .then()

    //* Simula o envio do formulário
    const form = event.target;
    // //* Submete o formulário
    form.submit();
    
}

//! habilita botão, remove texto e loader
function resetaStatus(){
    document.getElementById('loadingOverlay').style.display = 'none'; 
    $("#btRelatorioPrestador").prop("disabled", false);
    $("#closeModalPrest").prop("disabled", false);
    message.style.display = 'none';
    loader.style.display = 'none';
}

$(document).on('keydown', function (e) {
    //* Verifica se a tecla pressionada foi a tecla ESC (keyCode 27 ou key 'Escape')
    if (e.key === 'Escape' || e.keyCode === 27) {
        e.preventDefault(); //* Impede qualquer comportamento associado à tecla ESC (não fecha modal, não envia formulário, etc.)
        return false; //* Garante que nada mais aconteça após o evento
    }
});

//! radios buttons relatorio de prestador
$("#relatorioGrupo").on("click", function () {
    $("#divPrestadores").css("display", "none");
    $("#divGrupo").css("display", "block");

    // esconde os campos
    $("#dvSerieDoc, #dvTransacao,  #txtTransacao, #selecaoTransacao").hide();
});

$("#relatorioPrestador").on("click", function () {
    $("#divPrestadores").css("display", "block");
    $("#divGrupo").css("display", "none");

    // mostra os campos
    $("#dvSerieDoc, #dvTransacao, #txtTransacao, #selecaoTransacao").show();
});


//! CARREGA E PREENCHE TELAS PARA PERMISSÃO NO CADASTRO (somente administrador)
let cachedTelas = null; //* Variável global para armazenar os dados

async function getTelas(targetFormId ) {

    //* Verifica se os dados já estão em cache
    if (!cachedTelas) {
        try {
            const response = await axios({
                method: 'post',
                url: 'utils/getTelas.php',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                data: {}
            });
            cachedTelas = response.data.data; //* Armazena os dados no cache
            console.log("retorno get telas");
            console.log(response.data);
        } catch (error) {
            console.error('Erro ao carregar as telas:', error);
            return;
        }
    }

    //* Utiliza os dados em cache para preencher o formulário
    preencherTelas(targetFormId, cachedTelas);
}

function preencherTelas(targetFormId, telas) {
    let form = document.getElementById(targetFormId);
    if (!form) {
        console.error(`Elemento com ID ${targetFormId} não encontrado.`);
        return;
    }
    
    form.innerHTML = ""; // Limpa o conteúdo anterior

    const grupos = {};
    
    //* Organiza as telas por grupo
    telas.forEach(tela => {
        const { ID, DESCRICAO, ICONE, IDGRUPO, DESCRICAOGRUPO } = tela;
        if (!grupos[IDGRUPO]) {
            grupos[IDGRUPO] = { nome: DESCRICAOGRUPO, telas: [] };
        }
        grupos[IDGRUPO].telas.push({ id: ID, descricao: DESCRICAO, icone: ICONE });
    });

    //* Cria os grupos e telas dinamicamente
    for (const [idgrupo, grupo] of Object.entries(grupos)) {
        const grupoCard = document.createElement('div');
        grupoCard.className = 'card mb-3';
        
        grupoCard.innerHTML = `
            <div class="card-header">
                <input type="checkbox" id="grupo_${targetFormId}_${idgrupo}" class="form-check-input check-box cursor-pointer" onchange="toggleCheck('grupo_${targetFormId}_${idgrupo}', '${targetFormId}_${idgrupo}')">
                <label for="grupo_${targetFormId}_${idgrupo}" class="form-check-label fw-bold cursor-pointer">${grupo.nome}</label>
            </div>
            <div class="card-body" id="${targetFormId}_${idgrupo}"></div>
        `;
        
        const grupoBody = grupoCard.querySelector(`#${targetFormId}_${idgrupo}`);
        grupo.telas.forEach(tela => {
            const telaCheckbox = document.createElement('div');
            telaCheckbox.className = 'form-check';
            
            telaCheckbox.innerHTML = `
                <input type="checkbox" class="form-check-input check-box cursor-pointer" id="tela_${targetFormId}_${tela.id}" name="telas[]" value="${tela.id}">
                <label for="tela_${targetFormId}_${tela.id}" class="form-check-label cursor-pointer">
                    <i class="${tela.icone}"></i> ${tela.descricao}
                </label>
            `;
            grupoBody.appendChild(telaCheckbox);
        });

        form.appendChild(grupoCard);
    }

    const actionButtons = document.createElement('div');
    actionButtons.className = 'mt-3';
    // actionButtons.innerHTML = `
    //     <button type="button" class="btn btn-secondary" onclick="selectAll()">Selecionar Todas</button>
    //     <button type="button" class="btn btn-secondary" onclick="deselectAll()">Desmarcar Todas</button>
    // `;
    form.appendChild(actionButtons);
}


//! FUNÇÕES DO GERENCIAMENTO DE PERMISSÕES DE TELAS (CADASTRO)
function toggleCheck(grupoCheckboxId, grupoTelasId) {
    const isChecked = document.getElementById(grupoCheckboxId).checked;
    const checkboxes = document.querySelectorAll(`#${grupoTelasId} .form-check-input`);
    checkboxes.forEach(checkbox => (checkbox.checked = isChecked));
}

function selectAll() {
    // const checkboxes = document.querySelectorAll('.form-check-input');
    const checkboxes = document.querySelectorAll('.check-box');
    checkboxes.forEach(checkbox => (checkbox.checked = true));
}

function deselectAll() {
    // const checkboxes = document.querySelectorAll('.form-check-input');
    const checkboxes = document.querySelectorAll('.check-box');
    checkboxes.forEach(checkbox => (checkbox.checked = false));
}


//! PEGAR PERMISSÕES PARA CARREGAR SIDEBAR
async function getPermissoesTela() {
    await axios({
        method: 'post',
        url: 'utils/get_permissoes_sidebar.php',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        data: {}
    })
    .then(function (response) {
        console.log("getPermissoesTela");
        console.log(response.data);
        if (response.data.error) {
            console.error('Erro ao carregar permissões:', response.data.message);
            return;
        }

        //* Array de permissões retornado do PHP
        const permissoes = response.data.data;

        //* Agrupar permissões por grupo
        const grupos = permissoes.reduce((acc, tela) => {
            if (!acc[tela.DESCRICAOGRUPO]) {
                acc[tela.DESCRICAOGRUPO] = [];
            }
            acc[tela.DESCRICAOGRUPO].push(tela);
            return acc;
        }, {});

        //* Criar o sidebar dinamicamente
        const sidebar = document.getElementById('sidebar');
        sidebar.innerHTML = ''; //* Limpar conteúdo anterior

        const toggleButton = document.createElement('button');
        toggleButton.id = 'toggleSidebar';
        toggleButton.innerHTML = '<i class="bi bi-chevron-double-right"></i>';
        sidebar.appendChild(toggleButton);

        Object.keys(grupos).forEach(grupo => {
            const grupoDiv = document.createElement('div');
            grupoDiv.id = `grupo-${grupo.replace(/\s+/g, '-').toLowerCase()}`;

            // Título do grupo
            const grupoTitulo = document.createElement('div');
            grupoTitulo.className = 'text-center';
            grupoTitulo.innerHTML = `<span>${grupo}</span>`;
            grupoDiv.appendChild(grupoTitulo);

            // Botões do grupo
            grupos[grupo].forEach(tela => {
                const button = document.createElement('button');
                button.setAttribute('data-bs-toggle', 'modal');
                //*remove acentuação e junta texto ↓
                button.setAttribute('data-bs-target', `#modal${tela.DESCRICAO.normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/\s+/g, '')}`);
                button.setAttribute('data-tippy-content', tela.DESCRICAO);

                button.innerHTML = `
                    <i class="${tela.ICONE}"></i>
                    <span>${tela.DESCRICAO}</span>
                `;
                grupoDiv.appendChild(button);
            });

            // Adicionar o grupo ao sidebar
            sidebar.appendChild(grupoDiv);
        });

        //* Abre sidebar toggle
        const toggleSidebarButton = document.getElementById('toggleSidebar');
        const tippyButtons = document.querySelectorAll('[data-tippy-content]');
        let tippyInstances = [];

        //* Função para inicializar tooltips apenas quando sidebar estiver recolhido
        function initializeTooltips() {
            //* Limpa tooltips existentes antes de criar novos
            destroyTooltips();
            tippyButtons.forEach(button => {
                //* Verifica se o sidebar NÃO está expandido
                if (!sidebar.classList.contains('expanded')) {
                    const instance = tippy(button, {
                        animation: 'scale',
                        theme: 'light',
                        duration: [300, 200],
                        placement: 'right',
                        delay: [100, 0],
                        appendTo: () => document.body,
                    });
                    tippyInstances.push(instance);
                }
            });
        }

        //* Função para destruir tooltips
        function destroyTooltips() {
            tippyInstances.forEach(instance => instance.destroy());
            tippyInstances = [];
        }

        //* Alternar Sidebar
        toggleSidebarButton.addEventListener('click', () => {
            sidebar.classList.toggle('expanded');
            const icon = toggleSidebarButton.querySelector('i');
            icon.classList.toggle('bi-chevron-double-right');
            icon.classList.toggle('bi-chevron-double-left');

            //* Se a sidebar estiver expandida, destruir tooltips
            if (sidebar.classList.contains('expanded')) {
                destroyTooltips();
            } else {
                initializeTooltips();
            }
        });

        //* Garante que os tooltips sejam ajustados no carregamento inicial
        if (!sidebar.classList.contains('expanded')) {
            initializeTooltips();
        }
    })
    .catch(function (error) {
        console.error('Erro ao carregar permissões:', error);
    });
}

//! CARREGA USUARIOS DO ADMINISTRADOR
let usuariosData = null;
async function getUsuariosDoAdm(){
    await axios({
        method: 'post',
        url: 'utils/get_usuarios_do_adm.php',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        data: {}
    })
    .then(function (response) {
        console.log("get_usuarios_do_adm");
        console.log(response.data);
        if(response.data['error'] == false){
            usuariosData = response.data;
            preencherListaUsuarios(usuariosData);
        }

    })   
}

//! LISTA USUARIOS DO ADMINISTRADOR EM CHECKBOX
function preencherListaUsuarios(usuarios) {
    const listaUsuariosDiv = document.getElementById("listaUsuarios");
    listaUsuariosDiv.innerHTML = ""; //* Limpa a lista antes de adicionar novos itens

    usuarios.data.usuarios.forEach(usuario => {
        const checkbox = document.createElement("input");
        checkbox.type = "radio";
        checkbox.className = "form-check-input check-usuario";
        checkbox.id = `usuario_${usuario.ID}`;
        checkbox.name = "usuarios[]";
        checkbox.value = usuario.ID;

        const label = document.createElement("label");
        label.className = "form-check-label";
        label.htmlFor = `usuario_${usuario.ID}`;
        label.textContent = usuario.NOME;

        const div = document.createElement("div");
        div.className = "form-check";

        div.appendChild(checkbox);
        div.appendChild(label);
        listaUsuariosDiv.appendChild(div);
    });
}

function toggleCheckUsuario(){
    const isChecked = document.getElementById("allUsuarios").checked;
    const checkboxes = document.querySelectorAll(`.check-usuario`);

    checkboxes.forEach(checkbox => {
        checkbox.checked = isChecked;

    })


}

//! selecionar um usuario no checkbox p/ carregar permissões de telas
$("#listaUsuarios").on("change", "input[type='radio']", function () {
    //* Seleciona todos os checkboxes marcados dentro da div
    const selectedCheckboxes = $("#listaUsuarios input[type='radio']:checked");

    LimpaChecks();//*Limpa todos os checkbox de permissões
    
    //* Cria um array de IDs dos checkboxes marcados
    const selectedValues = selectedCheckboxes.map(function () {
        return this.value;
    }).get(); //* .get() para transformar em array

    //* Acessa os detalhes de cada usuário selecionado no JSON
    const detalhes = selectedValues.map(id => {
        document.getElementById("btnResetSenha").style.display = "inline-block";
        return usuariosData.data.detalhes.filter(detalhe => detalhe.IDUSUARIO == id);
    });

    if(detalhes.length > 0){
        for(let i = 0; i< detalhes.length; i++){
            atualizarCheckboxesTelas(detalhes[i]);
        }
    }
});

//! RESETAR SENHA DO USUARIO SELECIONADO
document.getElementById("btnResetSenha").addEventListener("click", async function () {
    const selectedRadio = document.querySelector("#listaUsuarios input[type='radio']:checked");
    if (!selectedRadio) {
        alert("Selecione um usuário primeiro.");
        return;
    }

    const userId = selectedRadio.value;
    const userName = selectedRadio.parentNode.textContent.trim();

    if (confirm("Deseja realmente resetar a senha do usuário: " + userName + "?")) {
        try {
            const response = await fetch("reset_senha.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "id=" + encodeURIComponent(userId)
            });

            const data = await response.json();
            alert(data.mensagem);
        } catch (error) {
            alert("Erro ao comunicar com o servidor.");
            console.error(error);
        }
    }
});


function LimpaChecks(){
    const checkboxes = document.querySelectorAll("#formPermissoesEditar input[type='checkbox'][name='telas[]']");  
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
            checkbox.indeterminate = false;
        })
}

function atualizarCheckboxesTelas(detalhes) {
    console.log(detalhes);

    const checkboxes = document.querySelectorAll("#formPermissoesEditar input[type='checkbox'][name='telas[]']");
    for(let i = 0; i < detalhes.length; i++){
        // console.log(detalhes[i].ID);
        checkboxes.forEach(checkbox => {
            if(checkbox.value == detalhes[i].ID){
                if(checkbox.checked == true){
                    checkbox.indeterminate = true;
                }else if(checkbox.checked == false){
                    checkbox.checked = true;
                }else{
                    checkbox.checked = false;
                }
            }
   
        })
    }

    
}