<?php

/**
 *  @author          Tassos.gr <info@tassos.gr>
 *  @link            https://www.tassos.gr
 *  @copyright       Copyright © 2024 Tassos All Rights Reserved
 *  @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\Parser;

defined('_JEXEC') or die;

use NRFramework\Parser\Tokens;

/**
 *  Lexer base class
 * 
 *  TODO: Rename to Tokenizer??
 */
abstract class Lexer
{
   /**
    *  EOF character
    */
   const EOF = -1;

   /**
    *  Tokens instance
    *
    *  @var NRFramework\Parser\Tokens
    */
   protected $tokens = null;  // Tokens instance

   /**
    *  Input string
    *
    *  @var string
    */
   protected $input;

   /**
    *  Input string length
    */
   protected $length;

   /**
    *  The index of the current character
    *  in the input string 
    *
    * @var integer
    */
   protected $index = 0;

   /**
    *  Current character in input string
    *
    *  @var string
    */
   protected $cur;

   /**
    *  A Mark(position) inside the input string.
    *  Used when matching ahead of the 'current' character
    *
    *  @var integer
    */
   protected $mark = 0;

   /**
    *  Holds the Lexer's state
    *
    *  @var object
    */
   protected $state;

   /**
    *  Lexer constructor
    *
    *  @param string $input
    */
   public function __construct($input)
   {
       $this->input   = $input;
       $this->length  = strlen($input);
       $this->cur     = $this->length >= 1 ? $this->input[0] : Lexer::EOF;
       $this->tokens  = new Tokens();
       
       // inititalize state
       $this->state                     = new \StdClass();
       $this->state->skip_whitespace    = true;
   }

   /**
    *  Returns the next token from the input string.
    *
    *  @return NRFramework\Parser\Token
    */
   abstract function nextToken();

   /**
    *  Moves n characters ahead in the input string.
    *  Returns all n characters.
    *  Detects "end of file".
    * 
    *  @param  integer $n  Number of characters to advance
    *  @return string      The n previous characters
    */
   public function consume($n = 1)
   {
       $prev = '';
       for ($i=0; $i < $n; $i++)
       {
           $prev .= $this->cur;
           if ( ($this->index + 1) >= $this->length) 
           {
               $this->cur = Lexer::EOF;
               break;
           }
           else
           {
               $this->index++;
               $this->cur = $this->input[$this->index];
           }
       }

       return $prev;
   }

   /**
    *  Sets the skip_whitespce state
    *
    *  @param boolean $skip
    *  @return void
    */
   public function skipWhitespace($skip = true)
   {
       $this->state->skip_whitespace = $skip;
   }

   /**
    *  Marks the current index
    *
    *  @return void
    */
   public function mark()
   {
       $this->mark = $this->index;
   }

   /**
    *  Reset index to previously marked position (or at the start of the stream if not marked)
    *
    *  @return void
    */
   public function reset()
   {
       $this->index   = $this->mark;
       $this->cur     = $this->input[$this->index];
       $this->mark    = 0;
   }

   /**
    *  Get the token types array from the Tokens instance
    *
    *  @return void
    */
   public function getTokensTypes()
   {
       return $this->tokens->getTypes();
   }

   /**
    *  Returns the current position in the input stream
    *
    *  @return integer
    */
   public function getStreamPosition()
   {
       return $this->index;
   }

   /**
    *  whitespace : (' '|'\t'|'\n'|'\r')
    *  Ignores any whitespace while advancing
    *  @return null
    */
   protected function whitespace()
   {
       while (preg_match('/\s+/', $this->cur)) $this->consume();
   }
}
