<?php

namespace net\devnique\Regex\Token\Factory {

	use net\devnique\Regex;

	class SetOp extends Regex\Token\Factory {

		protected static $token = [
			"-", "&&"
		];

		public function create($token) {
			
			$set = $this->pattern->compileData("current_set");
			
			/* Only process this, if we are inside a set */
			if($set) {

				/* Get Current Token */
				$curr = $this->pattern->compileData("current_token");
				/* Store current Position */
				$pos = $this->pattern->getPos();
				/* Move past current token */
				$this->pattern->movePos(strlen($token));
				/* Fetch next token */
				$next = $this->pattern->nextToken();
	
				switch($token) {
	
					case '&&': /**< Intersection Operation */

						if(!($next instanceof Regex\Token\Set)) {
	
							throw new Exception("Only Sets can intersect with Sets", $this->pattern);
	
						}
	
						$set->addIntersection($next);
						return $this->pattern->nextToken();
	
					break;
	
					case '-': /**< Can either be a Range or a Substraction Operation */
	
						if($next instanceof Regex\Token\Set) { /**< Seems to be a Substraction */
							
							/** We handle Substrction as Intersection with negated Set */
							$next->negate();
							$set->addIntersection($next);
							return $this->pattern->nextToken();
	
						}				
		
						if($curr instanceof Regex\Token\Char && $next instanceof Regex\Token\Char) { /**< Seems to be a Range */
	
							$curr->toRange($next);
							$this->pattern->compileData("current_token", $curr);
							return $this->pattern->nextToken();
	
						}
	
						/* Move to position before current token and return a Char Token */
						$this->pattern->setPos($pos);
	
					break;
		
				}
			}
		
			/* No Set Operation, simply return a Char Token */	
			return new Regex\Token\Char($this->pattern);

		}

		static public function getToken() {

			return static::$token;

		}

	}

}

?>
