<?php
include_once("polaroider.php");

//Generate the polaroid form the GET-strings...
$polaroidi = new Polaroid($_GET['photo'], "255,255,255", $_GET['angle'], $_GET['text'], 0, 0);
$polaroidi->CreatePolaroid();
	
?>

