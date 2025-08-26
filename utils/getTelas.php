<?php

// session_start();

// require_once "../config/AW00DB.php";
// require_once "../config/oracle.class.php";
// require_once "../config/AW00MD.php";
// header('Content-Type: application/json; charset=utf-8');

// $db = new oracle();
// $conn = $db->connect($db_host, $db_user, $db_pwd, $db_name, $pconnect);

// // Verificar conexão
// if (!$conn) {
//     $e = oci_error();
//     echo "Erro de conexão: " . htmlentities($e['message'], ENT_QUOTES);
//     exit;
// }

// // Configurar codificação
// oci_set_client_info($conn, 'UTF-8');

// $idusuario = $_SESSION['idusuario'];

// // Consulta SQL inicial
// $sql = "select t.*, g.descricaogrupo from gp.th_telas t 
//     inner join gp.th_grupo g on g.id = t.idgrupo 
//     inner join gp.th_administrador a on a.idtelas = t.id 
//     where a.idusuario = $idusuario "; 

// // Preparar consulta
// $stmt = oci_parse($conn, $sql);

// if (!$stmt) {
//     $e = oci_error($conn);
//     echo "Erro ao preparar a consulta: " . htmlentities($e['message'], ENT_QUOTES);
//     exit;
// }

// // Executar consulta
// $result = oci_execute($stmt);

// if (!$result) {
//     $e = oci_error($stmt);
//     echo "Erro ao executar a consulta: " . htmlentities($e['message'], ENT_QUOTES);
//     exit;
// }

// // Inicializar array para armazenar os resultados
// $data = [];
// while ($row = oci_fetch_assoc($stmt)) {
//     $data[] = $row; // Adiciona cada linha ao array $data
// }

// // Liberar recursos
// oci_free_statement($stmt);
// oci_close($conn);

// echo json_encode($data);

header('Content-Type: application/json; charset=utf-8');
session_start();

require_once "../config/AW00DB.php";
require_once "../config/oracle.class.php";
require_once "../config/AW00MD.php";

try {
    // Obter o ID do usuário da sessão
    if (!isset($_SESSION['idusuario'])) {
        throw new Exception("Usuário não autenticado.");
    }

    $idusuario = $_SESSION['idusuario'];

    // Consulta para obter as permissões
    $sql = "select t.*, g.descricaogrupo from gp.th_telas t 
            inner join gp.th_grupo g on g.id = t.idgrupo 
            inner join gp.th_administrador a on a.idtelas = t.id 
            where a.idusuario = :idusuario";

    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ':idusuario', $idusuario);

    // Executar a consulta
    if (!oci_execute($stmt)) {
        $error = oci_error($stmt);
        throw new Exception("Erro ao executar a consulta: " . $error['message']);
    }

    // Capturar todos os registros
    $permissoesTelas = [];
    while ($rowPermissaoTela = oci_fetch_assoc($stmt)) {
        $permissoesTelas[] = $rowPermissaoTela;
    }

    // Resposta de sucesso
    $response = [
        'error' => false,
        'data' => $permissoesTelas,
        'type' => 'success',
    ];

} catch (Exception $e) {
    // Em caso de erro
    $response = [
        'error' => true,
        'message' => $e->getMessage(),
        'type' => 'danger',
    ];
} finally {
    // Liberar recursos
    if (isset($stmt)) {
        oci_free_statement($stmt);
    }
    if (isset($conn)) {
        oci_close($conn);
    }

    // Retornar resposta em JSON
    echo json_encode($response);
}
