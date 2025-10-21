<?php
header('Content-Type: text/html; charset=utf-8');
session_start();

if(!isset($_SESSION['dadosValoresDoc'])){
    header("Location: ../../dashboard.php");
}else{
    //* Recuperar os dados
    $dados = $_SESSION['dadosValoresDoc'];

    if($dados['error']){
        header("Location: ../../dashboard.php");
        exit;
    }else{
        $procedimentos = $dados['procedimento'] ?? [];
        $insumos = $dados['insumo'] ?? [];
    }
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autorizacao</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../style.css" rel="stylesheet"/>

    <style>
        table {
            word-wrap: break-word;
            table-layout: auto;
        }

        th, td {
            text-align: center;
            vertical-align: middle;
        }

        .table-responsive {
            margin: auto;
            max-width: 100%;
        }
    </style>



</head>
<body>
    <?php include_once "../../nav.php"; // CABEÇALHO NAVBAR ?>

    <div id="loadingOverlay">
        <div class="spinner"></div>
        <p id="msgLoader">Carregando</p>
    </div>

    <div class="d-flex align-items-center mt-100 mb-2" >
        <div style="position: absolute; left: 10px;">
            <button class="btn" onclick="location.href='../../dashboard.php'" style="background-color: #008e55; color: white; margin-left: 10px;">
                <i class="bi bi-arrow-left"></i> <!-- Ícone de voltar -->
            </button>
        </div>
        <div class="mx-auto text-center">
            <h3>Alterar Porcentagem Documento</h3> 
        </div>
    </div>

    <div id="tabelas">
        <!-- Tabela de Procedimentos -->
        <h4 class="mt-4 text-center">Tabela de Procedimentos</h4>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Documento</th>
                    <th>Prestador</th>
                    <th>Transação</th>
                    <th>Série</th>
                    <th>Nº Processo</th>
                    <th>Sequência</th>
                    <th>Período Ref</th>
                    <th>Ano Ref</th>
                    <th>Pacote</th>
                    <th>Valor Cobrado</th>
                    <th>Valor Glosado</th>
                    <th>% Individual</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($procedimentos)): ?>
                    <?php foreach ($procedimentos as $proc): ?>
                        <tr>
                            <td><?= htmlspecialchars($proc['DOC'] ?? '') ?></td>
                            <td><?= htmlspecialchars($proc['CD_PRESTADOR'] ?? '') ?></td>
                            <td><?= htmlspecialchars($proc['CD_TRANSACAO'] ?? '') ?></td>
                            <td><?= htmlspecialchars($proc['SERIE'] ?? '') ?></td>
                            <td><?= htmlspecialchars($proc['NR_PROCESSO'] ?? '') ?></td>
                            <td><?= htmlspecialchars($proc['SEQ'] ?? '') ?></td>
                            <td><?= htmlspecialchars($proc['NR_PERREF'] ?? '') ?></td>
                            <td><?= htmlspecialchars($proc['DT_ANOREF'] ?? '') ?></td>
                            <td><?= htmlspecialchars($proc['CD_PACOTE'] ?? '') ?></td>
                            <td><?= htmlspecialchars($proc['VL_COBRADO'] ?? '') ?></td>
                            <td><?= htmlspecialchars($proc['VL_GLOSADO'] ?? '') ?></td>
                            <td>
                                <input type="text" name="procedimentos[porcentagem][]" class="form-control text-center" maxlength="3" pattern="\d*" style="width: 80px;" oninput="validarValor(this)">
                            </td>
                            <!-- Hidden fields -->
                            <input type="hidden" name="procedimentos[doc][]" value="<?= $proc['DOC'] ?>">
                            <input type="hidden" name="procedimentos[prestador][]" value="<?= $proc['CD_PRESTADOR'] ?>">
                            <input type="hidden" name="procedimentos[perref][]" value="<?= $proc['NR_PERREF'] ?>">
                            <input type="hidden" name="procedimentos[anoref][]" value="<?= $proc['DT_ANOREF'] ?>">
                            <input type="hidden" name="procedimentos[pacote][]" value="<?= $proc['CD_PACOTE'] ?>">
                            <input type="hidden" name="procedimentos[seq][]" value="<?= $proc['SEQ'] ?>">
                            <input type="hidden" name="procedimentos[processo][]" value="<?= $proc['NR_PROCESSO'] ?>">
                        </tr
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="11" class="text-center">Nenhum procedimento encontrado.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="text-center">
            <!-- <button type="submit" class="btn btn-success mb-4 mt-3">Salvar Alterações</button> -->
        </div>

        <!-- Tabela de Insumos -->
        <h4 class="mt-4 text-center">Tabela de Insumos</h4>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Documento</th>
                    <th>Prestador</th>
                    <th>Transação</th>
                    <th>Série</th>
                    <th>Nº Processo</th>
                    <th>Sequência</th>
                    <th>Período Ref</th>
                    <th>Ano Ref</th>
                    <th>Pacote</th>
                    <th>Insumo</th>
                    <!-- <th>Valor Insumo</th> -->
                    <th>Valor Cobrado</th>
                    <th>Valor Glosado</th>
                    <th>% Individual</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($insumos)): ?>
                    <?php foreach ($insumos as $insu): ?>
                        <tr>
                            <td><?= htmlspecialchars($insu['DOC'] ?? '') ?></td>
                            <td><?= htmlspecialchars($insu['CD_PRESTADOR'] ?? '') ?></td>
                            <td><?= htmlspecialchars($insu['CD_TRANSACAO'] ?? '') ?></td>
                            <td><?= htmlspecialchars($insu['SERIE'] ?? '') ?></td>
                            <td><?= htmlspecialchars($insu['NR_PROCESSO'] ?? '') ?></td>
                            <td><?= htmlspecialchars($insu['SEQ'] ?? '') ?></td>
                            <td><?= htmlspecialchars($insu['NR_PERREF'] ?? '') ?></td>
                            <td><?= htmlspecialchars($insu['DT_ANOREF'] ?? '') ?></td>
                            <td><?= htmlspecialchars($insu['CD_PACOTE'] ?? '') ?></td>
                            <td><?= htmlspecialchars($insu['CD_INSUMO'] ?? '') ?></td>
                            <td><?= htmlspecialchars($insu['VL_COBRADO'] ?? '') ?></td>
                            <td><?= htmlspecialchars($insu['VL_GLOSADO'] ?? '') ?></td>
                            <td>
                                <input type="text" name="insumos[porcentagem][]" class="form-control text-center" maxlength="3" pattern="\d*" style="width: 80px;" oninput="validarValor(this)">
                            </td>
                            <!-- Hidden fields -->
                            <input type="hidden" name="insumos[doc][]" value="<?= $proc['DOC'] ?>">
                            <input type="hidden" name="insumos[prestador][]" value="<?= $proc['CD_PRESTADOR'] ?>">
                            <input type="hidden" name="insumos[perref][]" value="<?= $proc['NR_PERREF'] ?>">
                            <input type="hidden" name="insumos[anoref][]" value="<?= $proc['DT_ANOREF'] ?>">
                            <input type="hidden" name="insumos[pacote][]" value="<?= $proc['CD_PACOTE'] ?>">
                            <input type="hidden" name="insumos[seq][]" value="<?= $proc['SEQ'] ?>">
                            <input type="hidden" name="insumos[processo][]" value="<?= $proc['NR_PROCESSO'] ?>">
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="12" class="text-center">Nenhum insumo encontrado.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="text-center">
        <button 
           id="btnEdtPorcent" class="btn btn-success mb-5 " data-bs-toggle="modal" data-bs-target="#modalPorcentagem">
            Editar Porcentagem
        </button>
    </div>

    <?php 
        if(isset($_SESSION['retornoUpdatePorcentagem'])){
           ?>
            <script>
                //! mensagem de erro sweet alert
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: '<?php echo ($_SESSION['retornoUpdatePorcentagem']['type']); ?>',
                        title: '',
                        text: '<?php echo addslashes($_SESSION['retornoUpdatePorcentagem']['message']); ?>',
                        timer: 2000,
                        timerProgressBar: true,
                        showConfirmButton: false
                    });
                });
            </script>
           <?php
           unset($_SESSION['retornoUpdatePorcentagem']);
        }
    ?>
    
<!-- //! VERIFICA CAMPO DE % MANUAL -->
<script>
    function validarValor(input) {
  // 1. Remove qualquer caractere que não seja um dígito (0-9)
  let valorLimpo = input.value.replace(/\D/g, '');

  // 2. Converte para número inteiro para fazer a validação de limite
  let valor = parseInt(valorLimpo, 10);

  // 3. Verifica se é maior que 100
  if (valor > 100) {
    valorLimpo = '100';
  }

  // 4. Atualiza o valor do input
  input.value = valorLimpo;
}

</script>

    <!-- //! modal alterar valor porcentagem -->
    <div class="modal fade" id="modalPorcentagem" tabindex="-1" aria-labelledby="modalPorcentagem" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" >Alterar Porcentagem Documento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formAttPorcentagem" action="update_porcentagemDoc.php" method="POST">
                        <!-- Procedimentos -->
                        <?php foreach ($procedimentos as $proc): ?>
                            <input type="hidden" name="procedimentos[doc][]" value="<?= htmlspecialchars($proc['DOC'] ?? '') ?>">
                            <input type="hidden" name="procedimentos[prestador][]" value="<?= htmlspecialchars($proc['CD_PRESTADOR'] ?? '') ?>">
                            <input type="hidden" name="procedimentos[perref][]" value="<?= htmlspecialchars($proc['NR_PERREF'] ?? '') ?>">
                            <input type="hidden" name="procedimentos[anoref][]" value="<?= htmlspecialchars($proc['DT_ANOREF'] ?? '') ?>">
                            <input type="hidden" name="procedimentos[pacote][]" value="<?= htmlspecialchars($proc['CD_PACOTE'] ?? '') ?>">
                            <input type="hidden" name="procedimentos[seq][]" value="<?= htmlspecialchars($proc['SEQ'] ?? '') ?>">
                        <?php endforeach; ?>

                        <!-- Insumos -->
                        <?php foreach ($insumos as $insu): ?>
                            <input type="hidden" name="insumos[doc][]" value="<?= htmlspecialchars($insu['DOC'] ?? '') ?>">
                            <input type="hidden" name="insumos[prestador][]" value="<?= htmlspecialchars($insu['CD_PRESTADOR'] ?? '') ?>">
                            <input type="hidden" name="insumos[perref][]" value="<?= htmlspecialchars($insu['NR_PERREF'] ?? '') ?>">
                            <input type="hidden" name="insumos[anoref][]" value="<?= htmlspecialchars($insu['DT_ANOREF'] ?? '') ?>">
                            <input type="hidden" name="insumos[pacote][]" value="<?= htmlspecialchars($insu['CD_PACOTE'] ?? '') ?>">
                            <input type="hidden" name="insumos[seq][]" value="<?= htmlspecialchars($insu['SEQ'] ?? '') ?>">
                        <?php endforeach; ?>


                        <div class="mb-3 form-floating-label">
                            <input id="porcentagem" type="text" class="form-control inputback" name="porcentagem" placeholder=" " required maxlength="3" >
                            <label for="porcentagem">Valor Porcentagem *</label>
                        </div>
                        <div class="text-center">
                            <button id="btnAlterar" class="btn btn-success mb-4 mt-4">Alterar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('loadingOverlay').style.display = 'none'; 

            const form = document.getElementById('formAttPorcentagem');
            const inputPorcentagem = document.getElementById('porcentagem');

            form.addEventListener('submit', function (event) {
                const valor = inputPorcentagem.value.trim();

                //* Verifica se o campo está vazio
                if (valor === '') {
                    event.preventDefault(); //* Impede o envio do formulário
                    return;
                }

                if (!/^\d+$/.test(valor)) { //* Verifica se o valor é numérico
                    alert('Por favor, insira apenas números.');
                    event.preventDefault(); //* Impede o envio do formulário
                    return;
                }

                document.getElementById('msgLoader').innerText = "Editando valores, aguarde por favor";
                document.getElementById('loadingOverlay').style.display = 'flex';

                document.getElementById('btnAlterar').disabled = true; //* desabilita botão alterar

           
                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape' || e.keyCode === 27) {
                        e.preventDefault(); // Impede o fechamento do modal ao pressionar ESC
                        return false; // Impede que o evento continue e feche o modal
                    }
                });


            });

        })
    </script>
    
</body>
</html>
