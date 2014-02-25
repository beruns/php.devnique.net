<?php

namespace net\devnique\Regex\Token {

	use net\devnique\Regex;
	use net\devnique\StreamBuffer;

	class Char extends Regex\Token {

		protected $from = 0;
		protected $to = 0;

		protected $is_escaped = false;

		protected function parse() {

			$c = $this->pattern->nextByte();
			if($c == "\\") { 

				$this->is_escaped = true;
				$c = $this->pattern->nextByte();

			}

			$this->from = $this->to = $c;

		}

		public function isEscaped() {

			return $this->is_escaped;
		
		}

		public function toRange(Char $to) {

			/* Expand the current range */
			$this->to = max($this->to, $to->to);
			$this->from = min($this->from, $to->from);

			/* just in case from > to  */
			if($this->from > $this->to) {

				list($this->from, $this->to) = [$this->to, $this->from];

			}

		}	

		protected function matches(StreamBuffer $stream) {

			$c = $stream->nextByte();
			return ($c >= $this->from && $c <= $this->to);

		}

		public function __toString() {

			$s = $this->from;		
	
			if($this->from != $this->to) {

				 $s .= "-" . $this->to;

			}

			return $s;

		}

	}

}

?>
