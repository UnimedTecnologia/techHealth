<?php

header('Content-Type: application/json; charset=utf-8');
session_start();

set_time_limit(1200); // tempo em segundos (10 minutos)
date_default_timezone_set('America/Sao_Paulo');

// Validar e capturar os dados do POST
$dtInicioProdClin = $_POST['dtInicioProdClin']; // Ex: 2025-05
$dtFimProdClin = $_POST['dtFimProdClin'];       // Ex: 2025-07
$gruposPres = $_POST['gruposPres'] ?? '';

if (empty($gruposPres)) {
    echo json_encode([
        'success' => false,
        'message' => 'Grupo de prestadores não informado.'
    ]);
    exit;
}


if (!$dtInicioProdClin || !$dtFimProdClin) {
    echo json_encode([
        'success' => false,
        'message' => 'Parâmetros obrigatórios ausentes.'
    ]);
    exit;
}

// Extrair ano/mês
$anoInicio = (int)substr($dtInicioProdClin, 0, 4);
$mesInicio = (int)substr($dtInicioProdClin, 5, 2);
$anoFim = (int)substr($dtFimProdClin, 0, 4);
$mesFim = (int)substr($dtFimProdClin, 5, 2);

// Ex: transforma "10, 20,30" em "10,20,30"
$gruposPres = preg_replace('/\s+/', '', $gruposPres);

// Apenas permitir mesma base de ano (ajuste se quiser cruzar anos)
if ($anoInicio !== $anoFim) {
    echo json_encode([
        'success' => false,
        'message' => 'Selecione um intervalo dentro do mesmo ano.'
    ]);
    exit;
}

// Gerar array de meses entre inicio e fim
$meses = [];
for ($m = $mesInicio; $m <= $mesFim; $m++) {
    $meses[] = (int)$m;
}
$mesesIn = implode(',', $meses); // ex: 5,6,7

// Conexão com Oracle
require_once "../config/AW00DB.php";
require_once "../config/oracle.class.php";
require_once "../config/AW00MD.php";

$db = new oracle();
$conn = $db->connect($db_host, $db_user, $db_pwd, $db_name, $pconnect);

if (!$conn) {
    $e = oci_error();
    echo json_encode([
        'success' => false,
        'message' => 'Erro de conexão: ' . htmlentities($e['message'], ENT_QUOTES)
    ]);
    exit;
}

// Montar SQL com lista de meses dinâmicos
$sql = "
SELECT * FROM (
    SELECT L.ANO, L.MES, L.CD_UNIDADE_PRESTADORA, L.CD_UNIDADE_CARTEIRA, L.CD_POS_EQUIPE, L.GRAU_PARTICIPACAO, L.CD_PRESTADOR, L.NM_PRESTADOR,
           L.REG_PREST_EXEC, UPPER(L.NM_PREST_EXEC) NM_PREST_EXEC, L.CD_GRUPO_PRESTADOR, L.DS_GRUPO_PRESTADOR, L.UTILIZACAO,  L.CD_UNIDADE,
           L.UNIDADE_PRESTADORA, L.REG_PREST_DOC,  L.NM_PREST_DOC, L.REG_SOL_DOC,  L.NM_SOL_DOC, L.CD_TRANSACAO, L.TIPO, L.NR_DOC_ORIGINAL,
           L.NM_USUARIO, L.CARTEIRINHA, L.TP_INSUMO, L.CODIGO, L.DESCRICAO, L.QTD_MOV, L.VL_PAGO, L.VL_CONTAS, L.ESPECIALIDADE, L.CDESPPRESTEXECUTANTE
      FROM gp.V_ANALITICO_MOV_LOCAL L
     WHERE L.ANO = :ano AND L.MES IN ($mesesIn)
       AND L.CD_GRUPO_PRESTADOR in ($gruposPres)
       AND L.IN_LIBERADO_PAGTO = 1

    UNION

    SELECT I.ANO, I.MES, I.CD_UNIDADE_PRESTADORA, I.CD_UNIDADE_CARTEIRA, I.CD_POS_EQUIPE, I.GRAU_PARTICIPACAO, I.CD_PRESTADOR, I.NM_PRESTADOR,
           I.REG_PREST_EXEC, UPPER(I.NM_PREST_EXEC) NM_PREST_EXEC, I.CD_GRUPO_PRESTADOR, I.DS_GRUPO_PRESTADOR, I.UTILIZACAO,  I.CD_UNIDADE,
           I.UNIDADE_PRESTADORA, I.REG_PREST_DOC,  I.NM_PREST_DOC, I.REG_SOL_DOC,  I.NM_SOL_DOC, I.CD_TRANSACAO, I.TIPO, I.NR_DOC_ORIGINAL,
           I.NM_USUARIO, I.CARTEIRINHA, I.TP_INSUMO, I.CODIGO, I.DESCRICAO, I.QTD_MOV, I.VL_PAGO, I.VL_CONTAS, I.ESPECIALIDADE, I.CDESPPRESTEXECUTANTE
      FROM gp.V_ANALITICO_MOV_INTER I
     WHERE I.ANO = :ano AND I.MES IN ($mesesIn)
       AND I.CD_GRUPO_PRESTADOR in ($gruposPres)
       AND I.IN_LIBERADO_PAGTO = 1
)";

$selectStmt = oci_parse($conn, $sql);
oci_bind_by_name($selectStmt, ':ano', $anoInicio);

// Executar SELECT
$result = oci_execute($selectStmt);
if (!$result) {
    $e = oci_error($selectStmt);
    echo json_encode([
        'success' => false,
        'message' => "Erro ao executar a consulta: " . htmlentities($e['message'], ENT_QUOTES),
        'file' => ""
    ]);
    exit;
}

// Buscar os dados
$data = [];
while ($row = oci_fetch_assoc($selectStmt)) {
    $data[] = $row;
}

// Verificar se há dados
if (empty($data)) {
    echo json_encode([
        'success' => false,
        'message' => "Nenhum dado encontrado.",
        'file' => ""
    ]);
    exit;
}

$filename = "relatorioProdClin_" . date("Ymd_His") . ".csv";
$filepath = __DIR__ . "/tmp/" . $filename;

if (!file_exists(dirname($filepath))) {
    mkdir(dirname($filepath), 0777, true);
}

// $fp = fopen($filepath, 'w');
$fp = fopen($filepath, 'w');

// Adiciona BOM UTF-8 para o Excel reconhecer a codificação
fwrite($fp, "\xEF\xBB\xBF");


// Escrever cabeçalho
fputcsv($fp, array_keys($data[0]), ';');

// Escrever dados
foreach ($data as $row) {
    if (isset($row['CARTEIRINHA']) && !empty($row['CARTEIRINHA'])) {
        $row['CARTEIRINHA'] = '="' . $row['CARTEIRINHA'] . '"';
    }
    fputcsv($fp, $row, ';');
}


// foreach ($data as $row) {
//     fputcsv($fp, $row, ';');
// }

fclose($fp);


// Retorno final
// echo json_encode([
//     'success' => true,
//     'file' => "redeCredenciada/tmp/" . $filename
// ]);
echo json_encode([
    'success' => true,
    'file' => "redeCredenciada/tmp/" . $filename // Caminho completo correto
]);
exit;
