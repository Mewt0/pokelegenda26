<?php
header('Content-Type: text/css;charset=utf-8');
include('css/browser.php');
include('css/browser.class.php');
$browser = browser();
include('css/browsers/'.$browser.'.class.php');
if(!isset($_GET['file'])) die('/* get parametr "file" is not set */');
if(!preg_match('/^[a-zA-Z0-9_\.]+$/i',$_GET['file'])) die('/* get parametr "file" is not valid */');
$file = 'css/css/'.$_GET['file'].'.css';
if(!file_exists($file)) die('CSS file "'.$file.'" not find');
$cache = 'css/cache/'.$browser.'_'.$_GET['file'].'.css';
if(file_exists($cache)){
	if(filemtime($file)>=filemtime($cache)){
		$br = new $browser($file, $cache);
	}
}else{
	$br = new $browser($file, $cache);
}
print file_get_contents($cache);
?>