<?php
header('Content-Type: text/html; charset=utf-8');
session_start();

set_time_limit(3600); // Definir o tempo máximo de execução para 3600 segundos (1h)

//* faz o select com prestadores principais ou grupo prestadores

$ano_ref = (int) $_POST['anoref'];
$num_ref = (int) $_POST['numref'];
$serie_documento = $_POST['seriedoc'];

require_once "../config/AW00DB.php";
require_once "../config/oracle.class.php";
require_once "../config/AW00MD.php";

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
       D.DT_ANOREF, D.NR_PERREF,D.CD_PRESTADOR_PRINCIPAL, P.CD_PRESTADOR AS PREST_EXECUTANTE,
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
              D.DT_ANOREF = :ano_ref  
             AND D.NR_PERREF = :num_ref AND D.CHAR_20  <> 7 ";

// Bind de parâmetros
$bindings = [
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
    $sql .= " AND D.CD_TRANSACAO IN ($tran)";
}
if (!empty($_POST['prestador_principal'])) {
    $prestador_principal = $_POST['prestador_principal'];
    $sql .= " AND D.CD_PRESTADOR_PRINCIPAL in ( $prestador_principal ) ";
}
if (!empty($_POST['grupo_prestadores'])) {
    $grupo_prestadores = $_POST['grupo_prestadores'];
    $sql .= " AND C.CD_GRUPO_PRESTADOR in ( $grupo_prestadores ) ";
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
// $result = oci_execute($stmt);
$result = oci_execute($stmt, OCI_NO_AUTO_COMMIT);


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

// Verifica se é para gerar CSV
if (isset($_POST['download']) && $_POST['download'] == 'excel') {
    if (empty($data)) {
        echo "Sem dados para gerar o relatório.";
        $_SESSION['erroRelatorio'] = "Nenhum dado encontrado.";
        header("Location: ../dashboard.php");
        exit;
    }

    // Gerar CSV
    $nomeDoc = $ano_ref . "_" . $prestador_principal . "_" . $num_ref;
    $arquivoPath = '../relatorios/relatorio_' . $nomeDoc . '.csv'; // Alterar para .csv

    $file = fopen($arquivoPath, 'w');
    if ($file) {
        // Adicionar BOM para UTF-8 
        fwrite($file, "\xEF\xBB\xBF");

        // Cabeçalhos com espaços extras
        $headers = array_keys($data[0]);
        $headersComEspacos = [];
        foreach ($headers as $header) {
            // Adiciona espaços após o título
            $headersComEspacos[] = $header . '       ';
        }
        fputcsv($file, $headersComEspacos, ';'); // Usa ponto e vírgula como delimitador

        // Dados
        foreach ($data as $row) {
            fputcsv($file, $row, ';');
        }

        fclose($file);
    } else {
        echo "Erro ao criar o arquivo.";
        exit;
    }

    // Salva a informação na sessão
    $_SESSION['dadosRelatorio'] = [
        'data' => $data,
        'nomeDoc' => $nomeDoc,
        'arquivoPath' => $arquivoPath,
    ];


    // Redireciona para o dashboard
    header("Location: ../dashboard.php");
    exit;
}

oci_free_statement($stmt);
oci_close($conn);

?>