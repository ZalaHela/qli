<?php

require("../views/template/index.php");

class Studenci extends Module{

  public $templates = array(
    "single"=>"../views/studenci/single.phtml",
    "list"=>"../views/studenci/list.phtml",
  );

  public $queries = array(
    "list" => "SELECT person.id as id, CONCAT(last,' ',first) as last, phone, groupa.name as grupa,  sum(platnosci.amt) as saldo  FROM person LEFT OUTER JOIN groupa ON (person.groupid=groupa.id) left outer join platnosci ON (platnosci.pid = person.id) GROUP by person.id ",
    "list_by_group" => "SELECT person.id as id, CONCAT(last,' ',first) as last, phone, groupa.name as grupa,  sum(platnosci.amt) as saldo  FROM person JOIN groupa ON (person.groupid=groupa.id) left outer join platnosci ON (platnosci.pid = person.id) WHERE groupid=? GROUP by person.id ",
    "list_by_harmonogram" => "SELECT person.id as id, CONCAT(last,' ',first) as last, phone, groupa.name as grupa,  sum(platnosci.amt) as saldo  FROM person JOIN groupa ON (person.groupid=groupa.id) left outer join platnosci ON (platnosci.pid = person.id) WHERE harmonogramid=? GROUP by person.id ",
    "delete" => "DELETE FROM person WHERE id=?",
    "single" => "SELECT * FROM person WHERE id=?",
    "update" => "UPDATE person SET first=?, last=?, phone=?, groupid=?, odkiedy=?, harmonogramid=? WHERE id=?",
    "insert" => "INSERT INTO person SET first=?, last=?, phone=?, groupid=?, odkiedy=?, harmonogramid=?"
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

      $stmt = $db->prepare("SELECT harmonogram.id as id, CONCAT(harmonogram.nazwa ,' (', taryfa.wartosc,'zl)') as nazwa FROM harmonogram left outer join taryfa on (harmonogram.taryfaid = taryfa.id) order by nazwa");
      $stmt->execute(array());
      $data["harmonogram"] = array(
        "all" => $stmt->fetchAll(PDO::FETCH_ASSOC),
        "active" => isset($data["harmonogramid"])?$data["harmonogramid"]:NULL
      );
    }else if($action == "list"){
      if(isset($_GET["groupid"]))$this->queries["list"] = $this->queries["list_by_group"];
      if(isset($_GET["harmonogramid"]))$this->queries["list"] = $this->queries["list_by_harmonogram"];
    }
    return $data;
  }

};

?>

