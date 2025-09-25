<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

$statusGuia = $_POST['statusSelectReg']; 
$nr_guia    = $_POST['nr_guiaReg'];
$ano        = $_POST['ano'];

require_once "../config/AW00DB.php";
require_once "../config/oracle.class.php";
require_once "../config/AW00MD.php";

try {
    // Primeiro, buscar o PROGRESS_RECID da guia
    $sqlSelect = "SELECT A.PROGRESS_RECID as RecIDGuia 
                  FROM GP.GUIAUTOR A 
                  WHERE A.NR_GUIA_ATENDIMENTO = :nr_guia 
                  AND A.AA_GUIA_ATENDIMENTO = :ano";
    
    $stidSelect = oci_parse($conn, $sqlSelect);
    oci_bind_by_name($stidSelect, ":nr_guia", $nr_guia);
    oci_bind_by_name($stidSelect, ":ano", $ano);
    oci_execute($stidSelect);
    
    $guia = oci_fetch_assoc($stidSelect);
    
    if (!$guia) {
        throw new Exception("Guia não encontrada para o número e ano informados.");
    }
    
    $progressRecId = $guia['RECIDGUIA'];
    
    // oci_set_autocommit($conn, false);
    
    //* 1. UPDATE na tabela GP.GUIAUTOR
    $sqlUpdate = "UPDATE GP.GUIAUTOR A 
                  SET A.U##IN_LIBERADO_GUIAS = :statusGuia, 
                      A.IN_LIBERADO_GUIAS = :statusGuia 
                  WHERE A.PROGRESS_RECID = :progressRecId";
    
    $stidUpdate = oci_parse($conn, $sqlUpdate);
    oci_bind_by_name($stidUpdate, ":statusGuia", $statusGuia);
    oci_bind_by_name($stidUpdate, ":progressRecId", $progressRecId);
    oci_execute($stidUpdate, OCI_NO_AUTO_COMMIT); // Executar sem auto-commit
    
    //* 2. Buscar o próximo valor da sequência para NR_SEQUENCIA_ALT
    $sqlSequence = "SELECT MAX(NR_SEQUENCIA_ALT) + 1 as SEQ 
                    FROM GP.GUIA_HIS GH 
                    WHERE GH.AA_GUIA_ATENDIMENTO = :anoGuia
                    AND GH.NR_GUIA_ATENDIMENTO = :nrGuia";
    
    $stidSequence = oci_parse($conn, $sqlSequence);
    oci_bind_by_name($stidSequence, ":anoGuia", $ano);
    oci_bind_by_name($stidSequence, ":nrGuia", $nr_guia);
    oci_execute($stidSequence, OCI_NO_AUTO_COMMIT);
    $sequence = oci_fetch_assoc($stidSequence);
    
    // Se não houver registros anteriores, começar com 1
    $nrSequenciaAlt = $sequence['SEQ'] ?: 1;
    
    //* 3. Buscar informações adicionais necessárias para o INSERT
    $sqlInfoGuia = "SELECT CD_UNIDADE 
                    FROM GP.GUIAUTOR 
                    WHERE PROGRESS_RECID = :progressRecId";
    
    $stidInfo = oci_parse($conn, $sqlInfoGuia);
    oci_bind_by_name($stidInfo, ":progressRecId", $progressRecId);
    oci_execute($stidInfo, OCI_NO_AUTO_COMMIT);
    $infoGuia = oci_fetch_assoc($stidInfo);
    
    $cdUnidade = $infoGuia['CD_UNIDADE'];
    $cdUserid = $_SESSION['usuario'];
    // $cdUserid = $_SESSION['idusuario'];
    
    //* 4. INSERT na tabela GP.GUIA_HIS
    // **CORREÇÃO: Removido o GP.GUIA_HIS_SEQ.NEXTVAL do PROGRESS_RECID se não for necessário**
    $sqlInsert = "INSERT INTO GP.GUIA_HIS 
                  (CD_UNIDADE, AA_GUIA_ATENDIMENTO, NR_GUIA_ATENDIMENTO, NR_SEQUENCIA_ALT,
                   U##IN_LIB_GUIAS_ALT, IN_LIB_GUIAS_ALT, CD_USERID_ALT, DT_ALT, DT_ATUALIZACAO, CHAR_4, PROGRESS_RECID) 
                  VALUES 
                  (:cdUnidade, :anoGuia, :nrGuia, :nrSequenciaAlt,
                   :uInLibGuiasAlt, :inLibGuiasAlt, :cdUseridAlt, SYSDATE, SYSDATE, 'TECH_HEALTH', GP.GUIA_HIS_SEQ.NEXTVAL)";
    
    $stidInsert = oci_parse($conn, $sqlInsert);
    oci_bind_by_name($stidInsert, ":cdUnidade", $cdUnidade);
    oci_bind_by_name($stidInsert, ":anoGuia", $ano);
    oci_bind_by_name($stidInsert, ":nrGuia", $nr_guia);
    oci_bind_by_name($stidInsert, ":nrSequenciaAlt", $nrSequenciaAlt);
    oci_bind_by_name($stidInsert, ":uInLibGuiasAlt", $statusGuia);
    oci_bind_by_name($stidInsert, ":inLibGuiasAlt", $statusGuia);
    oci_bind_by_name($stidInsert, ":cdUseridAlt", $cdUserid);
    
    oci_execute($stidInsert, OCI_NO_AUTO_COMMIT);
    
    // Commit da transação
    oci_commit($conn);
    
    // Reativar auto-commit para operações futuras
    // oci_set_autocommit($conn, true);
    
    // Retornar sucesso
    echo json_encode([
        'type' => 'success',
        'message' => 'Status da guia atualizado com sucesso!'
    ]);
    
} catch (Exception $e) {
    // Rollback em caso de erro
    oci_rollback($conn);
    oci_set_autocommit($conn, true); // Reativar auto-commit mesmo em caso de erro
    
    echo json_encode([
        'type' => 'error',
        'message' => 'Erro ao atualizar status: ' . $e->getMessage()
    ]);
} finally {
    // Fechar statements se existirem
    if (isset($stidSelect)) oci_free_statement($stidSelect);
    if (isset($stidUpdate)) oci_free_statement($stidUpdate);
    if (isset($stidSequence)) oci_free_statement($stidSequence);
    if (isset($stidInfo)) oci_free_statement($stidInfo);
    if (isset($stidInsert)) oci_free_statement($stidInsert);
}

?>