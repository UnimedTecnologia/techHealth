<?php
header('Content-Type: text/html; charset=utf-8');
session_start();

set_time_limit(600); // Definir o tempo máximo de execução para 600 segundos (10 minutos)

$prestador_principal = $_POST['prestador_principal'];
$ano_ref             = (int) $_POST['anoref'];
$num_ref             = (int) $_POST['numref'];
$serie_documento     = $_POST['seriedoc'];


require_once "../config/AW00DB.php";
require_once "../config/oracle.class.php";
require_once "../config/AW00MD.php";

// Dependência para gerar Excel (PhpSpreadsheet)
require '../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$db = new oracle();
$conn = $db->connect($db_host, $db_user, $db_pwd, $db_name, $pconnect);

// Verificar conexão
if (!$conn) {
    $e = oci_error();
    echo "Erro de conexão: " . htmlentities($e['message'], ENT_QUOTES);
    exit;
}

// Configurar codificação
oci_set_client_info($conn, 'UTF-8');

// Consulta SQL inicial
$sql = "SELECT P.DT_REALIZACAO, P.HR_REALIZACAO,
       CASE WHEN D.CD_UNIDADE_CARTEIRA = 540 THEN Z.NM_USUARIO ELSE OU.NM_USUARIO END NOME, 
       CT.NM_CONTRATANTE CONTRATANTE, 
       CASE WHEN D.CD_UNIDADE_CARTEIRA = 540 THEN UPPER ('LOCAL') ELSE ('INTERCAMBIO') END UNIMED,
       D.NR_DOC_ORIGINAL, D.NR_DOC_SISTEMA,  D.CD_TRANSACAO, D.NR_SERIE_DOC_ORIGINAL, 
       D.AA_GUIA_ATENDIMENTO, D.NR_GUIA_ATENDIMENTO,
       D.DT_ANOREF, D.NR_PERREF,D.CD_PRESTADOR_PRINCIPAL,
       C.NM_PRESTADOR, P.CD_PACOTE, P.CD_ESP_AMB||LPAD(P.CD_GRUPO_PROC_AMB,2,'0')||LPAD(P.CD_PROCEDIMENTO,3,'0')||P.DV_PROCEDIMENTO PROCEDIMENTO, A.DES_PROCEDIMENTO, P.QT_PROCEDIMENTOS, 
       P.VL_COBRADO, P.VL_BASE_VALOR_SISTEMA, P.VL_REAL_PAGO, P.CD_POS_EQUIPE GRAU_PART, 
       P.CHAR_4 PROF_EXEC, P.CHAR_13 REGISTRO, P.CHAR_14 NR_REGISTRO, P.CHAR_11 CBO, P.CDESPPRESTEXECUTANTE ESP_EXEC, P.CHAR_19,
     
       D.nm_prof_sol SOLICITANTE, D.char_16 REG_SOL, D.char_17 REGISTRO_SOL, D.char_18 UF_SOL
       FROM GP.DOCRECON D
            INNER JOIN GP.MOVIPROC P ON P.NR_DOC_ORIGINAL              = D.NR_DOC_ORIGINAL
                                    AND P.NR_SERIE_DOC_ORIGINAL        = D.NR_SERIE_DOC_ORIGINAL
                                    AND P.DT_ANOREF                    = D.DT_ANOREF
                                    AND P.NR_PERREF                    = D.NR_PERREF
            INNER JOIN GP.PRESERV  C ON C.CD_PRESTADOR                 = D.CD_PRESTADOR_PRINCIPAL
            LEFT JOIN GP.USUARIO   Z ON Z.CD_MODALIDADE                = D.CD_MODALIDADE
                                    AND Z.NR_TER_ADESAO                = D.NR_TER_ADESAO
                                    AND Z.CD_USUARIO                   = D.CD_USUARIO
            LEFT JOIN GP.OUT_UNI OU  ON D.CD_UNIDADE_CARTEIRA          = OU.CD_UNIDADE
                                    AND D.CD_CARTEIRA_USUARIO          = OU.CD_CARTEIRA_USUARIO
            LEFT JOIN GP.PROPOST PP  ON Z.CD_MODALIDADE                = PP.CD_MODALIDADE
                                    AND Z.NR_TER_ADESAO                = PP.NR_TER_ADESAO
            LEFT JOIN GP.CONTRAT CT  ON PP.NR_INSC_CONTRATANTE         = CT.NR_INSC_CONTRATANTE
            INNER JOIN GP.AMBPROCE    A ON A.CD_ESP_AMB                   = P.CD_ESP_AMB
                                    AND A.CD_GRUPO_PROC_AMB            = P.CD_GRUPO_PROC_AMB
                                    AND A.CD_PROCEDIMENTO              = P.CD_PROCEDIMENTO
                                    AND A.DV_PROCEDIMENTO              = P.DV_PROCEDIMENTO
       WHERE
             D.CD_PRESTADOR_PRINCIPAL in ( :prestador_principal )
             AND D.DT_ANOREF = :ano_ref  
             AND D.NR_PERREF = :num_ref AND D.CHAR_20  <> 7 "; //AND D.CD_TRANSACAO IN (3030,3040)

// Bind de parâmetros
$bindings = [
    ':prestador_principal' => $prestador_principal,
    ':ano_ref' => $ano_ref,
    ':num_ref' => $num_ref,
];

// Adicionar condições opcionais
if (!empty($_POST['seriedoc'])) {
    $serie_documento = $_POST['seriedoc'];
    $sql .= " AND D.NR_SERIE_DOC_ORIGINAL = :serie_documento  ";
    $bindings[':serie_documento'] = $serie_documento;
}
if (!empty($_POST['transacoes'])) {
    $tran = $_POST['transacoes'];
    //echo "<br><br>";
    //$transacoes = explode(',', $_POST['transacoes']); // Divide a string em um array
    //$transacoes = array_map('intval', $transacoes);  // Converte os valores para inteiros
    // Cria os placeholders para a consulta SQL
    //$placeholders = implode(',', array_fill(0, count($transacoes), '?'));

    //$sql .= " AND D.CD_TRANSACAO IN ($placeholders)";
    $sql .= " AND D.CD_TRANSACAO IN ($tran)";
}

// Preparar consulta
$stmt = oci_parse($conn, $sql);

if (!$stmt) {
    $e = oci_error($conn);
    echo "Erro ao preparar a consulta: " . htmlentities($e['message'], ENT_QUOTES);
    exit;
}

// Associar os parâmetros
foreach ($bindings as $key => $value) {
    oci_bind_by_name($stmt, $key, $bindings[$key]);
}

// Executar consulta
$result = oci_execute($stmt);

if (!$result) {
    $e = oci_error($stmt);
    echo "Erro ao executar a consulta: " . htmlentities($e['message'], ENT_QUOTES);
    exit;
}

// Inicializar array para armazenar os resultados
$data = [];

while ($row = oci_fetch_assoc($stmt)) {
    $data[] = $row; // Adiciona cada linha ao array $data
}

// Verifica se é para gerar Excel
if (isset($_POST['download']) && $_POST['download'] == 'excel') {
    if (empty($data)) {
        echo "Sem dados para gerar o relatório.";
        $_SESSION['erroRelatorio'] = "Nenhum dado encontrado.";
        header("Location: ../dashboard.php");
        exit;
    }

    // Criar planilha
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Cabeçalhos
    $headers = array_keys($data[0]);
    $colIndex = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($colIndex . '1', $header);
        $colIndex++;
    }

    // Dados
    $rowIndex = 2;
    foreach ($data as $row) {
        $colIndex = 'A';
        foreach ($row as $cell) {
            $sheet->setCellValue($colIndex . $rowIndex, $cell);
            $colIndex++;
        }
        $rowIndex++;
    }


    $nomeDoc = $ano_ref . "_" . $prestador_principal . "_" . $num_ref; //* Nome do relatorio

    //* Retorna para dashboard e envia relatorio para baixar
    $arquivoPath = '../relatorios/relatorio_' . $nomeDoc . '.xlsx';
    $writer = new Xlsx($spreadsheet);
    $writer->save($arquivoPath);

    // Salva a informação na sessão para indicar que o relatório está disponível
    $_SESSION['dadosRelatorio'] = [
        'data' => $data,                // Dados relevantes do relatório
        'nomeDoc' => $nomeDoc,          // Nome do documento
        'arquivoPath' => $arquivoPath,  // Caminho do arquivo
    ];

    // Redireciona para o dashboard
    header("Location: ../dashboard.php");
    exit;

    // APENAS Download do Excel
    /*header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="relatorio_'. $nomeDoc .'.xlsx"');
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');

    $_SESSION['dadosRelatorio'] = $data;
    header("Location: ../dashboard.php");
    exit; */
}


oci_free_statement($stmt);
oci_close($conn);
