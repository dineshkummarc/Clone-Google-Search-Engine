<?php
include("../config.php");
if(isset($_POST["src"])){
    $query = $con->prepare("UPDATE image_search_index SET broken = '1' WHERE image_url = :imageUrl ");
    $query->bindParam(":image_url", $_POST["src"]);
    $query->execute();
}else{
    echo "no image passed to page";
}