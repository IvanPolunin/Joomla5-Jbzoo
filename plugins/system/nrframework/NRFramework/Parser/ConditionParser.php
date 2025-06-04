<?php

/**
 *  @author          Tassos.gr <info@tassos.gr>
 *  @link            https://www.tassos.gr
 *  @copyright       Copyright © 2024 Tassos All Rights Reserved
 *  @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\Parser;

defined('_JEXEC') or die;

use NRFramework\Parser\Parser;
use NRFramework\Parser\ConditionLexer;
/**
 *  ConditionParser
 *  LL(1) recursive-decent parser
 *  Uses NRFramework\Parser\ConditionLexer as input source
 * 
 *  Grammar:
 *  -------- 
 *  expr        : condition (logic_op condition)* (option)*
 *  condition   : {negate_op} alias (parameter)* | (alias|l_func) ({negate_op}? operator (values)? (parameter)*
 *  alias       : {ident}
 *  values      : value ({comma} value)*
 *  value       : {quotedval} | ({literal} | {ident})+
 *  func        : {ident} {l_paren} values {r_paren}
 *  l_func      : func
 *  r_func      : func  
 *  parameter   : {param} ({equals} value)?
 *  option      : {ident} ({equals} value)?
 *  logic_op    : {and} | {or}
 *  operator    : {equals} | {starts_with} | {ends_with} | {empty} | {contains} | {contains_any} | {contains_all}| {contains_only} | {lt} | {lte} | {gt} | {gte}
 */
class ConditionParser extends Parser
{
    /**
     *  Constructor
     *
     *  @param ConditionLexer $input
     */
    public function __construct(ConditionLexer $input)
    {
        parent::__construct($input, 2);
    }
    
    /**
     *  value : {quotedval} | ({literal} | {ident})+
     * 
     *  @return string
     *  @throws Exception
     */
    public function value()
    {
        if ($this->lookahead[0]->type === 'quotedvalue')
        {
            $text = $this->lookahead[0]->text;
            $this->match('quotedvalue');
            return $text;
        }
        else if ($this->lookahead[0]->type !== 'ident' && $this->lookahead[0]->type !== 'literal')
        {
            throw new \Exception("Syntax error in ConditionParser::value(); expecting 'ident' or 'literal'; found {$this->lookahead[0]}");
        }

        $text = $this->lookahead[0]->text;
        $this->consume();

        while ($this->lookahead[0]->type === 'ident' || $this->lookahead[0]->type === 'literal')
        {
            $text .= ' ' . $this->lookahead[0]->text;
            $this->consume();
        }

        return $text;
    }

    /**
     *  values : value ({comma} value)*
     * 
     *  @return array
     */
    public function values()
    {
        $vals = [];
        $vals[] = $this->value();

        while ($this->lookahead[0]->type === 'comma')
        {
            $this->consume();
            $vals[] = $this->value();
        }
        return $vals;
    }

    /**
     * func : {ident} {l_paren} values {r_paren}
     *  
     */
    public function func()
    {
        $func_name = $this->lookahead[0]->text;
        $this->match('ident');
        $this->match('l_paren');
        
        if ($this->lookahead[0]->type === 'quotedvalue' ||
            $this->lookahead[0]->type === 'ident' ||
            $this->lookahead[0]->type === 'literal')
        {
            $func_args = $this->values();
        }

        $this->match('r_paren');

        return ['func_name' => $func_name, 'func_args' => $func_args ?? []];
    }

    /**
     *  parameter : {param} ({equals} value)?
     * 
     *  @return string
     */
    public function param()
    {
        $param = $this->lookahead[0]->text;
        $value = true;
        $this->match('param');

        // If this is the 'context' parameter make sure that it appears as the last token
        // if ($param === 'context')
        // {
        //     $this->consume(); // consume the 'equals' operator
        //     $value = $this->value(); // expect a value
        //     if ($this->lookahead[0]->type !== 'EOF')
        //     {
        //         throw new \Exception("Syntax error in ConditionParser::param(); the 'context' parameter can only appear as the last token");
        //     }
        // } 
        // else
        if ($this->isOperator($this->lookahead[0]->type))
        {
            if ($this->lookahead[0]->type === 'equals')
            {
                $this->consume(); // consume the 'equals' operator
                $value = $this->value(); // expect a value
            }
            else
            {
                // only the 'equals' operator is supported for the 'param' rule.
                throw new \Exception("Syntax error in ConditionParser::param(); expecting 'equals', found {$this->lookahead[0]}");
            }
        }

        return ['param' => $param, 'value' => $value];
    }

    /**
     *  alias : {ident}
     * 
     *  @return string
     */
    public function alias()
    {
        $sel = $this->lookahead[0]->text;
        $this->match('ident');
        return $sel;
    }


    /**
     *  condition : {negate_op} alias (parameter)* | alias ({negate_op}? operator values)? (parameter)*
     * 
     *  @return object
     */
    public function condition()
    {
        $result = [];
        $operator = '';
        $params = [];
        $negate_op = false;

        if ($this->lookahead[0]->type === 'negate_op')
        {
            $this->match('negate_op');
            $operator = 'empty';
            $result['alias'] = $this->alias();
        }
        else
        {
            if($this->lookahead[0]->type === 'ident' && $this->lookahead[1]->type === 'l_paren')
            {
                $l_func = $this->func();
                $result['l_func_name'] = $l_func['func_name'];
                $result['l_func_args'] = $l_func['func_args'];
            }
            else
            {
                $result['alias'] = $this->alias();
            }

            if ($this->lookahead[0]->type === 'negate_op')
            {
                $this->match('negate_op');
                $negate_op = true;
                // expect an operator after '!'
                if (!$this->isOperator($this->lookahead[0]->type))
                {
                    throw new Exceptions\SyntaxErrorException("Expecting an 'operator' after '!', found {$this->lookahead[0]}");
                }
            }
            if ($this->isOperator($this->lookahead[0]->type))
            {
                $operator = $this->operator();
                if($this->lookahead[0]->type === 'ident' && $this->lookahead[1]->type === 'l_paren')
                {
                    $r_func = $this->func();
                    $result['r_func_name'] = $r_func['func_name'];
                    $result['r_func_args'] = $r_func['func_args'];
                }
                else if (
                    $this->lookahead[0]->type === 'quotedvalue' ||
                    $this->lookahead[0]->type === 'ident' ||
                    $this->lookahead[0]->type === 'literal'
                )
                {
                    

                    $values = $this->values();
                    if (count($values) === 1)
                    {
                        $values = $values[0];
                    }
                    $result['values'] = $values;
                }
            }
        }

        while ($this->lookahead[0]->type === 'param')
        {
            $params[] = $this->param();
        }

        if (!$operator) {
            $operator = 'empty';
            $negate_op = true;
        }

        //
        $_params = [];
        foreach($params as $p)
        {
            $_params[$p['param']] = $p['value'];
        }
        
        $result['operator'] = $operator;
        $result['negate_op'] = $negate_op;
        $result['params'] = $_params;
        return $result;
    }

    /**
     *  operator : {equals} | {starts_with} | {ends_with} | {empty} | {contains} | {contains_any} | {contains_all}| {contains_only} | {lt} | {lte} | {gt} | {gte}
     * 
     *  @return string
     *  @throws Exception
     */
    public function operator()
    {
        if (!$this->isOperator($this->lookahead[0]->type))
        {
            throw new Exceptions\SyntaxErrorException("Expecting an 'operator', found " . $this->lookahead[0]);
        }

        $op = $this->lookahead[0]->type;
        $this->consume();
        return $op;
    }

    /**
     *  expr  : condition ({logic_op} condition)* (option)*
     * 
     *  @return array The condition expression results
     */
    public function expr()
    {
        $logic_op = 'and';
        $res = [
            'conditions'    => [$this->condition()],
            'logic_op'      => 'and',
            'context'       => null,
            'global_params' => []
        ];

        if ($this->lookahead[0]->type === 'or')
        {
            $logic_op = 'or';
        }

        while ($this->lookahead[0]->type !== 'EOF')
        {
            $this->match($logic_op);
            $res['conditions'][] = $this->condition();
        }

        $res['logic_op'] = $logic_op;

        // check the last parsed condition for global parameters
        $globalParams = [
            'debug',
            'dateformat',
            'context',
            'nopreparecontent',
            'excludebots'
        ];

        $last_params = $res['conditions'][count($res['conditions'])-1]['params'];
        foreach(array_keys($last_params) as $param_key)
        {
            if (in_array(strtolower($param_key), $globalParams))
            {
                $res['global_params'][strtolower($param_key)] = $last_params[$param_key];
                unset($res['conditions'][count($res['conditions'])-1]['params'][$param_key]);
            }
        }
        // foreach ($last_params as $idx => $param)
        // {
        //     if (in_array($param['param'], $globalParams))
        //     {
        //         $res['global_params'][$param['param']] = $param['value'];
        //         unset($res['conditions'][count($res['conditions'])-1]['params'][$idx]);
        //     }
        // }
        return $res;
    }

    /**
     * Helper method that checks if the given Token is an operator.
     */
    protected function isOperator($token_type)
    {

        return in_array($token_type, [
            'equals',
            'starts_with',
            'ends_with',
            'contains',
            'contains_any',
            'contains_all',
            'contains_only',
            'lt',
            'lte',
            'gt',
            'gte',
            'empty'
        ]);    
    }
}
