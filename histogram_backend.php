<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Cache-Control: no-cache, no-store, must-revalidate");

require_once("dbroconf.php");
require_once("helper_php/time.php");

foreach ($db_ro_confs as $conf) {
  if ($_GET["fs"] == $conf["fs"]) {
    $conn = new mysqli($conf["host"], $conf["user"], $conf["pass"], $conf["db"]);

    $sql = array();

    array_push($sql, "SELECT COUNT(*) AS number,SUM(size) AS size FROM ENTRIES WHERE ENTRIES.type='file' AND ENTRIES.owner LIKE BINARY '" . $_GET["owner"] . "' AND ENTRIES.last_access>" . $thirtydaysago);
    array_push($sql, "SELECT COUNT(*) AS number,SUM(size) AS size FROM ENTRIES WHERE ENTRIES.type='file' AND ENTRIES.owner LIKE BINARY '" . $_GET["owner"] . "' AND ENTRIES.last_access<=" . $thirtydaysago . " AND ENTRIES.last_access>" . $sixtydaysago);
    array_push($sql, "SELECT COUNT(*) AS number,SUM(size) AS size FROM ENTRIES WHERE ENTRIES.type='file' AND ENTRIES.owner LIKE BINARY '" . $_GET["owner"] . "' AND ENTRIES.last_access<=" . $sixtydaysago . " AND ENTRIES.last_access>" . $ninetydaysago);
    array_push($sql, "SELECT COUNT(*) AS number,SUM(size) AS size FROM ENTRIES WHERE ENTRIES.type='file' AND ENTRIES.owner LIKE BINARY '" . $_GET["owner"] . "' AND ENTRIES.last_access<=" . $ninetydaysago . " AND ENTRIES.last_access>" . $sixmonthsago);
    array_push($sql, "SELECT COUNT(*) AS number,SUM(size) AS size FROM ENTRIES WHERE ENTRIES.type='file' AND ENTRIES.owner LIKE BINARY '" . $_GET["owner"] . "' AND ENTRIES.last_access<=" . $sixmonthsago . " AND ENTRIES.last_access>" . $oneyearago);
    array_push($sql, "SELECT COUNT(*) AS number,SUM(size) AS size FROM ENTRIES WHERE ENTRIES.type='file' AND ENTRIES.owner LIKE BINARY '" . $_GET["owner"] . "' AND ENTRIES.last_access<=" . $oneyearago . " AND ENTRIES.last_access>" . $twoyearsago);
    array_push($sql, "SELECT COUNT(*) AS number,SUM(size) AS size FROM ENTRIES WHERE ENTRIES.type='file' AND ENTRIES.owner LIKE BINARY '" . $_GET["owner"] . "' AND ENTRIES.last_access<=" . $twoyearsago . " AND ENTRIES.last_access>" . $threeyearsago);
    array_push($sql, "SELECT COUNT(*) AS number,SUM(size) AS size FROM ENTRIES WHERE ENTRIES.type='file' AND ENTRIES.owner LIKE BINARY '" . $_GET["owner"] . "' AND ENTRIES.last_access<=" . $threeyearsago . " AND ENTRIES.last_access>" . $fiveyearsago);
    array_push($sql, "SELECT COUNT(*) AS number,SUM(size) AS size FROM ENTRIES WHERE ENTRIES.type='file' AND ENTRIES.owner LIKE BINARY '" . $_GET["owner"] . "' AND ENTRIES.last_access<=" . $fiveyearsago);
    $selectsql = $sql[$_GET["id"]];

    $outp = "[";
    $result = $conn->query($selectsql) or trigger_error($conn->error."[$selectsql]");

    $results_age = array("<30 Days", "30 - 60 Days", "60 - 90 Days", "90 Days - 6 Months", "6 Months - 1 Year", "1 - 2 Years", "2 - 3 Years", "3 - 5 Years", ">5 Years");

    $rs = $result->fetch_array(MYSQLI_ASSOC);

    if ($outp != "[") {$outp .= ",";}
    $outp .= '{"File_System":"'             . $conf["fs"] . '",';
    $outp .= '"Age":"'                      . $results_age[$_GET["id"]] . '",';
    $outp .= '"Number_of_Files":'           . (is_null($rs["number"]) ? 0 : $rs["number"])  . ',';
    $outp .= '"Percentage_of_Total_Files":' . "0,";
    $outp .= '"Size_of_Files":'             . (is_null($rs["size"]) ? 0 : $rs["size"])  . ',';
    $outp .= '"Percentage_of_Total_Size":' . "0}";
    $outp .= "]";
    echo($outp);
  }
}
?>
