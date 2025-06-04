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

/**
 *  ShortcodeLexer
 * 
 *  Tokenizes a string using the following grammar.
 *  Acts as the input "stream" for NRFramework\Parser\ShortcodeParser
 * 
 *  Tokens:
 *  -------
 *  sc_open        : shortcode tag opening character(s),  default: {
 *  sc_close       : shortcode tag closing character(s), default: }
 *  if_keyword     : if keyword,    default: 'if'
 *  endif_keyword  : endif keyword, default: '/if'
 *  text           : any character sequence
 *  text_preserved : any character sequence with quoted values preserved
 *  whitespace     : ' ' | '\r' | '\n' | '\t'
 */
class ShortcodeLexer extends Lexer
{
    /**
     *  Shortcode opening character(s) (default: {)
     *
     *  @var string
     */
    protected $sc_open_char;

    /**
     *  Shortcode closing character(s) (default: })
     *
     *  @var string
     */
    protected $sc_close_char;

    /**
     *  if keyword (default: 'if')
     *
     *  @var string
     */
    protected $if_keyword;

    /**
     *  endif keyword (default: '/if')
     *
     *  @var string
     */
    protected $endif_keyword;

    /**
     *  ShortcodeLexer constructor  
     *
     *  @param string $input
     *  @param object $options
     */
    public function __construct($input, $options = null)
    {
        parent::__construct($input);        

        $this->tokens->addType('sc_open');
        $this->tokens->addType('sc_close');
        $this->tokens->addType('if_keyword');
        $this->tokens->addType('endif_keyword');
        $this->tokens->addType('char');
        
        $this->sc_open_char     = $options->tag_open_char ?? '{';
        $this->sc_close_char    = $options->tag_close_char ?? '}';
        $this->if_keyword       = $options->if_keyword ?? 'if';
        $this->endif_keyword    = '/' . $this->if_keyword;
    }

    /**
     *  Returns the next token from the input string
     *
     *  @return RestrictContent\Parser\Token
     */
    public function nextToken()
    {
        static $if_flag = false;

        while ($this->cur !== Lexer::EOF)
        {
            if ($this->state->skip_whitespace && preg_match('/\s+/', $this->cur))
            {
                $this->whitespace();
                continue;
            }

            if ($this->predictScOpen())
            {
                return $this->sc_open();
            }
            else if ($this->predictScClose())
            {
                return $this->sc_close();
            }  
            // check for if/endif
            else if ($this->predictIf(false))
            {
                $if_flag = true;
                return $this->_if();
            }
            else if ($this->predictEndif(false))
            {
                return $this->_endif();
            }
            // check for text
            else {
                $token   = $this->text($if_flag);
                $if_flag = false; 
                return $token;
            }
        }
        // return EOF token at the end of stream
        return $this->tokens->create('EOF', '<EOF>', -1);
    }

    /**
     *  Predicts an upcoming if keyword from the input stream
     *
     *  @param  bool $reset Reset to the marked position when the keyword is found
     *  @return bool
     */
    protected function predictIf($reset = true)
    {
        $this->mark();
        $tmp = $this->consume(2);
        if ($tmp === $this->if_keyword)
        {
            if ($reset)
            {
                $this->reset();
            }
            return true;
        }

        $this->reset();
        return false;
    }

    /**
     *  Predicts an upcoming endif keyword from the input stream
     *
     *  @param  bool $reset Reset to the marked position when the keyword is found
     *  @return bool
     */
    protected function predictEndif($reset = true)
    {
        $this->mark();
        $tmp = $this->consume(3);
        if ($tmp === $this->endif_keyword)
        {
            if ($reset)
            {
                $this->reset();
            }
            return true;
        }

        $this->reset();
        return false;
    }

    /**
     *  Predicts any upcoming keyword
     *
     *  @return bool
     */
    protected function predictKeywords()
    {
        return $this->predictIf() || $this->predictEndif();
    }

    /**
     *  Predicts any upcoming special character
     *
     *  @return bool
     */
    protected function predictSpecialChars()
    {
        return $this->predictScOpen() || $this->predictScClose();
    }

    /**
     *  Predicts upcoming shortcode opening character(s), default: {
     *
     *  @return bool
     */
    protected function predictScOpen()
    {
        $sc_length = \strlen($this->sc_open_char);
        $res = false;
        $this->mark();
        $tmp = $this->consume($sc_length);
        if ($tmp === $this->sc_open_char)
        {
            $res = true;
        }

        $this->reset();
        return $res;
    }

    /**
     *  Predicts upcoming shortcode closing character(s), default: {
     *
     *  @return bool
     */
    protected function predictScClose()
    {
        $sc_length = \strlen($this->sc_close_char);
        $res = false;
        $this->mark();
        $tmp = $this->consume($sc_length);
        if ($tmp === $this->sc_close_char)
        {
            $res = true;
        }

        $this->reset();
        return $res;
    }

    /**
     *  sc_open : shortcode tag opening character,  default: {
     * 
     *  @return Token
     */
    protected function sc_open()
    {
        $pos = $this->index;
        $length = \strlen($this->sc_open_char);
        $this->consume($length);
        return $this->tokens->create('sc_open', $this->sc_open_char, $pos);
    }

    /**
     *  sc_close : shortcode tag closeing character, default: }
     * 
     *  @return Token
     */
    protected function sc_close()
    {
        $pos = $this->index;
        $length = \strlen($this->sc_close_char);
        $this->consume($length);
        return $this->tokens->create('sc_close', $this->sc_close_char, $pos);
    }

    /**
     *  if_keyword, default: 'if'
     * 
     *  @return Token
     */
    protected function _if()
    {
        return $this->tokens->create('if_keyword', $this->if_keyword, $this->index - \strlen($this->if_keyword));        
    }

    /**
     *  endif_keyword, default: '/if'
     * 
     *  @return Token
     */
    protected function _endif()
    {
        return $this->tokens->create('endif_keyword', $this->endif_keyword, $this->index - \strlen($this->endif_keyword));
    }


    /**
     *  text : any character sequence
     * 
     *  @param bool $preserve Preserve keywords and special characters inside quotes
     *  @return Token
     */
    protected function text($preserve)
    {
        if ($preserve)
        {
            return $this->text_preserved();
        }

        $pos = $this->index;
        $buf = '';
        while ($this->cur !== Lexer::EOF)
        {
            if ($this->predictKeywords() || $this->predictSpecialChars())
            {
                return $this->tokens->create('text', $buf, $pos);
            }
            $buf .= $this->cur;
            $this->consume();
        }

        return $this->tokens->create('EOF', '<EOF>', -1);
    }

    /**
     *  text_preserved : any character sequence with quoted values preserved
     * 
     *  @return Token
     */
    protected function text_preserved()
    {
        $quote_queue = [];
        $buf         = '';
        $pos         = $this->index;

        while ($this->cur !== Lexer::EOF)
        {
            // manage quote parsing
            if ($this->cur == '"' || $this->cur == "'")
            {
                if ($this->cur == end($quote_queue))
                {
                    // remove last added quote
                    array_pop($quote_queue);
                }
                else
                {
                    // add quote to the queue
                    array_push($quote_queue, $this->cur);
                } 
            }
            // End parsing when any keyword or special character is found
            // handles quoted values
            if ($this->predictKeywords() || $this->predictSpecialChars())
            {
                // return the expression's text if no quotes are open
                if (empty($quote_queue))
                {
                    return $this->tokens->create('text_preserved', trim($buf), $pos);
                }
            }

            // add current character to buffer
            $buf .= $this->cur;
            $this->consume();
        }

        return $this->tokens->create('EOF', '<EOF>', -1);
    }
}
