<?php

require("../views/template/index.php");

class Studenci extends Module{

  public $templates = array(
    "single"=>"../views/studenci/single.phtml",
    "list"=>"../views/studenci/list.phtml",
  );

  public $queries = array(
    "list_common" => "SELECT person.id as id, 
      CONCAT(last,' ',first) as last, phone, 
      sum(platnosci.amt) as saldo,  
      sum(CASE WHEN platnosci.amt < 0 AND (now() <= date_add(tdate, interval (20-1) day))  
       THEN 0
       ELSE platnosci.amt END) as zaleglosc, date(dataurodzenia) as dataurodzenia 
      FROM person left outer join platnosci ON (platnosci.pid = person.id) ",
    "list_all" => "GROUP by person.id",
    "list_by_harmonogram" => "WHERE harmonogramid=? GROUP by person.id ",
    "delete" => "DELETE FROM person WHERE id=?",
    "single" => "SELECT * FROM person WHERE id=?",
    "update" => "UPDATE person SET first=?, last=?, phone=?, groupid=?, odkiedy=?, harmonogramid=?, dataurodzenia=? WHERE id=?",
    "insert" => "INSERT INTO person SET first=?, last=?, phone=?, groupid=?, odkiedy=?, harmonogramid=?, dataurodzenia=?"
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
      if(isset($_GET["harmonogramid"])){
        $this->queries["list"] = $this->queries["list_common"].$this->queries["list_by_harmonogram"];
      }else{
        $this->queries["list"] = $this->queries["list_common"].$this->queries["list_all"];
      }
    }
    return $data;
  }

};

?>

