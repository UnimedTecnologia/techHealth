<?php
// require '../vendor/autoload.php'; // Ajuste conforme o local do autoload.php

// use PhpOffice\PhpSpreadsheet\Spreadsheet;
// use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

header('Content-Type: text/html; charset=utf-8');
session_start();

// Validar e capturar os dados do POST
$dtEmissaoIni31 = isset($_POST['dtEmissaoIni31']) ? $_POST['dtEmissaoIni31'] : null;
$dtEmisaoFim31  = isset($_POST['dtEmisaoFim31']) ? $_POST['dtEmisaoFim31'] : null;
$codCart31      = isset($_POST['codCart31']) ? $_POST['codCart31'] : null;

// Validar os dados
if (!$dtEmissaoIni31 || !$dtEmisaoFim31 ) {
    echo "Parâmetros obrigatórios ausentes.";
    exit;
}

// Converter datas para formato dd/mm/yyyy
$dtEmissaoIni31 = date("d/m/Y", strtotime($dtEmissaoIni31));
$dtEmisaoFim31  = date("d/m/Y", strtotime($dtEmisaoFim31));

// Conexão com Oracle
require_once "../config/AW00DB.php";
require_once "../config/oracle.class.php";
require_once "../config/AW00MD.php";

$db = new oracle();
$conn = $db->connect($db_host, $db_user, $db_pwd, $db_name, $pconnect);

if (!$conn) {
    $e = oci_error();
    echo "Erro de conexão: " . htmlentities($e['message'], ENT_QUOTES);
    exit;
}

$sql = "SELECT X.DT_EMISSAO_GUIA, X.AA_GUIA_ATENDIMENTO, X.NR_GUIA_ATENDIMENTO, X.NM_GRUPO, X.DT_VENC,
       X.H_SEQ_INICIO PRI_SEQ_ALT, CASE WHEN U1.COD_USUARIO IS NULL THEN H1.CD_USERID_ALT ELSE U1.NOM_USUARIO END PRI_USER_ALT, 
       X.H_SEQ_FINAL ULT_SEQ_ALT,  CASE WHEN U2.COD_USUARIO IS NULL THEN H2.CD_USERID_ALT ELSE U2.NOM_USUARIO END ULT_USER_ALT,
       X.NM_USUARIO, X.CD_UNIDADE_CARTEIRA, X.CD_CARTEIRA_USUARIO, X.LOCAL, X.CD_TIPO_GUIA, X.DS_TIPO_GUIA,
       X.CD_UNIDADE_PRINCIPAL, X.CD_PRESTADOR_PRINCIPAL, X.PRINCIPAL, X.PROF_SOLICITANTE, X.SOLICITANTE, X.IN_LIBERADO_GUIAS
  FROM ( SELECT V.DT_EMISSAO_GUIA, V.CD_TIPO_GUIA, V.DS_TIPO_GUIA, V.AA_GUIA_ATENDIMENTO, V.NR_GUIA_ATENDIMENTO, V.NM_USUARIO, V.CD_UNIDADE_CARTEIRA, 
                V.LOCAL, V.CD_CARTEIRA_USUARIO, V.AA_GUIA_ATEND_ORIGEM, V.NR_GUIA_ATEND_ORIGEM GUIA_ORIGEM, V.NM_GRUPO, V.CD_UNIDADE_PRINCIPAL, 
                V.CD_PRESTADOR_PRINCIPAL, V.PRINCIPAL, V.PROF_SOLICITANTE, V.SOLICITANTE, V.COD_LIB_GUIAS, V.IN_LIBERADO_GUIAS, V.DT_VENCIMENTO DT_VENC, 
                MIN(H.NR_SEQUENCIA_ALT) H_SEQ_INICIO, MAX(H.NR_SEQUENCIA_ALT) H_SEQ_FINAL, V.DES_TIP_FATURAM, V.ELETIVO_URGENCIA
           FROM GP.V_ANALITICO_GUIAS V INNER JOIN GP.GUIA_HIS H ON H.AA_GUIA_ATENDIMENTO = V.AA_GUIA_ATENDIMENTO AND H.NR_GUIA_ATENDIMENTO = V.NR_GUIA_ATENDIMENTO
                                     LEFT JOIN GP.PRESERV P2 ON P2.CD_PRESTADOR       = V.CDPRESTADORSOLICITANTE
          WHERE V.DT_EMISSAO_GUIA BETWEEN to_date(:dataini,'dd/mm/yyyy') AND to_date(:datafim,'dd/mm/yyyy')
            AND V.CD_TIPO_GUIA = 31 /* INTERNACAO - ATENDIMENTO */ 
            AND H.IN_LIB_GUIAS_ALT = 2 /* GUIAS AUTORIZADAS */
            AND V.COD_LIB_GUIAS   IN ( 2, 4, 5, 9, 10 )
          GROUP BY V.DT_EMISSAO_GUIA, V.CD_TIPO_GUIA, V.DS_TIPO_GUIA, V.AA_GUIA_ATENDIMENTO, V.NR_GUIA_ATENDIMENTO, V.NM_USUARIO, V.CD_UNIDADE_CARTEIRA, 
                   V.LOCAL, V.CD_CARTEIRA_USUARIO, V.AA_GUIA_ATEND_ORIGEM, V.NR_GUIA_ATEND_ORIGEM, V.NM_GRUPO, V.CD_UNIDADE_PRINCIPAL, 
                   V.CD_PRESTADOR_PRINCIPAL, V.PRINCIPAL, V.PROF_SOLICITANTE, V.SOLICITANTE, V.IN_LIBERADO_GUIAS, V.DT_VENCIMENTO, V.DES_TIP_FATURAM, 
                   V.ELETIVO_URGENCIA, V.COD_LIB_GUIAS ) X 
 INNER JOIN GP.GUIA_HIS H1 ON H1.AA_GUIA_ATENDIMENTO = X.AA_GUIA_ATENDIMENTO AND H1.NR_GUIA_ATENDIMENTO = X.NR_GUIA_ATENDIMENTO AND H1.NR_SEQUENCIA_ALT = X.H_SEQ_INICIO
 INNER JOIN GP.GUIA_HIS H2 ON H2.AA_GUIA_ATENDIMENTO = X.AA_GUIA_ATENDIMENTO AND H2.NR_GUIA_ATENDIMENTO = X.NR_GUIA_ATENDIMENTO AND H2.NR_SEQUENCIA_ALT = X.H_SEQ_FINAL
  LEFT JOIN EMSFND.USUAR_MESTRE U1 ON H1.CD_USERID_ALT = U1.COD_USUARIO LEFT JOIN EMSFND.USUAR_MESTRE U2 ON H2.CD_USERID_ALT = U2.COD_USUARIO "; 
    
 if($codCart31 <> null){
    $sql .= " WHERE CD_UNIDADE_CARTEIRA = :carteira";
 }


$selectStmt = oci_parse($conn, $sql);
if (!$selectStmt) {
    $e = oci_error($conn);
    echo "Erro ao preparar a consulta: " . htmlentities($e['message'], ENT_QUOTES);
    exit;
}

oci_bind_by_name($selectStmt, ':dataini', $dtEmissaoIni31);
oci_bind_by_name($selectStmt, ':datafim', $dtEmisaoFim31);
if($codCart31 <> null){
    oci_bind_by_name($selectStmt, ':carteira', $codCart31);
}


// Executar SELECT
$result = oci_execute($selectStmt);
if (!$result) {
    $e = oci_error($selectStmt);
    echo "Erro ao executar a consulta: " . htmlentities($e['message'], ENT_QUOTES);
    exit;
}

// Buscar os dados
$data = [];
while ($row = oci_fetch_assoc($selectStmt)) {
    $data[] = $row;
}

// Verificar se há dados
if (empty($data)) {
    echo "Nenhum dado encontrado.";
    exit;
}

// Gerar Excel com PhpSpreadsheet
// $spreadsheet = new Spreadsheet();
// $sheet = $spreadsheet->getActiveSheet();

// // Cabeçalhos
// $headers = array_keys($data[0]);
// $colIndex = 1;
// foreach ($headers as $header) {
//     // $sheet->setCellValueByColumnAndRow($colIndex, 1, $header);
//     $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
//     $sheet->setCellValue($columnLetter . '1', $header);
//     $colIndex++;
// }

// // Dados
// $rowIndex = 2;
// foreach ($data as $row) {
//     $colIndex = 1;
//     foreach ($row as $cell) {
//         // $sheet->setCellValueByColumnAndRow($colIndex, $rowIndex, $cell);
//         $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
//         $sheet->setCellValue($columnLetter . $rowIndex, $cell);
//         $colIndex++;
//     }
//     $rowIndex++;
// }

// $filename = "relatorio_guias_31_" . date("Ymd_His") . "_" . uniqid() . ".xlsx";
// $filepath = __DIR__ . "/tmp/" . $filename; // pasta tmp fora da pasta "autorizacoes"
// $webpath = "autorizacoes/tmp/" . $filename; // caminho que será retornado ao navegador

// if (!file_exists(dirname($filepath))) {
//     mkdir(dirname($filepath), 0777, true);
// }

// $writer = new Xlsx($spreadsheet);
// $writer->save($filepath);

// header('Content-Type: application/json');
// echo json_encode([
//     "success" => true,
//     "file" => $webpath 
// ]);
// exit;

$filename = "relatorio_guias_31_" . date("Ymd_His") . ".csv";
$filepath = __DIR__ . "/tmp/" . $filename;

// Criar diretório se não existir
if (!file_exists(dirname($filepath))) {
    mkdir(dirname($filepath), 0777, true);
}

$fp = fopen($filepath, 'w');

// Escrever cabeçalhos
fputcsv($fp, array_keys($data[0]), ';');

// Escrever dados
foreach ($data as $row) {
    fputcsv($fp, $row, ';'); // separador ;
}

fclose($fp);

// Retornar JSON com caminho
echo json_encode([
    'success' => true,
    'file' => "autorizacoes/tmp/" . $filename
]);
exit;