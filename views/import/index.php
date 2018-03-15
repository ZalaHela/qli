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


  function custom_handle(){
    global $_POST;
    global $_GET;

    if(isset($_GET["action"]) && $_GET["action"]=="upload"){
      echo "yey! upload!";
      $target_dir = "../uploads/";
      $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
      if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
      } else {
        echo "Sorry, there was an error uploading your file.";
      }

      $csv = array_map('str_getcsv', file( $target_file ));
      print_r($csv);

      return true;
    }

    return false;
  }
 
   
};

?>

