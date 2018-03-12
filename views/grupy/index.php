<?php

require_once("../views/template/index.php");

class Grupy extends Module{

  public $templates = array(
    "single"=>"../views/grupy/single.phtml",
    "list"=>"../views/grupy/list.phtml",
  );

  public $queries = array(
    "list" => "SELECT * FROM groupa order by name",
    "delete" => "DELETE FROM groupa WHERE id=?",
    "single" => "SELECT * FROM groupa WHERE id=?",
    "update" => "UPDATE groupa SET name=?, descr=? WHERE id=?",
    "insert" => "INSERT INTO groupa SET name=?, descr=?"
  );

};

?>

