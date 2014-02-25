<?php

namespace net\devnique\StreamBuffer {

	use net\devnique\StreamBuffer;

	class Tokenizer extends StreamBuffer {

		protected $token_tree = null;

		public function nextToken() {

			if(!$this->token_tree || $this->EOF()) return;

			$pos = $this->pos;
			$node = $this->token_tree->getNode($this);
			$this->pos = $pos;

			if($handler = $node->getData()) {

				return $handler($this, $node->getKey());

			} else {

				return $this->nextByte();

			}
		
		}			

		public function registerHandler($token, \Closure $handler) {

			if(!$this->token_tree) $this->token_tree = new StreamBuffer\Tree();
			$s = new StreamBuffer();

			if(is_array($token)) {

				foreach($token as $k => $t) {

					$this->registerHandler($t, $handler);

				}
	
			} else {

				$this->token_tree->addNode($s->setSource($token), $handler);	
	
			}
	
		}

	}

}

?>
