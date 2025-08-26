<?php
header('Content-Type: text/html; charset=utf-8');
session_start();

$cpfnome = $_POST['cpfNome'];

// Remover espaços em branco no início e no fim
$cpfnome = trim($cpfnome);

// Verificar se é CPF (apenas números e 11 dígitos)
if (preg_match('/^\d{11}$/', $cpfnome)) {
    // Trata como CPF
    $select = 'U.CD_CPF = :cpf';
    $bindings = [
        ':cpf' => $cpfnome,
    ];
} else {
    // Trata como nome
    $select = 'U.NM_USUARIO LIKE UPPER(:nome)';
    $bindings = [
        ':nome' => '%' . strtoupper($cpfnome) . '%',
    ];
}



require_once "../../config/AW00DB.php";
require_once "../../config/oracle.class.php";
require_once "../../config/AW00MD.php";

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
$sql = "SELECT U.CD_MODALIDADE, U.NR_TER_ADESAO, U.CD_USUARIO, C.CD_SIT_CARTEIRA, U.NM_USUARIO, U.Nr_Proposta, u.cd_cpf,
       LPAD(U.CD_UNIMED,4,'0')||LPAD(DECODE(C.CD_CARTEIRA_INTEIRA, NULL, C.CD_CARTEIRA_ANTIGA, C.CD_CARTEIRA_INTEIRA),13,'0') CARTEIRA_COMPLETA
  FROM GP.USUARIO U INNER JOIN GP.CAR_IDE  C  ON C.CD_MODALIDADE   = U.CD_MODALIDADE 
                                                         AND C.CD_USUARIO      = U.CD_USUARIO 
                                                         AND C.NR_TER_ADESAO   = U.NR_TER_ADESAO 
 WHERE $select 
 GROUP BY U.CD_MODALIDADE, U.NR_TER_ADESAO, U.CD_USUARIO, U.NM_USUARIO, U.NR_PROPOSTA, u.cd_cpf, U.CD_UNIMED, C.CD_CARTEIRA_ANTIGA, C.CD_CARTEIRA_INTEIRA, 
          C.CD_SIT_CARTEIRA";


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

// Verificar resultados
if (empty($data)) {
    $_SESSION['erroCarteira'] = "Beneficiário não encontrado";
    header("Location: ../../dashboard.php");
    exit;
} else {
    unset($_SESSION['erroCarteira']); // Limpa sessão
    $_SESSION['dadosCarteira'] = $data;
    header("Location: ./");
    exit;
}

// Liberar recursos
oci_free_statement($stmt);
oci_close($conn);
