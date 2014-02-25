<?php

namespace net\devnique\Regex\Token\Factory {

	use net\devnique\Regex;

	class Char extends Regex\Token\Factory {

		public function create($token) {

			return new Regex\Token\Char($this->pattern);

		}

		public static function getToken() {

			return Regex\Token\Factory::IS_DEFAULT;

		}


	}

}

?>
