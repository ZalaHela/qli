<?php

require("../views/template/index.php");

class Studenci extends Module{

  public $templates = array(
    "single"=>"../views/studenci/single.phtml",
    "list"=>"../views/studenci/list.phtml",
  );

  public $queries = array(
    "list" => "SELECT person.id as id, first, last, phone, groupa.name as grupa,  sum(peyments.amt) as saldo  FROM person LEFT OUTER JOIN groupa ON (person.groupid=groupa.id) left outer join peyments ON (peyments.pid = person.id) GROUP by person.id ",
    "list_by_group" => "SELECT person.id as id, first, last, phone, groupa.name as grupa,  sum(peyments.amt) as saldo  FROM person JOIN groupa ON (person.groupid=groupa.id) left outer join peyments ON (peyments.pid = person.id) WHERE groupid=? GROUP by person.id ",
    "delete" => "DELETE FROM person WHERE id=?",
    "single" => "SELECT * FROM person WHERE id=?",
    "update" => "UPDATE person SET first=?, last=?, phone=?, groupid=? WHERE id=?",
    "insert" => "INSERT INTO person SET first=?, last=?, phone=?, groupid=?"
  );
 
  function load_data($data, $action){
    global $db;
    if($action == "create_form" || $action == "edit" ){
      $stmt = $db->prepare("SELECT * FROM groupa order by name");
      $stmt->execute(array());
      $data["grupa"] = array(
        "all" => $stmt->fetchAll(PDO::FETCH_ASSOC),
        "active" => isset($data["groupid"])?$data["groupid"]:NULL
      );
    }else if($action == "list"){
       if(isset($_GET["groupid"]))$this->queries["list"] = $this->queries["list_by_group"];
    }
    return $data;
  }

};

?>

