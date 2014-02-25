<?php


	require_once("autoload.php");
	$p = new net\devnique\Regex\Pattern("/a{2}+/");
	$s = new net\devnique\StreamBuffer("aaaaaaa");
	echo $p->match($s);

?>
