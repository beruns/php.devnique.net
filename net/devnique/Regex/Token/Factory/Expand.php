<?php

namespace net\devnique\Regex\Token\Factory {

	use net\devnique\Regex;

	class Expand extends Regex\Token\Factory {

		protected static $token = [
		
			":upper:" => "A-Z",
			":lower:" => "a-z",
			":digit:" => "0-9",
			":alpha:" => ":lower::upper:",
			":alnum:" => ":alpha::digit:",
			":word:" => ":alnum:_",
			":blank:" => " \t",
			":space:" => ":blank:\r\n\v\f",

			"\d" => "[:digit:]",
			"^\d" => "[^:digit:]",
			"\D" => "^\d",
			"^\D" => "\d",
			"\w" => "[:word:]",
			"^\w" => "[^:word:]",
			"\W" => "^\w",
			"^\W" => "\w",
			"\s" => "[:space:]",
			"^\s" => "[^:space:]",
			"\S" => "^\s",
			"^\S" => "\s",

			//"*" => "{0,}",
			//"+" => "{1,}",
			//"?" => "{0, 1}",
			/*"}?" => false, // lazy greediness
			"}+" => false */

		];

		public function create($token) {

			if(static::$token[$token]) {

				$this->pattern->remove(strlen($token));
				$this->pattern->insert(static::$token[$token]);
		
			} else {

				$this->pattern->movePos(strlen($token));

			}

			return $this->pattern->nextToken();


		}

		public static function getToken() {

			return array_keys(static::$token);

		}


	}

}

?>
