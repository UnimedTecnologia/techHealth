<!-- Modal Relatorio Prestador-->
<div class="modal fade" id="modalRelatorioPrestador" tabindex="-1" aria-labelledby="modalRelatorioPrestLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalRelatorioPrestLabel">Relatório Prestador</h5>
                <button id="closeModalPrest" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formPrestador" action="utils/relatorio_prestador.php" method="POST" onsubmit="exibirLoader(event)">
                    <input type="hidden" name="download" value="excel">

                    <div class="d-flex justify-content-between">
                        <label>
                            <input id="relatorioPrestador" type="radio" name="reportOption" value="relatorioPrestador" checked>
                            Relatório por Prestador
                        </label>
                        <label>
                            <input id="relatorioGrupo" type="radio" name="reportOption" value="relatorioGrupo">
                            Relatório por Grupo
                        </label>
                        
                    </div>

                    <!-- //! prestadores -->
                    <div id="divPrestadores" class="mt-3">
                        <label for="prestador" class="form-label">Prestador*</label>
                        <select id="prestador" class="custom-select"  > <!-- name="prestador_principal"-->
                                <option value="" disabled selected>Prestador </option>
                            <option value="1"></option>
                        </select>
                        <div id="listaPrestadores" class="div-selecaoPrest"></div>
                    </div>
                    <!-- //! grupo prestadores -->
                    <div id="divGrupo" class="mt-3 " style="overflow:auto; display:none">
                        <label for="grupoPrest" class="form-label">Grupo de Prestadores*</label>
                        <select id="grupoPrest" class="custom-select" > <!-- name="grupo_prestadores" -->
                                <option value="" disabled selected>Grupo </option>
                            <option value="1"></option>
                        </select >
                        <div id="listaGrupo" class="div-selecaoGrupo"></div>
                        
                    </div>


                    
                    <div class="mb-3 form-floating-label">
                        <input id="anoref" type="text" pattern="\d*" class="form-control inputback" name="anoref" placeholder=" " required >
                        <label for="anoref">Ano Referência *</label>
                    </div>
                    
                    <div class="mb-3 form-floating-label">
                        <input id="numref" type="text" pattern="\d*" class="form-control inputback" name="numref" placeholder=" " required >
                        <label for="numref">Número Período Referência *</label>
                    </div>

                    <div class="mb-3 form-floating-label">
                        <input id="seriedoc" type="text" class="form-control inputback" name="seriedoc" placeholder=" "  >
                        <label for="seriedoc">Série Doc. Original</label>
                    </div>

                    
                    <div class="mb-3">
                        <select id="transacao" name="cd_transacao" class="custom-select" >
                                <option value="" disabled selected>Transação </option>
                            <option value="1"></option>
                        </select>
                    </div>
                    <label for="transacao" class="form-label">Código transação</label>
                    <div id="selecaoTransacao" class="mb-5 div-selecao" style="overflow:auto">

                    </div>

                    
                    <div class="text-center">
                        <div id="message" style="display: none; margin-top: 10px; color: green;">Aguarde, o relatório está sendo gerado...</div>
                        <div id="loader" class="spinner-grow spinner-grow-sm text-success" role="status" style="display:none;">
                            <span class="sr-only"></span>
                        </div>
                    </div>

                    <div class="text-center">
                        <button id="btRelatorioPrestador" type="submit" class="btn btn-success" >Gerar relatório 
                        
                        </button>
                    </div>
                    
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    //! Limpa o foco quando o modal está prestes a ser fechado
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('modalRelatorioPrestador');
        
        modal.addEventListener('hide.bs.modal', function() {
            if (modal.contains(document.activeElement)) {
            document.activeElement.blur();
            }
        });
    });
</script>