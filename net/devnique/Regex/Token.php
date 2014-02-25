<?php

/**
* @file Token.php
* @brief Token Base Class
* @author Georg Hubinger (gh@devnique.net)
* @version 
* @date 2014-02-19
*/
namespace net\devnique\Regex {

	use net\devnique\StreamBuffer;

	/**
	 * BaseClass for any Regex Token
	 */
	abstract class Token {

		const TYPE_AND = 0; /**< Add next token to 'and' branch */
		const TYPE_OR = 1; /**< Add next token to 'or' branch */

		const MATCH_GREEDY = 0; /**< Greedy Match */
		const MATCH_LAZY = 1; /**< Lazy Match */
		const MATCH_POSSESSIVE = 2; /**< Possessive Match */

		protected $pattern = null;

		protected $and = null; /**< 'and' branch (linked list) */
		protected $or = null; /**< 'or' branch */

		protected $repeatable = true; /**< how many chars of the target are eaten by a matching instance */

		protected $min = 1; /**< minimum repetition */
		protected $max = 1; /**< maximum repetiton */
		protected $greediness = self::MATCH_GREEDY; /**< match type */

		/**
		 * virtual Token Constructor (should consume bytes from pattern via Pattern::nextByte or Pattern::nextToken)
		 * @return void (ignored)
		 */
		abstract protected function parse();

		/**
		 * Token specific match method (should consume $token->match_len bytes from stream via StreamBuffer::nextByte())
		 * @param net\devnique\StreamBuffer $stream StreamBuffer Object to match against
		 * @return true|false should return true on match, false otherwise
		 */
		abstract protected function matches(StreamBuffer $stream);

		/**
		 * Constructor, reserved for possible future automated logic
		 * @param net\devnique\Regex\Pattern $pattern Pattern Object
		 */
		final public function __construct(Pattern $pattern) {

			$this->pattern = $pattern;
			$this->parse();
		
		}

		/**
		 * Append followup / alternative token to parse tree
		 * @param net\devnique\Regex\Token $token Token to add
		 * @param int $type Token::TYPE_AND / Token::TYPE_OR indicates weither to append $token to 'and'/'or' branch
		 */
		protected function addNext(Token $token, $type = self::TYPE_AND) {

			if($type == self::TYPE_AND) {

				if($this->or) return $this->or->addNext($token, $type);
				if($this->and) return $this->and->addNext($token, $type);
				return $this->and = $token;

			} else {

				if($this->or) return $this->or->addNext($token, $type);
				return $this->or = $token;

			}
		
		}

		/**
		 * Set Token's repetition params
		 * @param unsigned int $min Minimum occurances of Token
		 * @param unsigned int $max Maximum occurances of Token
		 * @param int $greediness Token::self::MATCH_GREEDY|Token::self::MATCH_LAZY|Token::self::MATCH_POSSESSIVE
		 */
		final public function setRepetition($min, $max, $greediness = self::MATCH_GREEDY) {

			if(!$this->repeatable) throw new Token\Exception("Nothing to repeat", $pattern);

			$this->min = $min;
			$this->max = $max;
			$this->greediness = $greediness;

		}

		/**
		 * Check weither or Path matches
		 * @param net\devnique\StreamBuffer $stream StreamBuffer Object to check against
		 * @return true|false true if or path defined and matches, false otherwise
		 */
		protected function matchOrPath(StreamBuffer $stream) {
	
			if($this->or) {

				return $this->or->match($stream);

			}

			return false;
	
		}

		/**
		 * Check weither and Path matches
		 * @param net\devnique\StreamBuffer $stream StreamBuffer Object to check against
		 * @return true|false true if and path not defined or matches, false otherwise
		 */
		protected function matchAndPath(StreamBuffer $stream) {

			if($this->and) {

				return $this->and->match($stream);

			}

			return true;
	
		}
		
		/**
		 * Process a 'greedy' match
		 * First we match as many times as possible, rollback as long as the and path doesn't match
		 * @param net\devnique\StreamBuffer $stream StreamBuffer Object to proccess
		 * @param int $i current number of matched bytes
		 * @return true|false true if a greedy match is successfully performed
		 */
		protected function matchGreedy(StreamBuffer $stream, $i) {

			/* Store current Streambuffer position */
			$pos = $stream->getPos();

			/* Recursively procceed until max matches or no match anymore */
			if($i != $this->max && $this->matches($stream)) {

				if($this->matchGreedy($stream, ++$i)) {
					return true;
				}

			}
	
			/* Rollback to prev position and match and path */
			$stream->setPos($pos);
			if($this->matchAndPath($stream)) {

				return true;						

			}

			/* Rollback is done by Token::match() or previous Token::matchGreedy() in recursion */
			return false;
			
		}

		/**
		 * Process a 'lazy' match
		 * Try if and path matches and if not, match once more against the token itself until and path matches
		 * @param net\devnique\StreamBuffer $stream StreamBuffer Object to proccess
		 * @param int $i current number of matched bytes
		 * @return true|false true if a lazy match is successfully performed
		 */
		protected function matchLazy(StreamBuffer $stream, $i) {

			/* Store current StreamBuffer position */
			$pos = $stream->getPos();
			/* First try to match and path */
			if($this->matchAndPath($stream)) {

				return true;						

			}

			/* Rollback to correct position (matchAndPath could have changed it)*/
			$stream->setPos($pos);

			/* if no match was found, rematch against the token itself and recursively proceed */
			if($i != $this->max && $this->matches($stream)) {

				return $this->matchLazy($stream, ++$i);

			}

			/* Rollback is done by Token::match() */
			return false;

		
		}

		/**
		 * Process a 'possessive' match
		 * Match as often as possible and then match and path (no rollbacks/rematches are done)
		 * @param net\devnique\StreamBuffer $stream StreamBuffer Object to proccess
		 * @param int $i current number of matched bytes
		 * @return true|false true if a possessive match is successfully performed
		 */
		protected function matchPossessive(StreamBuffer $stream, $i) {

			/* store current StreamBuffer position */
			$pos = $stream->getPos();

			/* while we can match more, match more*/
			while($i != $this->max && $this->matches($stream)) {

				/* if the while condition becomes false because of Token::matches, we need to rewind the position to the last matching one */
				$pos = $stream->getPos();

			}

			/* Set last matching position */
			$stream->setPos($pos);
	
			/* Check and path, no rollbacks are done */
			return $this->matchAndPath($stream);

		}

		/**
		 * match Streambuffer against current token, against the and path and, if no match succeeds, against the or path
		 * @param net\devnique\StreamBuffer $stream StreamBuffer object to proccess
		 * @ eturn true|false true if match is successful
		 */
		final public function match(StreamBuffer $stream) {

			$pos = $stream->getPos();

			/* First we need to matcn the minimum nuber of matches */
			for($i = 0; $i < $this->min; ++$i) {
	
				if(!$this->matches($stream)) {

					/* not enough bytes matched, rollback and try or path */
					$stream->setPos($pos);
					return $this->matchOrPath($stream); 
					
				}

			}

			/* Select match style */
			if($this->greediness == self::MATCH_GREEDY) {
		
				/* ... successfull greedy match */
				if($this->matchGreedy($stream, $i)) {

					return true; 

				}

			} elseif($this->greediness == self::MATCH_LAZY) {

				/* ... successfull lazy match */
				if($this->matchLazy($stream, $i)) {

					return true; 
			
				}

			} else {

				/* ... successfull possessive match */
				if($this->matchPossessive($stream, $i)) {

					return true;					

				}

			}

			/* rollback and try or path */
			$stream->setPos($pos);
			return $this->matchOrPath($stream); 

		}

		public static function isAlpha($char) {

			$char = strtolower($char);
			return ('a' <= $char &&	$char <= 'z');		

		}

		public static function isNumeric($char) {

			return ('0' <= $char &&	$char <= '9');		

		}

		public static function isAlnum($char) {

			return static::isAlpha($char) || static::isNumeric($char);
			
		}

		public static function isWord($char) {
	
			return static::isAlnum($char) || $char == "_";

		}

	}

}

?>
