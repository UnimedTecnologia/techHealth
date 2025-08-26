document.addEventListener('DOMContentLoaded', function () {
    initSelect2('#prestadorPrincipal', '#modalAlterarCarteira');
    getPrestador('../../utils/get_prestador.php', ['#prestadorPrincipal']);

    const modal = document.getElementById('modalAlterarCarteira');
    modal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget; // Botão que abriu o modal
        const modalCodModalidade = modal.querySelector('#modalCodModalidade');
        const modalTermo = modal.querySelector('#modalTermo');
        const modalCodUsuario = modal.querySelector('#modalCodUsuario');
        const modalCarteira = modal.querySelector('#modalCarteira');
        const modalCodUnidade = modal.querySelector('#modalCodUnidade');
        const modalChar22 = modal.querySelector('#modalChar22');
       
        // Preencher os campos do modal com os atributos data-*
        modalCodModalidade.value = button.getAttribute('data-cd_modalidade');
        modalTermo.value = button.getAttribute('data-nr_ter_adesao');
        modalCodUsuario.value = button.getAttribute('data-cd_usuario');
        //modalCarteira.value = button.getAttribute('data-carteira_completa');
        const carteiraCompleta = button.getAttribute('data-carteira_completa');
        const codUnidade = carteiraCompleta.slice(0, 4);
        modalCodUnidade.value = codUnidade;
        modalCarteira.value = carteiraCompleta.slice(4);

        modalChar22.value = ";;"+codUnidade+";"+carteiraCompleta.slice(4)+";;;";
        
    });
});


document.getElementById("verificaCarteiraForm").addEventListener("submit", function (e) {
    e.preventDefault(); // Impede o envio padrão do formulário

    const formData = new FormData(this);

    getInfoCarteira(formData);
   
});

function getInfoCarteira(formData){
    //* Limpa campos antes da busca
    $("#modalCodUnidade2, #modalCarteira2, #modalCodModalidade2, #modalTermo2, #modalCodUsuario2, #modalChar222").val('');
    $("#codSequencia").text("");
    //* DESABILITA O BOTÃO DE ATUALIZAR
    $("#btnAtualizarCarteira").prop("disabled", true);

    axios.post('verifica_carteira_beneficiario.php', formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }
    })
        .then(function (response) {
            console.log(response.data);  // Dados da resposta
            if (response.data.error) {
                alert(response.data.message);  // alerta se não encontrar dados
            } else {

                //! Verifica se retornou mais de 1 resultado
                // console.log(response.data.data.length);
                if(response.data.data.length > 1){
                    //* Se tiver mais de 1 array, precisa exibir uma opção para o usuario escolher qual deseja usar. Monta uma tabela exibindo e ele escolhe
                    // alert("Foram encontrados mais de 1");
                    populateModal(response.data.data); // Preenche a tabela com os dados
                    const modal = new bootstrap.Modal(document.getElementById("dataModal"));
                    modal.show();

                }else{

                    const item = response.data.data[0]; // Pega primeiro item do array
                    $("#modalCodUnidade2").val(item.CD_UNIDADE_CARTEIRA);
                    $("#modalCarteira2").val(item.CD_CARTEIRA_USUARIO);
                    $("#modalCodModalidade2").val(item.CD_MODALIDADE);
                    $("#modalTermo2").val(item.NR_TER_ADESAO);
                    $("#modalCodUsuario2").val(item.CD_USUARIO);
                    $("#modalChar222").val(item.CHAR_22);
                    $("#codSequencia").text(item.NR_DOC_SISTEMA);

                    //* HABILITA BOTÃO DE ATUALIZAR
                    $("#btnAtualizarCarteira").prop("disabled", false);
                }
            }

        })
        .catch(function (error) {
            console.error('Erro na requisição:', error);
        });
}


//! fechar modal
document.addEventListener("DOMContentLoaded", function () {
    var modal = document.getElementById('modalAlterarCarteira');
    var form = document.getElementById('verificaCarteiraForm');

    // Limpa o formulário ao fechar o modal
    modal.addEventListener('hidden.bs.modal', function () {
        //* LIMPA FORMULARIO 
        form.reset();

        //* LIMPA CAMPOS 
        $("#modalCodUnidade2, #modalCarteira2, #modalCodModalidade2, #modalTermo2, #modalCodUsuario2, #modalChar222").val('');
        $("#codSequencia").text("");
        
        //* DESABILITA O BOTÃO DE ATUALIZAR
        $("#btnAtualizarCarteira").prop("disabled", true);
        
    });
    
});


// Função para preencher a tabela no modal
function populateModal(data) {
    const tableBody = document.querySelector("#dataTable tbody");
    tableBody.innerHTML = ""; // Limpa a tabela antes de adicionar novos dados
  
    data.forEach((item, index) => {
      const row = document.createElement("tr");
      row.innerHTML = `
        <td>${item.CD_CARTEIRA_USUARIO}</td>
        <td>${item.CD_MODALIDADE}</td>
        <td>${item.CD_UNIDADE_CARTEIRA}</td>
        <td>${item.NR_DOC_ORIGINAL}</td>
        <td>${item.NR_DOC_SISTEMA}</td>
        <td><button class="btn btn-success btn-select" data-index="${index}">Selecionar</button></td>
      `;
      tableBody.appendChild(row);
    });
  
    // Adiciona evento de clique para os botões "Selecionar"
    document.querySelectorAll(".btn-select").forEach((button) => {
      button.addEventListener("click", function () {
        const selectedIndex = this.getAttribute("data-index");
        const item = data[selectedIndex];
        // console.log(item);
  
        // Preenche os campos com os valores selecionados
        $("#modalCodUnidade2").val(item.CD_UNIDADE_CARTEIRA);
        $("#modalCarteira2").val(item.CD_CARTEIRA_USUARIO);
        $("#modalCodModalidade2").val(item.CD_MODALIDADE);
        $("#modalTermo2").val(item.NR_TER_ADESAO);
        $("#modalCodUsuario2").val(item.CD_USUARIO);
        $("#modalChar222").val(item.CHAR_22);
        $("#codSequencia").text(item.NR_DOC_SISTEMA);

        //* HABILITA BOTÃO DE ATUALIZAR
        $("#btnAtualizarCarteira").prop("disabled", false);
  
        // Fecha o modal
        const modal = bootstrap.Modal.getInstance(document.getElementById("dataModal"));
        modal.hide();
      });
    });
  }


  document.getElementById("alteraCartBenef").addEventListener("submit", function (e) {
    e.preventDefault();

    const loadingOverlay = document.getElementById('loadingOverlay');
    const msgLoader = document.getElementById('msgLoader');

    msgLoader.innerText = "Carregando dados, aguarde por favor...";
    loadingOverlay.style.display = 'flex';

    $("#btnAtualizarCarteira").prop("disabled",true);
    // Copia o valor do <span> para o campo oculto
    var valorSequencia = document.getElementById('codSequencia').textContent;
    document.getElementById('hiddenCodSequencia').value = valorSequencia;
    //TODO PEGAR DADOS DO OUTRO FORM
    $("#hiddenprestadorPrincipal").val($("#prestadorPrincipal").val());
    $("#hiddenanoRef").val($("#anoRef").val());
    $("#hiddenperiodoRef").val($("#periodoRef").val());
    $("#hiddennrDocOrig").val($("#nrDocOrig").val());

    // console.log({
    //     prestadorPrincipal: $("#hiddenprestadorPrincipal").val(),
    //     anoRef: $("#hiddenanoRef").val(),
    //     periodoRef: $("#hiddenperiodoRef").val(),
    //     nrDocOrig: $("#hiddennrDocOrig").val()
    // });

    const formData = new FormData(this);

    axios.post('alterar_carteira_beneficiario.php', formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }
    })
        .then(function (response) {
            console.log(response.data);
            loadingOverlay.style.display = 'none'; //* remove loader
            $("#retornoUpdate").text(response.data.message);
            $("#retornoUpdate").addClass("text-"+response.data.type);
            
            setTimeout(function () {
                var retorno = document.getElementById('retornoUpdate');

                if (retorno) {
                    retorno.style.display = 'none';
                }
            }, 4000);

            if(response.data.error){
                alert(response.data.message);
            }else{
                // $("#btnAtualizarCarteira").prop("disabled",false);
            }
        })


  })
