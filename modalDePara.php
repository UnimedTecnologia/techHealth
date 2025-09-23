<!-- Modal -->
<div class="modal fade" id="modalDePara" tabindex="-1" aria-labelledby="modalDeParaLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-xl"> <!-- modal grande pra caber a tabela -->
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Consultas De/Para</h5>
        <button id="closeModalPrest" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <!-- Nav Tabs -->
        <ul class="nav nav-tabs" id="consultaTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tabCodigo-tab" data-bs-toggle="tab" data-bs-target="#tabCodigo" type="button" role="tab">
              Consulta por Código
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="tabPeriodo-tab" data-bs-toggle="tab" data-bs-target="#tabPeriodo" type="button" role="tab">
              Consulta por Período
            </button>
          </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content mt-3">
          <!-- Consulta por Código -->
          <div class="tab-pane fade show active" id="tabCodigo" role="tabpanel">
            <form id="formCodigo">
              <div class="mb-3">
                <label for="codigoInsumo" class="form-label">Código do Insumo</label>
                <input type="text" partern="[0-9]*" class="form-control" id="codigoInsumo" name="codigo" required autocomplete="off">
              </div>
              <button type="submit" class="btn btn-primary">Buscar</button>
            </form>
            <div class="mt-3">
              <table class="table table-striped table-sm" id="tabelaCodigo">
                <thead>
                  <tr>
                    <th>TIPO INTERNO</th>
                    <th>INSUMO INTERNO</th>
                    <th>DESCRIÇÃO</th>
                    <th>DESPESA</th>
                    <th>INSUMO EXTERNO</th>
                    <th>USUÁRIO</th>
                    <th>ATUALIZAÇÃO</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
          </div>

          <!-- Consulta por Período -->
          <div class="tab-pane fade" id="tabPeriodo" role="tabpanel">
            <form id="formPeriodo">
              <div class="row">
                <div class="col-md-5">
                  <label for="dataInicio" class="form-label">Data Início</label>
                  <input type="date" class="form-control" id="dataInicio" name="inicio" required>
                </div>
                <div class="col-md-5">
                  <label for="dataFim" class="form-label">Data Fim</label>
                  <input type="date" class="form-control" id="dataFim" name="fim" required>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                  <button type="submit" class="btn btn-primary w-100">Buscar</button>
                </div>
              </div>
            </form>
                <div class="mt-3">
                <button id="btnExportarExcel" class="btn btn-success btn-sm mb-2" style="display:none;">Exportar Excel</button>
                <div class="d-flex justify-content-between mb-2">
                    <div>
                        <span id="totalArquivos" class="text-primary fw-bold"></span>
                    </div>
                </div>
              <table class="table table-striped table-sm" id="tabelaPeriodo">
                <thead>
                  <tr>
                    <th>TIPO INTERNO</th>
                    <th>INSUMO INTERNO</th>
                    <th>DESCRIÇÃO</th>
                    <th>DESPESA</th>
                    <th>INSUMO EXTERNO</th>
                    <th>USUÁRIO</th>
                    <th>ATUALIZAÇÃO</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
          </div>
        </div>
      </div><!-- modal-body -->
    </div>
  </div>
</div>

<script>
$(document).ready(function(){

  // Consulta por Código
  $("#formCodigo").on("submit", function(e){
    e.preventDefault();
    let codigo = $("#codigoInsumo").val();

    $.ajax({
      url: "contas_medicas/depara/consulta_codigo.php",
      type: "POST",
      data: { codigo: codigo },
      dataType: "json",
      success: function(res){
        let tbody = $("#tabelaCodigo tbody");
        tbody.empty();
        if(res.length > 0){
          res.forEach(r => {
            tbody.append(`
              <tr>
                <td>${r.CD_TIPO_INSUMO_INTERNO}</td>
                <td>${r.CD_INSUMO_INTERNO}</td>
                <td>${r.DS_INSUMO}</td>
                <td>${r.DESPESA}</td>
                <td>${r.CD_INSUMO_EXTERNO}</td>
                <td>${r.CD_USERID}</td>
                <td>${r.DT_ATUALIZACAO}</td>
              </tr>
            `);
          });
        } else {
          tbody.append("<tr><td colspan='7' class='text-center'>Nenhum dado encontrado</td></tr>");
        }
      }
    });
  });

  // Consulta por Período
  $("#formPeriodo").on("submit", function(e){
    e.preventDefault();
    let inicio = $("#dataInicio").val();
    let fim    = $("#dataFim").val();

    $.ajax({
      url: "contas_medicas/depara/consulta_periodo.php",
      type: "POST",
      data: { inicio: inicio, fim: fim },
      dataType: "json",
      success: function(res){
        let tbody = $("#tabelaPeriodo tbody");
        tbody.empty();
        if(res.length > 0){
          $("#btnExportarExcel").show(); // habilita botão
          res.forEach(r => {
            tbody.append(`
              <tr>
                <td>${r.CD_TIPO_INSUMO_INTERNO}</td>
                <td>${r.CD_INSUMO_INTERNO}</td>
                <td>${r.DS_INSUMO}</td>
                <td>${r.DESPESA}</td>
                <td>${r.CD_INSUMO_EXTERNO}</td>
                <td>${r.CD_USERID}</td>
                <td>${r.DT_ATUALIZACAO}</td>
              </tr>
            `);
          });
          document.getElementById("totalArquivos").innerText = 'Total de registros: ' + res.length;
        } else {
          $("#btnExportarExcel").hide();
          tbody.append("<tr><td colspan='7' class='text-center'>Nenhum dado encontrado</td></tr>");
        }
      }
    });
  });

  // Exportar para Excel
  $("#btnExportarExcel").on("click", function(){
    let inicio = $("#dataInicio").val();
    let fim    = $("#dataFim").val();
    window.location.href = "contas_medicas/depara/exporta_excel.php?inicio="+inicio+"&fim="+fim;
  });

});
</script>
