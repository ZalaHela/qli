<?php

require_once("../views/template/index.php");

class Payments extends Module{

  public $templates = array(
    "single"=>"../views/platnosci/single.phtml",
    "list"=>"../views/platnosci/list.phtml",
  );

  public $queries = array(
    "list" => "SELECT *, 
      (CASE WHEN amt < 0 THEN date_add(tdate, interval (20-1) day) ELSE 'N/A' END) as tprzedawnienie, 
      (CASE WHEN amt < 0 
        THEN 
         (CASE WHEN (now() > date_add(tdate, interval (20-1) day)) THEN 'TAK' ELSE 'NIE' END) 
        ELSE 'N/A'
      END) as przedawnienie 
      FROM platnosci where pid=? ORDER BY platnosci.id desc",
    "delete" => "DELETE FROM platnosci WHERE id=?",
    "single" => "SELECT * FROM platnosci WHERE id=?",
    "update" => "UPDATE platnosci SET descr=?, tdate=?, amt=?, pid=? WHERE id=?",
    "insert" => "INSERT INTO platnosci SET descr=?, tdate=?, amt=?, pid=?"
  );

  function load_data($data, $action){
    global $db;
    global $_GET;

    if($action == "list" ){
      $stmt = $db->prepare("SELECT * FROM person WHERE id=?");
      $stmt->execute(array($_GET["pid"]));
      $person = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $data["person"] = $person[0]["last"]." ".$person[0]["first"];

      $stmt = $db->prepare("SELECT sum(amt) as saldo FROM platnosci WHERE pid=?");
      $stmt->execute(array($_GET["pid"]));
      $saldo = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $data["saldo"] = $saldo[0]["saldo"];
      
    }
    return $data;
  }

  function custom_handle(){
    global $_POST;
    global $_GET;
    global $db;

    if(isset($_GET["action"]) && $_GET["action"]=="stress"){
      echo "STRESS-TEST...<br>";

      for($j=0; $j < 300; $j++){
        $stmt = $db->prepare("INSERT INTO person SET id=?, first=?, last=?, phone=?, groupid=?, odkiedy=?, harmonogramid=?, dataurodzenia=?");
        $stmt->execute(array(2000+$j,"stress-".$j ,"stress",(234+$j)+"",0,"",0,""));

        for($i=0; $i < 30; $i++){
          $stmt = $db->prepare("INSERT INTO platnosci SET descr=?, tdate=?, amt=?, pid=?");
          $stmt->execute(array("stress","",1,2000+$j));
        }

        echo "person " . $j . " <br>";
      }

      
       
      return true;
    }

    return false;
  }

};

?>

