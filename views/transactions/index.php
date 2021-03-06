<?php

require_once("../views/template/index.php");

class Transactions extends Module{

  public $templates = array(
    "single"=>"../views/transactions/single.phtml",
    "list"=>"../views/transactions/list.phtml",
  );

  public $queries = array(
    "count" => "SELECT count(*) count FROM platnosci", 
    "list" => "SELECT platnosci.id as id, platnosci.descr as descr, platnosci.amt as amt, platnosci.tdate as tdate,
                      CONCAT(person.last,' ',person.first) as person, person.id as pid
               FROM platnosci 
               LEFT OUTER JOIN person on (platnosci.pid = person.id)
               ORDER BY platnosci.id desc
              ",
    "delete" => "DELETE FROM platnosci WHERE id=?",
    "single" => "SELECT * FROM platnosci WHERE id=?",
    "update" => "UPDATE platnosci SET descr=?, tdate=?, amt=?, pid=? WHERE id=?",
    "insert" => "INSERT INTO platnosci SET descr=?, tdate=?, amt=?, pid=?"
  );

  function validate(){
    global $_POST;
    global $_GET;

    if(isset($_GET["action"]) && $_GET["action"]=="add_new"){
      if(isset($_POST["name"]) && $_POST["name"] == ""){
        print_error("Nazwa nie moze być pusta");
        return false;
      }
    }

    return true;
  }
 
  function xcustom_handle(){
    global $_POST;
    global $_GET;
    global $db;

    if(isset($_GET["action"]) && $_GET["action"]=="list"){
     
      $data = array();
      if(isset($this->queries["count"])){
        $stmt = $db->prepare($this->queries["count"]);
        $stmt->execute();
        $countres = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $total = $countres[0]["count"];

        $data["pagination"] = null;
        $maxperpage=5;
        if( $total > $maxperpage ) {
          $data["pagination"]=array();
          $data["pagination"]["count"]=ceil($total/$maxperpage);
          $data["pagination"]["size"] = $maxperpage;
          $data["pagination"]["active"] = isset($_GET["page"])?($_GET["page"]):0;
          $this->queries["list"] .= " LIMIT ".$maxperpage." OFFSET ". ($data["pagination"]["active"]*$maxperpage);
        }
      }

      $stmt = $db->prepare($this->queries["list"]);
      $stmt->execute();
      $tab = $stmt->fetchAll(PDO::FETCH_ASSOC);

      $data["table"] = $tab;
      require($this->templates["list"]);
      return true;
    }

    return false;
  }

};

?>

