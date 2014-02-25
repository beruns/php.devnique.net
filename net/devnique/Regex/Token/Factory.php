<?php

namespace net\devnique\Regex\Token {

	use net\devnique\Regex;

	abstract class Factory {

		const IS_DEFAULT = "";

		protected $pattern = null;

		public function __construct(Regex\Pattern $pattern) {

			$this->pattern = $pattern;

		}

		abstract public function create($token);
		abstract public static function getToken();

	}

}

?>
