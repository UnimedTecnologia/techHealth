<!-- Modal Relatorio Prestador-->
<div class="modal fade" id="modalRelatorioTituloPrestador" tabindex="-1" aria-labelledby="modalRelatorioTituloPrestadorLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalRelatorioTituloPrestadorLabel">Relatório Título Prestador</h5>
                <button id="closeModalPrest" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formTituloPrest" action="utils/relatorio_tituloPrestador.php" method="POST" target="_blank">
                    <input type="hidden" name="download" value="excel">

                    <!-- Prestadores -->
                    <div class="mt-3">
                        <label for="prestadorTitulo" class="form-label">Prestador*</label>
                        <select id="prestadorTitulo" class="custom-select" name="cdPrestador" required> 
                            <option value="" disabled selected>Prestador</option>
                        </select>
                    </div>

                    <div class="mb-3 form-floating-label">
                        <input id="codTitulo" type="text" pattern="[0-9]*" class="form-control inputback" 
                            name="codTitulo" placeholder=" " required maxlength="16">
                        <label for="codTitulo">Código Título *</label>
                    </div>

                    <div class="text-center">
                        <button id="btRelTitulo" type="submit" class="btn btn-success">Gerar relatório</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
<script>
    //! Limpa o foco quando o modal está prestes a ser fechado
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('modalRelatorioTituloPrestador');
        
        modal.addEventListener('hide.bs.modal', function() {
            if (modal.contains(document.activeElement)) {
            document.activeElement.blur();
            }
        });
    });

    //! MODAL TITULO
    var modal = document.getElementById('modalRelatorioTituloPrestador');
    var form = document.getElementById('formTituloPrest');
    //* Limpa o formulário ao fechar o modal
    modal.addEventListener('hidden.bs.modal', function () {
        form.reset();
    });

</script>