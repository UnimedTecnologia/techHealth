<!-- Modal Relatorio DIU -->
<div class="modal fade" id="modalRelatorioDIU" tabindex="-1" aria-labelledby="modalRelatorioDIULabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" >Relatório DIU</h5>
                <button id="closeModalPrest" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <button id="filtroDatas" class="bi bi-funnel btn btn-success" title="Filtrar manualmente"></button><span></span>
                <form id="formDIU" action="diu/relatorio_DIU.php" method="POST" onsubmit="loaderDiu(event)">
                    <input type="hidden" name="download" value="excel">

                    <!-- //! campos para filtro manual -->
                    <div class="mb-3 form-floating-label">
                        <input id="mesRelIni" type="date" pattern="\d*" class="form-control inputback" name="mesRelIni" placeholder=" " required readonly>
                        <label for="mesRelIni">Selecione o mês inicial do relatório *</label>
                    </div>
                    <div class="mb-3 form-floating-label">
                        <input id="mesRelFim" type="date" pattern="\d*" class="form-control inputback" name="mesRelFim" placeholder=" " required readonly>
                        <label for="mesRelFim">Selecione o mês final do relatório *</label>
                    </div>

                    <!-- //! campos procedimentos e insumos -->
                    <div id="divProc" class="mb-3 form-floating-label" style="display:none">
                        <input id="procedimentos" type="text" class="form-control inputback" name="procedimentos" placeholder=" " required readonly>
                        <label for="procedimentos">Procedimentos (separados por vírgula)</label>
                    </div>
                    <div id="divInsu" class="mb-3 form-floating-label" style="display:none">
                        <input id="insumos" type="text"  class="form-control inputback" name="insumos" placeholder=" " required readonly>
                        <label for="insumos">Insumos (separados por vírgula)</label>
                    </div>

                    <div class="text-center">
                        <button id="btRelatorioDiu" type="submit" class="btn btn-success" >Gerar relatório </button>
                    </div>
                    
                    
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        function getLastMonthDates() {
            let today = new Date();
            let firstDay = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            let lastDay = new Date(today.getFullYear(), today.getMonth(), 0);

            let formatDate = date => date.toISOString().split('T')[0];

            document.getElementById("mesRelIni").value = formatDate(firstDay);
            document.getElementById("mesRelFim").value = formatDate(lastDay);
            document.getElementById("procedimentos").value = '31303269,31303293';
            document.getElementById("insumos").value = '90327365,00198803,90458869,73682691,31303269,00784621';
        }

        getLastMonthDates();

        document.getElementById("filtroDatas").addEventListener("click", function () {
            document.getElementById("mesRelIni").toggleAttribute("readonly");
            document.getElementById("mesRelFim").toggleAttribute("readonly");
            document.getElementById("procedimentos").toggleAttribute("readonly");
            document.getElementById("insumos").toggleAttribute("readonly");

            let divProc = document.getElementById("divProc");
            divProc.style.display = divProc.style.display === "none" ? "block" : "none";
            let divInsu = document.getElementById("divInsu");
            divInsu.style.display = divInsu.style.display === "none" ? "block" : "none";
        });

        //! Limpa o foco quando o modal está prestes a ser fechado
        const modal = document.getElementById('modalRelatorioDIU');
        modal.addEventListener('hide.bs.modal', function() {
            if (modal.contains(document.activeElement)) {
            document.activeElement.blur();
            }
        });


    });

    function loaderDiu(event) {
        //* Previne o envio padrão para controlar o comportamento via JavaScript
        event.preventDefault();

        //* Obtém os elementos do texto e do loader
        const loadingOverlay = document.getElementById('loadingOverlay');
        const msgLoader = document.getElementById('msgLoader');

        msgLoader.innerText = "Carregando dados, aguarde por favor...";
        loadingOverlay.style.display = 'flex';

        //* Desabilita o botão para evitar múltiplos cliques
        document.getElementById("btRelatorioDiu").disabled = true;

        //* Envia o formulário após um pequeno delay para garantir que o loader seja exibido
        setTimeout(() => {
            event.target.submit();
        }, 100);
    }
</script>

