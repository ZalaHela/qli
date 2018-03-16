<?php

require_once("../views/template/index.php");

class Harmonogram extends Module{

  public $templates = array(
    "single"=>"../views/harmonogram/single.phtml",
    "list"=>"../views/harmonogram/list.phtml",
  );

  public $queries = array(
    "list" => "SELECT harmonogram.id as id,harmonogram.nazwa as nazwa,od,do,platne, czas_na_zaplacenie, CONCAT(taryfa.nazwa ,'(', wartosc,'zł)') as taryfa FROM harmonogram left outer join taryfa on (harmonogram.taryfaid = taryfa.id)",
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
      print_r($_POST);
      print_r($_GET);
      print_r(date_parse($_POST["evaldate"]));

      echo "<hr>";
      $stmt = $db->prepare("SELECT harmonogram.id as id, taryfa.wartosc as wartosc, harmonogram.od as od, harmonogram.do as do FROM harmonogram left outer join taryfa on (taryfa.id = harmonogram.taryfaid) where harmonogram.od <= '".$_POST["evaldate"]."' and harmonogram.do >= '".$_POST["evaldate"]."'");

      $stmt->execute(array());
      $harmonograms = $stmt->fetchAll(PDO::FETCH_ASSOC);

      foreach($harmonograms as $h){
        echo "<hr>";
        print_r($h);
        echo "<br>";
        echo "<br>";
        $stmt = $db->prepare("SELECT * from person where harmonogramid=?");
        $stmt->execute(array($h["id"]));
        $all = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach($all as $s){
          print_r($s);
          echo "<br>";
          $numer_tr = "ZA ".$data["year"]."-".$data["month"]."-id-".$s["id"];
          $stmt = $db->prepare("SELECT * from platnosci where pid=? and nr_tr=?");
          $stmt->execute(array($s["id"],$numer_tr));

          $platnosci = $stmt->fetchAll(PDO::FETCH_ASSOC);

          if(count($platnosci)==0){
            echo "plac dziwko!";
            $stmt = $db->prepare("INSERT INTO platnosci SET pid=?, descr=?, nr_tr=?, amt=?, tdate=?");
            $nazwa_tr = "Opłata za ".$data["year"]."-".$data["month"];
            $arr=array($s["id"],$nazwa_tr,$numer_tr,-$h["wartosc"], $_POST["evaldate"]);
            $stmt->execute($arr);
          }else{
            echo "chuj juz ociazony";
          }
        }

      }

      return true;
    }

    return false;
  }
 

};

?>

