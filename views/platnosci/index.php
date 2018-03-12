<?php

require_once("../views/template/index.php");

class Payments extends Module{

  public $templates = array(
    "single"=>"../views/platnosci/single.phtml",
    "list"=>"../views/platnosci/list.phtml",
  );

  public $queries = array(
    "list" => "SELECT * FROM peyments where pid=?",
    "delete" => "DELETE FROM peyments WHERE id=?",
    "single" => "SELECT * FROM peyments WHERE id=?",
    "update" => "UPDATE peyments SET descr=?, tdate=?, amt=?, pid=? WHERE id=?",
    "insert" => "INSERT INTO peyments SET descr=?, tdate=?, amt=?, pid=?"
  );

  function load_data($data, $action){
    global $db;
    global $_GET;

    if($action == "list" ){
      $stmt = $db->prepare("SELECT * FROM person WHERE id=?");
      $stmt->execute(array($_GET["pid"]));
      $person = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $data["person"] = $person[0]["last"]." ".$person[0]["first"];

      $stmt = $db->prepare("SELECT sum(amt) as saldo FROM peyments WHERE pid=?");
      $stmt->execute(array($_GET["pid"]));
      $saldo = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $data["saldo"] = $saldo[0]["saldo"];
      
    }
    return $data;
  }

};

?>

