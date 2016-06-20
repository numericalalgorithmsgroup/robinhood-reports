<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once("dbroconf.php");
require_once("helper_php/time.php");

foreach ($db_ro_confs as $conf) {
  if ($_GET["fs"] == $conf["fs"]) {
    $conn = new mysqli($conf["host"], $conf["user"], $conf["pass"], $conf["db"]);

    $largedirsql = "SELECT ENTRIES.id AS id, ENTRIES.owner AS owner, ENTRIES.gr_name AS gr_name, this_path(NAMES.parent_id, NAMES.name) AS path FROM ENTRIES LEFT JOIN NAMES ON ENTRIES.id=NAMES.id WHERE ENTRIES.type='dir' AND ENTRIES.owner LIKE BINARY '" . $_GET["owner"] . "'";

    $outp = "[";
    $largedirresult = $conn->query($largedirsql) or trigger_error($conn->error."[$largedirsql]");
  
    while($largedirrs = $largedirresult->fetch_array(MYSQLI_ASSOC)) {
      $dirsizesql = "SELECT SUM(ENTRIES.size) AS dirsize, COUNT(*) AS count FROM NAMES LEFT JOIN ENTRIES ON NAMES.id=ENTRIES.id WHERE NAMES.parent_id='" . $largedirrs["id"] . "' AND ENTRIES.type='file'";
      $dirsizeresult = $conn->query($dirsizesql) or trigger_error($conn->error."[$dirsizesql]");
      $dirsizers = $dirsizeresult->fetch_array(MYSQLI_ASSOC);

      $olddirsizesql = "SELECT SUM(ENTRIES.size) AS olddirsize, COUNT(*) AS oldcount FROM NAMES LEFT JOIN ENTRIES ON NAMES.id=ENTRIES.id WHERE NAMES.parent_id='" . $largedirrs["id"] . "' AND ENTRIES.type='file' AND ENTRIES.last_access<" . $sixmonthsago;
      $olddirsizeresult = $conn->query($olddirsizesql) or trigger_error($conn->error."[$olddirsizesql]");
      $olddirsizers = $olddirsizeresult->fetch_array(MYSQLI_ASSOC);

      # Not very useful to display directories with no files in them
      if ($dirsizers["dirsize"] != 0) {
        if ($outp != "[") {$outp .= ",";}
        $outp .= '{"Directory":"'              . str_replace('0x200000007:0x1:0x0', $conf["fs"], $largedirrs["path"]) . '",';
        $outp .= '"Size_of_Files":'            . (is_null($dirsizers["dirsize"]) ? 0 : $dirsizers["dirsize"])  . ',';
        $outp .= '"Number_of_Files":'          . (is_null($dirsizers["count"]) ? 0 : $dirsizers["count"])  . ',';
        $outp .= '"Size_of_Old_Files":'        . (is_null($olddirsizers["olddirsize"]) ? 0 : $olddirsizers["olddirsize"])  . ',';
        $outp .= '"Number_of_Old_Files":'      . (is_null($olddirsizers["oldcount"]) ? 0 : $olddirsizers["oldcount"])  . '}';
      }
    }
    $outp .= "]";
    echo($outp);
  }
}
?>
