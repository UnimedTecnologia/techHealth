<?php
// relatorioGuiasLocalIntercambio.php
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('America/Sao_Paulo');
// Produção: desligue display_errors. Em dev você pode ligar.
// Mas para evitar que HTML de erro quebre o JSON, vamos suprimir saída direta.
error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    // ---- segurança / limites ----
    set_time_limit(120); // pode aumentar se o relatório for grande

    require_once "../../config/AW00DB.php";
    require_once "../../config/oracle.class.php";
    require_once "../../config/AW00MD.php";

    // --- Recebe parâmetros POST ---
    $dataini = $_POST['dtGuiaLocalIntercambioIni'] ?? '';
    $datafim  = $_POST['dtGuiaLocalIntercambioFim'] ?? '';
    $matriculasRaw = trim($_POST['matriculaAutorizacao'] ?? '');

    if (empty($dataini) || empty($datafim)) {
        echo json_encode(['success' => false, 'error' => 'Informe data início e data fim.']);
        exit;
    }

    // Formata datas (input type=date envia yyyy-mm-dd)
    $dataini_fmt = date('d/m/Y', strtotime($dataini));
    $datafim_fmt = date('d/m/Y', strtotime($datafim));

    // --- Trata matrículas: aceita "114..,114..,..." ou espaço-separado
    $matriculas_array = [];
    if ($matriculasRaw !== '') {
        // remove espaços soltos e aspas simples/duplas desnecessárias
        $clean = str_replace(["'", '"'], '', $matriculasRaw);
        // aceita vírgula, espaço ou ambos
        $parts = preg_split('/[\s,]+/', $clean, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($parts as $p) {
            $p = trim($p);
            if ($p !== '') $matriculas_array[] = $p;
        }
    }

    // Se usuário não informou nenhuma matrícula, retornamos erro JSON
    if (empty($matriculas_array)) {
        echo json_encode(['success' => false, 'error' => 'Informe ao menos uma matrícula (adicione ou mantenha as preselecionadas).']);
        exit;
    }

    // Escapa e monta o IN('a','b',...)
    $matriculas_escaped = array_map(function($m){ return "'" . addslashes($m) . "'"; }, $matriculas_array);
    $matriculas_sql = implode(',', $matriculas_escaped);

    // --- SQL com filtro dinâmico ---
    $sql = "
        SELECT
            X.CD_USERID_ALT,
            SUBSTR(X.NOM_USUARIO, 1, INSTR(X.NOM_USUARIO, ' ') -1) AS NOME,
            COUNT(*) AS total_guias,
            SUM(CASE WHEN X.LIBERADO_GUIAS = 'AUTORIZADA' THEN 1 ELSE 0 END) AS total_autorizadas,
            SUM(CASE WHEN X.CD_UNIDADE_CARTEIRA = 540 THEN 1 ELSE 0 END) AS total_local,
            SUM(CASE WHEN X.CD_UNIDADE_CARTEIRA <> 540 THEN 1 ELSE 0 END) AS total_intercambio
        FROM (
            SELECT
                A.DT_EMISSAO_GUIA,
                A.CD_TIPO_GUIA,
                T.DS_TIPO_GUIA,
                A.NR_GUIA_ATENDIMENTO,
                A.AA_GUIA_ATEND_ORIGEM,
                A.NR_GUIA_ATEND_ORIGEM,
                (A.DT_EMISSAO_GUIA + T.QT_DIAS_VALIDADE) DT_VENC,
                A.IN_LIBERADO_GUIAS,
                A.CD_UNIDADE_CARTEIRA,
                CASE
                    WHEN A.IN_LIBERADO_GUIAS =  1 THEN 'DIGITADA'
                    WHEN A.IN_LIBERADO_GUIAS =  2 THEN 'AUTORIZADA'
                    WHEN A.IN_LIBERADO_GUIAS =  3 THEN 'CANCELADA'
                    WHEN A.IN_LIBERADO_GUIAS =  4 THEN 'PROCESSADA PELO RC'
                    WHEN A.IN_LIBERADO_GUIAS =  5 THEN 'FECHADA'
                    WHEN A.IN_LIBERADO_GUIAS =  6 THEN 'ORÇAMENTO'
                    WHEN A.IN_LIBERADO_GUIAS =  7 THEN 'FATURADA'
                    WHEN A.IN_LIBERADO_GUIAS =  8 THEN 'NEGADA'
                    WHEN A.IN_LIBERADO_GUIAS =  9 THEN 'PENDENTE AUDITORIA'
                    WHEN A.IN_LIBERADO_GUIAS = 10 THEN 'PENDENTE LIBERAÇÃO'
                    WHEN A.IN_LIBERADO_GUIAS = 11 THEN 'PENDENTE LAUDO MÉDICO'
                    WHEN A.IN_LIBERADO_GUIAS = 12 THEN 'PENDENTE DE JUSTIFICATIVA MÉDICA'
                    WHEN A.IN_LIBERADO_GUIAS = 13 THEN 'PENDENTE DE PERÍCIA'
                    WHEN A.IN_LIBERADO_GUIAS = 19 THEN 'EM AUDITORIA'
                    WHEN A.IN_LIBERADO_GUIAS = 20 THEN 'EM ATENDIMENTO'
                    WHEN A.IN_LIBERADO_GUIAS = 23 THEN 'EM PERÍCIA'
                    WHEN A.IN_LIBERADO_GUIAS = 30 THEN 'REEMBOLSO'
                    WHEN A.IN_LIBERADO_GUIAS = 31 THEN 'ENVIADA ORD. SERVIÇO'
                    WHEN A.IN_LIBERADO_GUIAS = 32 THEN 'RECEBIDA ORD. SERVIÇO'
                END AS LIBERADO_GUIAS,
                H.CD_USERID_ALT,
                U.NOM_USUARIO
            FROM GP.GUIAUTOR A
            INNER JOIN GP.GUIA_HIS H
                ON H.CD_UNIDADE = A.CD_UNIDADE
                AND H.AA_GUIA_ATENDIMENTO = A.AA_GUIA_ATENDIMENTO
                AND H.NR_GUIA_ATENDIMENTO = A.NR_GUIA_ATENDIMENTO
            INNER JOIN GP.TIP_GUIA T
                ON T.CD_TIPO_GUIA = A.CD_TIPO_GUIA
            INNER JOIN EMSFND.USUAR_MESTRE U
                ON U.COD_USUARIO = H.CD_USERID_ALT
            WHERE A.DT_EMISSAO_GUIA BETWEEN TO_DATE(:dataini,'dd/mm/yyyy') AND TO_DATE(:datafim,'dd/mm/yyyy')
              AND H.CD_USERID_ALT IN ($matriculas_sql)
        ) X
        GROUP BY X.CD_USERID_ALT, X.NOM_USUARIO
        ORDER BY NOME
    ";

    // --- Prepara e executa ---
    $stid = oci_parse($conn, $sql);
    if (!$stid) {
        $err = oci_error($conn);
        throw new Exception('Erro ao preparar consulta: ' . ($err['message'] ?? ''));
    }

    // Ajuste de prefetch para performance (tenta reduzir round-trips)
    if (function_exists('oci_set_prefetch')) {
        @oci_set_prefetch($stid, 1000); // ajuste conforme necessidade
    }

    oci_bind_by_name($stid, ':dataini', $dataini_fmt);
    oci_bind_by_name($stid, ':datafim', $datafim_fmt);

    $exec = @oci_execute($stid);
    if (!$exec) {
        $err = oci_error($stid);
        throw new Exception('Erro ao executar consulta: ' . ($err['message'] ?? ''));
    }

    // --- Busca todos os resultados (mais rápido para muitos registros) ---
    $rows = [];
    $nrows = oci_fetch_all($stid, $rows, 0, -1, OCI_FETCHSTATEMENT_BY_ROW + OCI_ASSOC);
    // oci_fetch_all retorna array de linhas quando usou OCI_FETCHSTATEMENT_BY_ROW? Estamos tratando abaixo.
    if ($nrows === 0 || empty($rows)) {
        echo json_encode(['success' => false, 'error' => 'Nenhum dado encontrado para o período/matrículas informadas.']);
        exit;
    }

    // oci_fetch_all com OCI_FETCHSTATEMENT_BY_ROW dá array de rows (cada row = assoc)
    // Se por alguma razão rows estiver associativo de col => array(colvalues), normalizamos:
    if (array_keys($rows) === range(0, count($rows) - 1)) {
        // já está em formato lista de rows
        $resultRows = $rows;
    } else {
        // converte col=>[..] para rows
        $resultRows = [];
        $cols = array_keys($rows);
        $count = count($rows[$cols[0]]);
        for ($i = 0; $i < $count; $i++) {
            $row = [];
            foreach ($cols as $c) {
                $row[$c] = $rows[$c][$i];
            }
            $resultRows[] = $row;
        }
    }

    // --- Prepara CSV com UTF-8 BOM e converte valores para UTF-8 se necessário ---
    $dir = __DIR__ . '/tmp';
    if (!is_dir($dir)) @mkdir($dir, 0777, true);
    $filename = 'relatorio_guias' . date('Ymd_His') . '.csv';
    $filepath = $dir . '/' . $filename;

    $fp = fopen($filepath, 'w');
    if ($fp === false) {
        throw new Exception('Não foi possível criar arquivo temporário.');
    }

    // BOM UTF-8 para Excel
    fwrite($fp, "\xEF\xBB\xBF");

    // cabeçalho
    $firstRow = $resultRows[0];
    fputcsv($fp, array_keys($firstRow), ';');

    // dados (garante UTF-8)
    foreach ($resultRows as $r) {
        foreach ($r as $k => $v) {
            if (!mb_detect_encoding($v, 'UTF-8', true)) {
                $r[$k] = utf8_encode($v);
            }
        }
        fputcsv($fp, $r, ';');
    }

    fclose($fp);

    // --- Retorna URL pública relativa (ajuste se necessário) ---
    $publicPath = 'autorizacoes/relatorioGuiasLocalIntercambio/tmp/' . $filename;

    echo json_encode(['success' => true, 'file' => $publicPath]);
    exit;

} catch (Exception $ex) {
    // garante que retornamos JSON e sem HTML extra
    $msg = $ex->getMessage();
    echo json_encode(['success' => false, 'error' => $msg]);
    exit;
}
