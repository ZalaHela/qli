<?php

require_once("../views/template/index.php");

class Import extends Module{

  public $templates = array(
    "single"=>"../views/import/single.phtml",
    "list"=>"../views/import/list.phtml",
  );

  public $queries = array(
    "list" => "SELECT timport.id as id, timport.data, timport.tytul, timport.kwota, CONCAT(person.last,' ',person.first,' (',person.phone,')') as osoba FROM timport left outer join person on (person.id = timport.personid)",
    "single" => "SELECT * FROM timport WHERE id=?",
    "delete" => "DELETE FROM timport WHERE id=?",
    "update" => "UPDATE timport SET tytul=?, data=?, kwota=?, personid=? WHERE id=?",
    "insert" => "INSERT INTO timport SET first=?, last=?, phone=?, groupid=?"
  );


  function upload_file(){
    global $_POST;
    global $_GET;
    global $_FILES;
    global $db;

    $target_dir = "../uploads/";
    $this->target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $this->target_file)) {
      return true;
    } else {
      return false;
    }
    return false;
  }


  function parese_uploaded_csv(){
    global $_POST;
    global $_GET;
    global $_FILES;
    global $db;
    $csv = array();
    $i = 0;
    $handle = fopen($this->target_file, "r");

    if ($handle) {
        while (($line = fgets($handle)) !== false) {
            // process the line read.
            if($i>18){
              $line = str_replace('\'', '', $line);
              $line = str_replace('"', '', $line);
              $line = trim($line);
              
              $res=iconv('ISO-8859-2','UTF-8',$line);

              if($res){
                $line = $res;
              }

              $row = explode(';',$line);
              if(!isset($row[8])) continue;
              $v = (float) $row[8];
              if($v>0){
                try{
                  $matches=array();
                  preg_match_all("/([0-9]{3}.?[0-9]{3}.?[0-9]{3})/", $row[3] ,$matches);

                  $personid = NULL;
                  if(count($matches) > 0 && count($matches[0])>0){
                    $int = filter_var($matches[0][0], FILTER_SANITIZE_NUMBER_INT);
                    $lookup_by_phone = true;
                    $phone = $int;
                    $stmt = $db->prepare("SELECT * from person where phone=?");
                    $stmt->execute(array($phone));
                    $person = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if(count($person) > 0) $personid = $person[0]["id"];
                  }
                  
                  $stmt = $db->prepare("INSERT INTO timport SET nr_transakcji=?, data=?, kontrahent=?, tytul=?, nr_rachunku=?, kwota=?, personid=?, importdetails=?");
                  $stmt->execute(array($row[7]." ".$row[0], $row[0], $row[2], $row[3], $row[4], $row[8], $personid, NULL));
                  array_push($csv, $row);
                }catch(Exception $e){

                }
              }
            }   
            $i++;
        }

        fclose($handle);
    } 
  }


  function custom_handle(){
    global $_POST;
    global $_GET;
    global $_FILES;
    global $db;

    if(isset($_GET["action"]) && $_GET["action"]=="upload"){
     
      if ($this->upload_file()) {
        $type="success";
        $message="Zaimportowano plik.";
        require("../views/alerts/alert.phtml");
      } else {
        $type="danger";
        $message="Błąd, import nie powiódł się.";
        require("../views/alerts/alert.phtml");
        return true;
      }

      $this->parese_uploaded_csv();
      //require_once("../views/import/dumptable.phtml");

      $this->h_list();
      return true;
    } else if(isset($_GET["action"]) && $_GET["action"]=="accept"){
     
      $stmt = $db->prepare("SELECT * FROM timport WHERE id=?");
      $stmt->execute(array($_GET["id"]));
      $imp = $stmt->fetchAll(PDO::FETCH_ASSOC);

      if(count($imp) > 0){
        $val = $imp[0];
        try{
          $stmt = $db->prepare("INSERT INTO platnosci SET pid=?, amt=?, nr_tr=?, descr=?, tdate=?");
          $stmt->execute(array($val["personid"], $val["kwota"], $val["nr_transakcji"], "Przelew na konto: '".$val["tytul"]."'", $val["data"]));
          $stmt = $db->prepare("DELETE from timport WHERE id=?");
          $stmt->execute(array($_GET["id"]));
          $type="success";
          $message="Zapisano wpłatę '".$val["tytul"]."'";
          require("../views/alerts/alert.phtml");
        }catch(Exception $e){
          $stmt = $db->prepare("SELECT * FROM platnosci WHERE nr_tr=?");
          $stmt->execute(array( $val["nr_transakcji"] ));
          if(count($stmt->fetchAll(PDO::FETCH_ASSOC)) > 0){
             $type="danger";
             $message="Błąd, naliczono już kwotę dla tego przelewu: '".$val["tytul"]."'";
             require("../views/alerts/alert.phtml");
          }
        }
      }

      $this->h_list();
      return true;
    }

    

    return false;
  }


  function load_data($data, $action){
    global $db;
    if($action == "create_form" || $action == "edit" ){
      $stmt = $db->prepare("SELECT id, CONCAT(last,' ',first, ' (', phone ,')') as name FROM person order by last");
      $stmt->execute(array());
      $data["person"] = array(
        "all" => $stmt->fetchAll(PDO::FETCH_ASSOC),
        "active" => isset($data["personid"])?$data["personid"]:NULL
      );

    } 

    return $data;
  }
 
   
};

?>

