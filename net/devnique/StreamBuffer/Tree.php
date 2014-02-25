<?php
/**
 *
 */
namespace net\devnique\StreamBuffer {
	
	use net\devnique\StreamBuffer;

	/**
	 *
	 */
	class Tree {

		protected $data = null;
		protected $key = "";
		protected $children = [];
		

		/**
		 *
		 */
		public function __construct(StreamBuffer $key = null, $data = null) {

			if($key) {

				$this->addNode($key, $data);

			} else {

				$this->data = $data;		

			}

		}

		public function getKey() {

			return $this->key;	

		}

		/**
		 *
		 */
		public function getData() {

			return $this->data;
			
		}

		/**
		 *
		 */
		public function getNode(StreamBuffer $key = null) {

			if($key && !$key->EOF()) {
	
				$c = $key->nextByte();
			
				if(isset($this->children[$c])) {

					return $this->children[$c]->getNode($key);

				} 

				$key->movePos(-1);
		

			}

			return $this;
		
		}

		/**
		 *
		 */
		public function addNode(StreamBuffer $key, $data) {

			if($key->eof()) {

				$this->data = $data;

			} else {
		
				$c = $key->nextByte();

				if(!isset($this->children[$c])) {

					$this->children[$c] = new Tree();
					$this->children[$c]->key = $this->key . $c;

				}

				$this->children[$c]->addNode($key, $data);

			}

		}

		public static function fromArray(array $data) {

			$tree = new static();
			$key = new StreamBuffer();

			foreach($data as $k => $v) {

				$key->setSource($k);
				$tree->addNode($key, $v);

			}

			return $tree;

		}
		
	}

}


?>
