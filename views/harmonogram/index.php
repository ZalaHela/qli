<?php

require_once("../views/template/index.php");

class Harmonogram extends Module{

  public $templates = array(
    "single"=>"../views/harmonogram/single.phtml",
    "list"=>"../views/harmonogram/list.phtml",
  );

  public $queries = array(
    "list" => "SELECT harmonogram.id as id,harmonogram.nazwa as nazwa,od,do,platne, czas_na_zaplacenie, CONCAT(taryfa.nazwa ,' (', wartosc,'zl)') as taryfa FROM harmonogram left outer join taryfa on (harmonogram.taryfaid = taryfa.id)",
    "delete" => "DELETE FROM harmonogram WHERE id=?",
    "single" => "SELECT * FROM harmonogram WHERE id=?",
    "update" => "UPDATE harmonogram SET nazwa=?, od=?, do=?, platne=?, czas_na_zaplacenie=?, taryfaid=? WHERE id=?",
    "insert" => "INSERT INTO harmonogram SET nazwa=?, od=?, do=?, platne=?, czas_na_zaplacenie=?, taryfaid=?"
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


  function custom_handle(){
    global $_POST;
    global $_GET;
    global $db;

    if(isset($_GET["action"]) && $_GET["action"]=="nalicz"){
      $data = date_parse($_POST["evaldate"]);

      $stmt = $db->prepare("SELECT harmonogram.id as id, taryfa.wartosc as wartosc, harmonogram.od as od, harmonogram.do as do FROM harmonogram left outer join taryfa on (taryfa.id = harmonogram.taryfaid) where harmonogram.od <= '".$_POST["evaldate"]."' and harmonogram.do >= '".$_POST["evaldate"]."'");

      $stmt->execute(array());
      $harmonograms = $stmt->fetchAll(PDO::FETCH_ASSOC);

      $ilosc = 0;
      foreach($harmonograms as $h){
        $stmt = $db->prepare("SELECT * from person where harmonogramid=?");
        $stmt->execute(array($h["id"]));
        $all = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach($all as $s){
          $numer_tr = "ZA ".$data["year"]."-".$data["month"]."-id-".$s["id"];
          $stmt = $db->prepare("SELECT * from platnosci where pid=? and nr_tr=?");
          $stmt->execute(array($s["id"],$numer_tr));
          $platnosci = $stmt->fetchAll(PDO::FETCH_ASSOC);
          if(count($platnosci)==0){
            $stmt = $db->prepare("INSERT INTO platnosci SET pid=?, descr=?, nr_tr=?, amt=?, tdate=?");
            $nazwa_tr = "Oplata za ".$data["year"]."-".$data["month"];
            $arr=array($s["id"],$nazwa_tr,$numer_tr,-$h["wartosc"], $_POST["evaldate"]);
            $stmt->execute($arr);
            $ilosc++;
          }else{
          }
        }
      }

     if($ilosc==0){
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

