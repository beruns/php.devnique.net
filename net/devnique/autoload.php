<?php

namespace net\devnique {

	spl_autoload_register(function($class) {

		if(strpos($class, __NAMESPACE__ . "\\")  !== false) {

			$class = str_ireplace(__NAMESPACE__ . "\\" , "", $class);

		}

		$class = str_ireplace("\\", DIRECTORY_SEPARATOR , $class);
		$class = __DIR__ . DIRECTORY_SEPARATOR . $class . ".php";

		if(file_exists($class)) {	

			require_once($class);
	
		}

	});

}

?>
