<?php
putenv('NLS_LANG=AMERICAN_AMERICA.AL32UTF8'); //* Formatação UTF-8
header('Content-Type: application/json; charset=utf-8'); // Certifique-se de que o PHP avisa ao navegador sobre o charset
ini_set('default_charset', 'UTF-8');                    // Configure o charset padrão para UTF-8 no PHP


// $db_host = "192.168.1.2";
// $database = "oracle";
// $db_user = "AUTOWEB";
// $db_pwd = "EBAD";
// $db_host = "192.168.6.7";
// $db_name = "INFO_NEW.uni540";
// $db_user = "AUTOWEB";
// $db_pwd  = "EBAD";
$db_host = "10.10.10.23";
$db_name = "INFO";
$db_user = "AUTOWEB";
$db_pwd  = "EBAD";

// Conexão direta com Easy Connect
$db_name = "(DESCRIPTION =
  (ADDRESS = (PROTOCOL = TCP)(HOST = 10.10.10.23)(PORT = 1521))
  (CONNECT_DATA =
    (SERVICE_NAME = info)
  )
)";
// $db_name = " (DESCRIPTION =
//     (ADDRESS_LIST =
//       (ADDRESS = (PROTOCOL = TCP)(HOST = 192.168.1.2)(PORT = 1521))
//     )
//     (CONNECT_DATA =
//       (SERVICE_NAME = INFO)
//     )
//   ) ";

//!TESTE
// $db_name = " (DESCRIPTION =
//     (ADDRESS_LIST =
//       (ADDRESS = (PROTOCOL = TCP)(HOST = 192.168.1.2)(PORT = 1521))
//     )
//     (CONNECT_DATA =
//       (SERVICE_NAME = teste)
//     )
//   ) ";

$pconnect = 0;


?>
