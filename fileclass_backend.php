<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Cache-Control: no-cache, no-store, must-revalidate");

require_once("dbroconf.php");

foreach ($db_ro_confs as $conf) {
  if ($_GET["fs"] == $conf["fs"]) {
    $conn = new mysqli($conf["host"], $conf["user"], $conf["pass"], $conf["db"]);

    $fileclasssql = "SELECT id FROM ENTRIES WHERE ENTRIES.release_class LIKE BINARY '" . $_GET["page"] . "_files' AND ENTRIES.owner LIKE BINARY '" . $_GET["owner"] . "'";

    $outp = "[";
    $fileclassresult = $conn->query($fileclasssql) or trigger_error($conn->error."[$fileclasssql]");
  
    while($fileclassrs = $fileclassresult->fetch_array(MYSQLI_ASSOC)) {
      if ($outp != "[") {$outp .= ",";}
      $mdsql = "SELECT this_path(parent_id,name) AS path, size, owner, gr_name FROM ENTRIES LEFT JOIN NAMES ON ENTRIES.id=NAMES.id WHERE ENTRIES.id='" . $fileclassrs["id"] . "'";
      $mdresult = $conn->query($mdsql) or trigger_error($conn->error."[$mdsql]");
      $mdrs = $mdresult->fetch_array(MYSQLI_ASSOC);

      $outp .= '{"File":"'         . str_replace($conf["rootid"], $conf["fs"], $mdrs["path"]) . '",';
      $outp .= '"Size":'           . (is_null($mdrs["size"]) ? 0 : $mdrs["size"])  . ',';
      $outp .= '"Owner":"'         . $mdrs["owner"] . '",';
      $outp .= '"Group":"'         . $mdrs["gr_name"] . '"}';
    }
    $outp .= "]";
    echo($outp);
  }
}
?>
