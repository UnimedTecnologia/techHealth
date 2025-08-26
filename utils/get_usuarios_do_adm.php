<?php
header('Content-Type: application/json; charset=utf-8');
// putenv('NLS_LANG=AMERICAN_AMERICA.AL32UTF8'); //* Formatação UTF-8
session_start();
// Incluir arquivos de configuração e classes
require_once "../config/AW00DB.php";
require_once "../config/oracle.class.php";
require_once "../config/AW00MD.php";

// Obter o ID do usuário da sessão
if (!isset($_SESSION['idusuario'])) {
    throw new Exception("Usuário não autenticado.");
}

$idusuario = $_SESSION['idusuario'];

try {
    // Instanciar a classe oracle para conexão com o banco
    $db = new oracle();
    $conn = $db->connect($db_host, $db_user, $db_pwd, $db_name, $pconnect);

    oci_set_client_identifier($conn, 'UTF-8'); 

    // Primeiro SELECT
    $sql1 = "SELECT id, nome, usuario, adm FROM gp.th_usuario WHERE idadm = :idusuario";

    // Preparar a consulta
    $stmt1 = oci_parse($conn, $sql1);
    oci_bind_by_name($stmt1, ':idusuario', $idusuario);

    // Executar a consulta
    $result1 = oci_execute($stmt1);

    // Inicializar array para armazenar os resultados
    $data = [];

    if ($result1) {
        // Recuperar os resultados do primeiro SELECT
        $ids = [];
        while ($row = oci_fetch_assoc($stmt1)) {
            $data[] = $row; // Adiciona cada linha ao array $data
            $ids[] = $row['ID']; // Coleta os IDs para o segundo SELECT
        }

        if (!empty($ids)) {
            // Construir a cláusula IN para o segundo SELECT
            // $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $placeholders = [];
            foreach ($ids as $index => $id) {
                $placeholders[] = ":id" . $index;
            }
            $placeholders = implode(',', $placeholders); // Ex.: :id0, :id1, :id2
            
            $sql2 = "select p.idusuario,t.* from gp.th_permissoes p
                     left join gp.th_telas t on t.id = p.idtelas WHERE p.idusuario IN ($placeholders)";

            // Preparar a consulta
            $stmt2 = oci_parse($conn, $sql2);

            // Bind dos IDs dinamicamente
            foreach ($ids as $index => $id) {
                $paramName = ":id" . $index;
                oci_bind_by_name($stmt2, $paramName, $ids[$index]);
            }

            // Executar a consulta
            $result2 = oci_execute($stmt2);

            // Inicializar array para armazenar os resultados do segundo SELECT
            $data2 = [];
            if ($result2) {
                while ($row2 = oci_fetch_assoc($stmt2)) {
                    $data2[] = $row2;
                }
            }

            // Retornar os dados do primeiro e segundo SELECT
            $response = [
                'error' => false,
                'message' => '',
                'data' => [
                    'usuarios' => $data,
                    'detalhes' => $data2
                ]
            ];
        } else {
            $response = [
                'error' => true,
                'message' => 'Nenhum ID encontrado para o usuário',
                'data' => []
            ];
        }
    } else {
        // Se ocorrer erro ao executar o primeiro SELECT
        $error = oci_error($stmt1);
        $response = [
            'error' => true,
            'message' => 'Erro ao executar o primeiro SELECT: ' . $error['message'],
            'data' => []
        ];
    }
} catch (Exception $e) {
    // Tratar exceções gerais
    $response = [
        'error' => true,
        'message' => 'Erro no servidor: ' . $e->getMessage(),
        'data' => []
    ];
}

// Fechar conexão com o banco
if(isset($stmt1)){
    oci_free_statement($stmt1);
}
if(isset($stmt2)){
    oci_free_statement($stmt2);
}

oci_close($conn);

echo json_encode($response);
