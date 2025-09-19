<?php

// Ou registre em um arquivo temporário
file_put_contents('/tmp/debug_php.log', "Iniciando script\n", FILE_APPEND);

header('Content-Type: text/html; charset=utf-8');
session_start();

set_time_limit(3600);
ini_set('memory_limit', '2G');

$ano_ref = $_POST['anoref'];
$num_ref = $_POST['numref'];
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

$meses        = array_filter(array_map('trim', explode(',', $num_ref)), 'is_numeric');
$placeholdersMes = [];
foreach ($meses as $i => $val) {
    $placeholdersMes[] = ":pMes{$i}";
}
$arrayMesStr = implode(',', $placeholdersMes);

//! CONSULTA PARA GRUPO DE PRESTADORES
if (!empty($_POST['grupo_prestadores'])) {
    $grupos = explode(',', $_POST['grupo_prestadores']);
    $placeholdersGrupo = implode(',', array_map(function($item) { 
        return "'" . trim($item) . "'"; 
    }, $grupos));

    $sql = "
    SELECT * FROM (
    SELECT L.ANO, L.MES, L.CD_UNIDADE_PRESTADORA, L.CD_UNIDADE_CARTEIRA, L.CD_POS_EQUIPE, L.GRAU_PARTICIPACAO, L.CD_PRESTADOR, L.NM_PRESTADOR,
        L.REG_PREST_EXEC, UPPER(L.NM_PREST_EXEC) NM_PREST_EXEC, L.CD_GRUPO_PRESTADOR, L.DS_GRUPO_PRESTADOR, L.UTILIZACAO,  L.CD_UNIDADE,
        L.UNIDADE_PRESTADORA, L.REG_PREST_DOC,  L.NM_PREST_DOC, L.REG_SOL_DOC,  L.NM_SOL_DOC, L.CD_TRANSACAO, L.TIPO, L.NR_DOC_ORIGINAL,
        L.NM_USUARIO, L.CARTEIRINHA, L.TP_INSUMO, L.CODIGO, L.DESCRICAO, L.QTD_MOV, L.VL_PAGO, L.VL_CONTAS
    FROM gp.V_ANALITICO_MOV_LOCAL L WHERE L.ANO = :ano AND L.MES IN ($arrayMesStr)
        
        AND L.CD_GRUPO_PRESTADOR in ( $placeholdersGrupo )
        AND L.IN_LIBERADO_PAGTO = 1  UNION
    SELECT I.ANO, I.MES, I.CD_UNIDADE_PRESTADORA, I.CD_UNIDADE_CARTEIRA, I.CD_POS_EQUIPE, I.GRAU_PARTICIPACAO, I.CD_PRESTADOR, I.NM_PRESTADOR,
        I.REG_PREST_EXEC, UPPER(I.NM_PREST_EXEC) NM_PREST_EXEC, I.CD_GRUPO_PRESTADOR, I.DS_GRUPO_PRESTADOR, I.UTILIZACAO,  I.CD_UNIDADE,
        I.UNIDADE_PRESTADORA, I.REG_PREST_DOC,  I.NM_PREST_DOC, I.REG_SOL_DOC,  I.NM_SOL_DOC, I.CD_TRANSACAO, I.TIPO, I.NR_DOC_ORIGINAL,
        I.NM_USUARIO, I.CARTEIRINHA, I.TP_INSUMO, I.CODIGO, I.DESCRICAO, I.QTD_MOV, I.VL_PAGO, I.VL_CONTAS
    FROM gp.V_ANALITICO_MOV_INTER I WHERE I.ANO = :ano AND I.MES IN ($arrayMesStr)
        
        AND I.CD_GRUPO_PRESTADOR IN ( $placeholdersGrupo )
        AND I.IN_LIBERADO_PAGTO = 1)
    ORDER BY 1, 2, 5, 9 ";

     $bindings = [
        ':ano' => $ano_ref,
    ];

    foreach ($meses as $i => $val) {
        $bindings[":pMes{$i}"] = (int)$val; // garante que seja número
    }

}else{

    //!CONSULTA BASE PARA PROCEDIMENTOS
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


    // Combinar as consultas
    $sql = "($sql1) UNION ($sql2)";
}
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

}

oci_free_statement($stmt);
oci_close($conn);