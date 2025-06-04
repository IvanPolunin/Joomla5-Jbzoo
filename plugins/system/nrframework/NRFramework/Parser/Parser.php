<?php

/**
 *  @author          Tassos.gr <info@tassos.gr>
 *  @link            https://www.tassos.gr
 *  @copyright       Copyright © 2024 Tassos All Rights Reserved
 *  @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\Parser;

defined('_JEXEC') or die;

use NRFramework\Parser\Lexer;
use NRFramework\Parser\RingBuffer;

/**
 *  Parser base class
 *  LL(k) recursive-decent parser with backtracking support
 */
abstract class Parser
{
    /**
     *  Lexer instance (feeds the parser with tokens)
     *  
     *  @var NRFramework\Parser\Lexer
     */
    protected $input = null;

    /**
     *  Ring buffer of the next k tokens
     *  from the input stream
     *
     *  @var RingBuffer
     */
    protected $lookahead = null;

    /**
     *  k: Number of lookahead tokens
     *
     *  @var int
     */
    protected $k;

    /**
     *  Array(stack) containing  the current 
     *  contents of the lookahead buffer when 
     *  marking the position of the stream
     * 
     *  @var array
     */
    protected $lookahead_history = null;

    /**
     *  Lexer constructor
     *
     *  @param Lexer   $input
     *  @param integer $k, number of lookahead tokens
     */
    public function __construct(Lexer $input, $k = 1)
    {
        if (!is_integer($k) || ($k < 1))
        {
            throw new \InvalidArgumentException('Parser: $k must be greater than 0!');
        }
        $this->k                 = $k;
        $this->input             = $input;
        $this->lookahead_history = [];

        // initialize lookahead buffer
        $this->resetBuffer();
    }

    /**
     *  Checks the type of the next token.
     *  Advances the position in the token stream.
     *  
     *  @param string $type
     *  @return void
     * 
     *  @throws Exception
     */
    public function match($type)
    {
        if ($this->lookahead[0]->type === $type)
        {
            $this->consume();
            return;
        }

        throw new Exceptions\SyntaxErrorException('Expecting token ' . $type . ', found ' . $this->lookahead[0]);
    }

    /**
     *  Retrieves the next token from the input stream
     *  and add it to the buffer.
     *
     *  @return void
     */
    public function consume()
    {
        $this->lookahead[] = $this->input->nextToken();
    }

    /**
     *  Marks the position in the token stream
     * 
     *  @return void
     */
    public function mark()
    {
        array_push($this->lookahead_history, $this->lookahead);
        $this->input->mark();
    }

    /**
     *  Reset to a previously marked position
     *  in the token stream
     * 
     *  @return void
     */
    public function reset()
    {
        $this->input->reset();

        // reset lookahead buffer if not marked
        if (empty($this->lookahead_history))
        {
            $this->resetBuffer();
        }
        // normal reset
        else
        {
            $this->lookahead = array_pop($this->lookahead_history);
        }
    }

    /**
     *  Resets and refills the lookahead buffer starting
     *  from the current position in the token stream
     *
     *  @return void
     */
    protected function resetBuffer()
    {
        $this->lookahead = new RingBuffer($this->k);
        for ($i=0; $i < $this->k; $i++)
        {
            $this->consume();
        }
    }
}
