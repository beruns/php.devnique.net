<?php

namespace net\devnique\Regex\Token\Factory {

	use net\devnique\Regex;

	class MetaChar extends Regex\Token\Factory {

		protected static $token = [
			"^", "$", "|", "(", ")", "[", "]", "/"
		];

		public function create($token) {
			
			if($token == "/" && !$this->pattern->compileData("root")) {
				return new Regex\Token\Root($this->pattern);
			}
		
			if($token == "(" && !$this->pattern->compileData("current_set")) {
				return new Regex\Token\Group($this->pattern);
			}

			if($token == "[") {
				return new Regex\Token\Set($this->pattern);
			}

			if($token == "$") {

				$this->pattern->nextByte();
				$c = $this->pattern->nextByte();
				$this->pattern->movePos(-2);	
		
				if(Regex\Token::isNumeric($c)) {
					return new Regex\Token\Backref($this->pattern);	
				}

				return new Regex\Token\EOS($this->pattern);


			}

			return new Regex\Token\MetaChar($this->pattern);
			

		}

		static public function getToken() {

			return static::$token;

		}

	}

}

?>
