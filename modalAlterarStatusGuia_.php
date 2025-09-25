<div class="modal fade" id="modalAlterarStatusGuia" tabindex="-1" aria-labelledby="modalAlterarStatusGuiaLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Alterar Status da Guia</h5>
        <button id="closeModal" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <!-- Form de filtro -->
        <form id="formConsultaGuia" class="row g-2 mb-3">
          <div class="col-md-2">
            <input type="text" id="anoGuiaStatus" name="anoGuiaStatus" class="form-control" placeholder="Ano" required>
          </div>
          <div class="col-md-3">
            <input type="text" id="numeroGuiaStatus" name="numeroGuiaStatus" class="form-control" placeholder="Número da Guia" required>
          </div>
          <div class="col-md-3">
            <select id="novoStatus" name="novoStatus" class="form-select" required>
              <option value="">-- Selecionar Status --</option>
              <option value="9">Pendente Auditoria</option>
              <option value="10">Pendente Liberação</option>
            </select>
          </div>
          <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Consultar</button>
          </div>
        </form>

        <!-- Tabela que será carregada -->
        <div id="resultadoConsulta"></div>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function(){
    $("#formConsultaGuia").on("submit", function(e){
        e.preventDefault();

        $.ajax({
        url: "regulacao/consulta_guia.php",
        type: "POST",
        data: $(this).serialize(),
        success: function(data){
            $("#resultadoConsulta").html(data);
        }
        });
    });

    // ação do botão update (delegado pois botão vem do ajax)
    $(document).on("click", ".btnUpdateGuia", function(){
        let upd_guia = $(this).data("upd_guia");
        let upd_hist = $(this).data("upd_hist");
        let statusAtual = $(this).data("status_atual");
        let guia = $(this).data("guia");
        let novoStatus = $("#novoStatus").val(); // Pega o status escolhido no select do formulário

        if (!novoStatus) {
            Swal.fire("Atenção", "Selecione um status antes de consultar!", "warning");
            return;
        }

        // mensagem de confirmação customizada
        let msg = "Deseja realmente atualizar a guia " + guia + " para o status " + (novoStatus == 9 ? "Pendente Auditoria" : "Pendente Liberação") + "?";
        if (statusAtual == 3) {
            msg = "Essa guia está cancelada, deseja realmente abrir para o status " + (novoStatus == 9 ? "Pendente Auditoria" : "Pendente Liberação") + "?";
        }

        Swal.fire({
            title: "Confirmação",
            text: msg,
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Sim, atualizar",
            cancelButtonText: "Cancelar"
        }).then((result) => {
            if (result.isConfirmed) {
            $.ajax({
                url: "update_guia.php",
                type: "POST",
                data: { upd_guia: upd_guia, upd_hist: upd_hist, novoStatus: novoStatus },
                success: function(resp){
                Swal.fire("Sucesso", resp, "success");
                $("#formConsultaGuia").submit(); // recarregar a tabela
                }
            });
            }
        });
    });

});
</script>