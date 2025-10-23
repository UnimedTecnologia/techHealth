<?php
/* //* ////////////////////////////////////////////////////////////
UPDATES: TABELA MOVIPROC E HISTOR_MOVIMEN_PROCED
UPDATES: TABELA MOV_INSU E HISTOR_MOVIMEN_INSUMO
*/ //* ////////////////////////////////////////////////////////////
header('Content-Type: text/html; charset=utf-8');
session_start();
set_time_limit(600); //* Definir o tempo máximo de execução para 300 segundos (5 minutos)
// Dados recebidos via POST
$porcentagem = $_POST['porcentagem'] ?? null; 
$procedimentos = $_POST['procedimentos'] ?? [];
$insumos = $_POST['insumos'] ?? [];

// Configuração e conexões
require_once "../../config/AW00DB.php";
require_once "../../config/oracle.class.php";
require_once "../../config/AW00MD.php";

oci_set_client_info($conn, 'UTF-8');
$fatorMult = $porcentagem / 100;

// Função para executar o update
function executeUpdate($conn, $sql, $bindings, $entity) {
    $stmt = oci_parse($conn, $sql);

    if (!$stmt) {
        $e = oci_error($conn);
        die(json_encode([
            'error' => true,
            'message' => "Erro ao preparar a consulta de $entity: " . htmlentities($e['message'], ENT_QUOTES),
            'type' => 'danger'
        ]));
    }

    foreach ($bindings as $key => $value) {
        oci_bind_by_name($stmt, $key, $bindings[$key]);
    }

    if (!oci_execute($stmt, OCI_COMMIT_ON_SUCCESS)) {
        $e = oci_error($stmt);
        die(json_encode([
            'error' => true,
            'message' => "Erro ao executar update de $entity: " . htmlentities($e['message'], ENT_QUOTES),
            'type' => 'danger'
        ]));
    }
}

//* Atualização dos procedimentos
foreach ($procedimentos['doc'] as $index => $doc) {
    $prestador = $procedimentos['prestador'][$index] ?? '';
    $perref = $procedimentos['perref'][$index] ?? '';
    $anoref = $procedimentos['anoref'][$index] ?? '';
    $pacote = $procedimentos['pacote'][$index] ?? '';
    $seq = $procedimentos['seq'][$index] ?? '';

    $sql = "UPDATE gp.MOVIPROC P
            SET VL_PRINCIPAL = 0,
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

    $bindings = [
        ':doc'         => $doc,
        ':prestador'   => $prestador,
        ':perref'      => $perref,
        ':anoref'      => $anoref,
        ':pacote'      => $pacote,
        ':seq'         => $seq,
        ':porcentagem' => $porcentagem,
        ':fatorMult'   => $fatorMult
    ];

    executeUpdate($conn, $sql, $bindings, "procedimento");

    //!UPDATE HISTORICO PROCEDIMENTO
    $sqlHistProc = "UPDATE gp.HISTOR_MOVIMEN_PROCED P
        SET VL_PRINCIPAL = 0,
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
        P.DT_ATUALIZACAO = TO_DATE(:dataAtualizacao, 'DD/MM/YYYY'), P.CD_USERID = :userId
         WHERE P.NR_DOC_ORIGINAL = :doc 
              AND P.NR_PERREF = :perref 
              AND P.DT_ANOREF = :anoref
              AND P.CD_PACOTE = :pacote 
              AND P.CD_PRESTADOR = :prestador
              AND P.NR_SEQ_DIGITACAO = :seq";

    $bindings = [
        ':doc'         => $doc,
        ':prestador'   => $prestador,
        ':perref'      => $perref,
        ':anoref'      => $anoref,
        ':pacote'      => $pacote,
        ':seq'         => $seq,
        ':porcentagem' => $porcentagem,
        ':fatorMult'   => $fatorMult,
        ':dataAtualizacao' => date('d/m/Y'),
        ':userId' => $_SESSION['idusuario']
    ];

    executeUpdate($conn, $sqlHistProc, $bindings, "Historico Procedimento");

}

// Atualização dos insumos
foreach ($insumos['doc'] as $index => $doc) {
    $prestador = $insumos['prestador'][$index] ?? '';
    $perref = $insumos['perref'][$index] ?? '';
    $anoref = $insumos['anoref'][$index] ?? '';
    $pacote = $insumos['pacote'][$index] ?? '';
    $seq = $insumos['seq'][$index] ?? '';

    // Exemplo de SQL para insumos (caso necessário)
    $sql = "UPDATE gp.MOV_INSU P
       SET P.PC_APLICADO = :porcentagem,
           P.VL_INSUMO  = (SELECT PP.VL_INSUMO * :fatorMult 
            FROM gp.PACINSUM PP
            WHERE PP.CD_PACOTE = P.CD_PACOTE
                AND PP.CD_INSUMO   = P.CD_INSUMO
                AND PP.DT_LIMITE > TRUNC(SYSDATE)),
           P.VL_COBRADO = (SELECT PP.VL_INSUMO * :fatorMult 
            FROM gp.PACINSUM PP
            WHERE PP.CD_PACOTE = P.CD_PACOTE
                AND PP.CD_INSUMO   = P.CD_INSUMO
                AND PP.DT_LIMITE > TRUNC(SYSDATE))
        WHERE P.NR_DOC_ORIGINAL = :doc 
              AND P.NR_PERREF = :perref 
              AND P.DT_ANOREF = :anoref
              AND P.CD_PACOTE = :pacote 
              AND P.CD_PRESTADOR = :prestador
              AND P.NR_SEQ_DIGITACAO = :seq ";

    $bindings = [
        ':doc'         => $doc,
        ':prestador'   => $prestador,
        ':perref'      => $perref,
        ':anoref'      => $anoref,
        ':pacote'      => $pacote,
        ':seq'         => $seq,
        ':porcentagem' => $porcentagem,
        ':fatorMult'   => $fatorMult
    ];

    executeUpdate($conn, $sql, $bindings, "insumo");

    //!UPDATE HISTORICO INSUMOS
    $sqlHistInsu = "UPDATE gp.HISTOR_MOVIMEN_INSUMO I
      SET I.PC_APLICADO = :porcentagem,
           I.VL_INSUMO  = (SELECT PP.VL_INSUMO * :fatorMult 
            FROM gp.PACINSUM PP
            WHERE PP.CD_PACOTE = I.CD_PACOTE
                AND PP.CD_INSUMO   = I.CD_INSUMO
                AND PP.DT_LIMITE > TRUNC(SYSDATE)),
           I.VL_COBRADO = (SELECT PP.VL_INSUMO * :fatorMult 
            FROM gp.PACINSUM PP
            WHERE PP.CD_PACOTE = I.CD_PACOTE
                AND PP.CD_INSUMO   = I.CD_INSUMO
                AND PP.DT_LIMITE > TRUNC(SYSDATE)),
                I.DT_ATUALIZACAO = TO_DATE(:dataAtualizacao, 'DD/MM/YYYY'), I.CD_USERID = :userId
        WHERE I.NR_DOC_ORIGINAL = :doc 
              AND I.NR_PERREF = :perref 
              AND I.DT_ANOREF = :anoref
              AND I.CD_PACOTE = :pacote 
              AND I.CD_PRESTADOR = :prestador
              AND I.NR_SEQ_DIGITACAO = :seq ";
              
        $bindings = [
        ':doc'         => $doc,
        ':prestador'   => $prestador,
        ':perref'      => $perref,
        ':anoref'      => $anoref,
        ':pacote'      => $pacote,
        ':seq'         => $seq,
        ':porcentagem' => $porcentagem,
        ':fatorMult'   => $fatorMult,
        ':dataAtualizacao' => date('d/m/Y'),
        ':userId' => $_SESSION['idusuario']
        ];

    executeUpdate($conn, $sqlHistInsu, $bindings, "Histórico Insumos");
}

    //* Finaliza a execução
    oci_close($conn);

    $response = [
        'error' => false,
        'message' => 'Atualização realizada com sucesso.',
        'type' => 'success'
    ];

    //!SUCESSO AO FAZER UPDATES - GET NOVOS VALORES (para montar tabela)
    $_POST['codPrest'] = $prestador;
    $_POST['numDoc'] = $doc;
    $_POST['numPacote'] = $pacote;
    $_POST['periodoRef'] = $perref;
    $_POST['dtAnoRef'] = $anoref;
     //* arquivo que irá usar os parâmetros do $_POST 
     include 'verificar_valores_documento.php';


