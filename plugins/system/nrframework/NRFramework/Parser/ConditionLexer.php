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
 *  ConditionLexer
 * 
 *  Tokens:
 *  -------
 *  and                     : 'AND'
 *  or                      : 'OR'
 *  quotedval               : quotes ~(quotes)* quotes
 *  literal                 : ~(whitespace | quotes)+
 *  ident                   : ('a'..'z' | 'A'..'Z' | '_' | '\-' | '\.')+
 *  quotes                  : '\'' | '\"'
 *  comma                   : ','
 *  l_paren                 : '('
 *  r_paren                 : ')'
 *  
 *  negate_op               : '!'
 *  equals                  : '='  | 'equals'
 *  contains                : '*=' | 'contains'
 *  contains_any            : 'containsAny'
 *  contains_all            : 'containsAll'
 *  contains_only           : 'containsOnly'
 *  ends_with               : '$=' | 'endsWith'
 *  starts_with             : '^=' | 'startsWith'
 *  lt                      : '<'  | 'lt'  | 'lowerThan'
 *  lte                     : '<=' | 'lte' | 'lowerThanEqual'
 *  gt                      : '>'  | 'gt'  | 'greaterThan'
 *  gte                     : '>=' | 'gte' | 'greaterThanEqual'
 *  empty                   : 'empty'
 * 
 *  param                   : '--' . ident
 *  whitespace              : ' ' | '\r' | '\n' | '\t'
 */
class ConditionLexer extends Lexer
{
    /**
     *  ConditionLexer constructor  
     *
     * @param string $input
     */
    public function __construct($input)
    {
        parent::__construct($input);        
        // single char tokens
        $this->tokens->addType('comma');
        $this->tokens->addType('quote');
        $this->tokens->addType('dquote');
        $this->tokens->addType('l_paren');
        $this->tokens->addType('r_paren');
        // operators
        $this->tokens->addType('negate_op');
        $this->tokens->addType('equals');
        $this->tokens->addType('contains');
        $this->tokens->addType('contains_all');
        $this->tokens->addType('contains_any');
        $this->tokens->addType('contains_only');
        $this->tokens->addType('ends_with');
        $this->tokens->addType('starts_with');

        $this->tokens->addType('lt');
        $this->tokens->addType('gt');
        $this->tokens->addType('lte');
        $this->tokens->addType('gte');
        $this->tokens->addType('empty');
        // logical operators
        $this->tokens->addType('and');
        $this->tokens->addType('or');
        // values/literals/identifiers/parameters
        $this->tokens->addType('quotedvalue');
        $this->tokens->addType('literal');
        $this->tokens->addType('ident');
        $this->tokens->addType('param');
    }

    /**
     *  Returns the next token from the input string
     *
     *  @return NRFramework\Parser\Token
     *  @throws Exception
     */
    public function nextToken()
    {
        while ($this->cur !== Lexer::EOF)
        {
            
            if (preg_match('/\s+/', $this->cur))
            {
                $this->whitespace();
                continue;
            }
            
            switch ($this->cur)
            {
                // match tokens from single char predictions
                case ',':
                    return $this->comma();
                case "'":
                    return $this->quotedValue("'");
                case '"':
                    return $this->quotedValue('"');
                case '=':
                    return $this->equals();
                case '!':
                    return $this->negate_op();
                case '*':
                    return $this->contains();
                case '$':
                    return $this->ends_with();
                case '^':
                    return $this->starts_with();
                case '<':
                    return $this->lt_or_lte();
                case '>':
                    return $this->gt_or_gte();
                case '(':
                    return $this->l_paren();
                case ')':
                    return $this->r_paren();
                case '-':
                    $this->mark();
                    $next_chars = $this->consume(2);
                    if ($next_chars === '--')
                    {
                        $this->reset();
                        return $this->param();
                    }
                    $this->reset();
    
                // match other tokens
                default:
                    if (!$this->isValidChar())
                    {
                        throw new Exceptions\SyntaxErrorException('Invalid character: ' . $this->cur);
                    }
                    $token = null;

                    // try to match literal operators
                    $token = $this->literal_ops();
                    if($token)
                    {
                        return $token;
                    }

                    // try to match boolean operators
                    $token = $this->_and();
                    if($token)
                    {
                        return $token;
                    }

                    $token = $this->_or();
                    if($token)
                    {
                        return $token;
                    }

                    // if we get here the token is certainly a literal
                    $pos    = $this->index;
                    $token  = $this->literal();
                    if ($token)
                    {
                        // check if the literal also qualifies to be an identifier
                        if ($this->isValidIdentifier($token->text))
                        {
                            $token = $this->tokens->create('ident', $token->text, $pos);
                        }
                        return $token;
                    }
                    return null;                                        
            }
        }
        return $this->tokens->create('EOF', '<EOF>', -1);
    }

    /**
     * Checks if a string qualifies to be an identifier
     * 
     * @return bool
     */
    protected function isValidIdentifier($text)
    {
        $ident_regex = '/(^[a-zA-Z\_]{1}$)|(^[a-zA-Z\_](?=([\w\-\.]*))([\w\-\.]*))/';
        return preg_match($ident_regex, $text);
    }

    /**
     *  Check if the current character is valid for 
     *  some matching rules (and, or, literal, ident)
     *
     *  @return boolean
     */
    protected function isValidChar()
    {
        $r = '/[^\s\'\",=\!\(\)\~\*\<\>\$\^]/';

        return preg_match($r, $this->cur);
    }

    /**
     *  literal  : ~(whitespace | quotes)+ //one or more chars except whitespace and quotes
     * 
     *  @return Token|void
     */
    protected function literal()
    {
        $pos = $this->index;
        $buf = '';
        do
        { 
            if (!$this->isValidChar())
            {
                break;
            }
            $buf .= $this->cur;
            $this->consume(); 
        }
        while ($this->cur !== Lexer::EOF);

        if (strlen($buf) > 0)
        {
            return $this->tokens->create('literal', $buf, $pos);
        }
    }

    /**
     *  and : 'AND'
     *
     *  @return Token|void
     */
    protected function _and()
    {
        $pos = $this->index;
        $this->mark();
        $buf = '';
        $buf .= $this->cur;
        $this->consume();
        $buf .= $this->cur;
        $this->consume();
        $buf .= $this->cur;
        $this->consume();

        if (preg_match('/and/', strtolower($buf)))
        {
            return $this->tokens->create('and', trim($buf), $pos);
        }

        $this->reset();
    }
    /**
     *  or : 'OR'
     *
     *  @return Token|void
     */
    public function _or()
    {
        $pos = $this->index;
        $this->mark();
        $buf = '';
        $buf .= $this->cur;
        $this->consume();
        $buf .= $this->cur;
        $this->consume();

        if (preg_match('/or/', strtolower($buf)))
        {
            return $this->tokens->create('or', trim($buf), $pos);
        }

        $this->reset();
    }

    /**
     *  quotedval : quotes ~(quotes)* quotes
     *
     *  @return Token|void
     *  @throws Exception
     */
    protected function quotedValue($q)
    {
        $pos = $this->index;
        $otherQuote = $q === '"' ? "'" : '"';
        $quote_queue = [];
        $buf = '';

        $quote_queue[] = $q;
        $this->consume();
        while (!empty($quote_queue))
        {
            if ($this->cur === Lexer::EOF)
            {
                throw new Exceptions\SyntaxErrorException('Missing quote at: ' . $buf);
            }

            if ($this->cur === end($quote_queue))
            {
                array_pop($quote_queue);
                // if it's not the opening quote
                if (!empty($quote_queue))
                {
                    $buf .= $this->cur;
                }
            }
            else if ($this->cur === $otherQuote)
            {
                array_push($quote_queue, $otherQuote);
                $buf .= $otherQuote;
            }
            else
            {
                $buf .= $this->cur;
            }
            $this->consume();
        }
        return $this->tokens->create('quotedvalue', $buf, $pos);
    }

    /**
     * param : '--' . ident
     * 
     * @return Token|void
     */
    protected function param()
    {
        $pos = $this->index;
        $this->mark();
        $buf = '';
        $buf .= $this->cur;
        $this->consume();
        $buf .= $this->cur;
        $this->consume();

        if ($buf === '--')
        {
            $buf = '';
            do
            { 
                if (!$this->isValidChar())
                {
                    break;
                }
                $buf .= $this->cur;
                $this->consume(); 
            }
            while ($this->cur !== Lexer::EOF);

            if (strlen($buf) > 0 && $this->isValidIdentifier($buf))
            {
                return $this->tokens->create('param', $buf, $pos);
            }
        }

        $this->reset();
    }

    /**
     *  equals : '='
     *
     *  @return Token|void
     */
    protected function equals()
    {
        $pos = $this->index;
        $this->consume();
        return $this->tokens->create('equals', "=", $pos);
    }

    protected function negate_op()
    {
        $pos = $this->index;
        $this->consume();
        return $this->tokens->create('negate_op', "!", $pos);
    }

    /**
     *  comma : ','
     *
     *  @return Token
     */
    protected function comma()
    {
        $pos = $this->index;
        $this->consume();
        return $this->tokens->create('comma', ",", $pos);
    }

    /**
     * l_paren : '('
     */
    protected function l_paren()
    {
        $pos = $this->index;
        $this->consume();
        return $this->tokens->create('l_paren', '(', $pos);
    }

    /**
     * r_paren : ')'
     */
    protected function r_paren()
    {
        $pos = $this->index;
        $this->consume();
        return $this->tokens->create('r_paren', ')', $pos);
    }

    /**
     * contains: '*='
     * 
     * @return Token|void
     */
    protected function contains()
    {
        $pos = $this->index;
        $this->mark();
        $buf = $this->cur;
        $this->consume();
        $buf .= $this->cur;
        $this->consume();
        
        if ($buf === '*=')
        {
            return $this->tokens->create('contains', "*=", $pos);
        }

        $this->reset();
    }

    /**
     * contains_word: '~='
     * 
     * @return Token|void
     */
    protected function contains_word()
    {
        $pos = $this->index;
        $this->mark();
        $buf = $this->cur;
        $this->consume();
        $buf .= $this->cur;
        $this->consume();
        
        if ($buf === '~=')
        {
            return $this->tokens->create('contains_word', "~=", $pos);
        }

        $this->reset();
    }


    /**
     * ends_with: '$='
     * 
     * @return Token|void
     */
    protected function ends_with()
    {
        $pos = $this->index;
        $this->mark();
        $buf = $this->cur;
        $this->consume();
        $buf .= $this->cur;
        $this->consume();
        
        if ($buf === '$=')
        {
            return $this->tokens->create('ends_with', "$=", $pos);
        }

        $this->reset();
    }

    /**
     * starts_with: '$='
     * 
     * @return Token|void
     */
    protected function starts_with()
    {
        $pos = $this->index;
        $this->mark();
        $buf = $this->cur;
        $this->consume();
        $buf .= $this->cur;
        $this->consume();
        
        if ($buf === '^=')
        {
            return $this->tokens->create('starts_with', "^=", $pos);
        }

        $this->reset();
    }

    /**
     * lt_or_lte: '<' | '<='
     * 
     * @return Token|void
     */
    protected function lt_or_lte()
    {
        $pos = $this->index;
        $this->mark();
        $buf = $this->cur;
        $this->consume();
        $buf .= $this->cur;
        $this->consume();
        
        if ($buf === '<=')
        {
            return $this->tokens->create('lte', "<=", $pos);
        }
        else
        {
            $this->reset();
            $this->consume();
            return $this->tokens->create('lt', '<', $pos);
        }

        $this->reset();
    }

    /**
     * gt_or_gte: '>' | '>='
     * 
     * @return Token|void
     */
    protected function gt_or_gte()
    {
        $pos = $this->index;
        $this->mark();
        $buf = $this->cur;
        $this->consume();
        $buf .= $this->cur;
        $this->consume();
        
        if ($buf === '>=')
        {
            return $this->tokens->create('gte', ">=", $pos);
        }
        else
        {
            $this->reset();
            $this->consume();
            return $this->tokens->create('gt', '>', $pos);
        }

        $this->reset();
    }

    /**
     * Literal Operators predictor
     * 
     * @return Token|null
     */
    protected function literal_ops()
    {
        $pos = $this->index;
        $this->mark();
        $lit = $this->literal();

        if ($lit)
        {
            switch (strtolower($lit->text))
            {
                case 'equals':
                    return $this->tokens->create('equals', $lit->text, $pos);
                case 'startswith':
                    return $this->tokens->create('starts_with', $lit->text, $pos);
                case 'endswith':
                    return $this->tokens->create('ends_with', $lit->text, $pos);
                case 'contains':
                    return $this->tokens->create('contains', $lit->text, $pos);
                case 'containsall':
                    return $this->tokens->create('contains_all', $lit->text, $pos);
                case 'containsany':
                    return $this->tokens->create('contains_any', $lit->text, $pos);
                case 'containsonly':
                    return $this->tokens->create('contains_only', $lit->text, $pos);
                case 'lt':
                case 'lowerthan':
                    return $this->tokens->create('lt', $lit->text, $pos);
                case 'lte':
                case 'lowerthanequal':
                    return $this->tokens->create('lte', $lit->text, $pos);
                case 'gt':
                case 'greaterthan':
                    return $this->tokens->create('gt', $lit->text, $pos);
                case 'gte':
                case 'greaterthantequal':
                    return $this->tokens->create('gte', $lit->text, $pos);
                case 'empty':
                    return $this->tokens->create('empty', $lit->text, $pos);
            }
        }
        $this->reset();
    }
}
