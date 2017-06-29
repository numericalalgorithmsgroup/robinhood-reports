<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Cache-Control: no-cache, no-store, must-revalidate");

require_once("dbroconf.php");

foreach ($db_ro_confs as $conf) {
  if ($_GET["fs"] == $conf["fs"]) {
    $conn = new mysqli($conf["host"], $conf["user"], $conf["pass"], $conf["db"]);
    # TODO - change this from checking dirattr to checking largedir class - Robinhood has already tagged those large directories
    $bigdirsql = "SELECT parent_id, COUNT(*) AS dirattr FROM NAMES GROUP BY parent_id HAVING dirattr>=50000";

    $outp = "[";
    $bigdirresult = $conn->query($bigdirsql) or trigger_error($conn->error."[$bigdirsql]");
  
    while($bigdirrs = $bigdirresult->fetch_array(MYSQLI_ASSOC)) {
      if ($outp != "[") {$outp .= ",";}
      if ($conf["version"] == "rbhv2") {
        $mdsql = "SELECT this_path(parent_id,name) AS path, owner, gr_name FROM ENTRIES LEFT JOIN NAMES ON ENTRIES.id=NAMES.id WHERE ENTRIES.id='" . $bigdirrs["parent_id"] . "'";
      } else {
        $mdsql = "SELECT this_path(parent_id,name) AS path, uid AS owner, gid AS gr_name FROM ENTRIES LEFT JOIN NAMES ON ENTRIES.id=NAMES.id WHERE ENTRIES.id='" . $bigdirrs["parent_id"] . "'";
      }
      $mdresult = $conn->query($mdsql) or trigger_error($conn->error."[$mdsql]");
      $mdrs = $mdresult->fetch_array(MYSQLI_ASSOC);

      $outp .= '{"Directory":"'         . str_replace('0x200000007:0x1:0x0', $conf["fs"], $mdrs["path"]) . '",';
      $outp .= '"Number_of_Files":'     . (is_null($bigdirrs["dirattr"]) ? 0 : $bigdirrs["dirattr"])  . ',';
      $outp .= '"Owner":"'              . $mdrs["owner"] . '",';
      $outp .= '"Group":"'              . $mdrs["gr_name"] . '"}';
    }
    $outp .= "]";
    echo($outp);
  }
}
?>
