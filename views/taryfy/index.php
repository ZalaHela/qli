<?php

require_once("../views/template/index.php");

class Taryfa extends Module{

  public $templates = array(
    "single"=>"../views/taryfy/single.phtml",
    "list"=>"../views/taryfy/list.phtml",
  );

  public $queries = array(
    "list" => "SELECT * FROM taryfa",
    "delete" => "DELETE FROM taryfa WHERE id=?",
    "single" => "SELECT * FROM taryfa WHERE id=?",
    "update" => "UPDATE taryfa SET nazwa=?, wartosc=? WHERE id=?",
    "insert" => "INSERT INTO taryfa SET nazwa=?, wartosc=?"
  );

 

};

?>

