<?php
header('Content-Type: text/html; charset=utf-8');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$codPrest   = (int) $_POST['codPrest'];
$numDoc     = (int) $_POST['numDoc'];
$numPacote  = (int) $_POST['numPacote'];
$periodoRef = (int) $_POST['periodoRef'];
$dtAnoRef   = (int) $_POST['dtAnoRef'];

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

    //! GET PERCENTUAL PROCEDIMENTO
    $sqlProc = "SELECT P.NR_DOC_ORIGINAL DOC, P.CD_PRESTADOR, P.CD_TRANSACAO, P.NR_SERIE_DOC_ORIGINAL SERIE, P.NR_SEQ_DIGITACAO SEQ, P.NR_PERREF, P.DT_ANOREF, P.CD_PACOTE,
       P.PC_APLICADO, P.DEC_11, P.DEC_12, P.DEC_13, P.VL_PRINCIPAL, P.VL_AUXILIAR, P.VL_COBRADO, PP.VL_PROCEDIMENTO AS VL_PACOTE,
       P.CD_CLASSE_ERRO CL_ERRO, P.CD_COD_GLO GLOSA, P.VL_GLOSADO, P.VL_REAL_GLOSADO, P.VL_BASE_VALOR_SISTEMA, P.VL_REAL_PAGO
   FROM gp.MOVIPROC P 
       INNER JOIN gp.PACPROCE PP ON PP.CD_PACOTE  = P.CD_PACOTE
       WHERE P.NR_DOC_ORIGINAL = :numdoc 
         AND P.NR_PERREF       = :periodoref
         AND P.DT_ANOREF       = :anoref
         AND P.CD_PACOTE       = :numpacote 
        --  AND PP.VL_PROCEDIMENTO = (SELECT E.VL_PROCEDIMENTO FROM gp.PACPROCE E WHERE E.CD_PACOTE = :numpacote   AND E.DT_LIMITE > TRUNC(SYSDATE))
         AND PP.VL_PROCEDIMENTO = (
          SELECT MAX(E.VL_PROCEDIMENTO)
          FROM gp.PACPROCE E 
          WHERE E.CD_PACOTE = :numpacote AND E.DT_LIMITE > TRUNC(SYSDATE)
        )
         AND P.CD_PRESTADOR    = :codprest ";

    //! GET PERCENTUAL INSUMO
    $sqlInsu = "SELECT I.NR_DOC_ORIGINAL DOC, I.CD_PRESTADOR, I.CD_TRANSACAO, I.NR_SERIE_DOC_ORIGINAL SERIE, I.NR_SEQ_DIGITACAO SEQ, I.NR_PERREF, I.DT_ANOREF, I.CD_PACOTE, I.CD_INSUMO,
        I.PC_APLICADO, I.VL_INSUMO, I.VL_COBRADO, E.VL_INSUMO AS VL_PACOTE,
        I.CD_CLASSE_ERRO CL_ERRO, I.CD_COD_GLO GLOSA, I.VL_GLOSADO, I.VL_REAL_GLOSADO 
    FROM gp.PACINSUM E
    INNER JOIN gp.MOV_INSU I ON I.CD_PACOTE = E.CD_PACOTE AND I.CD_INSUMO = E.CD_INSUMO
    WHERE I.NR_DOC_ORIGINAL = :numdoc 
        AND I.NR_PERREF       = :periodoref
        AND I.DT_ANOREF       = :anoref
        AND E.CD_PACOTE       = :numpacote 
        AND E.CD_INSUMO       = I.CD_INSUMO 
        AND E.DT_LIMITE > TRUNC(SYSDATE)
        AND I.CD_PRESTADOR    = :codprest ";

// Bind de parâmetros
$bindings = [
    ':numdoc'     => $numDoc,
    ':periodoref' => $periodoRef,
    ':anoref'     => $dtAnoRef,
    ':numpacote'  => $numPacote,
    ':codprest'   => $codPrest,
];

//* Preparar consulta
$stmtProc = executeQuery($conn, $sqlProc, $bindings);

//* Inicializar array para armazenar os resultados
$dataProc = [];

while ($row = oci_fetch_assoc($stmtProc)) {
    $dataProc[] = $row; // Adiciona cada linha ao array $dataProc
}

//* Verificar resultados
// if (empty($dataProc)) {
//     //! se não encontrar, retorna para dashboard com a mensagem
//     $response = [
//         'error' => true,
//         'message' => 'Dados não encontrados',
//         'procedimento' => '',
//         'insumo' => ''
//     ];
//     $_SESSION['dadosValoresDoc'] = $response;
//     header("Location: ../../dashboard.php");
//     exit;

// } else {

    //! encontrando valores na tabela procedimento - busca na tabela insumo
    //* Preparar consulta
    $stmtInsu = executeQuery($conn, $sqlInsu, $bindings);

    //* Inicializar array para armazenar os resultados
    $dataInsu = [];

    while ($row = oci_fetch_assoc($stmtInsu)) {
        $dataInsu[] = $row; // Adiciona cada linha ao array $dataInsu
    }
        

    //!VALIDA VALORES DO PACOTE
    $sqlPac = "SELECT I.CD_PACOTE, A.TP_PROCEDIMENTO, A.CDPROCEDIMENTOCOMPLETO AS CODIGO, I.VL_PROCEDIMENTO, I.DT_INICIO_VIGENCIA, I.DT_FIM_VIGENCIA FROM gp.PACPROCE I 
                INNER JOIN gp.AMBPROCE A ON A.CD_ESP_AMB = I.CD_ESP_AMB AND A.CD_GRUPO_PROC_AMB = I.CD_GRUPO_PROC_AMB AND A.CD_PROCEDIMENTO = I.CD_PROCEDIMENTO AND A.DV_PROCEDIMENTO = I.DV_PROCEDIMENTO
                WHERE I.CD_PACOTE = :numpacote   AND I.DT_LIMITE > TRUNC(SYSDATE)
                UNION
                SELECT I.CD_PACOTE, TO_CHAR(I.CD_TIPO_INSUMO), I.CD_INSUMO AS CODIGO, I.VL_INSUMO, I.DT_INICIO_VIGENCIA, I.DT_FIM_VIGENCIA FROM gp.PACINSUM I 
                WHERE I.CD_PACOTE = :numpacote   AND I.DT_LIMITE > TRUNC(SYSDATE)";

    $bindingsPac = [
        ':numpacote'  => $numPacote,
    ];
    
    $stmtPacote = executeQuery($conn, $sqlPac, $bindingsPac);

    //* Processa os resultados para verificar se os valores de DT_INICIO_VIGENCIA são iguais
    $dtInicioVigencias = [];

    while ($row = oci_fetch_assoc($stmtPacote)) {
        $dtInicioVigencias[] = $row['DT_INICIO_VIGENCIA'];
    }

    //* Verifica se todos os valores de DT_INICIO_VIGENCIA são iguais
    if (count(array_unique($dtInicioVigencias)) > 1) {
        //* Os valores de DT_INICIO_VIGENCIA não são iguais
        $response = [
            'error' => true,
            'message' => 'Início Vigência com datas diferentes. Verifique o pacote.',
            'procedimento' => '',
            'insumo' => '',
        ];
    } else {
        //* Os valores de DT_INICIO_VIGENCIA são iguais
        $response = [
            'error' => false,
            'message' => '',
            'procedimento' => $dataProc,
            'insumo' => $dataInsu,
        ];
    }


    // Liberar recursos
    oci_free_statement($stmtProc);
    oci_free_statement($stmtInsu);
    oci_close($conn);

    $_SESSION['dadosValoresDoc'] = $response;
    header("Location: index.php");
     exit;

// }

function executeQuery($conn, $sql, $bindings) {
    $stmt = oci_parse($conn, $sql);
    if (!$stmt) {
        $e = oci_error($conn);
        throw new Exception("Erro ao preparar a consulta: " . $e['message']);
    }
    foreach ($bindings as $key => $value) {
        oci_bind_by_name($stmt, $key, $bindings[$key]);
    }
    if (!oci_execute($stmt)) {
        $e = oci_error($stmt);
        if (strpos($e['message'], 'ORA-01427') !== false) {
            // throw new Exception("Erro: A consulta retornou mais de uma linha onde era esperado apenas um resultado. Verifique os filtros utilizados.");
        } else {
            // throw new Exception("Erro ao executar a consulta: " . $e['message']);
        }
    }
    return $stmt;
}


// function executeQuery($conn, $sql, $bindings) {
//     $stmt = oci_parse($conn, $sql);
//     if (!$stmt) {
//         $e = oci_error($conn);
//         throw new Exception("Erro ao preparar a consulta: " . $e['message']);
//     }
//     foreach ($bindings as $key => $value) {
//         oci_bind_by_name($stmt, $key, $bindings[$key]);
//     }
//     if (!oci_execute($stmt)) {
//         $e = oci_error($stmt);
//         throw new Exception("Erro ao executar a consulta: " . $e['message']);
//     }
//     return $stmt;
// }



