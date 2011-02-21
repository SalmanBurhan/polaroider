<?php
    // Include class to get,rotate and polaroid picture
    include_once("class/polaroider/polaroider.php");

    $picurl = $_GET['url'];
    $picangle = $_GET['angle'];
    $pictext = $_GET['text'];


    $polaroid = new Polaroid($picurl,"", $picangle, $pictext, 0, 0) ;

    $polaroid->CreatePolaroid();
?>
