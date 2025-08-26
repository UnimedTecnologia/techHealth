<?php
header('Content-Type: application/json; charset=utf-8');

// Incluir arquivos de configuração e classes
require_once "../config/AW00DB.php";
require_once "../config/oracle.class.php";
require_once "../config/AW00MD.php";

    try {
        // Instanciar a classe oracle para conexão com o banco
        $db = new oracle();
        $conn = $db->connect($db_host, $db_user, $db_pwd, $db_name, $pconnect);

        oci_set_client_identifier($conn, 'UTF-8'); // Para garantir que a codificação usada seja UTF-8

        // Escrever a consulta SQL com o parâmetro da carteirinha
        $sql = "select p.cd_prestador, p.nm_prestador from gp.preserv p where p.cd_unidade = 540 
                and (p.dt_exclusao is null or p.dt_exclusao > trunc(sysdate))
                order by 1 ";

        // Preparar a consulta
        $stmt = oci_parse($conn, $sql);

        // Executar a consulta
        $result = oci_execute($stmt);

        // Inicializar array para armazenar os resultados
        $data = [];

        if ($result) {

            // Recuperar os resultados da consulta
            while ($row = oci_fetch_assoc($stmt)) {
                $data[] = $row; // Adiciona cada linha ao array $data
            }

            // Verificar se algum resultado foi encontrado
            if (!empty($data)) {
                // Retornar os dados encontrados no formato JSON
                $response = [
                    'error' => false,
                    'message' => '',
                    'data' => $data
                ];

            } else {
                // Retornar array vazio com mensagem indicando que nada foi encontrado
                $response = [
                    'error' => true,
                    'message' => 'Prestadores não encontrados',
                    'data' => []
                ];

            }
        } else {
            // Se ocorrer erro ao executar a consulta
            $error = oci_error($stmt);
            $response = [
                'error' => true,
                'message' => 'Erro ao executar a consulta: ' . $error['message'],
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
oci_free_statement($stmt);
oci_close($conn);

//echo json_encode($response, JSON_UNESCAPED_UNICODE);
echo json_encode($response);
