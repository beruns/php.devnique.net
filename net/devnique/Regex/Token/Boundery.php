<?php

namespace net\devnique\Regex\Token {

	use net\devnique\Regex;
	use net\devnique\StreamBuffer;

	class Boundery extends Regex\Token {

		protected $repeatable = false;
	
		protected function parse() {

			$c = $this->pattern->nextByte();

			if($c != "\\" || $this->pattern->nextByte() != "b") {

				throw new Exception("Not a valid boundery Pattern", $this->pattern);				

			}

			

		}

		public function matches(StreamBuffer $stream) {

			/* At start of Stream, the next byte has to be a word char */
			if($stream->getPos() == 0) {	

				$next = $stream->nextByte();
				$stream->movePos(-1);
				return static::isWord($next);

			} elseif($stream->EOF()) { /* At end of stream, the prev byte has to be a word char */

				$stream->movePos(-1);
				$prev = $stream->nextByte();
				return static::isWord($prev);

			} else { /* Otherwise, prev char has to be a word char if next is not (or vv) */


				$next = $stream->nextByte();
				$stream->movePos(-2);
				$prev = $stream->nextByte();

				return (static::isWord($next) != static::isWord($prev));

			}

		}
		

	}

}

?>
