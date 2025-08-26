<?php
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
    $sql = "select t.*, g.descricaogrupo from gp.th_permissoes p 
            inner join gp.th_telas t on t.id = p.idtelas 
            inner join gp.th_grupo g on g.id = t.idgrupo 
            where p.idusuario = :idusuario";

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
