<?php
$db = new PDO('mysql:host=localhost;dbname=apka;charset=utf8mb4', 'root', '');
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

require("../views/head.phtml");
require("../views/nav.phtml");


require("../views/studenci/index.php");
require("../views/grupy/index.php");
require("../views/platnosci/index.php");
$studenci = new Studenci(); 
$grupy = new Grupy(); 
$platnosci = new Payments(); 

if(isset($_GET["m"]) && $_GET["m"] == "studenci"){
	$studenci->handle();
}else if(isset($_GET["m"]) && $_GET["m"] == "grupy"){
	$grupy->handle();
}else if(isset($_GET["m"]) && $_GET["m"] == "payments"){
	$platnosci->handle();
}



require("../views/foot.phtml");
?>