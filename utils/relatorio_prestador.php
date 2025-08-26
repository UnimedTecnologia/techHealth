<?php
header('Content-Type: text/html; charset=utf-8');
session_start();

set_time_limit(3600);

$ano_ref = (int) $_POST['anoref'];
$num_ref = (int) $_POST['numref'];
$serie_documento = $_POST['seriedoc'] ?? null;

require_once "../config/AW00DB.php";
require_once "../config/oracle.class.php";
require_once "../config/AW00MD.php";

require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// require_once '../vendor/phpoffice/phpexcel/Classes/PHPExcel.php';

$db = new oracle();
$conn = $db->connect($db_host, $db_user, $db_pwd, $db_name, $pconnect);

if (!$conn) {
    $e = oci_error();
    echo "Erro de conexão: " . htmlentities($e['message'], ENT_QUOTES);
    exit;
}

oci_set_client_info($conn, 'UTF-8');

// Consulta base para procedimentos
$sql1 = "SELECT  
       P.DT_REALIZACAO,
       CASE WHEN D.CD_UNIDADE_CARTEIRA = 540 THEN Z.NM_USUARIO ELSE OU.NM_USUARIO END NOME, 
       CT.NM_CONTRATANTE CONTRATANTE, 
       CASE WHEN D.CD_UNIDADE_CARTEIRA = 540 THEN UPPER('LOCAL') ELSE 'INTERCAMBIO' END UNIMED,
       D.CD_UNIDADE_CARTEIRA, D.CD_CARTEIRA_USUARIO,
       D.NR_DOC_ORIGINAL, D.NR_DOC_SISTEMA, D.CD_TRANSACAO, D.NR_SERIE_DOC_ORIGINAL, 
       D.AA_GUIA_ATENDIMENTO, D.NR_GUIA_ATENDIMENTO,
       D.DT_ANOREF, D.NR_PERREF, D.CD_PRESTADOR_PRINCIPAL,
       C.NM_PRESTADOR, 
       P.CD_PACOTE, 
       A.CDPROCEDIMENTOCOMPLETO, 
       A.DES_PROCEDIMENTO, 
       P.QT_PROCEDIMENTOS, 
       P.VL_COBRADO, 
       P.VL_BASE_VALOR_SISTEMA, 
       P.VL_REAL_PAGO, 
       P.CD_POS_EQUIPE GRAU_PART,
       P.CHAR_4 PROF_EXEC, P.CHAR_13 REGISTRO, P.CHAR_14 NR_REGISTRO, P.CHAR_11 CBO, 
       P.CDESPPRESTEXECUTANTE ESP_EXEC, P.CHAR_19,      
       D.nm_prof_sol SOLICITANTE, D.char_16 REG_SOL, D.char_17 REGISTRO_SOL, D.char_18 UF_SOL
  FROM GP.DOCRECON D
       INNER JOIN GP.MOVIPROC P ON P.NR_DOC_ORIGINAL = D.NR_DOC_ORIGINAL
                               AND P.NR_SERIE_DOC_ORIGINAL = D.NR_SERIE_DOC_ORIGINAL
                               AND P.DT_ANOREF = D.DT_ANOREF
                               AND P.NR_PERREF = D.NR_PERREF
       INNER JOIN GP.PRESERV C ON C.CD_PRESTADOR = D.CD_PRESTADOR_PRINCIPAL
       LEFT JOIN GP.USUARIO Z ON Z.CD_MODALIDADE = D.CD_MODALIDADE
                              AND Z.NR_TER_ADESAO = D.NR_TER_ADESAO
                              AND Z.CD_USUARIO = D.CD_USUARIO
       LEFT JOIN GP.OUT_UNI OU ON D.CD_UNIDADE_CARTEIRA = OU.CD_UNIDADE
                              AND D.CD_CARTEIRA_USUARIO = OU.CD_CARTEIRA_USUARIO
       LEFT JOIN GP.PROPOST PP ON Z.CD_MODALIDADE = PP.CD_MODALIDADE
                               AND Z.NR_TER_ADESAO = PP.NR_TER_ADESAO
       LEFT JOIN GP.CONTRAT CT ON PP.NR_INSC_CONTRATANTE = CT.NR_INSC_CONTRATANTE
       INNER JOIN GP.AMBPROCE A ON A.CD_ESP_AMB = P.CD_ESP_AMB
                             AND A.CD_GRUPO_PROC_AMB = P.CD_GRUPO_PROC_AMB
                             AND A.CD_PROCEDIMENTO = P.CD_PROCEDIMENTO
                             AND A.DV_PROCEDIMENTO = P.DV_PROCEDIMENTO
        WHERE D.DT_ANOREF = :ano_ref  
         AND D.NR_PERREF = :num_ref 
         AND D.CHAR_20 <> 7";

// Consulta base para insumos
$sql2 = "SELECT  
       I.DT_REALIZACAO,
       CASE WHEN D.CD_UNIDADE_CARTEIRA = 540 THEN Z.NM_USUARIO ELSE OU.NM_USUARIO END NOME, 
       CT.NM_CONTRATANTE CONTRATANTE, 
       CASE WHEN D.CD_UNIDADE_CARTEIRA = 540 THEN UPPER('LOCAL') ELSE 'INTERCAMBIO' END UNIMED,
       D.CD_UNIDADE_CARTEIRA, D.CD_CARTEIRA_USUARIO,
       D.NR_DOC_ORIGINAL, D.NR_DOC_SISTEMA, D.CD_TRANSACAO, D.NR_SERIE_DOC_ORIGINAL, 
       D.AA_GUIA_ATENDIMENTO, D.NR_GUIA_ATENDIMENTO,
       D.DT_ANOREF, D.NR_PERREF, D.CD_PRESTADOR_PRINCIPAL,
       C.NM_PRESTADOR, 
       I.CD_PACOTE, 
       I.CD_INSUMO AS PROCEDIMENTO, 
       A.DS_INSUMO AS DES_PROCEDIMENTO, 
       I.QT_INSUMO AS QT_PROCEDIMENTOS, 
       I.VL_COBRADO, 
       I.VL_BASE_VALOR_SISTEMA, 
       I.VL_REAL_PAGO, 
       NULL AS GRAU_PART,
       I.CHAR_4 PROF_EXEC, I.CHAR_13 REGISTRO, I.CHAR_14 NR_REGISTRO, I.CHAR_11 CBO, 
       I.CDESPPRESTEXECUTANTE ESP_EXEC, I.CHAR_19,      
       D.nm_prof_sol SOLICITANTE, D.char_16 REG_SOL, D.char_17 REGISTRO_SOL, D.char_18 UF_SOL
  FROM GP.DOCRECON D
       INNER JOIN GP.MOV_INSU I ON I.NR_DOC_ORIGINAL = D.NR_DOC_ORIGINAL
                               AND I.NR_SERIE_DOC_ORIGINAL = D.NR_SERIE_DOC_ORIGINAL
                               AND I.DT_ANOREF = D.DT_ANOREF
                               AND I.NR_PERREF = D.NR_PERREF
       INNER JOIN GP.PRESERV C ON C.CD_PRESTADOR = D.CD_PRESTADOR_PRINCIPAL
       LEFT JOIN GP.USUARIO Z ON Z.CD_MODALIDADE = D.CD_MODALIDADE
                              AND Z.NR_TER_ADESAO = D.NR_TER_ADESAO
                              AND Z.CD_USUARIO = D.CD_USUARIO
       LEFT JOIN GP.OUT_UNI OU ON D.CD_UNIDADE_CARTEIRA = OU.CD_UNIDADE
                              AND D.CD_CARTEIRA_USUARIO = OU.CD_CARTEIRA_USUARIO
       LEFT JOIN GP.PROPOST PP ON Z.CD_MODALIDADE = PP.CD_MODALIDADE
                               AND Z.NR_TER_ADESAO = PP.NR_TER_ADESAO
       LEFT JOIN GP.CONTRAT CT ON PP.NR_INSC_CONTRATANTE = CT.NR_INSC_CONTRATANTE
       INNER JOIN GP.INSUMOS A ON A.CD_TIPO_INSUMO = I.CD_TIPO_INSUMO
                               AND A.CD_INSUMO = I.CD_INSUMO
       WHERE D.DT_ANOREF = :ano_ref  
         AND D.NR_PERREF = :num_ref 
         AND D.CHAR_20 <> 7";

// Parâmetros básicos
$bindings = [
    ':ano_ref' => $ano_ref,
    ':num_ref' => $num_ref
];

// Adicionar condições conforme os parâmetros recebidos
if (!empty($serie_documento)) {
    $sql1 .= " AND D.NR_SERIE_DOC_ORIGINAL = :serie_documento";
    $sql2 .= " AND D.NR_SERIE_DOC_ORIGINAL = :serie_documento";
    $bindings[':serie_documento'] = $serie_documento;
}

// Tratamento para múltiplas transações
if (!empty($_POST['transacoes'])) {
    $transacoes = explode(',', $_POST['transacoes']);
    $placeholders = implode(',', array_map(function($item) { 
        return "'" . trim($item) . "'"; 
    }, $transacoes));
    
    $sql1 .= " AND D.CD_TRANSACAO IN ($placeholders)";
    $sql2 .= " AND D.CD_TRANSACAO IN ($placeholders)";
}

// Tratamento para múltiplos prestadores
if (!empty($_POST['prestador_principal'])) {
    $prestadores = explode(',', $_POST['prestador_principal']);
    $placeholders = implode(',', array_map(function($item) { 
        return "'" . trim($item) . "'"; 
    }, $prestadores));
    
    $sql1 .= " AND D.CD_PRESTADOR_PRINCIPAL IN ($placeholders)";
    $sql2 .= " AND D.CD_PRESTADOR_PRINCIPAL IN ($placeholders)";
}

// Tratamento para múltiplos grupos de prestadores
if (!empty($_POST['grupo_prestadores'])) {
    $grupos = explode(',', $_POST['grupo_prestadores']);
    $placeholders = implode(',', array_map(function($item) { 
        return "'" . trim($item) . "'"; 
    }, $grupos));
    
    $sql1 .= " AND C.CD_GRUPO_PRESTADOR IN ($placeholders)";
    $sql2 .= " AND C.CD_GRUPO_PRESTADOR IN ($placeholders)";
}

// Combinar as consultas
$sql = "($sql1) UNION ($sql2)";

// Preparar e executar a consulta
$stmt = oci_parse($conn, $sql);
if (!$stmt) {
    $e = oci_error($conn);
    echo "Erro ao preparar a consulta: " . htmlentities($e['message'], ENT_QUOTES);
    exit;
}

// Vincular parâmetros
foreach ($bindings as $key => $value) {
    oci_bind_by_name($stmt, $key, $bindings[$key]);
}

// Executar a consulta
$result = oci_execute($stmt, OCI_NO_AUTO_COMMIT);
if (!$result) {
    $e = oci_error($stmt);
    echo "Erro ao executar a consulta: " . htmlentities($e['message'], ENT_QUOTES);
    exit;
}

// Coletar os dados
$data = [];
while ($row = oci_fetch_assoc($stmt)) {
    $data[] = $row;
}

// Gerar Excel se solicitado
if (isset($_POST['download']) && $_POST['download'] == 'excel') {
    if (empty($data)) {
        $_SESSION['erroRelatorio'] = "Nenhum dado encontrado para os critérios selecionados.";
        header("Location: ../dashboard.php");
        exit;
    }

//!relatorio excel (xlsx) local
    try {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Adicionar cabeçalhos
        $headers = array_keys($data[0]);
        $column = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($column . '1', $header);
            $column++;
        }

        // Adicionar dados
        $rowNumber = 2;
        foreach ($data as $row) {
            $column = 'A';
            foreach ($row as $value) {
                $sheet->setCellValue($column . $rowNumber, $value === null ? '' : $value);
                $column++;
            }
            $rowNumber++;
        }

        // Autoajustar largura das colunas
        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Definir nome do arquivo
        $nomeDoc = $ano_ref . "_" . ($_POST['prestador_principal'] ?? 'all') . "_" . $num_ref;
        $arquivoPath = '../relatorios/relatorio_' . $nomeDoc . '.xlsx';

        // Garantir que o diretório existe
        if (!file_exists('../relatorios')) {
            mkdir('../relatorios', 0777, true);
        }

        // Salvar o arquivo
        $writer = new Xlsx($spreadsheet);
        $writer->save($arquivoPath);

        // Salvar informações na sessão
        $_SESSION['dadosRelatorio'] = [
            'data' => $data,
            'nomeDoc' => $nomeDoc,
            'arquivoPath' => $arquivoPath,
        ];

        // Redirecionar
        header("Location: ../dashboard.php");
        exit;

    } catch (Exception $e) {
        $_SESSION['erroRelatorio'] = "Erro ao gerar o arquivo Excel: " . $e->getMessage();
        header("Location: ../dashboard.php");
        exit;
    }

    //!relatorio excel (xlsx) maquina 15
    // Criar nova instância do PHPExcel
    // $objPHPExcel = new PHPExcel();
    
    // // Definir propriedades do documento
    // $objPHPExcel->getProperties()
    //     ->setCreator("Tech Health")
    //     ->setLastModifiedBy("Tech Health")
    //     ->setTitle("Relatório")
    //     ->setSubject("Relatório")
    //     ->setDescription("Relatório gerado pelo sistema");
    
    // // Selecionar planilha ativa
    // $sheet = $objPHPExcel->getActiveSheet();
    
    // // Adicionar cabeçalhos
    // $headers = array_keys($data[0]);
    // $col = 0;
    // foreach ($headers as $header) {
    //     $sheet->setCellValueByColumnAndRow($col, 1, $header);
    //     $col++;
    // }
    
    // // Adicionar dados
    // $rowNumber = 2;
    // foreach ($data as $row) {
    //     $col = 0;
    //     foreach ($row as $value) {
    //         $sheet->setCellValueByColumnAndRow($col, $rowNumber, $value);
    //         $col++;
    //     }
    //     $rowNumber++;
    // }
    
    // // Autoajustar largura das colunas
    // foreach (range(0, count($headers) - 1) as $column) {
    //     $sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($column))->setAutoSize(true);
    // }
    
    // // Definir nome do arquivo
    // $nomeDoc = $ano_ref . "_" . (isset($prestador_principal) ? $prestador_principal : 'todos') . "_" . $num_ref;
    // $arquivoPath = '../relatorios/relatorio_' . $nomeDoc . '.xlsx';
    
    // // Salvar o arquivo
    // $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    // $objWriter->save($arquivoPath);
    
    // // Salvar informações na sessão
    // $_SESSION['dadosRelatorio'] = [
    //     'data' => $data,
    //     'nomeDoc' => $nomeDoc,
    //     'arquivoPath' => $arquivoPath,
    // ];
    
    // // Redirecionar
    // header("Location: ../dashboard.php");
    // exit;

}

oci_free_statement($stmt);
oci_close($conn);