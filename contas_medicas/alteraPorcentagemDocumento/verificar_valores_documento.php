<?php
header('Content-Type: text/html; charset=utf-8');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$codPrest   = (int) $_POST['codPrest'];
$numDoc     = (int) $_POST['numDoc'];
$periodoRef = (int) $_POST['periodoRef'];
$dtAnoRef   = (int) $_POST['dtAnoRef'];

$_SESSION['parametros_porcentagem'] = [
    'codPrest' => $_POST['codPrest'] ?? '',
    'numDoc' => $_POST['numDoc'] ?? '',
    'periodoRef' => $_POST['periodoRef'] ?? '',
    'dtAnoRef' => $_POST['dtAnoRef'] ?? ''
];


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

oci_set_client_info($conn, 'UTF-8');

// ---------------------------------------------------------------
// NOVO SELECT ÚNICO
// ---------------------------------------------------------------
$sql = "
WITH params AS (
  SELECT 
    :cd_prestador AS cd_prestador,
    :nr_perref AS nr_perref,
    :dt_anoref AS dt_anoref,
    :nr_doc AS nr_doc
  FROM dual
),
pacotes AS (
  SELECT DISTINCT 
         NVL(P.CD_PACOTE, I.CD_PACOTE) AS CD_PACOTE
  FROM GP.DOCRECON D
  LEFT JOIN GP.MOVIPROC P
    ON P.NR_DOC_ORIGINAL       = D.NR_DOC_ORIGINAL
   AND P.NR_DOC_SISTEMA        = D.NR_DOC_SISTEMA
   AND P.CD_TRANSACAO          = D.CD_TRANSACAO
   AND P.NR_SERIE_DOC_ORIGINAL = D.NR_SERIE_DOC_ORIGINAL
  LEFT JOIN GP.MOV_INSU I
    ON I.NR_DOC_ORIGINAL       = D.NR_DOC_ORIGINAL
   AND I.NR_DOC_SISTEMA        = D.NR_DOC_SISTEMA
   AND I.CD_TRANSACAO          = D.CD_TRANSACAO
   AND I.NR_SERIE_DOC_ORIGINAL = D.NR_SERIE_DOC_ORIGINAL
  JOIN GP.params prm ON 1=1
  WHERE D.CD_PRESTADOR_PRINCIPAL = prm.cd_prestador
    AND D.NR_PERREF = prm.nr_perref
    AND D.DT_ANOREF = prm.dt_anoref
    AND D.NR_DOC_ORIGINAL = prm.nr_doc
    AND NVL(P.CD_PACOTE, I.CD_PACOTE) <> 0
),
lines AS (
  SELECT 
    D.NR_DOC_ORIGINAL,
    P.CD_PACOTE,
    P.NR_PROCESSO,
    P.NR_SEQ_DIGITACAO,
    P.DT_DIGITACAO,
    P.HR_DIGITACAO,
    P.PROGRESS_RECID,
    HP.PROGRESS_RECID AS PROGRESS_RECID_HIST,
    P.VL_COBRADO,
    P.VL_GLOSADO,
    'P' AS origem,
    TO_DATE(TO_CHAR(P.DT_DIGITACAO, 'YYYY-MM-DD') || ' ' || 
            LPAD(FLOOR(P.HR_DIGITACAO/100),4,'0'), 'YYYY-MM-DD HH24MI') AS DT_HR_DIG,
    FLOOR(P.HR_DIGITACAO/100)*3600 + MOD(P.HR_DIGITACAO,100)*60 AS HR_SEGUNDOS
  FROM GP.DOCRECON D
  LEFT JOIN GP.MOVIPROC P
    ON P.NR_DOC_ORIGINAL       = D.NR_DOC_ORIGINAL
   AND P.NR_DOC_SISTEMA        = D.NR_DOC_SISTEMA
   AND P.CD_TRANSACAO          = D.CD_TRANSACAO
   AND P.NR_SERIE_DOC_ORIGINAL = D.NR_SERIE_DOC_ORIGINAL
  LEFT JOIN GP.HISTOR_MOVIMEN_PROCED HP
    ON HP.NR_DOC_ORIGINAL       = P.NR_DOC_ORIGINAL
   AND HP.NR_DOC_SISTEMA        = P.NR_DOC_SISTEMA
   AND HP.CD_TRANSACAO          = P.CD_TRANSACAO
   AND HP.NR_SERIE_DOC_ORIGINAL = P.NR_SERIE_DOC_ORIGINAL
   AND HP.NR_PROCESSO           = P.NR_PROCESSO
   AND HP.NR_SEQ_DIGITACAO      = P.NR_SEQ_DIGITACAO
  JOIN GP.params prm ON 1 = 1
  JOIN GP.pacotes pc ON pc.CD_PACOTE = P.CD_PACOTE
  WHERE D.CD_PRESTADOR_PRINCIPAL = prm.cd_prestador
    AND D.NR_PERREF = prm.nr_perref
    AND D.DT_ANOREF = prm.dt_anoref
    AND D.NR_DOC_ORIGINAL = prm.nr_doc

  UNION ALL

  SELECT 
    D.NR_DOC_ORIGINAL,
    I.CD_PACOTE,
    I.NR_PROCESSO,
    I.NR_SEQ_DIGITACAO,
    I.DT_DIGITACAO,
    I.HR_DIGITACAO,
    I.PROGRESS_RECID,
    HI.PROGRESS_RECID AS PROGRESS_RECID_HIST,
    I.VL_COBRADO,
    I.VL_GLOSADO,
    'I' AS origem,
    TO_DATE(TO_CHAR(I.DT_DIGITACAO, 'YYYY-MM-DD') || ' ' || 
            LPAD(FLOOR(I.HR_DIGITACAO/100),4,'0'), 'YYYY-MM-DD HH24MI') AS DT_HR_DIG,
    FLOOR(I.HR_DIGITACAO/100)*3600 + MOD(I.HR_DIGITACAO,100)*60 AS HR_SEGUNDOS
  FROM GP.DOCRECON D
  LEFT JOIN GP.MOV_INSU I
    ON I.NR_DOC_ORIGINAL       = D.NR_DOC_ORIGINAL
   AND I.NR_DOC_SISTEMA        = D.NR_DOC_SISTEMA
   AND I.CD_TRANSACAO          = D.CD_TRANSACAO
   AND I.NR_SERIE_DOC_ORIGINAL = D.NR_SERIE_DOC_ORIGINAL
  LEFT JOIN GP.HISTOR_MOVIMEN_INSUMO HI
    ON HI.NR_DOC_ORIGINAL       = I.NR_DOC_ORIGINAL
   AND HI.NR_DOC_SISTEMA        = I.NR_DOC_SISTEMA
   AND HI.CD_TRANSACAO          = I.CD_TRANSACAO
   AND HI.NR_SERIE_DOC_ORIGINAL = I.NR_SERIE_DOC_ORIGINAL
   AND HI.NR_PROCESSO           = I.NR_PROCESSO
   AND HI.NR_SEQ_DIGITACAO      = I.NR_SEQ_DIGITACAO
  JOIN GP.params prm ON 1 = 1
  JOIN GP.pacotes pc ON pc.CD_PACOTE = I.CD_PACOTE
  WHERE D.CD_PRESTADOR_PRINCIPAL = prm.cd_prestador
    AND D.NR_PERREF = prm.nr_perref
    AND D.DT_ANOREF = prm.dt_anoref
    AND D.NR_DOC_ORIGINAL = prm.nr_doc
),
numbered AS (
  SELECT
    l.*,
    DENSE_RANK() OVER (
      PARTITION BY l.NR_DOC_ORIGINAL
      ORDER BY l.CD_PACOTE, l.DT_HR_DIG
    ) AS PACOTE_OCORRENCIA_BASE
  FROM lines l
),
ajustado AS (
  SELECT 
    n.*,
    LAG(n.HR_SEGUNDOS) OVER (PARTITION BY n.CD_PACOTE ORDER BY n.DT_HR_DIG) AS HR_SEG_ANTERIOR,
    LAG(n.PACOTE_OCORRENCIA_BASE) OVER (PARTITION BY n.CD_PACOTE ORDER BY n.DT_HR_DIG) AS PACOTE_OCORRENCIA_ANTERIOR
  FROM numbered n
),
final AS (
  SELECT
    a.*,
    CASE 
      WHEN a.HR_SEG_ANTERIOR IS NULL THEN a.PACOTE_OCORRENCIA_BASE
      WHEN ABS(a.HR_SEGUNDOS - a.HR_SEG_ANTERIOR) <= 50 THEN a.PACOTE_OCORRENCIA_ANTERIOR
      ELSE a.PACOTE_OCORRENCIA_BASE
    END AS PACOTE_OCORRENCIA
  FROM ajustado a
)
SELECT
  NR_DOC_ORIGINAL,
  CD_PACOTE,
  TO_CHAR(DT_DIGITACAO, 'DD/MM/YYYY') AS DT_DIGITACAO,
  HR_DIGITACAO,
  NR_PROCESSO,
  NR_SEQ_DIGITACAO,
  origem,
  PROGRESS_RECID,
  PROGRESS_RECID_HIST,
  PACOTE_OCORRENCIA,
  VL_COBRADO,
  VL_GLOSADO
FROM final
ORDER BY PACOTE_OCORRENCIA, CD_PACOTE, DT_HR_DIG, origem
";

$bindings = [
    ':cd_prestador' => $codPrest,
    ':nr_perref'    => $periodoRef,
    ':dt_anoref'    => $dtAnoRef,
    ':nr_doc'       => $numDoc
];

try {
    $stmt = oci_parse($conn, $sql);
    foreach ($bindings as $key => $val) {
        oci_bind_by_name($stmt, $key, $bindings[$key]);
    }

    if (!oci_execute($stmt)) {
        $e = oci_error($stmt);
        throw new Exception("Erro ao executar consulta: " . $e['message']);
    }

    $data = [];
    while ($row = oci_fetch_assoc($stmt)) {
        $data[] = $row;
    }

    $_SESSION['dadosValoresDoc'] = [
        'error' => false,
        'message' => '',
        'resultados' => $data
    ];

} catch (Exception $e) {
    $_SESSION['dadosValoresDoc'] = [
        'error' => true,
        'message' => $e->getMessage(),
        'resultados' => []
    ];
}

// Libera recursos e redireciona
oci_free_statement($stmt);
oci_close($conn);

header("Location: index.php");
exit;
