<?php
// utils/relatorio_tituloPrestador.php

set_time_limit(5400);
ini_set('memory_limit', '768M');

require_once "../config/AW00DB.php";
require_once "../config/oracle.class.php";
require_once "../config/AW00MD.php";

// autoload do Composer (verifique se o caminho está correto)
$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require $autoloadPath;
}

// PHPSPREADSHEET classes (usadas apenas se estiverem disponíveis)
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Converte índice numérico de coluna (1-based) para letra (A, B, ..., Z, AA, AB, ...)
 */
function colIndexToLetter(int $colIndex): string {
    $letter = '';
    while ($colIndex > 0) {
        $mod = ($colIndex - 1) % 26;
        $letter = chr(65 + $mod) . $letter;
        $colIndex = intdiv($colIndex - $mod - 1, 26);
    }
    return $letter;
}

// pega parâmetros
$cdPrestador = $_POST['cdPrestador'] ?? null;
$codTitulo   = $_POST['codTitulo'] ?? null;

if (!$cdPrestador || !$codTitulo) {
    // resposta amigável (sem expor erro/crash)
    echo '<!doctype html><html><head><meta charset="utf-8"><title>Relatório</title></head><body>';
    echo '<p>Parâmetros inválidos. Certifique-se de selecionar um prestador e informar o código do título.</p>';
    echo '</body></html>';
    exit;
}

// normaliza codTitulo para 16 dígitos (zeros à esquerda)
$codTitulo = str_pad(preg_replace('/\D/', '', $codTitulo), 16, '0', STR_PAD_LEFT);

// -------------------------------------------------
// SQL (USE AQUI O SEU SQL COMPLETO — eu já coloquei o SQL com :cdPrestador e :codTitulo)
// -------------------------------------------------
// SQL com binds
$sql = "
SELECT MI.DT_ANOREF, MI.NR_PERREF, MI.CD_MODALIDADE, MI.NR_TER_ADESAO, MI.CD_UNIDADE_CARTEIRA, PT.CD_CONTRATANTE, CT.NM_CONTRATANTE, D.NR_LOTE_IMP, 
       D.NR_SEQUENCIA_IMP, D.CD_TRANSACAO, D.NR_DOC_ORIGINAL, D.AA_GUIA_ATENDIMENTO, D.NR_GUIA_ATENDIMENTO, D.TP_ATEND, D.TP_INTER, T.COD_DOCTO_AP, 
       D.CD_PRESTADOR_IMP, TM.DS_TIPO_MEDICINA TP_MEDICINA, DECODE(D.CHAR_20,1,'PEND_AG',2, 'PEND_LIB',3, 'LIB', 4, 'PG', 5, 'FAT', 6, 'PG_FAT', 'CANC') STATUS,
       MI.CD_PACOTE, E.CD_TIPO_INSUMO, E.CD_INSUMO, E.DS_INSUMO, MI.CD_PRESTADOR_PAGAMENTO, NVL(MI.VL_REAL_PAGO,0) VL_PAGO, MI.VL_REAL_GLOSADO,
       MI.PROGRESS_RECID, NVL(PN.VL_REAL_PAGO,0) VL_PAGO_NOTA, PN.PROGRESS_RECID REC_NOTA, NVL(FE.VL_CONTAS,0) VL_CONTAS
  FROM GP.TITUPRES T  
       INNER JOIN GP.TIPOMEDI TM ON TM.CD_TIPO_MEDICINA = T.CD_TIPO_MEDICINA
       INNER JOIN GP.MOVIMEN_INSUMO_NOTA PN ON PN.CD_PRESTADOR = T.CD_PRESTADOR
                                           AND PN.COD_DOCTO_PAGTO_AP = T.U##COD_DOCTO_AP
       INNER JOIN GP.MOV_INSU MI ON MI.CD_UNIDADE = PN.CD_UNIDADE
                                AND MI.CD_UNIDADE_PRESTADORA = PN.CD_UNIDADE_PRESTADORA
                                AND MI.CD_TRANSACAO = PN.CD_TRANSACAO
                                AND MI.NR_SERIE_DOC_ORIGINAL = PN.NR_SERIE_DOC_ORIGINAL
                                AND MI.NR_DOC_ORIGINAL = PN.NR_DOC_ORIGINAL
                                AND MI.NR_DOC_SISTEMA = PN.NR_DOC_SISTEMA
                                AND MI.NR_PROCESSO = PN.NR_PROCESSO
                                AND MI.NR_SEQ_DIGITACAO = PN.NR_SEQ_DIGITACAO
       LEFT JOIN GP.USUARIO US ON MI.CD_MODALIDADE = US.CD_MODALIDADE
                              AND MI.CD_USUARIO = US.CD_USUARIO
                              AND MI.NR_TER_ADESAO = US.NR_TER_ADESAO
                              AND US.LOG_17 <> 1
       LEFT JOIN GP.PROPOST PT ON PT.NR_PROPOSTA = US.NR_PROPOSTA
                              AND PT.CD_MODALIDADE = US.CD_MODALIDADE
       LEFT JOIN GP.CONTRAT CT ON CT.NR_INSC_CONTRATANTE = PT.NR_INSC_CONTRATANTE
                              AND CT.CD_CONTRATANTE = PT.CD_CONTRATANTE
       LEFT JOIN GP.FATEVECO FE ON FE.CD_UNIDADE = MI.CD_UNIDADE
                               AND FE.CD_UNIDADE_PRESTADOR = MI.CD_UNIDADE_PRESTADORA
                               AND FE.CD_TRANSACAO = MI.CD_TRANSACAO
                               AND FE.U##NR_SERIE_DOC_ORIGINAL = MI.U##NR_SERIE_DOC_ORIGINAL
                               AND FE.NR_DOC_ORIGINAL = MI.NR_DOC_ORIGINAL
                               AND FE.NR_DOC_SISTEMA = MI.NR_DOC_SISTEMA
                               AND FE.NR_PROCESSO = MI.NR_PROCESSO
                               AND FE.NR_SEQ_DIGITACAO = MI.NR_SEQ_DIGITACAO
                               AND FE.AA_REFERENCIA = MI.DT_ANOREF
                               AND FE.MM_REFERENCIA = MI.NR_PERREF
                               AND NOT FE.CD_EVENTO IN (303,304)
       INNER JOIN GP.INSUMOS E ON MI.CD_TIPO_INSUMO = E.CD_TIPO_INSUMO
                              AND MI.CD_INSUMO = E.CD_INSUMO
       INNER JOIN GP.DOCRECON D ON D.CD_UNIDADE = PN.CD_UNIDADE
                               AND D.CD_UNIDADE_PRESTADORA = PN.CD_UNIDADE_PRESTADORA
                               AND D.CD_TRANSACAO = PN.CD_TRANSACAO
                               AND UPPER(D.NR_SERIE_DOC_ORIGINAL) = PN.NR_SERIE_DOC_ORIGINAL
                               AND D.NR_DOC_ORIGINAL = PN.NR_DOC_ORIGINAL
                               AND D.NR_DOC_SISTEMA = PN.NR_DOC_SISTEMA
 WHERE T.CD_PRESTADOR = :cdPrestador 
   AND T.COD_DOCTO_AP = :codTitulo
 GROUP BY MI.DT_ANOREF, MI.NR_PERREF, MI.CD_MODALIDADE, MI.NR_TER_ADESAO, MI.CD_UNIDADE_CARTEIRA, PT.CD_CONTRATANTE, CT.NM_CONTRATANTE, D.NR_LOTE_IMP, 
          D.NR_SEQUENCIA_IMP, D.CD_TRANSACAO, D.NR_DOC_ORIGINAL, D.AA_GUIA_ATENDIMENTO, D.NR_GUIA_ATENDIMENTO, D.TP_ATEND, D.TP_INTER, T.COD_DOCTO_AP, 
          D.CD_PRESTADOR_IMP, TM.DS_TIPO_MEDICINA, D.CHAR_20, MI.CD_PACOTE, E.CD_TIPO_INSUMO, E.CD_INSUMO, E.DS_INSUMO, NVL(MI.VL_REAL_PAGO,0), 
          MI.CD_PRESTADOR_PAGAMENTO, MI.VL_REAL_GLOSADO, MI.PROGRESS_RECID, NVL(PN.VL_REAL_PAGO,0), PN.PROGRESS_RECID, NVL(FE.VL_CONTAS,0)
";

// -------------------------------------------------
// Execução segura e geração do arquivo
// -------------------------------------------------

$stid = null;
try {
    // verifica se $conn foi definido pelos seus includes (AW00DB / oracle.class)
    if (!isset($conn) || !$conn) {
        throw new RuntimeException('Conexão Oracle não encontrada. Verifique os arquivos de configuração (AW00DB, oracle.class).');
    }

    $stid = oci_parse($conn, $sql);
    if (!$stid) {
        $err = oci_error($conn);
        throw new RuntimeException('Erro ao preparar query: ' . ($err['message'] ?? 'erro desconhecido'));
    }

    // binds
    oci_bind_by_name($stid, ":cdPrestador", $cdPrestador);
    oci_bind_by_name($stid, ":codTitulo", $codTitulo);

    $exec = oci_execute($stid);
    if (!$exec) {
        $err = oci_error($stid);
        throw new RuntimeException('Erro ao executar query: ' . ($err['message'] ?? 'erro desconhecido'));
    }

    // Se a biblioteca PhpSpreadsheet estiver disponível, gera XLSX
    if (class_exists(\PhpOffice\PhpSpreadsheet\Spreadsheet::class)) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // cabeçalho
        $ncols = oci_num_fields($stid);
        for ($i = 1; $i <= $ncols; $i++) {
            $colLetter = colIndexToLetter($i);
            $sheet->setCellValue($colLetter . "1", oci_field_name($stid, $i));
        }

        // linhas
        $rowIndex = 2;
        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            $colIndex = 1;
            foreach ($row as $item) {
                $colLetter = colIndexToLetter($colIndex);
                // evita warnings: converte null para string vazia
                $sheet->setCellValue($colLetter . $rowIndex, $item === null ? '' : $item);
                $colIndex++;
            }
            $rowIndex++;
        }

        // força o download do xlsx
        $filename = "relatorio_tituloPrestador_" . date("Ymd_His") . ".xlsx";
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    // Caso PhpSpreadsheet NÃO esteja instalado: fallback para CSV
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="relatorio_tituloPrestador.csv"');
    $output = fopen('php://output', 'w');
    // cabeçalhos
    $ncols = oci_num_fields($stid);
    $headers = [];
    for ($i = 1; $i <= $ncols; $i++) {
        $headers[] = oci_field_name($stid, $i);
    }
    fputcsv($output, $headers, ';');

    while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
        // valores em ordem
        fputcsv($output, array_values($row), ';');
    }
    fclose($output);
    exit;
} catch (Throwable $e) {
    // registra o erro no log do servidor e mostra mensagem amigável ao usuário
    error_log('Erro relatorio_tituloPrestador: ' . $e->getMessage());
    echo '<!doctype html><html><head><meta charset="utf-8"><title>Relatório</title></head><body>';
    echo '<p>Ocorreu um erro ao gerar o relatório. Verifique os logs do servidor ou entre em contato com o administrador.</p>';
    echo '</body></html>';
    exit;
} finally {
    if (isset($stid) && $stid) {
        @oci_free_statement($stid);
    }
    if (isset($conn) && $conn) {
        @oci_close($conn);
    }
}
