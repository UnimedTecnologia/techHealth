<?php 
header('Content-Type: text/html; charset=utf-8');
session_start();
$newPrestador  = $_POST['newPrestador'] ?? null;
$oldPrestadorPrincipal = $_POST['oldPrestadorPrincipal'] ?? null;
$nrDocOriginal = $_POST['nrDocOriginal'] ?? null;
$transacao = $_POST['transacao'] ?? null;
$nrSerie = $_POST['nrSerie'] ?? null;
$nrPeriodo = $_POST['nrPeriodo'] ?? null;
$anoRef = $_POST['anoRef'] ?? null;

$numero_documentos = array_map('trim', explode(',', $nrDocOriginal)); // ['123', '456', '789']

require_once "../../config/AW00DB.php";
require_once "../../config/oracle.class.php";
require_once "../../config/AW00MD.php";


$db = new oracle();
$conn = $db->connect($db_host, $db_user, $db_pwd, $db_name, $pconnect);

//* Verificar conexão
if (!$conn) {
    $e = oci_error();
    echo json_encode(['error' => true, 'message' => "Erro de conexão: " . htmlentities($e['message'], ENT_QUOTES)]);
    exit;
}

oci_set_client_info($conn, 'UTF-8'); //* Configuração de codificação

//* Função para executar a consulta
function executarUpdate($conn, $sql, $bindings) {
    $stmt = oci_parse($conn, $sql);
    if (!$stmt) {
        $e = oci_error($conn);
        echo "Erro ao preparar consulta: " . htmlentities($e['message'], ENT_QUOTES);
        // return false;
        return ['success' => false, 'message' => 'Erro ao preparar a consulta: ' . htmlentities($e['message'], ENT_QUOTES)];
    }

    foreach ($bindings as $key => &$value) {
        oci_bind_by_name($stmt, $key, $value);
    }

    if (oci_execute($stmt, OCI_COMMIT_ON_SUCCESS)) {
        // return true;
        return ['success' => true];
    } else {
        $e = oci_error($stmt);
        echo "Erro na execução do update: " . htmlentities($e['message'], ENT_QUOTES);
        // return false;
        return ['success' => false, 'message' => 'Erro na execução do update: ' . htmlentities($e['message'], ENT_QUOTES)];
    }
}


$bindings = [
    ':newPrestador' => (int)$newPrestador,
    ':oldPrestadorPrincipal' => (int)$oldPrestadorPrincipal,
    // ':nrDocOriginal' => $nrDocOriginal,
    ':transacao' => (int)$transacao,
    ':nrSerie' => $nrSerie,
    // ':dataAtualizacao' => date("Y-m-d"), //* Formato para SQL
    // ':userId' => (int)$_SESSION['user_id'], //* User ID da sessão
];
$placeholders = [];
foreach ($numero_documentos as $key => $doc) {
    $placeholder = ":numero_documento_" . $key;
    $placeholders[] = $placeholder;
    $bindings[$placeholder] = $doc;
}
$bindingsHistorico = [
    ':newPrestador' => (int)$newPrestador,
    ':dataAtualizacao' => date("d/m/Y"),
    ':userId' => $_SESSION['idusuario'],
    ':oldPrestadorPrincipal' => (int)$oldPrestadorPrincipal,
    // ':nrDocOriginal' => $nrDocOriginal,
    ':transacao' => (int)$transacao,
    ':nrSerie' => $nrSerie,
];
$placeholdersH = [];
foreach ($numero_documentos as $key => $docH) {
    $placeholderH = ":numero_documento_" . $key;
    $placeholdersH[] = $placeholderH;
    $bindingsHistorico[$placeholderH] = $docH;
}

//* Adicionar condições opcionais
if ($nrPeriodo) {
    $bindings[':nrPeriodo'] = $nrPeriodo;
    $bindingsHistorico[':nrPeriodo'] = $nrPeriodo;
}
if ($anoRef) {
    $bindings[':anoRef'] = $anoRef;
    $bindingsHistorico[':anoRef'] = $anoRef;
}


//* Atualizações
$atualizacoes = [];
//* Atualização principal
if (!empty($_SESSION['dadosPrestador'])) {
    $sql = "UPDATE gp.DOCRECON D SET D.CD_PRESTADOR_PRINCIPAL = :newPrestador 
        WHERE D.CD_PRESTADOR_PRINCIPAL = :oldPrestadorPrincipal
        AND D.NR_DOC_ORIGINAL IN (" . implode(',', $placeholders) . ")
        AND D.CD_TRANSACAO = :transacao
        AND D.NR_SERIE_DOC_ORIGINAL = :nrSerie ";
    if ($nrPeriodo) $sql .= " AND D.NR_PERREF = :nrPeriodo ";
    if ($anoRef) $sql .= " AND D.DT_ANOREF = :anoRef ";
    $atualizacoes[] = executarUpdate($conn, $sql, $bindings);
}

//* Atualização de insumos e histórico de insumos
if (!empty($_SESSION['dadosInsumos'])) {
    $sql = "UPDATE gp.MOV_INSU D SET D.CD_PRESTADOR = :newPrestador, D.CD_PRESTADOR_PAGAMENTO = :newPrestador 
        WHERE D.CD_PRESTADOR = :oldPrestadorPrincipal
        AND D.NR_DOC_ORIGINAL IN (" . implode(',', $placeholders) . ")
        AND D.CD_TRANSACAO = :transacao
        AND D.NR_SERIE_DOC_ORIGINAL = :nrSerie ";
    if ($nrPeriodo) $sql .= " AND D.NR_PERREF = :nrPeriodo ";
    if ($anoRef) $sql .= " AND D.DT_ANOREF = :anoRef ";
    $atualizacoes[] = executarUpdate($conn, $sql, $bindings);
    

    //* Histórico de insumos
    $sqlHist = "UPDATE gp.HISTOR_MOVIMEN_INSUMO 
        SET CD_PRESTADOR = :newPrestador, CD_PRESTADOR_PAGAMENTO = :newPrestador, DT_ATUALIZACAO = :dataAtualizacao, CD_USERID = :userId
        WHERE CD_PRESTADOR = :oldPrestadorPrincipal
        AND NR_DOC_ORIGINAL IN (" . implode(',', $placeholdersH) . ")
        AND CD_TRANSACAO = :transacao
        AND NR_SERIE_DOC_ORIGINAL = :nrSerie ";
    if ($nrPeriodo) $sqlHist .= " AND NR_PERREF = :nrPeriodo ";
    if ($anoRef) $sqlHist .= " AND DT_ANOREF = :anoRef ";
    $atualizacoes[] = executarUpdate($conn, $sqlHist, $bindingsHistorico);
}


//* Atualização de procedimentos e histórico de procedimentos
if (!empty($_SESSION['dadosProcedimento'])) {
    $sql = "UPDATE gp.MOVIPROC D SET D.CD_PRESTADOR = :newPrestador, D.CD_PRESTADOR_PAGAMENTO = :newPrestador 
        WHERE D.CD_PRESTADOR = :oldPrestadorPrincipal
        AND D.NR_DOC_ORIGINAL IN (" . implode(',', $placeholders) . ")
        AND D.CD_TRANSACAO = :transacao
        AND D.NR_SERIE_DOC_ORIGINAL = :nrSerie ";
    if ($nrPeriodo) $sql .= " AND D.NR_PERREF = :nrPeriodo ";
    if ($anoRef) $sql .= " AND D.DT_ANOREF = :anoRef ";
    $atualizacoes[] = executarUpdate($conn, $sql, $bindings);

    //* Histórico de procedimentos
    $sqlHist = "UPDATE gp.HISTOR_MOVIMEN_PROCED 
        SET CD_PRESTADOR = :newPrestador, CD_PRESTADOR_PAGAMENTO = :newPrestador, DT_ATUALIZACAO = :dataAtualizacao, CD_USERID = :userId
        WHERE CD_PRESTADOR = :oldPrestadorPrincipal
        AND NR_DOC_ORIGINAL IN (" . implode(',', $placeholdersH) . ")
        AND CD_TRANSACAO = :transacao
        AND NR_SERIE_DOC_ORIGINAL = :nrSerie ";
    if ($nrPeriodo) $sqlHist .= " AND NR_PERREF = :nrPeriodo ";
    if ($anoRef) $sqlHist .= " AND DT_ANOREF = :anoRef ";
    $atualizacoes[] = executarUpdate($conn, $sqlHist, $bindingsHistorico);
}


$temErro = false;
$detalhesErros = [];

// Verifica cada atualização manualmente
foreach ($atualizacoes as $index => $res) {
    if (!isset($res['success']) || !$res['success']) {
        $temErro = true;
        $detalhesErros[] = [
            'index' => $index,
            'detalhes' => $res,
        ];
    }
}

if (!$temErro) {
    // Realiza commit e responde com sucesso
    oci_commit($conn);
    echo json_encode([
        'error' => false,
        'message' => 'Todas as atualizações foram realizadas com sucesso.',
        'type' => 'success',
    ]);

    // Sucesso - Carrega os novos valores
    $_POST['numDoc']   = $nrDocOriginal;
    $_POST['codPrest'] = (int)$newPrestador;
    $_POST['codTrans'] = (int)$transacao;
    $_POST['serieDoc'] = $nrSerie;
    $_POST['update_prestador'] = true;

    include 'get_prestador_insumo_proced.php';
} else {
    // Realiza rollback e responde com erro
    oci_rollback($conn);
    echo json_encode([
        'error' => true,
        'message' => 'Erro em algumas atualizações.',
        'details' => $detalhesErros,
        'type' => 'danger',
    ]);
}


//* Checar resultados
// $erros = array_filter($atualizacoes, fn($res) => !$res['success']);
// if (empty($erros)) {
//     oci_commit($conn);
//     echo json_encode(['error' => false, 'message' => 'Todas as atualizações foram realizadas com sucesso.', 'type' => 'success']);
    
//     //!SUCESSO AO FAZER TODOS OS UPDATES - GET NOVOS VALORES
//     $_POST['numDoc']   =  $nrDocOriginal;
//     $_POST['codPrest'] = (int) $newPrestador;
//     $_POST['codTrans'] = (int) $transacao;
//     $_POST['serieDoc'] = $nrSerie;
//     $_POST['update_prestador'] = true;
//     //* arquivo que irá usar os parâmetros do $_POST 
//     include 'get_prestador_insumo_proced.php';

// } else {
//     oci_rollback($conn);
//     echo json_encode(['error' => true, 'message' => 'Erro em algumas atualizações.', 'details' => $erros, 'type' => 'danger']);
// }


?>