<?php 
 include 'src/php//Main.php';

 $main = new Main();

 header("content-type: application/json");

 echo $main->getResponse();

?>