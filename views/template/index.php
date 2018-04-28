<?php

function serialise($arr){
  $x=array();
  foreach($arr as $key=>$value){
    array_push($x,$key."=".$value);
  }
  return join("&",$x);
}

function sorted_column($colname, $vname){
  global $_GET;
  // wez kopie
  $getcpy = $_GET;

  $link = "";
  $getcpy["action"]="list";

  if(isset($getcpy["orderby"]) && $getcpy["orderby"] == $vname && !isset($getcpy["DESC"])){
    $getcpy["DESC"]=true;
  }else{
    unset($getcpy["DESC"]);
    $getcpy["orderby"]=$vname;
  }
 
  $link=serialise($getcpy);

  echo '<a href="?'.$link.'">';
  echo $colname;
  if(isset($_GET["orderby"]) && $_GET["orderby"] == $vname){
    echo ' <span class="glyphicon glyphicon-sort-by-attributes'. (isset($_GET["DESC"])? "-alt":"") .'"></span>';    
  }
  echo '</a>';
}

function get($data, $d, $val=""){
    if(isset($data[$d])) echo $data[$d];
    else echo $val;
}

function get_sl($data, $d, $val=""){
    if(isset($data[$d])) echo addslashes($data[$d]);
    else echo $val;
}

function get_html($data, $d, $val=""){
    if(isset($data[$d])) echo htmlspecialchars($data[$d]);
    else echo $val;
}

function action(){
    global $_GET;
    if(isset($_GET["id"])) echo "update&id=".$_GET["id"];
    else echo "add_new";
}

function print_error($msg){
  $type="danger";
  $message=$msg;
  require("../views/alerts/alert.phtml");  
}

function print_success($msg){
  $type="success";
  $message=$msg;
  require("../views/alerts/alert.phtml");  
}

class Module {

  public $templates = array(
    "single"=>"../views/template/single.phtml",
    "list"=>"../views/template/list.phtml",
  );

  public $queries = array(
    "list" => "SELECT * FROM groupa",
    "delete" => "DELETE FROM groupa WHERE id=?",
    "single" => "SELECT * FROM groupa WHERE id=?",
    "update" => "UPDATE groupa SET name=?, descr=? WHERE id=?",
    "insert" => "INSERT INTO groupa SET name=?, descr=?"
  );

  function get_all(){
    global $db;
    global $_POST;
    global $_GET;
    $orderby = "";
    if(isset($_GET["orderby"]) && $_GET["orderby"]!=""){
      $orderfld = $_GET["orderby"];
      $orderby = " ORDER BY ".$orderfld." ";
      if(isset($_GET["DESC"])) $orderby = $orderby." DESC";
    }
    $query=$this->queries["list"].$orderby;
    //print_r($query);
    $filt = $this->get_values($this->queries["list"], $_POST,  $_GET, $_SESSION);
    $stmt = $db->prepare($query);
    $stmt->execute($filt);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $data;
  }

  function delete_by_id($id){
    global $db;
    $stmt = $db->prepare($this->queries["delete"]);    
    return $stmt->execute(array($id));
  }


  function get_by_id($id){
    global $db;
    $stmt = $db->prepare($this->queries["single"]);
    $stmt->execute(array($id));
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if(count($data)>0)return $data[0];
    else return null;
  }

  function get_values($query, $post, $get, $sess){
    $arr = array_merge($post,$get,$sess);
    $matches="";
    preg_match_all('/\s*([^= ,]+)=\?\s*/', $query, $matches, PREG_SET_ORDER);
    $values = array();
    foreach($matches as $m) array_push($values, isset($arr[$m[1]])?$arr[$m[1]]:NULL); 
    return $values;
  }

  //////////

  function h_update(){
    global $db;
    global $_POST;
    global $_GET;
    global $_SESSION;

    
    
    /* UPDATE */
    $stmt = $db->prepare($this->queries["update"]);
    $ok = $stmt->execute($this->get_values($this->queries["update"], $_POST,  $_GET, $_SESSION));
    if($ok){
      $type="success";
      $message="Zmodyfikowano";
      require("../views/alerts/alert.phtml");
      $this->h_list();
    }else{
      $type="danger";
      $message="Błąd, modyfikacja nie powiodła się.";
      require("../views/alerts/alert.phtml");
    }
  }
 
  function h_add_new(){
    global $db;
    global $_POST;
    global $_GET;

    $stmt = $db->prepare($this->queries["insert"]);
    $ok = $stmt->execute($this->get_values($this->queries["insert"], $_POST, $_GET, $_SESSION));
    //$stmt->debugDumpParams();

    if($ok){
      $type="success";
      $message="Stworzono";
      require("../views/alerts/alert.phtml");
      $this->h_list();
    }else{
      $type="danger";
      $message="Błąd, nie można dodać";
      require("../views/alerts/alert.phtml");
    }
  }

  function load_data($data, $action){
    return $data;
  }

  function h_create_form(){
    global $_POST;
    global $_GET;
    $data = array();
    $data=$this->load_data($data, "create_form");
    require($this->templates["single"]);
  }

  function h_edit($d=null){
    global $_POST;
    global $_GET;
    $data=null;
    if(isset($d)) $data = $d;
    else if(isset($_GET["id"]))$data=$this->get_by_id($_GET["id"]);
    $data=$this->load_data($data, "edit");
    require($this->templates["single"]);
  }

  function h_remove(){
    global $_POST;
    global $_GET;
    $ok = $this->delete_by_id($_GET["id"]);
    if($ok){
      $type="success";
      $message="Usunięto";
      require("../views/alerts/alert.phtml");
      $this->h_list();
    }else{
      $type="danger";
      $message="Błąd, nie można usunąć.";
      require("../views/alerts/alert.phtml");
    }
  }

  function h_list(){
    $data = array();
    $data = $this->load_data($data, "list");
    $data["table"] = $this->get_all();
    require($this->templates["list"]);
  } 

  function custom_handle(){
    global $_POST;
    global $_GET;
    return false;
  }

  function validate(){
    return true;
  }

  function handle(){
    global $_POST;
    global $_GET;


    if(isset($_GET["action"]) && $_GET["action"]=="update"){
      $this->h_update();
    }
    else if(isset($_GET["action"]) && $_GET["action"]=="add_new"){
      if(!$this->validate()){
        $this->h_edit($_POST);
        return;
      }
      $this->h_add_new();
    }
    else if(isset($_GET["action"]) && $_GET["action"]=="create_form"){
      $this->h_create_form();
    }
    else if(isset($_GET["action"]) && isset($_GET["id"]) && $_GET["action"]=="edit"){
      $this->h_edit();
    }
    else if(isset($_GET["action"]) && isset($_GET["id"]) && $_GET["action"]=="remove"){
      $this->h_remove();
    }else if($this->custom_handle()){
      // here goes custom (specific) handler
    }else{
      // default handler
      $this->h_list();
    }
  }
};




?>

