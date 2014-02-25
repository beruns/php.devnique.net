<?php
/**
 *
 */
namespace net\devnique {

	/**
	 *
	 */
	class StreamBufferException extends \Exception {}

	/**
	 *
	 */
	class StreamBuffer {

		protected $stream = null; 

		protected $buf = "";
		protected $buf_len = 0;

		protected $pos = 0;	

		/**
		 *
		 */
		public function __construct($src = null) {

			if($src) $this->setSource($src);

		}

		/**
		 *
		 */
		public function setSource($src) {
		
			if(is_resource($src)) {

				if(get_resource_type($src) != "stream") {
		
					throw new StreamBufferException("\$stream must be a valid stream resource (or a string).");
					
				}

				$this->stream = $src;
				$this->buf_len  = 0;

			} else {

				$this->buf = (string) $src;
				$this->buf_len = strlen($this->buf);

			}

			$this->pos = 0;
			return $this;

		}

		/**
		 *
		 */
		public function nextByte() {
	
			if($this->stream && $this->pos == $this->buf_len) {

				if(($c = fgetc($this->stream)) !== false) {

					$this->buf .= $c;
					$this->buf_len++;

				} else {
	
					// We have read all of the stream so we'll release it
					$this->stream = null;
	
				}

			}

			return ($this->pos < $this->buf_len ? $this->buf[$this->pos++] : false);
			
		}

		/**
		 *
		 */
		public function getPos() {
	
			return $this->pos;
	
		}

		/**
		 *
		 */
		public function setPos($pos) {

			$pos = ($pos < 0 ? 0 : $pos);

			if($this->buf_len < $pos) {
	
				if($this->stream) {

					while($this->pos < $pos) {

						if($this->nextByte() === false) {

							break;

						}

					}
	
				}
				
				$pos = $this->buf_len;	

			}

			$this->pos = $pos;
		}

		/**
		 *
		 */
		public function movePos($offset = 1) {
			
			$this->setPos($this->pos += $offset);

		}

		/**
		 *
		 */
		public function toStart() {
		
			$this->setPos(0);		
	
		}

		/**
		 *
		 */
		public function toEnd() {

			$this->pos = $this->buf_len;

			if($this->stream) {

				while(!$this->EOF()) {

					$this->nextByte();

				}

			}

		}

		public function subString($from, $to) {

			$this->setPos($to);
			return substr($this->buf, $from, ($to - $from));
		}

		/**
		 *
		 */
		public function EOF() {

			if($this->nextByte() === false) {
				return true;
			}	

			$this->pos--;
			return false;

		}

		/**
		 *
		 */
		public function insert($string) {

			$string = (string) $string;
			$add = strlen($string);

			for($i = $this->buf_len - 1; $i >= $this->pos; --$i) {

				$this->buf[$i + $add] = $this->buf[$i];

			}

			for($i = 0; $i < $add; ++$i) {

				$this->buf[$this->pos + $i] = $string[$i];

			}

			$this->buf_len += $add;

		}

		/**
		 *
		 */
		public function replace($char) {

			$char = (string) $char;

			if(!$char) {

				return $this->removeChar();

			}

			$this->buf[$this->pos] = $char[0];

		}

		/**
		 *
		 */
		public function remove($num = 1) {

			for($i = $this->pos; $i < $this->buf_len - $num; ++$i) {

				$this->buf[$i] = $this->buf[$i + $num];

			}
		
			while($i < $this->buf_len) {
				$this->buf[$i++] = "";
			}
			
			$this->buf_len -= $num;

		}

		/**
		 *
		 */
		public function __toString() {

			return $this->buf;

		}

	}

}


?>
