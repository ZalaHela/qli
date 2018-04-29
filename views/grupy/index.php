<?php

require_once("../views/template/index.php");

class Grupy extends Module{

  public $templates = array(
    "single"=>"../views/grupy/single.phtml",
    "list"=>"../views/grupy/list.phtml",
  );

  public $queries = array(
    "list" => "SELECT * FROM groupa ORDER BY id",
    "delete" => "DELETE FROM groupa WHERE id=?",
    "single" => "SELECT * FROM groupa WHERE id=?",
    "update" => "UPDATE groupa SET name=?, descr=? WHERE id=?",
    "insert" => "INSERT INTO groupa SET name=?, descr=?"
  );

  function validate(){
    global $_POST;
    global $_GET;

    if(isset($_GET["action"]) && ( $_GET["action"]=="add_new" || $_GET["action"]=="update") ){
      if(!isset($_POST["name"]) || $_POST["name"] == ""){
        print_error("Nazwa nie moze być pusta");
        return false;
      }
    }

    return true;
  }

};

?>

