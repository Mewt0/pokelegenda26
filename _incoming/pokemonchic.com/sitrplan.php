<?php
declare(strict_types=1);
require_once ("include/function/config.php");
require_once ("include/function/db3.php");
$db = db($config);

if(!empty($_GET['hash']) && $_GET['hash'] === 'drthu9erndfpasdd0'){
  insert('prob',array('ewr'=>rand(1,50)));
}


?>