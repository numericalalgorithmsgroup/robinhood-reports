<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once("../dbroconf.php");

foreach ($db_ro_confs as $conf) {
  if ($_GET["fs"] == $conf["fs"]) {
    $conn = new mysqli($conf["host"], $conf["user"], $conf["pass"], $conf["db"]);

    $usersql = "SELECT DISTINCT owner from ACCT_STAT";

    $outp = "[";
    $userresult = $conn->query($usersql) or trigger_error($conn->error."[$usersql]");
  
    while ($userrs = $userresult->fetch_array(MYSQLI_ASSOC)) {
      if ($outp != "[") {$outp .= ",";}
      $outp .= '"' . $userrs["owner"] . '"';
    }
    $outp .= "]";
    echo($outp);
  }
}
?>
