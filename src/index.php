<?php
$db = new PDO('mysql:host=localhost;dbname=apka;charset=utf8mb4', 'root', '');
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

require("../views/head.phtml");
require("../views/nav.phtml");


require("../views/studenci/index.php");
require("../views/grupy/index.php");
require("../views/platnosci/index.php");
require("../views/import/index.php");
require("../views/taryfy/index.php");
require("../views/harmonogram/index.php");
require("../views/transactions/index.php");


$studenci = new Studenci(); 
$grupy = new Grupy(); 
$platnosci = new Payments(); 
$import = new Import();
$taryfy = new Taryfa();
$harmonogram = new Harmonogram();
$transactions = new Transactions();

if(isset($_GET["m"]) && $_GET["m"] == "studenci"){
	$studenci->handle();
}else if(isset($_GET["m"]) && $_GET["m"] == "grupy"){
	$grupy->handle();
}else if(isset($_GET["m"]) && $_GET["m"] == "payments"){
	$platnosci->handle();
}else if(isset($_GET["m"]) && $_GET["m"] == "import"){
	$import->handle();
}else if(isset($_GET["m"]) && $_GET["m"] == "taryfy"){
	$taryfy->handle();
}else if(isset($_GET["m"]) && $_GET["m"] == "harmonogram"){
	$harmonogram->handle();
}else if(isset($_GET["m"]) && $_GET["m"] == "transactions"){
	$transactions->handle();
}else{
	$_GET["m"] = "studenci";
	$studenci->handle();
}



require("../views/foot.phtml");
?>