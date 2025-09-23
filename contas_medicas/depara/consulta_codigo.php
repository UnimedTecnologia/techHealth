<?php
header('Content-Type: application/json; charset=utf-8');

require_once "../../config/AW00DB.php";
require_once "../../config/oracle.class.php";
require_once "../../config/AW00MD.php";

$codigo = isset($_POST['codigo']) ? intval($_POST['codigo']) : 0;

$sql = "
SELECT AI.CD_TIPO_INSUMO_INTERNO,
       AI.CD_INSUMO_INTERNO,
       I.DS_INSUMO,
       AI.CD_TIPO_INSUMO_EXTERNO AS DESPESA,
       AI.CD_INSUMO_EXTERNO,
       AI.CD_USERID,
       TO_CHAR(AI.DT_ATUALIZACAO,'DD/MM/YYYY HH24:MI') AS DT_ATUALIZACAO
FROM gp.ASSINSUM AI
INNER JOIN gp.INSUMOS I
   ON I.CD_TIPO_INSUMO = AI.CD_TIPO_INSUMO_INTERNO
  AND I.CD_INSUMO      = AI.CD_INSUMO_INTERNO
INNER JOIN gp.TISS_ASSOC_TIP_DESPES T
   ON T.CDN_TIP_INSUMO = I.CD_TIPO_INSUMO
WHERE ((AI.CD_INSUMO_INTERNO = :codigo) OR (AI.CD_INSUMO_EXTERNO = :codigo))
  AND AI.DT_LIMITE >= TRUNC(SYSDATE)
";

$stid = oci_parse($conn, $sql);
oci_bind_by_name($stid, ":codigo", $codigo);
oci_execute($stid);

$dados = [];
while ($row = oci_fetch_assoc($stid)) {
    $dados[] = $row;
}

echo json_encode($dados);
oci_free_statement($stid);
oci_close($conn);
