<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

// Verificação se os parâmetros foram passados corretamente
if (!isset($_POST['nrGuiaReg'], $_POST['anoGuiaReg']) || empty($_POST['nrGuiaReg']) || empty($_POST['anoGuiaReg'])) {
    echo json_encode([
        'error' => true,
        'message' => 'Parâmetros inválidos',
        'type' => 'danger',
        'data' => ''
    ]);
    exit;
}

$nrGuiaReg  = $_POST['nrGuiaReg'];
$anoGuiaReg = $_POST['anoGuiaReg']; 

require_once "../config/AW00DB.php";
require_once "../config/oracle.class.php";
require_once "../config/AW00MD.php";

// Define UTF-8 corretamente
putenv('NLS_LANG=AMERICAN_AMERICA.UTF8');

// Conectar ao banco
$db = new oracle();
$conn = $db->connect($db_host, $db_user, $db_pwd, $db_name, $pconnect);

if (!$conn) {
    echo json_encode([
        'error' => true,
        'message' => 'Erro de conexão com o banco de dados',
        'type' => 'danger',
        'data' => ''
    ]);
    exit;
}

// Divide a string dos números da guia e cria placeholders para o Oracle
$guias = explode(',', $nrGuiaReg);
$placeholders = [];
$bindings = [':anoGuiaReg' => $anoGuiaReg];

foreach ($guias as $index => $guia) {
    $param = ":guia{$index}";
    $placeholders[] = $param;
    $bindings[$param] = (int) trim($guia);
}

// Monta a consulta com os placeholders
$sql ="SELECT DISTINCT A.NR_GUIA_ATENDIMENTO, 
    A.AA_GUIA_ATEND_ORIGEM, 
    A.NR_GUIA_ATEND_ORIGEM, 
    A.IN_LIBERADO_GUIAS,
  CASE WHEN A.IN_LIBERADO_GUIAS = 1 THEN UPPER('Digitada')  WHEN A.IN_LIBERADO_GUIAS = 2 THEN UPPER('Autorizada')
       WHEN A.IN_LIBERADO_GUIAS = 3 THEN UPPER('Cancelada')  WHEN A.IN_LIBERADO_GUIAS = 4 THEN UPPER('Processada pelo RC')
       WHEN A.IN_LIBERADO_GUIAS = 5 THEN UPPER('Fechada')  WHEN A.IN_LIBERADO_GUIAS = 6 THEN UPPER('Orçamento')
       WHEN A.IN_LIBERADO_GUIAS = 7 THEN UPPER('Faturada')  WHEN A.IN_LIBERADO_GUIAS = 8 THEN UPPER('Negada')
       WHEN A.IN_LIBERADO_GUIAS = 9 THEN UPPER('Pendente Auditoria')  WHEN A.IN_LIBERADO_GUIAS = 10 THEN UPPER('Pendente Liberação')
       WHEN A.IN_LIBERADO_GUIAS = 11 THEN UPPER('Pendente Laudo Médico')  WHEN A.IN_LIBERADO_GUIAS = 12 THEN UPPER('Pendente Justificativa Médica')
       WHEN A.IN_LIBERADO_GUIAS = 13 THEN UPPER('Pendente de Perícia')  WHEN A.IN_LIBERADO_GUIAS = 19 THEN UPPER('Em Auditoria')
       WHEN A.IN_LIBERADO_GUIAS = 20 THEN UPPER('Em Atendimento')  WHEN A.IN_LIBERADO_GUIAS = 23 THEN UPPER('Em Perícia')
       WHEN A.IN_LIBERADO_GUIAS = 30 THEN UPPER('Reembolso') ELSE NULL END STATUS_GUIA, 
       (A.DT_EMISSAO_GUIA + T.QT_DIAS_VALIDADE) DT_VENC,
  CASE WHEN A.IN_LIBERADO_GUIAS <> 3 THEN 'UPDATE GP.GUIAUTOR A SET A.U##IN_LIBERADO_GUIAS = 10, A.IN_LIBERADO_GUIAS = 10 WHERE A.PROGRESS_RECID ='||A.PROGRESS_RECID||';' ELSE 'OK' END
    UPD_GUIA,
  CASE WHEN H.IN_LIB_GUIAS_ALT  <> 3 THEN 'UPDATE GP.GUIA_HIS H SET H.U##IN_LIB_GUIAS_ALT  = 10, H.IN_LIB_GUIAS_ALT  = 10 WHERE H.PROGRESS_RECID ='||H.PROGRESS_RECID||';' ELSE 'OK' END
    UPD_HIST,
    A.PROGRESS_RECID as RecIDGuia,
    H.PROGRESS_RECID as RecIDHistorico,
    CASE WHEN A.CD_UNIDADE_CARTEIRA = 540 THEN U.NM_USUARIO ELSE OU.NM_USUARIO END NOME,
    LPAD(A.CD_UNIDADE_CARTEIRA, 4, 0) || LPAD(A.CD_CARTEIRA_USUARIO, 13, 0) AS CARTEIRINHA
  FROM GP.GUIAUTOR A INNER JOIN GP.GUIA_HIS H ON H.CD_UNIDADE           = A.CD_UNIDADE
                                             AND H.AA_GUIA_ATENDIMENTO  = A.AA_GUIA_ATENDIMENTO
                                             AND H.NR_GUIA_ATENDIMENTO  = A.NR_GUIA_ATENDIMENTO
                                             AND H.IN_LIB_GUIAS_ALT      = A.IN_LIBERADO_GUIAS
                    INNER JOIN GP.TIP_GUIA T ON A.CD_TIPO_GUIA          = T.CD_TIPO_GUIA
                    LEFT JOIN GP.USUARIO U ON U.CD_MODALIDADE = A.CD_MODALIDADE AND U.NR_TER_ADESAO = A.NR_TER_ADESAO
                                             AND U.CD_USUARIO = A.CD_USUARIO
                    LEFT JOIN GP.OUT_UNI OU ON A.CD_UNIDADE_CARTEIRA = OU.CD_UNIDADE
                                             AND A.CD_CARTEIRA_USUARIO = OU.CD_CARTEIRA_USUARIO 
WHERE A.AA_GUIA_ATENDIMENTO = :anoGuiaReg 
AND A.NR_GUIA_ATENDIMENTO IN (" . implode(',', $placeholders) . ")";

//todo
// $sql = "SELECT 
//     G.NR_GUIA_ATENDIMENTO, 
//     G.AA_GUIA_ATEND_ORIGEM, 
//     G.NR_GUIA_ATEND_ORIGEM, 
//     G.IN_LIBERADO_GUIAS,
//     CASE 
//         WHEN G.IN_LIBERADO_GUIAS =  1  THEN 'DIGITADA'
//         WHEN G.IN_LIBERADO_GUIAS =  2  THEN 'AUTORIZADA'
//         WHEN G.IN_LIBERADO_GUIAS =  3  THEN 'CANCELADA'
//         WHEN G.IN_LIBERADO_GUIAS =  4  THEN 'PROCESSADA PELO RC'
//         WHEN G.IN_LIBERADO_GUIAS =  5  THEN 'FECHADA'
//         WHEN G.IN_LIBERADO_GUIAS =  6  THEN 'ORÇAMENTO'
//         WHEN G.IN_LIBERADO_GUIAS =  7  THEN 'FATURADA'
//         WHEN G.IN_LIBERADO_GUIAS =  8  THEN 'NEGADA'
//         WHEN G.IN_LIBERADO_GUIAS =  9  THEN 'PENDENTE AUDITORIA'
//         WHEN G.IN_LIBERADO_GUIAS = 10  THEN 'PENDENTE LIBERAÇÃO'
//         WHEN G.IN_LIBERADO_GUIAS = 11  THEN 'PENDENTE LAUDO MÉDICO'
//         WHEN G.IN_LIBERADO_GUIAS = 12  THEN 'PENDENTE DE JUSTIFICATIVA MÉDICA'
//         WHEN G.IN_LIBERADO_GUIAS = 13  THEN 'PENDENTE DE PERÍCIA'
//         WHEN G.IN_LIBERADO_GUIAS = 19  THEN 'EM AUDITORIA'
//         WHEN G.IN_LIBERADO_GUIAS = 20  THEN 'EM ATENDIMENTO'
//         WHEN G.IN_LIBERADO_GUIAS = 23  THEN 'EM PERÍCIA'
//         WHEN G.IN_LIBERADO_GUIAS = 30  THEN 'REEMBOLSO'
//         ELSE 'STATUS DESCONHECIDO'
//     END AS STATUS_GUIA
// FROM GP.GUIAUTOR G 
// WHERE G.AA_GUIA_ATENDIMENTO = :anoGuiaReg 
// AND G.NR_GUIA_ATENDIMENTO IN (" . implode(',', $placeholders) . ")";

$stmt = oci_parse($conn, $sql);

if (!$stmt) {
    echo json_encode([
        'error' => true,
        'message' => 'Erro ao preparar a consulta',
        'type' => 'danger',
        'data' => ''
    ]);
    exit;
}

// Associar os parâmetros corretamente
foreach ($bindings as $key => $value) {
    oci_bind_by_name($stmt, $key, $bindings[$key]);
}

// Executar consulta
$result = oci_execute($stmt);

if (!$result) {
    $e = oci_error($stmt);
    echo json_encode([
        'error' => true,
        'message' => 'Erro ao executar a consulta: ' . htmlentities($e['message'], ENT_QUOTES),
        'type' => 'danger',
        'data' => ''
    ]);
    exit;
}

// Coleta os dados
$data = [];
while ($row = oci_fetch_assoc($stmt)) {
    $data[] = $row;
}

// Resposta JSON
if (empty($data)) {
    echo json_encode([
        'error' => true,
        'message' => 'Nenhuma guia encontrada',
        'type' => 'warning',
        'data' => ''
    ]);
} else {
    echo json_encode([
        'error' => false,
        'message' => 'Consulta realizada com sucesso',
        'type' => 'success',
        'data' => $data
    ]);
}

// Liberar recursos
oci_free_statement($stmt);
oci_close($conn);
