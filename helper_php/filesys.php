<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once("../dbroconf.php");

$outp = "[";
foreach ($db_ro_confs as $conf) {
  if ($outp != "[") {$outp .= ",";}
  $outp .= '"' . $conf["fs"] . '"';
}
$outp .= "]";
echo($outp);
?>
