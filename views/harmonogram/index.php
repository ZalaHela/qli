<?php

require_once("../views/template/index.php");

class Harmonogram extends Module{

  public $templates = array(
    "single"=>"../views/harmonogram/single.phtml",
    "list"=>"../views/harmonogram/list.phtml",
  );

  public $queries = array(
    "list" => "SELECT harmonogram.id as id,harmonogram.nazwa as nazwa,od,do,platne, czas_na_zaplacenie, CONCAT(taryfa.nazwa ,'(', wartosc,'zł)') as taryfa FROM harmonogram left outer join taryfa on (harmonogram.taryfaid = taryfa.id)",
    "delete" => "DELETE FROM harmonogram WHERE id=?",
    "single" => "SELECT * FROM harmonogram WHERE id=?",
    "update" => "UPDATE harmonogram SET nazwa=?, od=?, do=?, platne=?, czas_na_zaplacenie=?, taryfaid=? WHERE id=?",
    "insert" => "INSERT INTO harmonogram SET nazwa=?, od=?, do=?, platne=?, czas_na_zaplacenie=?, taryfaid=?"
  );

  function load_data($data, $action){
    global $db;
    if($action == "create_form" || $action == "edit" ){
      $stmt = $db->prepare("SELECT * FROM taryfa order by nazwa");
      $stmt->execute(array());
      $data["taryfa"] = array(
        "all" => $stmt->fetchAll(PDO::FETCH_ASSOC),
        "active" => isset($data["taryfaid"])?$data["taryfaid"]:NULL
      );
    }
    return $data;
  }

};

?>

