<?php

namespace net\devnique\Regex\Token {

	use net\devnique\Regex;
	use net\devnique\StreamBuffer;

	class Captures {

		protected $captured = [];

		public function set($id, $data) {

			$this->captured[$id] = $data;

		}

		public function get($id) {

			return $this->captured[$id];
			
		}
	
		public function register() {

			$curr = count($this->captured);
			$this->captured[] = "";
			return $curr;

		}

	}

	class Group extends Regex\Token {

		const OPEN_TAG = "(";
		const CLOSE_TAG = ")";

		protected $root = null;
		protected $capture = true;
		
		public $id = null;
		protected $start_at = 0;

		protected function parse() {

			if($this->pattern->nextByte() !== static::OPEN_TAG) {

				throw new Exception("Not a valid Group Pattern", $this->pattern);

			}

			$this->pattern->compileData("current_token", null);
			$parent_group = $this->pattern->compileData("current_group", $this);

			$path = self::TYPE_AND;

			while(!$this->pattern->EOF()) {

				$next = $this->pattern->nextToken();
	
				if($next instanceof MetaChar && $next == static::CLOSE_TAG) {

					break;

				} elseif ($next instanceof MetaChar && $next == "|") { 

					$path = self::TYPE_OR;
					continue;
					
				}

				if(!$this->root) {

					$this->root = $next;
	
				} else {

					$this->root->addNext($next, $path);

	
				}

				$path = self::TYPE_AND;

			}

			return  $this->pattern->compileData("current_group", $parent_group);

		}

		protected function matches(StreamBuffer $stream) {

			if($this->root) {

				$this->startCapture($stream);

				if($this->root->match($stream)) {

					$this->endCapture($stream);
					return true;

				}

				return false;
				

			}

			return true;

		}

		protected function startCapture(StreamBuffer $stream) {

			if($this->capture) {

				$captures = $this->pattern->executeData("capture_groups");

				if(!$captures) {
					$this->pattern->executeData("capture_groups", $captures = new Captures());
				}

				$this->id = $captures->register();
				$this->start_at = $stream->getPos();			
	
			}

		}

		protected function endCapture(StreamBuffer $stream) {

			if($this->capture) {

				$capture_groups = $this->pattern->executeData("capture_groups");
				$capture_groups->set($this->id, $stream->subString($this->start_at, $stream->getPos()));
	
			}
		
		}

		protected function processBranch(Regex\Token $token) {

			$s = "";
	
			while($token) {

				$s .= $token;
				$token = $token->and;

			}
	
			return $s;

		}

		public function __toString() {

			$s = static::OPEN_TAG;
			$curr = $this->root;

			while($curr) {

				$s .= $this->processBranch($curr);
				$curr = $curr->or;

				if($curr) { 

					$s .= "|";

				}
				
			}	

			$s .= static::CLOSE_TAG;

			return $s;

		}

	}

}

?>
