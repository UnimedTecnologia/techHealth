<?php 
header('Content-Type: text/html; charset=utf-8');
session_start();

if(!isset($_SESSION['dadosValoresDoc'])){
    header("Location: ../../dashboard.php");
    exit;
}

$dados = $_SESSION['dadosValoresDoc'];

if (isset($dados['error']) && $dados['error']) {
    header("Location: ../../dashboard.php");
    exit;
}

$resultados = $dados['resultados'] ?? [];

// 🔹 Separar registros por origem (P = procedimento, I = insumo)
$procedimentos = [];
$insumos = [];

// FUNÇÃO PARA FORMATAR VALORES
function formatarValorFinal($valor) {
    if ($valor === null || $valor === '') {
        return '0,00';
    }
    
    $valorStr = (string)$valor;
    
    // Se já está no formato brasileiro (com vírgula e ponto), retorna como está
    if (preg_match('/^\d{1,3}(?:\.\d{3})*,\d+$/', $valorStr)) {
        return $valorStr;
    }
    
    // Se tem apenas vírgula como separador decimal
    if (strpos($valorStr, ',') !== false && strpos($valorStr, '.') === false) {
        $partes = explode(',', $valorStr);
        $inteira = $partes[0];
        $decimal = $partes[1] ?? '00';
        $inteiraFormatada = number_format((float)$inteira, 0, '', '.');
        return $inteiraFormatada . ',' . $decimal;
    }
    
    // Formato float padrão (com ponto decimal)
    if (strpos($valorStr, '.') !== false) {
        $partes = explode('.', $valorStr);
        $inteira = $partes[0];
        $decimal = $partes[1];
        $inteiraFormatada = number_format((float)$inteira, 0, '', '.');
        return $inteiraFormatada . ',' . $decimal;
    }
    
    // Apenas número inteiro
    return number_format((float)$valorStr, 0, '', '.') . ',00';
}

foreach ($resultados as $item) {
    $origem = isset($item['ORIGEM']) ? strtoupper(trim($item['ORIGEM'])) : '';
    if ($origem === 'P') {
        $procedimentos[] = $item;
    } elseif ($origem === 'I') {
        $insumos[] = $item;
    }
}

// 🔹 AGRUPAR POR PACOTE OCORRÊNCIA
$pacotesOcorrencia = [];

foreach ($procedimentos as $proc) {
    $ocorrencia = $proc['PACOTE_OCORRENCIA'] ?? '';
    if ($ocorrencia !== '') {
        if (!isset($pacotesOcorrencia[$ocorrencia])) {
            $pacotesOcorrencia[$ocorrencia] = [
                'CD_PACOTE' => $proc['CD_PACOTE'] ?? '',
                'PACOTE_OCORRENCIA' => $ocorrencia,
                'procedimentos' => [],
                'insumos' => []
            ];
        }
        $pacotesOcorrencia[$ocorrencia]['procedimentos'][] = $proc;
    }
}

// Agrupar os insumos pela mesma ocorrência
foreach ($insumos as $insu) {
    $ocorrencia = $insu['PACOTE_OCORRENCIA'] ?? '';
    if ($ocorrencia !== '' && isset($pacotesOcorrencia[$ocorrencia])) {
        $pacotesOcorrencia[$ocorrencia]['insumos'][] = $insu;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alterar Porcentagem Documento</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../style.css" rel="stylesheet"/>

    <style>
        table { word-wrap: break-word; table-layout: fixed; }
        th, td { text-align: center; vertical-align: middle; font-size: 0.9rem; }
        .table-responsive { margin: auto; max-width: 100%; overflow-x: auto; }
        .small-input { width: 90px; margin: 0 auto; }
        .muted { color: #6c757d; }
        .table-dark th { background-color: #343a40; border-color: #454d55; }
        .valor { text-align: right; padding-right: 15px !important; font-family: 'Courier New', monospace; }
        .valor-small { font-size: 0.85rem; }
    </style>
</head>
<body>
<?php include_once "../../nav.php"; ?>

<div id="loadingOverlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); z-index:9999; justify-content:center; align-items:center; flex-direction:column; color:white;">
    <div class="spinner-border" role="status"></div>
    <p id="msgLoader" class="mt-2">Carregando</p>
</div>

<div class="container" style="margin-top:100px">
    <div class="d-flex align-items-center mb-4">
        <div>
            <button class="btn" onclick="location.href='../../dashboard.php'" style="background-color: #008e55; color: white;">
                <i class="bi bi-arrow-left"></i> Voltar
            </button>
        </div>
        <div class="mx-auto text-center">
            <h3>Alterar Porcentagem Documento</h3> 
        </div>
        <div style="width: 100px;"></div>
    </div>

   

    <form id="formPacotes" action="atualizar_pacotes.php" method="POST">
        <?php 
        $parametros = $_SESSION['parametros_porcentagem'] ?? [];
        ?>
        
        <!-- TABELA DE PACOTES - AGRUPADO POR OCORRÊNCIA -->
        <div class="card mb-4">
            <div class="card-header bg-warning text-dark">
                <h4 class="mb-0"><i class="bi bi-percent"></i> Pacotes por Ocorrência - <?= count($pacotesOcorrencia) ?> ocorrências</h4>
                <?php if (!empty($parametros)): ?>
                <div class="small mt-1">
                    Documento: <strong><?= htmlspecialchars($parametros['numDoc']) ?></strong> | 
                    Prestador: <strong><?= htmlspecialchars($parametros['codPrest']) ?></strong> | 
                    Período: <strong><?= htmlspecialchars($parametros['periodoRef']) ?></strong> | 
                    Ano: <strong><?= htmlspecialchars($parametros['dtAnoRef']) ?></strong>
                </div>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <p class="text-muted text-center small mb-3">
                    <i class="bi bi-exclamation-circle"></i> Informe a porcentagem por ocorrência de pacote. 
                    A atualização será aplicada ao procedimento e todos os insumos da mesma ocorrência.
                </p>
                
                <?php if (!empty($pacotesOcorrencia)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle table-sm table-striped">
                        <thead class="table-dark text-center">
                            <tr>
                                <th style="width: 5%">#</th>
                                <th style="width: 15%">Pacote</th>
                                <th style="width: 15%">Ocorrência</th>
                                <th style="width: 20%">Procedimentos</th>
                                <th style="width: 20%">Insumos</th>
                                <th style="width: 25%">% Individual</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $idx = 0; ?>
                            <?php foreach ($pacotesOcorrencia as $ocorrencia => $dados): $idx++; ?>
                                <tr>
                                    <td class="text-center"><?= $idx ?></td>
                                    <td style="font-weight:bold"><?= htmlspecialchars($dados['CD_PACOTE']) ?></td>
                                    <td style="font-weight:bold"><?= htmlspecialchars($ocorrencia) ?></td>
                                    <td class="small">
                                        <?= count($dados['procedimentos']) ?> procedimento(s)
                                    </td>
                                    <td class="small">
                                        <?= count($dados['insumos']) ?> insumo(s)
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-center align-items-center">
                                            <div class="input-group input-group-sm" style="max-width: 120px;">
                                                <input 
                                                    type="text" 
                                                    name="pacotes[<?= $idx ?>][porcentagem]" 
                                                    id="pacote_pct_<?= $idx ?>" 
                                                    class="form-control form-control-sm text-center" 
                                                    placeholder="0.00" 
                                                    maxlength="6"
                                                    oninput="validarPercentualPacote(this)">
                                                <span class="input-group-text">%</span>
                                            </div>
                                        </div>
                                        
                                        <!-- Hidden fields com TODOS os progress_recid para update -->
                                        <input type="hidden" name="pacotes[<?= $idx ?>][cd_pacote]" value="<?= htmlspecialchars($dados['CD_PACOTE']) ?>">
                                        <input type="hidden" name="pacotes[<?= $idx ?>][pacote_ocorrencia]" value="<?= htmlspecialchars($ocorrencia) ?>">

                                        <!-- Progress RECIDs dos Procedimentos -->
                                        <?php 
                                        $proc_recids = [];
                                        $proc_hist_recids = [];
                                        $insu_recids = [];
                                        $insu_hist_recids = [];

                                        foreach ($dados['procedimentos'] as $proc) {
                                            if (!empty($proc['PROGRESS_RECID'])) {
                                                $proc_recids[] = $proc['PROGRESS_RECID'];
                                            }
                                            if (!empty($proc['PROGRESS_RECID_HIST'])) {
                                                $proc_hist_recids[] = $proc['PROGRESS_RECID_HIST'];
                                            }
                                        }

                                        foreach ($dados['insumos'] as $insu) {
                                            if (!empty($insu['PROGRESS_RECID'])) {
                                                $insu_recids[] = $insu['PROGRESS_RECID'];
                                            }
                                            if (!empty($insu['PROGRESS_RECID_HIST'])) {
                                                $insu_hist_recids[] = $insu['PROGRESS_RECID_HIST'];
                                            }
                                        }
                                        ?>

                                        <!-- Campos hidden simplificados -->
                                        <?php foreach ($proc_recids as $recid): ?>
                                            <input type="hidden" name="pacotes[<?= $idx ?>][progress_recid_proc][]" value="<?= htmlspecialchars($recid) ?>">
                                        <?php endforeach; ?>

                                        <?php foreach ($proc_hist_recids as $recid): ?>
                                            <input type="hidden" name="pacotes[<?= $idx ?>][progress_recid_hist_proc][]" value="<?= htmlspecialchars($recid) ?>">
                                        <?php endforeach; ?>

                                        <?php foreach ($insu_recids as $recid): ?>
                                            <input type="hidden" name="pacotes[<?= $idx ?>][progress_recid_insu][]" value="<?= htmlspecialchars($recid) ?>">
                                        <?php endforeach; ?>

                                        <?php foreach ($insu_hist_recids as $recid): ?>
                                            <input type="hidden" name="pacotes[<?= $idx ?>][progress_recid_hist_insu][]" value="<?= htmlspecialchars($recid) ?>">
                                        <?php endforeach; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="alert alert-warning text-center">
                    <i class="bi bi-exclamation-triangle"></i> Nenhuma ocorrência de pacote encontrada.
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($pacotesOcorrencia)): ?>
        <div class="text-center mb-5">
            <button type="button" id="btnAtualizarPacotes" class="btn btn-success btn-lg">
                <i class="bi bi-check-circle"></i> Atualizar Pacotes (Aplicar a procedimentos e insumos)
            </button>
        </div>
        <?php endif; ?>

        <!-- Tabela de Procedimentos -->
        <?php if (!empty($procedimentos)): ?>
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="bi bi-clipboard-pulse"></i> Tabela de Procedimentos (<?= count($procedimentos) ?> registros)</h4>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle table-sm mb-0 table-striped">
                        <thead class="table-dark text-center">
                            <tr>
                                <th style="width: 12%">Pacote</th>
                                <th style="width: 12%">Documento</th>
                                <th style="width: 12%">Nº Processo</th>
                                <th style="width: 8%">Sequência</th>
                                <th style="width: 12%">Pacote Ocorrência</th>
                                <th style="width: 22%">Valor Cobrado</th>
                                <th style="width: 22%">Valor Glosado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($procedimentos as $proc): ?>
                                <tr>
                                    <td style="font-weight:bold"><?= htmlspecialchars($proc['CD_PACOTE'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($proc['NR_DOC_ORIGINAL'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($proc['NR_PROCESSO'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($proc['NR_SEQ_DIGITACAO'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($proc['PACOTE_OCORRENCIA'] ?? '') ?></td>
                                    <td class="valor valor-small">R$ <?= formatarValorFinal($proc['VL_COBRADO'] ?? '') ?></td>
                                    <td class="valor valor-small">R$ <?= formatarValorFinal($proc['VL_GLOSADO'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="alert alert-info text-center">
            <i class="bi bi-info-circle"></i> Nenhum procedimento encontrado.
        </div>
        <?php endif; ?>

        <!-- Tabela de Insumos -->
        <?php if (!empty($insumos)): ?>
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0"><i class="bi bi-box-seam"></i> Tabela de Insumos (<?= count($insumos) ?> registros)</h4>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle table-sm mb-0 table-striped">
                        <thead class="table-dark text-center">
                            <tr>
                                <th style="width: 12%">Pacote</th>
                                <th style="width: 12%">Documento</th>
                                <th style="width: 12%">Nº Processo</th>
                                <th style="width: 8%">Sequência</th>
                                <th style="width: 12%">Pacote Ocorrência</th>
                                <th style="width: 22%">Valor Cobrado</th>
                                <th style="width: 22%">Valor Glosado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($insumos as $insu): ?>
                                <tr>
                                    <td style="font-weight:bold"><?= htmlspecialchars($insu['CD_PACOTE'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($insu['NR_DOC_ORIGINAL'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($insu['NR_PROCESSO'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($insu['NR_SEQ_DIGITACAO'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($insu['PACOTE_OCORRENCIA'] ?? '') ?></td>
                                    <td class="valor valor-small">R$ <?= formatarValorFinal($insu['VL_COBRADO'] ?? '') ?></td>
                                    <td class="valor valor-small">R$ <?= formatarValorFinal($insu['VL_GLOSADO'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="alert alert-info text-center">
            <i class="bi bi-info-circle"></i> Nenhum insumo encontrado.
        </div>
        <?php endif; ?>

    </form>
</div>

<?php 
if(isset($_SESSION['retornoUpdatePorcentagem'])): 
    $parametros = $_SESSION['parametros_porcentagem'] ?? [];
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        icon: '<?= $_SESSION['retornoUpdatePorcentagem']['type']; ?>',
        title: '<?= $_SESSION['retornoUpdatePorcentagem']['type'] === 'success' ? 'Sucesso!' : 'Erro!'; ?>',
        text: '<?= addslashes($_SESSION['retornoUpdatePorcentagem']['message']); ?>',
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then((result) => {
        // Se foi um sucesso, simplesmente recarrega a página
        <?php if ($_SESSION['retornoUpdatePorcentagem']['type'] === 'success'): ?>
        setTimeout(() => {
            window.location.reload();
        }, 1000);
        <?php endif; ?>
    });
});
</script>
<?php 
// IMPORTANTE: Unset apenas após exibir a mensagem
unset($_SESSION['retornoUpdatePorcentagem']); 
endif; 
?>

<script>
// Função de validação do percentual
function validarPercentualPacote(input) {
    // Permite digitação livre de números, vírgula e ponto
    let valor = input.value;
    
    // Remove caracteres inválidos, mantendo apenas números, vírgula e ponto
    valor = valor.replace(/[^\d,.]/g, '');
    
    // Permite apenas uma vírgula ou ponto decimal
    const hasComma = valor.includes(',');
    const hasDot = valor.includes('.');
    
    if (hasComma && hasDot) {
        // Se tem ambos, remove o último adicionado
        const lastComma = valor.lastIndexOf(',');
        const lastDot = valor.lastIndexOf('.');
        if (lastComma > lastDot) {
            valor = valor.replace('.', '');
        } else {
            valor = valor.replace(',', '');
        }
    }
    
    // Substitui vírgula por ponto para validação numérica
    const valorNumerico = valor.replace(',', '.');
    
    // Verifica se é um número válido
    const num = parseFloat(valorNumerico);
    
    // Se estiver vazio ou não for número, apenas mantém o valor limpo
    if (valor === '' || isNaN(num)) {
        input.value = valor;
        input.classList.remove('is-invalid', 'is-valid');
        return;
    }
    
    // Validação do range
    if (num < 0) {
        input.value = '0';
        input.classList.add('is-invalid');
        input.classList.remove('is-valid');
        return;
    }
    
    if (num > 100) {
        input.value = '100';
        input.classList.add('is-invalid');
        input.classList.remove('is-valid');
        return;
    }
    
    // Mantém o valor como o usuário digitou (sem formatação automática)
    input.value = valor;
    input.classList.remove('is-invalid');
    input.classList.add('is-valid');
}

// Formatação ao perder o foco
function formatarPercentualAoPerderFoco(input) {
    const valor = input.value.replace(',', '.');
    const num = parseFloat(valor);
    
    if (!isNaN(num) && num >= 0 && num <= 100) {
        // Formata com 2 casas decimais apenas ao sair do campo
        input.value = num.toFixed(2).replace('.', ',');
    }
}

// Adicionar eventos aos inputs quando a página carregar
document.addEventListener('DOMContentLoaded', function() {
    const inputsPercentual = document.querySelectorAll('input[name*="[porcentagem]"]');
    
    inputsPercentual.forEach(input => {
        // Validação durante a digitação
        input.addEventListener('input', function() {
            validarPercentualPacote(this);
        });
        
        // Formatação ao perder o foco
        input.addEventListener('blur', function() {
            formatarPercentualAoPerderFoco(this);
        });
    });
});

// Botão de atualizar pacotes - VERSÃO SIMPLIFICADA
document.getElementById('btnAtualizarPacotes')?.addEventListener('click', function() {
    const form = document.getElementById('formPacotes');
    const formData = new FormData(form);
    
    // Verificar se há pelo menos uma porcentagem válida
    let hasValidPercentage = false;
    const porcentagemInputs = document.querySelectorAll('input[name*="[porcentagem]"]');
    
    porcentagemInputs.forEach(input => {
        const valor = input.value.replace(',', '.').trim();
        if (valor !== '' && !isNaN(parseFloat(valor)) && parseFloat(valor) > 0) {
            hasValidPercentage = true;
        }
    });
    
    if (!hasValidPercentage) {
        Swal.fire({
            icon: 'warning',
            title: 'Nada para atualizar',
            text: 'Informe pelo menos uma porcentagem válida (maior que 0) antes de atualizar.',
            timer: 3000,
            showConfirmButton: true
        });
        return;
    }

    // Mostrar loading
    document.getElementById('msgLoader').innerText = "Atualizando pacotes, aguarde...";
    document.getElementById('loadingOverlay').style.display = 'flex';
    this.disabled = true;

    fetch(form.action, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erro na resposta do servidor: ' + response.status);
        }
        return response.json();
    })
    .then(res => {
        document.getElementById('loadingOverlay').style.display = 'none';
        document.getElementById('btnAtualizarPacotes').disabled = false;
        
        if (res.error) {
            Swal.fire({ 
                icon: 'error', 
                title: 'Erro na atualização', 
                text: res.message || 'Erro desconhecido ao atualizar os pacotes.',
                confirmButtonText: 'OK'
            });
        } else {
            // Sucesso - recarrega a página imediatamente
            window.location.reload();
        }
    })
    .catch(err => {
        document.getElementById('loadingOverlay').style.display = 'none';
        document.getElementById('btnAtualizarPacotes').disabled = false;
        Swal.fire({ 
            icon: 'error', 
            title: 'Erro de comunicação', 
            text: 'Falha ao comunicar com o servidor: ' + err.message,
            confirmButtonText: 'OK'
        });
        console.error('Erro:', err);
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>