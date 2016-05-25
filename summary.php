<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once("dbroconf.php");
require_once("time.php");

foreach ($db_ro_confs as $conf) {
  if ($_GET["fs"] == $conf["fs"]) {
    $conn = new mysqli($conf["host"], $conf["user"], $conf["pass"], $conf["db"]);

    $usersql = "SELECT COUNT(DISTINCT owner) AS Users from ACCT_STAT";
    $numfilesql = "SELECT SUM(count) AS Files FROM ACCT_STAT WHERE type='file'";
    $sizesql = "SELECT SUM(size) AS Size FROM ACCT_STAT";
    $oldnumfilesql = "SELECT COUNT(*) AS Oldfiles FROM ENTRIES WHERE type='file' AND last_mod<" . $sixmonthsago;
    $oldsizesql = "SELECT SUM(size) AS Oldsize FROM ENTRIES WHERE ENTRIES.last_mod<" . $sixmonthsago;

    echo $usersql . "\n";
    echo $numfilesql . "\n";
    echo $sizesql . "\n";
    echo $oldnumfilesql . "\n";
    echo $oldsizesql . "\n";

    $outp = "[";
    $userresult = $conn->query($usersql) or trigger_error($conn->error."[$usersql]");
    $numfilesresult = $conn->query($numfilesql) or trigger_error($conn->error."[$numfilesql]");
    $sizeresult = $conn->query($sizesql) or trigger_error($conn->error."[$sizesql]");
    $oldnumfilesresult = $conn->query($oldnumfilesql) or trigger_error($conn->error."[$oldnumfilesql]");
    $oldsizeresult = $conn->query($oldsizesql) or trigger_error($conn->error."[$oldsizesql]");
  
    $userrs = $userresult->fetch_array(MYSQLI_ASSOC);
    $numfilesrs = $numfilesresult->fetch_array(MYSQLI_ASSOC);
    $sizers = $sizeresult->fetch_array(MYSQLI_ASSOC);
    $oldnumfilesrs = $oldnumfilesresult->fetch_array(MYSQLI_ASSOC);
    $oldsizers = $oldsizeresult->fetch_array(MYSQLI_ASSOC);
  
    if ($outp != "[") {$outp .= ",";}
    $outp .= '{"File_System":"'       . $conf["fs"] . '",';
    $outp .= '"Number_of_Users":'     . (is_null($userrs["Users"]) ? 0 : $userrs["Users"])  . ',';
    $outp .= '"Number_of_Files":'     . (is_null($numfilesrs["Files"]) ? 0 : $numfilesrs["Files"])  . ',';
    $outp .= '"Total_Size":'          . (is_null($sizers["Size"]) ? 0 : $sizers["Size"])  . ',';
    $outp .= '"Number_of_Old_Files":' . (is_null($oldnumfilesrs["Oldfiles"]) ? 0 : $oldnumfilesrs["Oldfiles"])  . ',';
    $outp .= '"Size_of_Old_Files":'   . (is_null($oldsizers["Oldsize"]) ? 0 : $oldsizers["Oldsize"])  . ',';
    $percentold = sprintf('%0.2f', ($oldsizers["Oldsize"]/$sizers["Size"])*100);
    $outp .= '"Percent_Old_Space":'   . (is_null($percentold) ? 0: $percentold) . '}';
    $outp .= "]";
    echo($outp);
  }
}
?>
