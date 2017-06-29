<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Cache-Control: no-cache, no-store, must-revalidate");

require_once("dbroconf.php");
require_once("helper_php/time.php");

foreach ($db_ro_confs as $conf) {
  if ($_GET["fs"] == $conf["fs"]) {
    $conn = new mysqli($conf["host"], $conf["user"], $conf["pass"], $conf["db"]);

    if ($conf["version"] == "rbhv2") {
      $detailsql = "SELECT full.owner, full.countsum, full.sizesum, old.oldcountsum, old.oldsizesum ";
      $detailsql .= "FROM (select owner,sum(count) AS countsum, sum(size) AS sizesum from ACCT_STAT WHERE type='file' GROUP BY owner) AS full ";
      $detailsql .= "LEFT JOIN (select owner,count(id) AS oldcountsum,sum(size) AS oldsizesum from ENTRIES WHERE type='file' AND last_access<";
      $detailsql .= $sixmonthsago . " GROUP BY owner) AS old ON full.owner = old.owner";
    } else {
      $detailsql = "SELECT full.owner, full.countsum, full.sizesum, old.oldcountsum, old.oldsizesum ";
      $detailsql .= "FROM (select uid AS owner, sum(count) AS countsum, sum(size) AS sizesum from ACCT_STAT WHERE type='file' GROUP BY owner) AS full ";
      $detailsql .= "LEFT JOIN (select uid AS owner, count(id) AS oldcountsum, sum(size) AS oldsizesum from ENTRIES WHERE type='file' AND last_access<";
      $detailsql .= $sixmonthsago . " GROUP BY owner) AS old ON full.owner = old.owner";
    }

    $outp = "[";
    $detailresult = $conn->query($detailsql) or trigger_error($conn->error."[$detailsql]");
  
    while($detailrs = $detailresult->fetch_array(MYSQLI_ASSOC)) {
      if ($outp != "[") {$outp .= ",";}
      $outp .= '{"File_System":"'       . $conf["fs"] . '",';
      $outp .= '"Owner":"'              . $detailrs["owner"] . '",';
      $outp .= '"Number_of_Files":'     . (is_null($detailrs["countsum"]) ? 0 : $detailrs["countsum"])  . ',';
      $outp .= '"Size_of_Files":'       . (is_null($detailrs["sizesum"]) ? 0 : $detailrs["sizesum"])  . ',';
      $outp .= '"Number_of_Old_Files":' . (is_null($detailrs["oldcountsum"]) ? 0 : $detailrs["oldcountsum"])  . ',';
      $outp .= '"Size_of_Old_Files":'   . (is_null($detailrs["oldsizesum"]) ? 0 : $detailrs["oldsizesum"])  . '}';
    }
    $outp .= "]";
    echo($outp);
  }
}
?>
