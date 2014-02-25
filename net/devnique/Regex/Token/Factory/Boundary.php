<?php

namespace net\devnique\Regex\Token\Factory {

	use net\devnique\Regex;

	class Boundary extends Regex\Token\Factory {

		public function create($token) {

		}

		public static function getToken() {

			return "\b";

		}


	}

}

?>
