<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once("dbroconf.php");
require_once("helper_php/time.php");
require_once("helper_php/dataconvert.php");

foreach ($db_ro_confs as $conf) {
  if ($_GET["fs"] == $conf["fs"]) {
    $conn = new mysqli($conf["host"], $conf["user"], $conf["pass"], $conf["db"]);

    $detailsql = "SELECT full.owner, full.countsum, full.sizesum, old.oldcountsum, old.oldsizesum ";
    $detailsql .= "FROM (select owner,sum(count) AS countsum, sum(size) AS sizesum from ACCT_STAT WHERE type='file' GROUP BY owner) AS full ";
    $detailsql .= "LEFT JOIN (select owner,count(id) AS oldcountsum,sum(size) AS oldsizesum from ENTRIES WHERE type='file' AND last_mod<";
    $detailsql .= $sixmonthsago . " GROUP BY owner) AS old ON full.owner = old.owner";

    $outp = "[";
    $detailresult = $conn->query($detailsql) or trigger_error($conn->error."[$detailsql]");
  
    while($detailrs = $detailresult->fetch_array(MYSQLI_ASSOC)) {
      $percentold = sprintf('%0.2f', ($detailrs["oldsizesum"]/$detailrs["sizesum"])*100);
      if ($outp != "[") {$outp .= ",";}
      $outp .= '{"File_System":"'       . $conf["fs"] . '",';
      $outp .= '"Owner":"'              . $detailrs["owner"] . '",';
      $outp .= '"Number_of_Files":'     . (is_null($detailrs["countsum"]) ? 0 : $detailrs["countsum"])  . ',';
      $outp .= '"Size_of_Files":'       . (is_null($detailrs["sizesum"]) ? 0 : $detailrs["sizesum"])  . ',';
      $outp .= '"Number_of_Old_Files":' . (is_null($detailrs["oldcountsum"]) ? 0 : $detailrs["oldcountsum"])  . ',';
      $outp .= '"Size_of_Old_Files":'   . (is_null($detailrs["oldsizesum"]) ? 0 : $detailrs["oldsizesum"])  . ',';
      $outp .= '"Percent_Old_Space":'   . (is_null($percentold) ? 0: $percentold) . '}';
    }
    $outp .= "]";
    echo($outp);
  }
}
?>
