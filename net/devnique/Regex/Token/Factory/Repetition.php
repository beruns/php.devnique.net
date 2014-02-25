<?php

namespace net\devnique\Regex\Token\Factory {

	use net\devnique\Regex;

	class Repetition extends Regex\Token\Factory {

		protected static $token = [
			"{", "*", "+", "?"
		];

		protected $min = -1;
		protected $max = -1;

		public function create($token) {

			$pos = $this->pattern->getPos();
			$this->min = $this->max = -1;

			if(!($token = $this->pattern->compileData("current_token"))) {

				$pattern->setPos($pos);
				return false;

			}

			$c = $this->pattern->nextByte();
			/* Explicit declaration */
			if($c == "{") {

				if(!$this->parseExplicit()) {

					$this->pattern->setPos($pos);
					return false;

				}

			} elseif($c == "*") {

				$this->min = 0;

			} elseif($c == "+") {

				$this->min = 1;
	
			} elseif($c == "?") {

				$this->min = 0;
				$this->max = 1;
			}
			
			$token->setRepetition($this->min, $this->max, $this->parseGreediness());
			return $this->pattern->nextToken();
			

		}

		protected function parseExplicit() {

			$tmp = "";

			/* see if pattern is like {\d+[,\d*]} */
			while(!$this->pattern->EOF()) {

				$c = $this->pattern->nextByte();

				switch($c) {

					case '}':

						if($tmp != "") $this->max = (int) $tmp;

						if($this->min == -1) {

							if($this->max == -1) {
			
								// Pattern was {}	
								return false;

							}

							$this->min = $this->max;

						}
	
						return true;
					
					break;

					case ',':

						if($tmp == "")  {

							// pattern was {,
							return false;

						}

						$this->min = (int) $tmp;
						$tmp = "";			
	
					break;

					case ' ':

						// skip whitespace in Repetition 
						continue;

					break;

					default:

						if(!is_numeric($c)) {

							// min or max included a non numeric char 
							return false;							

						}					

						$tmp .= $c;

					break;

				}

			}
		} 

		protected function parseGreediness() {

			$g = $this->pattern->nextByte();

			if($g == "?") {

				$g = Regex\Token::MATCH_LAZY;
							
			} elseif($g == "+") {

				$g = Regex\Token::MATCH_POSSESSIVE;
							
			} else {

				$this->pattern->movePos(-1);
				$g = Regex\Token::MATCH_GREEDY;

			}

			return $g;

		}

		public static function getToken() {

			return static::$token;

		}


	}

}

?>
