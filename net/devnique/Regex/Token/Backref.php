<?php

namespace net\devnique\Regex\Token {

	use net\devnique\Regex;
	use net\devnique\StreamBuffer;

	class Backref extends Regex\Token {

		protected $id = null;

		protected function parse() {

			$c = $this->pattern->nextByte();

			if($c != "$") { 

				throw new Exception("Not a valid Backref Pattern", $this->pattern);
				
			}

			$tmp = "";

			while(!$this->pattern->EOF()) {
				
				$pos = $this->pattern->getPos();
				$c = $this->pattern->nextByte();

				if(!static::isNumeric($c)) {

					$this->pattern->movePos(-1);
					break;

				}

				$tmp .= $c;

			}

			$this->id = (int) $tmp;

			if(!$this->id) {

				throw new Exception("Invalid Capturing Group id in Beckref", $this->pattern);

			}

		}

		protected function matches(StreamBuffer $stream) {

			$captures = $this->pattern->executeData("capture_groups");

			if(!$captures) {

				return false;

			}

			$match = $captures->get($this->id);
			$len = strlen($match);
	
			$tmp = "";

			while($len) {
				$tmp .= $stream->nextByte();
				--$len;
			}

			return $tmp == $match;
		}

		public function __toString() {

			return "\$" . $this->id;

		}

	}

}

?>
