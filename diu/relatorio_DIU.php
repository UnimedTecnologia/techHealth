<?php
header('Content-Type: text/html; charset=utf-8');
session_start();

// Recebe os dados do formulário
$mesRelIni = date("d/m/Y", strtotime($_POST['mesRelIni']));
$mesRelFim = date("d/m/Y", strtotime($_POST['mesRelFim']));
$procedimentos = $_POST['procedimentos'];
$insumos = $_POST['insumos'];

if (empty($procedimentos) && empty($insumos)) {
    $_SESSION['erroRelatorioDiu'] = "Nenhum dado encontrado.";
    header("Location: ../dashboard.php");
    exit;
}

// Converte os procedimentos e insumos em arrays
$procedimentosArray = explode(',', $procedimentos);
$insumosArray = explode(',', $insumos);

// Remove espaços em branco e formata para uso no SQL
$procedimentosFormatados = implode(',', array_map('trim', $procedimentosArray));
$insumosFormatados = implode(',', array_map('trim', $insumosArray));

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
$sql = "SELECT A.DT_EMISSAO_GUIA, A.AA_GUIA_ATENDIMENTO ANO, A.NR_GUIA_ATENDIMENTO NR_GUIA, A.CD_UNIDADE_CARTEIRA UNIMED, TO_CHAR(A.CD_CARTEIRA_USUARIO) CARTEIRA, 
  CASE WHEN A.CD_UNIDADE_CARTEIRA = '540' THEN U.NM_USUARIO              WHEN A.CD_UNIDADE_CARTEIRA <> '540' THEN  O.NM_USUARIO END NM_BENEFICIARIO,
       G.CDPROCEDIMENTOCOMPLETO PROCEDIMENTO, G.DES_PROCEDIMENTO, A.NM_PROF_SOL SOLICITANTE, A.CHAR_17 CRM_SOLICITANTE,
  CASE WHEN A.IN_LIBERADO_GUIAS =  1 THEN UPPER('Digitada')              WHEN A.IN_LIBERADO_GUIAS =  2 THEN UPPER('Autorizada')
       WHEN A.IN_LIBERADO_GUIAS =  3 THEN UPPER('Cancelada')             WHEN A.IN_LIBERADO_GUIAS =  4 THEN UPPER('Processada pelo RC')
       WHEN A.IN_LIBERADO_GUIAS =  5 THEN UPPER('Fechada')               WHEN A.IN_LIBERADO_GUIAS =  6 THEN UPPER('Orçamento')
       WHEN A.IN_LIBERADO_GUIAS =  7 THEN UPPER('Faturada')              WHEN A.IN_LIBERADO_GUIAS =  8 THEN UPPER('Negada')
       WHEN A.IN_LIBERADO_GUIAS =  9 THEN UPPER('Pendente Auditoria')    WHEN A.IN_LIBERADO_GUIAS = 10 THEN UPPER('Pendente Liberação')
       WHEN A.IN_LIBERADO_GUIAS = 11 THEN UPPER('Pendente Laudo Médico') WHEN A.IN_LIBERADO_GUIAS = 12 THEN UPPER('Pendente de Justificativa Médica')
       WHEN A.IN_LIBERADO_GUIAS = 13 THEN UPPER('Pendente de Perícia')   WHEN A.IN_LIBERADO_GUIAS = 19 THEN UPPER('Em Auditoria')
       WHEN A.IN_LIBERADO_GUIAS = 20 THEN UPPER('Em Atendimento')        WHEN A.IN_LIBERADO_GUIAS = 23 THEN UPPER('Em Perícia')
       WHEN A.IN_LIBERADO_GUIAS = 30 THEN UPPER('Reembolso') ELSE NULL END STATUS_GUIA, A.DATE_21 AS VALIDADE_GUIA
  FROM GP.GUIAUTOR A
       INNER JOIN GP.PROCGUIA P ON P.AA_GUIA_ATENDIMENTO  = A.AA_GUIA_ATENDIMENTO
                            AND P.NR_GUIA_ATENDIMENTO  = A.NR_GUIA_ATENDIMENTO
       INNER JOIN GP.AMBPROCE G ON G.CD_ESP_AMB           = P.CD_ESP_AMB
                            AND G.CD_GRUPO_PROC_AMB    = P.CD_GRUPO_PROC_AMB
                            AND G.CD_PROCEDIMENTO      = P.CD_PROCEDIMENTO
                            AND G.DV_PROCEDIMENTO      = P.DV_PROCEDIMENTO                    
       LEFT JOIN GP.USUARIO  U  ON U.CD_UNIMED            = A.CD_UNIDADE_CARTEIRA
                            AND U.CD_MODALIDADE        = A.CD_MODALIDADE
                            AND U.NR_TER_ADESAO        = A.NR_TER_ADESAO
                            AND U.CD_USUARIO           = A.CD_USUARIO
       LEFT JOIN GP.OUT_UNI  O  ON O.CD_UNIDADE           = A.CD_UNIDADE_CARTEIRA
                            AND O.CD_CARTEIRA_USUARIO  = A.CD_CARTEIRA_USUARIO
       WHERE A.DT_EMISSAO_GUIA BETWEEN TO_DATE(:mesRelIni, 'DD/MM/YYYY') AND TO_DATE(:mesRelFim, 'DD/MM/YYYY')
         AND G.CDPROCEDIMENTOCOMPLETO IN ($procedimentosFormatados)
         AND (A.IN_LIBERADO_GUIAS >=2 AND A.IN_LIBERADO_GUIAS NOT IN (3,8))

  UNION

  SELECT A.DT_EMISSAO_GUIA, A.AA_GUIA_ATENDIMENTO ANO, A.NR_GUIA_ATENDIMENTO NR_GUIA, A.CD_UNIDADE_CARTEIRA UNIMED, TO_CHAR(A.CD_CARTEIRA_USUARIO) CARTEIRA, 
  CASE WHEN A.CD_UNIDADE_CARTEIRA = '540' THEN U.NM_USUARIO              WHEN A.CD_UNIDADE_CARTEIRA <> '540' THEN  O.NM_USUARIO END NM_BENEFICIARIO,
       G.CD_INSUMO INSUMO, G.DS_INSUMO,A.NM_PROF_SOL SOLICITANTE, A.CHAR_17 CRM_SOLICITANTE,
  CASE WHEN A.IN_LIBERADO_GUIAS =  1 THEN UPPER('Digitada')              WHEN A.IN_LIBERADO_GUIAS =  2 THEN UPPER('Autorizada')
       WHEN A.IN_LIBERADO_GUIAS =  3 THEN UPPER('Cancelada')             WHEN A.IN_LIBERADO_GUIAS =  4 THEN UPPER('Processada pelo RC')
       WHEN A.IN_LIBERADO_GUIAS =  5 THEN UPPER('Fechada')               WHEN A.IN_LIBERADO_GUIAS =  6 THEN UPPER('Orçamento')
       WHEN A.IN_LIBERADO_GUIAS =  7 THEN UPPER('Faturada')              WHEN A.IN_LIBERADO_GUIAS =  8 THEN UPPER('Negada')
       WHEN A.IN_LIBERADO_GUIAS =  9 THEN UPPER('Pendente Auditoria')    WHEN A.IN_LIBERADO_GUIAS = 10 THEN UPPER('Pendente Liberação')
       WHEN A.IN_LIBERADO_GUIAS = 11 THEN UPPER('Pendente Laudo Médico') WHEN A.IN_LIBERADO_GUIAS = 12 THEN UPPER('Pendente de Justificativa Médica')
       WHEN A.IN_LIBERADO_GUIAS = 13 THEN UPPER('Pendente de Perícia')   WHEN A.IN_LIBERADO_GUIAS = 19 THEN UPPER('Em Auditoria')
       WHEN A.IN_LIBERADO_GUIAS = 20 THEN UPPER('Em Atendimento')        WHEN A.IN_LIBERADO_GUIAS = 23 THEN UPPER('Em Perícia')
       WHEN A.IN_LIBERADO_GUIAS = 30 THEN UPPER('Reembolso') ELSE NULL END STATUS_GUIA, A.DATE_21 AS VALIDADE_GUIA
  FROM GP.GUIAUTOR A
       INNER JOIN GP.INSUGUIA I ON I.AA_GUIA_ATENDIMENTO  = A.AA_GUIA_ATENDIMENTO
                            AND I.NR_GUIA_ATENDIMENTO  = A.NR_GUIA_ATENDIMENTO
       INNER JOIN GP.INSUMOS  G ON G.CD_INSUMO            = I.CD_INSUMO
       LEFT JOIN GP.USUARIO  U ON U.CD_UNIMED            = A.CD_UNIDADE_CARTEIRA
                            AND U.CD_MODALIDADE        = A.CD_MODALIDADE
                            AND U.NR_TER_ADESAO        = A.NR_TER_ADESAO
                            AND U.CD_USUARIO           = A.CD_USUARIO
       LEFT JOIN GP.OUT_UNI  O ON O.CD_UNIDADE           = A.CD_UNIDADE_CARTEIRA
                            AND O.CD_CARTEIRA_USUARIO  = A.CD_CARTEIRA_USUARIO
       WHERE A.DT_EMISSAO_GUIA BETWEEN TO_DATE(:mesRelIni, 'DD/MM/YYYY') AND TO_DATE(:mesRelFim, 'DD/MM/YYYY')
         AND G.CD_INSUMO IN ($insumosFormatados)
         AND (A.IN_LIBERADO_GUIAS >=2 AND A.IN_LIBERADO_GUIAS NOT IN (3,8))";

// Bind de parâmetros
$bindings = [
    ':mesRelIni' => $mesRelIni,
    ':mesRelFim' => $mesRelFim,
];

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


//! GERAR ARQUIVO XML
// Verifica se é para gerar CSV
if (isset($_POST['download']) && $_POST['download'] == 'excel') {
    if (empty($data)) {
        echo "Sem dados para gerar o relatório.";
        $_SESSION['erroRelatorioDiu'] = "Nenhum dado encontrado.";
        header("Location: ../dashboard.php");
        exit;
    }

    // Gerar CSV
    $nomeDoc = 'relatorio_diu_' . date('Ymd_His') ;
    $arquivoPath = 'temp/' . $nomeDoc . '.csv' ; // Certifique-se de que a pasta "temp" existe e tem permissão de escrita

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
            // Adicionar um TAB (\t) antes do número para evitar a conversão para notação científica
            if (isset($row['CARTEIRA'])) {
                $row['CARTEIRA'] = "\t" . $row['CARTEIRA'];
            }
            fputcsv($file, $row, ';');
        }

        fclose($file);
    } else {
        echo "Erro ao criar o arquivo.";
        exit;
    }

    // Salva a informação na sessão
    $_SESSION['RelatorioDiu'] = [
        'data' => $data,
        'nomeDoc' => $nomeDoc,
        'arquivoPath' => $arquivoPath,
    ];

    // Redireciona para o dashboard
    header("Location: ../dashboard.php");
    exit;
}


// Liberar recursos
oci_free_statement($stmt);
oci_close($conn);

?>