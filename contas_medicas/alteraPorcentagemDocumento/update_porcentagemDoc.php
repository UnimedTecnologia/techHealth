<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
set_time_limit(600);

require_once "../../config/AW00DB.php";
require_once "../../config/oracle.class.php";
require_once "../../config/AW00MD.php";

oci_set_client_info($conn, 'UTF-8');

// Função genérica para executar update
function executeUpdate($conn, $sql, $bindings, $entity) {
    $stmt = oci_parse($conn, $sql);
    if (!$stmt) {
        $e = oci_error($conn);
        throw new Exception("Erro ao preparar a consulta de $entity: " . htmlentities($e['message'], ENT_QUOTES));
    }

    foreach ($bindings as $key => &$value) {
        oci_bind_by_name($stmt, $key, $value);
    }

    if (!oci_execute($stmt, OCI_COMMIT_ON_SUCCESS)) {
        $e = oci_error($stmt);
        throw new Exception("Erro ao executar update de $entity: " . htmlentities($e['message'], ENT_QUOTES));
    }
}

// Dados recebidos via POST
$itens = $_POST['itens'] ?? [];

if (empty($itens)) {
    echo json_encode(['error' => true, 'message' => 'Nenhum item recebido.']);
    exit;
}

try {
    foreach ($itens as $item) {
        $origem = $item['ORIGEM'] ?? '';
        $porcentagem = (float) str_replace(',', '.', $item['porcentagem'] ?? 0);

        // Só atualiza se houver valor informado
        if ($porcentagem <= 0) continue;

        $fatorMult = $porcentagem / 100;
        $bindingsBase = [
            ':doc' => $item['NR_DOC_ORIGINAL'],
            ':prestador' => $item['CD_PRESTADOR'],
            ':perref' => $item['NR_PERREF'],
            ':anoref' => $item['DT_ANOREF'],
            ':pacote' => $item['CD_PACOTE'],
            ':seq' => $item['NR_SEQ_DIGITACAO'],
            ':porcentagem' => $porcentagem,
            ':fatorMult' => $fatorMult,
            ':dataAtualizacao' => date('d/m/Y'),
            ':userId' => $_SESSION['idusuario'] ?? 'sistema'
        ];

        if ($origem === 'P') {
            // PROCEDIMENTO
            $sqlMov = "UPDATE gp.MOVIPROC P
                       SET P.VL_PRINCIPAL = 0,
                           P.PC_APLICADO = 100,
                           P.DEC_11 = :porcentagem,
                           P.DEC_12 = :porcentagem,
                           P.DEC_13 = :porcentagem,
                           P.VL_AUXILIAR = (SELECT PP.VL_PROCEDIMENTO * :fatorMult
                                            FROM gp.PACPROCE PP
                                            WHERE PP.CD_PACOTE = P.CD_PACOTE
                                              AND PP.DT_LIMITE > TRUNC(SYSDATE)),
                           P.VL_COBRADO = (SELECT PP.VL_PROCEDIMENTO * :fatorMult
                                            FROM gp.PACPROCE PP
                                            WHERE PP.CD_PACOTE = P.CD_PACOTE
                                              AND PP.DT_LIMITE > TRUNC(SYSDATE))
                       WHERE P.NR_DOC_ORIGINAL = :doc
                         AND P.NR_PERREF = :perref
                         AND P.DT_ANOREF = :anoref
                         AND P.CD_PACOTE = :pacote
                         AND P.CD_PRESTADOR = :prestador
                         AND P.NR_SEQ_DIGITACAO = :seq";

            executeUpdate($conn, $sqlMov, $bindingsBase, "MOVIPROC");

            $sqlHist = "UPDATE gp.HISTOR_MOVIMEN_PROCED P
                        SET P.VL_PRINCIPAL = 0,
                            P.PC_APLICADO = 100,
                            P.DEC_11 = :porcentagem,
                            P.DEC_12 = :porcentagem,
                            P.DEC_13 = :porcentagem,
                            P.VL_AUXILIAR = (SELECT PP.VL_PROCEDIMENTO * :fatorMult
                                              FROM gp.PACPROCE PP
                                              WHERE PP.CD_PACOTE = P.CD_PACOTE
                                                AND PP.DT_LIMITE > TRUNC(SYSDATE)),
                            P.VL_COBRADO = (SELECT PP.VL_PROCEDIMENTO * :fatorMult
                                              FROM gp.PACPROCE PP
                                              WHERE PP.CD_PACOTE = P.CD_PACOTE
                                                AND PP.DT_LIMITE > TRUNC(SYSDATE)),
                            P.DT_ATUALIZACAO = TO_DATE(:dataAtualizacao, 'DD/MM/YYYY'),
                            P.CD_USERID = :userId
                        WHERE P.NR_DOC_ORIGINAL = :doc
                          AND P.NR_PERREF = :perref
                          AND P.DT_ANOREF = :anoref
                          AND P.CD_PACOTE = :pacote
                          AND P.CD_PRESTADOR = :prestador
                          AND P.NR_SEQ_DIGITACAO = :seq";

            executeUpdate($conn, $sqlHist, $bindingsBase, "HISTOR_MOVIMEN_PROCED");
        }

        elseif ($origem === 'I') {
            // INSUMO
            $sqlMov = "UPDATE gp.MOV_INSU I
                       SET I.PC_APLICADO = :porcentagem,
                           I.VL_INSUMO  = (SELECT PP.VL_INSUMO * :fatorMult 
                                            FROM gp.PACINSUM PP
                                            WHERE PP.CD_PACOTE = I.CD_PACOTE
                                              AND PP.CD_INSUMO = I.CD_INSUMO
                                              AND PP.DT_LIMITE > TRUNC(SYSDATE)),
                           I.VL_COBRADO = (SELECT PP.VL_INSUMO * :fatorMult 
                                            FROM gp.PACINSUM PP
                                            WHERE PP.CD_PACOTE = I.CD_PACOTE
                                              AND PP.CD_INSUMO = I.CD_INSUMO
                                              AND PP.DT_LIMITE > TRUNC(SYSDATE))
                       WHERE I.NR_DOC_ORIGINAL = :doc
                         AND I.NR_PERREF = :perref
                         AND I.DT_ANOREF = :anoref
                         AND I.CD_PACOTE = :pacote
                         AND I.CD_PRESTADOR = :prestador
                         AND I.NR_SEQ_DIGITACAO = :seq";

            executeUpdate($conn, $sqlMov, $bindingsBase, "MOV_INSU");

            $sqlHist = "UPDATE gp.HISTOR_MOVIMEN_INSUMO I
                        SET I.PC_APLICADO = :porcentagem,
                            I.VL_INSUMO  = (SELECT PP.VL_INSUMO * :fatorMult 
                                            FROM gp.PACINSUM PP
                                            WHERE PP.CD_PACOTE = I.CD_PACOTE
                                              AND PP.CD_INSUMO = I.CD_INSUMO
                                              AND PP.DT_LIMITE > TRUNC(SYSDATE)),
                            I.VL_COBRADO = (SELECT PP.VL_INSUMO * :fatorMult 
                                            FROM gp.PACINSUM PP
                                            WHERE PP.CD_PACOTE = I.CD_PACOTE
                                              AND PP.CD_INSUMO = I.CD_INSUMO
                                              AND PP.DT_LIMITE > TRUNC(SYSDATE)),
                            I.DT_ATUALIZACAO = TO_DATE(:dataAtualizacao, 'DD/MM/YYYY'),
                            I.CD_USERID = :userId
                        WHERE I.NR_DOC_ORIGINAL = :doc
                          AND I.NR_PERREF = :perref
                          AND I.DT_ANOREF = :anoref
                          AND I.CD_PACOTE = :pacote
                          AND I.CD_PRESTADOR = :prestador
                          AND I.NR_SEQ_DIGITACAO = :seq";

            executeUpdate($conn, $sqlHist, $bindingsBase, "HISTOR_MOVIMEN_INSUMO");
        }
    }

    oci_close($conn);
    echo json_encode(['error' => false, 'message' => 'Atualização concluída com sucesso.']);

} catch (Exception $e) {
    echo json_encode(['error' => true, 'message' => $e->getMessage()]);
}
