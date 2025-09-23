<?php
require '../../vendor/autoload.php'; // se estiver usando composer

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

require_once "../../config/AW00DB.php";
require_once "../../config/oracle.class.php";
require_once "../../config/AW00MD.php";


$inicio = $_GET['inicio'] ?? '';
$fim    = $_GET['fim'] ?? '';

$sql = "
SELECT AI.CD_TIPO_INSUMO_INTERNO,
       AI.CD_INSUMO_INTERNO,
       I.DS_INSUMO,
       AI.CD_TIPO_INSUMO_EXTERNO AS DESPESA,
       AI.CD_INSUMO_EXTERNO,
       AI.CD_USERID,
       TO_CHAR(AI.DT_ATUALIZACAO,'DD/MM/YYYY') AS DT_ATUALIZACAO
FROM gp.ASSINSUM AI
INNER JOIN gp.INSUMOS I
   ON I.CD_TIPO_INSUMO = AI.CD_TIPO_INSUMO_INTERNO
  AND I.CD_INSUMO      = AI.CD_INSUMO_INTERNO
INNER JOIN gp.TISS_ASSOC_TIP_DESPES T
   ON T.CDN_TIP_INSUMO = I.CD_TIPO_INSUMO
WHERE AI.DT_LIMITE >= TRUNC(SYSDATE)
  AND AI.DT_ATUALIZACAO BETWEEN TO_DATE(:inicio,'YYYY-MM-DD') AND TO_DATE(:fim,'YYYY-MM-DD')
";

$stid = oci_parse($conn, $sql);
oci_bind_by_name($stid, ":inicio", $inicio);
oci_bind_by_name($stid, ":fim", $fim);
oci_execute($stid);

// monta planilha
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$sheet->fromArray(["TIPO INTERNO","INSUMO INTERNO","DESCRIÇÃO","DESPESA","INSUMO EXTERNO","USUÁRIO","ATUALIZAÇÃO"], NULL, 'A1');

$rowNum = 2;
while ($row = oci_fetch_assoc($stid)) {
    $sheet->fromArray(array_values($row), NULL, "A{$rowNum}");
    $rowNum++;
}

// download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="relatorio_insumos.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
