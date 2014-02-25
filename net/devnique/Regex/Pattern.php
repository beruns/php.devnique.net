<?php

/**
* @file Pattern.php
* @brief 
* @author Georg Hubinger <gh@devnqiue.net>
* @version 
* @date 2014-02-19
*/
namespace net\devnique\Regex {

	use net\devnique\StreamBuffer;

	/**
	 *
	 */
	class Pattern extends StreamBuffer {


		protected $token_factories = []; /**< */
		protected $token_handler = null; /**< */

		protected $compile_data = null; /**< */
		protected $execute_data = null; /**< */

		protected $captures = null;

		protected $modifier = null; /**< */
	
		protected $root = null; /**< */

		/**
		 *
		 */
		public function __construct($pattern = null) {

	
			$this->registerFactory(new Token\Factory\Expand($this));
			$this->registerFactory(new Token\Factory\Repetition($this));
			$this->registerFactory(new Token\Factory\Char($this));
			$this->registerFactory(new Token\Factory\MetaChar($this));
			$this->registerFactory(new Token\Factory\SetOp($this));
			$this->registerFactory(new Token\Factory\Boundary($this));

			if($pattern) {

				$this->compile($pattern);

			}
			
		}

		/**
		 *
		 */
		protected function tokenHandler($token) {

			$factory = isset($this->token_factories[$token]) ? $this->token_factories[$token] : null;

			/* A factory array for the token has been found */
			if($factory) {

				/* Try all, latest registered first*/
				for($i = count($factory); $i != 0; --$i) {
	
					$handler = $factory[$i - 1];
					$next = $handler->create($token);

					if($next instanceof Token) {
	
						return $next;
	
					}
	
				}

				/* Nothing to dispatch anymore */
				if(!strlen($token)) {

					throw new Pattern\Exception("No default Factory handled Token", $this);

				}

			} elseif(!strlen($token)) {

				/* No default token handler found */
				throw new Pattern\Exception("No default Factory registered", $this);

			}

			/* Dispatch to handler for smaller token (cut off last char) */
			return $this->tokenHandler(substr($token, 0, -1));

		}

		/**
		 *
		 */
		protected function registerTokenFactory($token, Token\Factory $factory) {

			if(!isset($this->token_factories[$token])) {

				$this->token_factories[$token] = [];

			}

			$this->token_factories[$token][] = $factory;
			$this->token_handler->addNode(new StreamBuffer($token), true);

		}

		/**
		 *
		 */
		protected function registerFactory(Token\Factory $factory) {

			$token = $factory->getToken();
			$this->token_handler = ($this->token_handler ? $this->token_handler : new StreamBuffer\Tree());

			if(is_array($token)) {

				foreach($token as $k => $t) {
				
					$this->registerTokenFactory($t, $factory);
					
				}

			} else {

				$this->registerTokenFactory($token, $factory);
			}
	
		}

		/**
		 *
		 */
		public function nextToken() {

			if($this->EOF()) {

				throw new Pattern\Exception("Now next Token availbale");

			}
	
			if($this->token_handler) {

				$pos = $this->pos;
				$node = $this->token_handler->getNode($this);
				$this->pos = $pos;
			
				if($node->getData()) {
			
					return $this->compile_data["current_token"] = $this->tokenHandler($node->getKey());
					
				}
			 
		
			} 
	
			return $this->compile_data["current_token"] = $this->tokenHandler("");
	

		}

		/**
		 *
		 */
		public function compileData($key, $data = null) {

			$curr = (isset($this->compile_data[$key])?$this->compile_data[$key]:null);

			if(func_num_args() == 2) {
				$this->compile_data[$key] = $data;
			}

			return $curr;	

		}

		/**
		 *
		 */
		public function executeData($key, $data = null) {

			$curr = (isset($this->execute_data[$key])?$this->execute_data[$key]:null);

			if(func_num_args() == 2) {
				$this->execute_data[$key] = $data;
			}

			return $curr;	

		}

		/**
		 *
		 */
		public function match(StreamBuffer $stream) {

			return $this->root->match($stream);			

		}

		/**
		 *
		 */
		protected function parseModifier() {

			$this->modifier = [];
	
			while(!$this->EOF()) {

				$this->modifier[$this->nextByte()] = true;

			}

		}

		/**
		 *
		 */
		public function compile($pattern) {

			$this->comile_data = [];
			$this->captures = [];

			$this->setSource($pattern);

			try {

				$this->root = $this->nextToken();

			} catch(Pattern\Exception $e) {

				throw new Pattern\Exception("Compile Error: " . $e->getMessage());

			}


			$this->parseModifier();
	
			$this->current = null;
			$this->compile_data = [];
				
		}
	
	}


}


?>
