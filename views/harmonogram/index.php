<?php

require_once("../views/template/index.php");

class Harmonogram extends Module{

  public $templates = array(
    "single"=>"../views/harmonogram/single.phtml",
    "list"=>"../views/harmonogram/list.phtml",
  );

  public $queries = array(
    "list" => "SELECT harmonogram.id as id,harmonogram.nazwa as nazwa,od,do,platne, czas_na_zaplacenie, oplaty_miesieczne FROM harmonogram",
    "delete" => "DELETE FROM harmonogram WHERE id=?",
    "single" => "SELECT * FROM harmonogram WHERE id=?",
    "update" => "UPDATE harmonogram SET nazwa=?, od=?, do=?, platne=?, czas_na_zaplacenie=?, oplaty_miesieczne=? WHERE id=?",
    "insert" => "INSERT INTO harmonogram SET nazwa=?, od=?, do=?, platne=?, czas_na_zaplacenie=?, oplaty_miesieczne=?"
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

  function validate(){
    global $_POST;
    global $_GET;

   if(isset($_GET["action"]) && ( $_GET["action"]=="add_new" || $_GET["action"]=="update") ){
      if(!isset($_POST["nazwa"]) || $_POST["nazwa"] == ""){
        print_error("Nazwa nie moze być pusta"); 
        return false;
      }
    }

    return true;
  }


  function custom_handle(){
    global $_POST;
    global $_GET;
    global $db;

    if(isset($_GET["action"]) && $_GET["action"]=="nalicz"){
      // data naliczania
      $data_evaluacji = $_POST["evaldate"]."-01";
      $data = date_parse($data_evaluacji);
      $data_Ym = date("Y-m", strtotime($data_evaluacji));
      // select aktywne harmonogramy 
      $stmt = $db->prepare("SELECT 
                              harmonogram.id as id, 
                              harmonogram.od as od, 
                              harmonogram.do as do,
                              harmonogram.oplaty_miesieczne as oplaty_miesieczne
                            FROM harmonogram 
                            where 
                              harmonogram.od <= '".$data_evaluacji."' 
                              and harmonogram.do >= '".$data_evaluacji."'");

      $stmt->execute(array());
      $harmonograms = $stmt->fetchAll(PDO::FETCH_ASSOC);

      $ilosc = 0;
      // dla kazdego z nich 
      foreach($harmonograms as $h){
        // ludzie nalezacy do tego harmonogramu
        $stmt = $db->prepare("SELECT * from person where harmonogramid=?");
        $stmt->execute(array($h["id"]));
        $all = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $oplaty = json_decode($h["oplaty_miesieczne"]);
        $oplata_do_naliczenia = 0;
        foreach($oplaty as $key=>$value){
          if (date("Y-m", strtotime($value->sqld)) == $data_Ym){
              $oplata_do_naliczenia = $value->value;
              break;
          }
        }
        //echo  $data_Ym."=".$oplata_do_naliczenia;
        foreach($all as $s){
          $numer_tr = "ZA ".$data["year"]."-".$data["month"]."-id-".$s["id"];
          $stmt = $db->prepare("SELECT * from platnosci where pid=? and nr_tr=?");
          $stmt->execute(array($s["id"],$numer_tr));
          $platnosci = $stmt->fetchAll(PDO::FETCH_ASSOC);
          if(count($platnosci)==0){
            $stmt = $db->prepare("INSERT INTO platnosci SET pid=?, descr=?, nr_tr=?, amt=?, tdate=?");
            $nazwa_tr = "Oplata za ".$data["year"]."-".$data["month"];
            $arr=array($s["id"],$nazwa_tr,$numer_tr,-$oplata_do_naliczenia, $data_evaluacji);
            $stmt->execute($arr);
            $ilosc++;
          }else{
          }
        }
      }

     if(count($harmonograms)==0){
        $type="success";
        $message="Żaden harmonogram nie obejmuje tej daty.";
        require("../views/alerts/alert.phtml");
     }
     else if($ilosc==0){
        $type="success";
        $message="Obciążenia zostały już naliczone dla tego miesiąca.";
        require("../views/alerts/alert.phtml");
      }else{
        $type="success";
        $message="Obciążenia zostały naliczone dla ".$ilosc." studentów";
        require("../views/alerts/alert.phtml");        
      }
    }

    return false;
  }
 

};

?>

