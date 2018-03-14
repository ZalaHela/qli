<?php

require_once("../views/template/index.php");

class Import extends Module{

  public $templates = array(
    "single"=>"../views/import/single.phtml",
    "list"=>"../views/import/list.phtml",
  );

  public $queries = array(
    "list" => "SELECT * FROM timport",
    "list_by_group" => "SELECT person.id as id, first, last, phone, groupa.name as grupa,  sum(peyments.amt) as saldo  FROM person JOIN groupa ON (person.groupid=groupa.id) left outer join peyments ON (peyments.pid = person.id) WHERE groupid=? GROUP by person.id ",
    "delete" => "DELETE FROM person WHERE id=?",
    "single" => "SELECT * FROM person WHERE id=?",
    "update" => "UPDATE person SET first=?, last=?, phone=?, groupid=? WHERE id=?",
    "insert" => "INSERT INTO person SET first=?, last=?, phone=?, groupid=?"
  );
 
   
};

?>

