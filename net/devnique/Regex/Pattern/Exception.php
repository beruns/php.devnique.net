<?php

namespace net\devnique\Regex\Pattern {

	use net\devnique\Regex;

	class Exception extends \Exception {

		public function __construct($msg, Regex\Pattern $pattern = null) {

			parent::__construct($msg);

			if($pattern) {

				$this->message .= " at offset " . $pattern->getPos();
	
			}

		}

	}

}

?>
