<!-- Modal Correção Prestador-->
<div class="modal fade" id="modalCorrecaoPrestador" tabindex="-1" aria-labelledby="modalCorrecaoPrestLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCorrecaoPrestLabel">Correção Prestador</h5>
                <button id="closeModalPrest" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formCorrecaoPrest" action="contas_medicas/correcaoPrestador/" method="POST" >
                    
                    <div class="mt-3">
                        <label for="correcaoPrestador" class="form-label">Prestador*</label>
                        <select id="correcaoPrestador" class="custom-select" name="correcaoPrestador" >
                                <option value="" disabled selected>Prestador </option>
                            <option value="1"></option>
                        </select>
                    </div>
                    
                    <div class="text-center mt-4">
                        <button id="btnPesquisar" type="submit" class="btn btn-success" >Pesquisar 
                        
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
        const modal = document.getElementById('modalCorrecaoPrestador');
        
        modal.addEventListener('hide.bs.modal', function() {
            if (modal.contains(document.activeElement)) {
            document.activeElement.blur();
            }
        });
    });
</script>
