<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once("dbroconf.php");
require_once("helper_php/time.php");

foreach ($db_ro_confs as $conf) {
  if ($_GET["fs"] == $conf["fs"]) {
    $conn = new mysqli($conf["host"], $conf["user"], $conf["pass"], $conf["db"]);

    $zerodaysql = "SELECT COUNT(*) AS number,SUM(size) AS size FROM ENTRIES WHERE ENTRIES.type='file' AND ENTRIES.owner LIKE BINARY '" . $_GET["owner"] . "' AND ENTRIES.last_access>" . $thirtydaysago;
    $thirtydaysql = "SELECT COUNT(*) AS number,SUM(size) AS size FROM ENTRIES WHERE ENTRIES.type='file' AND ENTRIES.owner LIKE BINARY '" . $_GET["owner"] . "' AND ENTRIES.last_access<=" . $thirtydaysago . " AND ENTRIES.last_access>" . $sixtydaysago;
    $sixtydaysql = "SELECT COUNT(*) AS number,SUM(size) AS size FROM ENTRIES WHERE ENTRIES.type='file' AND ENTRIES.owner LIKE BINARY '" . $_GET["owner"] . "' AND ENTRIES.last_access<=" . $sixtydaysago . " AND ENTRIES.last_access>" . $ninetydaysago;
    $ninetydaysql = "SELECT COUNT(*) AS number,SUM(size) AS size FROM ENTRIES WHERE ENTRIES.type='file' AND ENTRIES.owner LIKE BINARY '" . $_GET["owner"] . "' AND ENTRIES.last_access<=" . $ninetydaysago . " AND ENTRIES.last_access>" . $sixmonthsago;
    $sixmonthsql = "SELECT COUNT(*) AS number,SUM(size) AS size FROM ENTRIES WHERE ENTRIES.type='file' AND ENTRIES.owner LIKE BINARY '" . $_GET["owner"] . "' AND ENTRIES.last_access<=" . $sixmonthsago . " AND ENTRIES.last_access>" . $oneyearago;
    $oneyearsql = "SELECT COUNT(*) AS number,SUM(size) AS size FROM ENTRIES WHERE ENTRIES.type='file' AND ENTRIES.owner LIKE BINARY '" . $_GET["owner"] . "' AND ENTRIES.last_access<=" . $oneyearago . " AND ENTRIES.last_access>" . $twoyearsago;
    $twoyearsql = "SELECT COUNT(*) AS number,SUM(size) AS size FROM ENTRIES WHERE ENTRIES.type='file' AND ENTRIES.owner LIKE BINARY '" . $_GET["owner"] . "' AND ENTRIES.last_access<=" . $twoyearsago . " AND ENTRIES.last_access>" . $threeyearsago;
    $threeyearsql = "SELECT COUNT(*) AS number,SUM(size) AS size FROM ENTRIES WHERE ENTRIES.type='file' AND ENTRIES.owner LIKE BINARY '" . $_GET["owner"] . "' AND ENTRIES.last_access<=" . $threeyearsago . " AND ENTRIES.last_access>" . $fiveyearsago;
    $fiveyearsql = "SELECT COUNT(*) AS number,SUM(size) AS size FROM ENTRIES WHERE ENTRIES.type='file' AND ENTRIES.owner LIKE BINARY '" . $_GET["owner"] . "' AND ENTRIES.last_access<=" . $fiveyearsago;

    $outp = "[";
    $zerodayresult = $conn->query($zerodaysql) or trigger_error($conn->error."[$zerodaysql]");
    $thirtydayresult = $conn->query($thirtydaysql) or trigger_error($conn->error."[$thirtydaysql]");
    $sixtydayresult = $conn->query($sixtydaysql) or trigger_error($conn->error."[$sixtydaysql]");
    $ninetydayresult = $conn->query($ninetydaysql) or trigger_error($conn->error."[$ninetydaysql]");
    $sixmonthresult = $conn->query($sixmonthsql) or trigger_error($conn->error."[$sixmonthsql]");
    $oneyearresult = $conn->query($oneyearsql) or trigger_error($conn->error."[$oneyearsql]");
    $twoyearresult = $conn->query($twoyearsql) or trigger_error($conn->error."[$twoyearsql]");
    $threeyearresult = $conn->query($threeyearsql) or trigger_error($conn->error."[$threeyearsql]");
    $fiveyearresult = $conn->query($fiveyearsql) or trigger_error($conn->error."[$fiveyearsql]");

    $results = array();
    $results_age = array("<30 Days", "30 - 60 Days", "60 - 90 Days", "90 Days - 6 Months", "6 Months - 1 Year", "1 - 2 Years", "2 - 3 Years", "3 - 5 Years", ">5 Years");

    array_push($results, $zerodayresult->fetch_array(MYSQLI_ASSOC));
    array_push($results, $thirtydayresult->fetch_array(MYSQLI_ASSOC));
    array_push($results, $sixtydayresult->fetch_array(MYSQLI_ASSOC));
    array_push($results, $ninetydayresult->fetch_array(MYSQLI_ASSOC));
    array_push($results, $sixmonthresult->fetch_array(MYSQLI_ASSOC));
    array_push($results, $oneyearresult->fetch_array(MYSQLI_ASSOC));
    array_push($results, $twoyearresult->fetch_array(MYSQLI_ASSOC));
    array_push($results, $threeyearresult->fetch_array(MYSQLI_ASSOC));
    array_push($results, $fiveyearresult->fetch_array(MYSQLI_ASSOC));

    foreach (array_keys($results) as $key) {
      if ($outp != "[") {$outp .= ",";}
      $outp .= '{"File_System":"'             . $conf["fs"] . '",';
      $outp .= '"Age":"'                      . $results_age[$key] . '",';
      $outp .= '"Number_of_Files":'           . (is_null($results[$key]["number"]) ? 0 : $results[$key]["number"])  . ',';
      $outp .= '"Percentage_of_Total_Files":' . "0,";
      $outp .= '"Size_of_Files":'             . (is_null($results[$key]["size"]) ? 0 : $results[$key]["size"])  . ',';
      $outp .= '"Percentage_of_Total_Size":' . "0}";
    }
    $outp .= "]";
    echo($outp);
  }
}
?>
