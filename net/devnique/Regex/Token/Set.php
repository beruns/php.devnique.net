<?php

namespace net\devnique\Regex\Token {

	use net\devnique\Regex;
	use net\devnique\StreamBuffer;

	class Set extends Regex\Token {

		const OPEN_TAG = "[";
		const CLOSE_TAG = "]";

		protected $root = null;
		protected $intersect = null;
		protected $negated = false;

		protected function parse() {

			if($this->pattern->nextByte() !== static::OPEN_TAG) {

				throw new Exception("Not a valid Set Pattern", $this->pattern);

			}

			$parent_set = $this->pattern->compileData("current_set", $this);
			$closed = false;

			while(!$this->pattern->EOF()) {
		
				$next = $this->pattern->nextToken();

				if($next instanceof MetaChar && $next == static::CLOSE_TAG) {		

					$closed = true;
					break;

				} elseif($next instanceof MetaChar && $next == "^" && !$this->root) {
	
					$this->negated = true;
					continue;

				} elseif(!($next instanceof Char || $next instanceof Set)) {

					throw new Exception("Only Characters or Sets can be in Sets", $this->pattern); 

				 }
	
				if(!$this->root) {

					$this->root = $next;

				} else {

					$this->root->addNext($next, static::TYPE_OR);
				
				}

			}	

			if(!$closed) {

				throw new Exception("Unexpected end of Set", $this->pattern);

			}

			return $this->pattern->compileData("current_set", $parent_set);
			

		}

		public function negate() {

			$this->negated = !$this->negated;
	
		}

		public function addIntersection(Set $set) {

			if($this->intersect) {

				return $this->intersect->addIntersection($set);

			}

			$this->intersect = $set;
			
			
		}

		public function matches(StreamBuffer $stream) {

			if($this->root) {

				return $this->root->match($stream);

			}

			return true;

		}

		public function __toString() {

			$s = "[" . ($this->negated ? "^":"");

			$curr = $this->root;

			while($curr) {

				$s .= $curr;
				$curr = $curr->or;

			}

			if($this->intersect) {

				$s .= "&&" . $this->intersect;

			}

			$s .= "]";

			return $s;

		}
		

	}

}

?>
