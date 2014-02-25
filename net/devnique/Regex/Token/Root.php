<?php

namespace net\devnique\Regex\Token {

	use net\devnique\Regex;
	use net\devnique\StreamBuffer;

	class Root extends Group {

		const OPEN_TAG = "/";
		const CLOSE_TAG = "/";

		protected function parse() {

			$this->pattern->compileData("root", $this);
			return parent::parse();

		}
		
	}

}

?>
