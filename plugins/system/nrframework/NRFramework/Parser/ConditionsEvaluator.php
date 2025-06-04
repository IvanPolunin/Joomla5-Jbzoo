<?php

/**
 *  @author          Tassos.gr <info@tassos.gr>
 *  @link            https://www.tassos.gr
 *  @copyright       Copyright © 2024 Tassos All Rights Reserved
 *  @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\Parser;

defined('_JEXEC') or die;

use DateTime;
use DateTimeZone;
use Exception;
use Joomla\CMS\Factory;

class ConditionsEvaluator
{
    /**
     * Payload associative array
     * 
     * @var array
     */
    protected $payload;

    /**
     * Parsed conditions
     * 
     * @var array
     */
    protected $conditions;

    /**
     * Framework Condition aliases
     * 
     * @var array
     */
    protected $condition_aliases;

    /**
     * Debug flag
     * 
     * @var bool
     */
    protected $debug;

    /**
     * @param array $conditions The parsed conditions
     * @param array $payload    Shortcode parser payload
     */
    public function __construct($conditions, $payload = null, $debug = false)
    {
        $this->conditions   = $conditions;
        $this->payload      = $payload;
        $this->debug        = $debug;

        $this->generateConditionAliasesMap();
    }

    /**
     * @return array
     */
    public function evaluate() : array
    {
        $results = [];
        $caseSensitive = false;
        
        foreach($this->conditions as $condition)
        {
            // case sensitivity param
            if (array_key_exists('caseSensitive', $condition['params']))
            {
                $caseSensitive = strtolower($condition['params']['caseSensitive']) != 'false';
            }

            $result = [
                'operator'  => $condition['operator'],
                'params'    => $condition['params']
            ];
            $l_value = null;
            $r_value = null;

            if(array_key_exists('r_func_name', $condition))
            {
                $r_value = $this->applyFunction($condition['r_func_name'], $condition['r_func_args']);
                $result['r_func_name'] = $condition['r_func_name'];
                $result['r_func_args'] = $condition['r_func_args'];
                $result['r_func_val'] = $r_value;
            }
            else
            {
                $r_value = $condition['values'] ?? null;
            }

            if (array_key_exists('alias', $condition) && $this->isPayloadCondition($condition['alias']))
            {
                $l_value = $this->payload[$condition['alias']];
                $result = array_merge($result, $this->evaluatePayloadCondition($l_value, $r_value, $condition['operator'], $caseSensitive));
                $result['pass'] = $condition['negate_op'] ? !$result['pass'] : $result['pass'];
                $result['actual_value'] = $this->payload[$condition['alias']];
            }
            else if (array_key_exists('alias', $condition) && $this->isFrameworkCondition($condition['alias']))
            {
                $result = array_merge($result, $this->evaluateFrameworkCondition($condition, $r_value));
            }
            else if (array_key_exists('l_func_name', $condition))
            {
                $l_value = $this->applyFunction($condition['l_func_name'], $condition['l_func_args']);
                $result['l_func_name'] = $condition['l_func_name'];
                $result['l_func_args'] = $condition['l_func_args'];
                $result['l_func_val'] = $l_value;

                $result = array_merge($result, $this->evaluatePayloadCondition($l_value, $r_value, $condition['operator'], $caseSensitive));
                $result['pass'] = $condition['negate_op'] ? !$result['pass'] : $result['pass'];
            }
            // not a payload or framework condition with the 'empty' op
            else if ($condition['operator'] === 'empty')
            {
                $result['pass'] = !$condition['negate_op'];
            }
            //
            else
            {
                // Unknown condition
                throw new Exceptions\InvalidConditionException($condition['alias']);
            }
            
            $results[] = $result;
        }
        return $results;
    }

    /**
     * 
     */
    public function applyFunction($func_name, $args)
    {
        $arg_values = [];
        foreach($args as $arg)
        {
            if ($this->isPayloadCondition($arg))
            {
                $arg_values[] = $this->payload[$arg];
            }
            else if ($this->isFrameworkCondition($arg))
            {
                $conditions_helper = \NRFramework\Conditions\ConditionsHelper::getInstance();
                $framework_condition = $conditions_helper->getCondition($this->condition_aliases[strtolower($arg)]);
                // Some framework condition don't implement the 'value()' method.
                if (method_exists($framework_condition, 'value'))
                {
                    $arg_values[] = $framework_condition->value();
                }
                else
                {
                    throw new Exceptions\ConditionValueException($arg);
                }
            }
            else
            {
                $arg_values[] = $arg;
            }
        }

        switch(strtolower($func_name))
        {
            case 'count':
                return $this->funcCount($arg_values);
            case 'today':
                return $this->funcToday();
            case 'now':
                return $this->funcNow();
            case 'date':
                return $this->funcDate($arg_values);
            case 'datediff':
                return $this->funcDateDiff($arg_values);
            default:
                throw new Exceptions\UnknownFunctionException($func_name);
            
        }
    }

    /**
     * 
     */
    public function funcCount($args)
    {
        if (count($args) !== 1)
        {
            throw new Exception("count() accepts 1 argument. " . count($args) . " were given.");
        }

        if (is_array($args[0]))
        {
            
            return count($args[0]);
        }
        else if (is_string($args[0]))
        {
            return mb_strlen($args[0]);
        }
        else
        {
            throw new Exception("count() accepts only strings and arrays.");
        }

    }

    /**
     * 
     */
    public function funcToday()
    {
        return (new DateTime('today'))->format('Y-m-d');
    }

    /**
     * 
     */
    public function funcNow()
    {
        return new DateTime('now');
    }

    /**
     * 
     */
    public function funcDate($args)
    {
        if (count($args) < 1 || count($args) > 3)
        {
            throw new Exception("date() accepts between 1 and 3 arguments. " . count($args) . " were given.");
        }

        if ($args[0] instanceof \DateTime || $args[0] instanceof \DateTimeImmutable)
        {
            return $args[0];
        }
        
        $date   = $args[0];
        $format = null;

        if (count($args) > 1)
        {
            $format = $args[1] === 'null' ? null : $args[1];
        }
        
        $timezone = new \DateTimeZone($args[2] ?? Factory::getApplication()->get('offset','UTC'));
        
        if ($format)
        {
            return \DateTime::createFromFormat('!'.$format, $date, $timezone);
        }

        return new \DateTime($date, $timezone);
    }

    /**
     * 
     */
    public function funcDateDiff($args)
    {
        if (count($args) != 2)
        {
            throw new Exception("dateDiff() accepts 2 arguments. " . count($args) . " were given.");
        }

        $date1 = $this->convertToDateTime($args[0]);
        $date2 = $this->convertToDateTime($args[1]);

        return abs($date1->diff($date2)->days);
    }

    /**
     * @var array $condition 
     * 
     * @return array Evaluation result
     */
    protected function evaluatePayloadCondition($l_value, $r_value, $operator, $caseSensitive = false) : array
    {
        if (!$caseSensitive)
        {
            $l_value = $this->_lowercaseValues($l_value);
            $r_value = $this->_lowercaseValues($r_value);
        }

        $result = [];
        switch($operator)
        {
            case 'equals':
                $result = $this->evaluateEquals($l_value, $r_value);
                break;
            case 'starts_with':
                $result =  $this->evaluateStartsWith($l_value, $r_value);
                break;
            case 'ends_with':
                $result =  $this->evaluateEndsWith($l_value, $r_value);
                break;
            case 'contains':
                $result =  $this->evaluateContains($l_value, $r_value);
                break;
            case 'contains_any':
                $result = $this->evaluateContainsAny($l_value, $r_value);
                break;
            case 'contains_all':
                $result = $this->evaluateContainsAll($l_value, $r_value);
                break;
            case 'contains_only':
                $result = $this->evaluateContainsOnly($l_value, $r_value);
                break;
            case 'lt':
                $result = $this->evaluateLessThan($l_value, $r_value);
                break;
            case 'lte':
                $result = $this->evaluateLessThanEquals($l_value, $r_value);
                break;
            case 'gt':
                $result = $this->evaluateGreaterThan($l_value, $r_value);
                break;
            case 'gte':
                $result = $this->evaluateGreaterThanEquals($l_value, $r_value);
                break;
            case 'empty':
                $result = $this->evaluateEmpty($l_value);
                break;
            default:
                throw new Exceptions\UnknownOperatorException($operator);
        }
        return $result;
    }

    /**
     * @var array $condition 
     * 
     * @return array Evaluation result
     */
    public function evaluateFrameworkCondition($condition, $r_value)
    {
        $operator = $condition['operator'];

        // Certain framework operators only work on single values.
        // Force fail if the parsed condition contains more than one value.
        if (in_array($operator, [
            'contains',
            'lt', 'lte',
            'gt', 'gte',
            'starts_with',
            'ends_with'
        ]))
        {
            if (is_array($r_value) && !empty($r_value))
            {
                throw new Exceptions\UnsupportedValueOperandException($operator, false);
            }
        }
        
        //
        $conditions_helper = \NRFramework\Conditions\ConditionsHelper::getInstance();
        $result = ['actual_value' => null];

        // Transform 'caseSensitive' parameter to 'ignoreCase'
        if (array_key_exists('caseSensitive', $condition['params']))
        {
            $condition['params']['ignoreCase'] = !$condition['params']['caseSensitive'];
        }

        // Instantiate the framework condition
        $framework_condition = $conditions_helper->getCondition(
            $this->condition_aliases[strtolower($condition['alias'])],
            $r_value,
            $operator,
            $condition['params']
        );

        // Try to grab the actual condition's value if 'debug' is enabled.
        if ($this->debug)
        {
            // Some framework conditions don't implement the 'value()' method.
            if (method_exists($framework_condition, 'value'))
            {
                $result['actual_value'] = $framework_condition->value();
            }
        }       

        // Special handling for Date/Time framework conditions
        if (in_array(strtolower($condition['alias']), ['date', 'time', 'datetime']))
        {
            $pass = $this->evaluatePayloadCondition($framework_condition->value(), $r_value, $operator)['pass'];
        }
        // Check if the condition passes using the 'passOne()' helper method
        else
        {
            $pass = $conditions_helper->passOne(
                $this->condition_aliases[strtolower($condition['alias'])],
                $r_value,
                $operator,
                $condition['params']
            );   
        }
        $result['pass'] = $condition['negate_op'] ? !$pass : $pass;

        return $result;
    }

    /**
     * Generates an array mapping Condition aliases to Condition class names
     */
    protected function generateConditionAliasesMap()
    {
        $conditions_namespace = 'NRFramework\\Conditions\\Conditions\\';
        $dir_iterator = new \RecursiveDirectoryIterator(JPATH_PLUGINS . "/system/nrframework/NRFramework/Conditions/Conditions/");
        $iterator = new \RecursiveIteratorIterator($dir_iterator, \RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $file)
        {
            $condition_class = str_replace(JPATH_PLUGINS . "/system/nrframework/NRFramework/Conditions/Conditions/", '', $file);
            $condition_class = str_replace('.php', '', $condition_class);
            $condition_class = str_replace('/', '\\', $condition_class);
            if (class_exists($conditions_namespace . $condition_class))
            {
                $this->condition_aliases[strtolower($file->getBasename('.php'))] = $condition_class;
                if (property_exists($conditions_namespace . $condition_class, 'shortcode_aliases'))
                {
                    foreach(($conditions_namespace . $condition_class)::$shortcode_aliases as $alias)
                    {
                        $this->condition_aliases[$alias] = $condition_class;
                    }
                }
            }
        }
    }

    /**
     * 
     */
    protected function convertToDateTime($date, $format = null, $tz = null)
    {
        if ($tz == null)
        {
            $tz = Factory::getApplication()->getCfg('offset','UTC');
        }    
        
        if ($date instanceof \DateTime || $date instanceof \DateTimeImmutable)
        {
            return $date;
        }

        try
        {
            if ($format)
            {
                return DateTime::createFromFormat($format, $date, $tz);
            }
            
            return new DateTime($date, new DateTimeZone($tz));
        }
        catch (\Throwable $t)
        {
            return null;
        }
    }

    /**
     * 
     */
    protected function isPayloadCondition($alias)
    {
        return $this->payload && array_key_exists($alias, $this->payload);
    }

    /**
     * 
     */
    protected function isFrameworkCondition($alias)
    {
        return array_key_exists(strtolower($alias), $this->condition_aliases);
    }

    /**
     * @return array Evaluation result
     */
    protected function evaluateEquals($l_value, $r_value) : array
    {
        // are we comparing arrays?
        if (is_array($l_value))
        {
            return $this->evaluateContainsAny($l_value, $r_value);
        }
        
        if (is_numeric($r_value))
        {
            return ['pass' => $l_value == $r_value];
        }
        
        
        // check if we are comparing dates
        $l_date = $this->convertToDateTime($l_value);
        $r_date = $this->convertToDateTime($r_value);
        if($l_date && $r_date)
        {
            if (is_string($r_value) && !preg_match("/\d{1,2}:\d{1,2}(:\d{1,2})?/", $r_value))
            {
                $l_date->setTime(0,0);
                $r_date->setTime(0,0);
            }
            return [
                'l_eval' => $l_date,
                'r_eval' => $r_date,
                'pass' => $l_date == $r_date
            ];
        }

        // generic equality test
        return ['pass' => $l_value == $r_value];
    }

    /**
     * @return array Evaluation result
     */
    protected function evaluateStartsWith($l_value, $r_value) : array
    {
        if (!is_string($l_value))
        {
            throw new Exceptions\UnsupportedOperatorException('startsWith', $l_value, false);
        }

        if (!is_string($r_value))
        {
            throw new Exceptions\UnsupportedValueOperandException('startsWith', false);
        }

        return ['pass' => $this->_starts_with($l_value, $r_value)];
    }

    /**
     * @return array Evaluation result
     */
    protected function evaluateEndsWith($l_value, $r_value) : array
    {
        if (!is_string($l_value))
        {
            throw new Exceptions\UnsupportedOperatorException('endsWith', $l_value, false);
        }

        if (!is_string($r_value))
        {
            throw new Exceptions\UnsupportedValueOperandException('endsWith', false);
        }

        return ['pass' => $this->_ends_with($l_value, $r_value)];
    }

    /**
     * @return array Evaluation result
     */
    protected function evaluateContains($l_value, $r_value) : array
    {
        if (!is_string($l_value))
        {
            throw new Exceptions\UnsupportedOperatorException('contains', $l_value, false);
        }

        if (!is_string($r_value))
        {
            throw new Exceptions\UnsupportedValueOperandException('contains', false);
        }

        return ['pass' => strlen($l_value) > 0 && strpos($l_value, $r_value) !== false];
    }

    /**
     * @return array Evaluation result
     */
    protected function evaluateContainsAny($l_value, $r_value) : array
    {
        if (!is_array($l_value))
        {
            throw new Exceptions\UnsupportedOperatorException('containsAny', $l_value, true);
        }

        $r_value = (array) $r_value;
        return ['pass' => !empty(array_intersect($l_value, $r_value))];
    }

    /**
     * @return array Evaluation result
     */
    protected function evaluateContainsAll($l_value, $r_value) : array
    {
        if (!is_array($l_value))
        {
            throw new Exceptions\UnsupportedOperatorException('containsAll', $l_value, true);
        }

        $r_value = (array) $r_value;
        return ['pass' => count(array_intersect($l_value, $r_value)) == count($r_value)];
    }

    /**
     * @return array Evaluation result
     */
    protected function evaluateContainsOnly($l_value, $r_value) : array
    {
        if (!is_array($l_value))
        {
            throw new Exceptions\UnsupportedOperatorException('containsOnly', $l_value, true);
        }

        $r_value = (array) $r_value;
        return ['pass' => count(array_diff($l_value, $r_value)) == 0];
    }

    /**
     * @return array Evaluation result
     */
    protected function evaluateLessThan($l_value, $r_value) : array
    {
        if (is_numeric($r_value))
        {
            return ['pass' => $l_value < $r_value];
        }

        // check if we are comparing dates
        $l_date = $this->convertToDateTime($l_value);
        $r_date = $this->convertToDateTime($r_value);
        if($l_date && $r_date)
        {
            if (is_string($r_value) && !preg_match("/\d{1,2}:\d{1,2}(:\d{1,2})?/", $r_value))
            {
                $l_date->setTime(0,0);
                $r_date->setTime(0,0);
            }
            return [
                'l_eval' => $l_date,
                'r_eval' => $r_date,
                'pass' => $l_date < $r_date
            ];
        }
       
        throw new Exceptions\SyntaxErrorException("The 'lessThan' operator accepts only numeric values and dates.");
    }

    /**
     * @return array Evaluation result
     */
    protected function evaluateLessThanEquals($l_value, $r_value) : array
    {
        if (is_numeric($r_value))
        {
            return ['pass' => $l_value <= $r_value];
        }

         // check if we are comparing dates
         $l_date = $this->convertToDateTime($l_value);
         $r_date = $this->convertToDateTime($r_value);
         if($l_date && $r_date)
         {
            if (is_string($r_value) && !preg_match("/\d{1,2}:\d{1,2}(:\d{1,2})?/", $r_value))
            {
                $l_date->setTime(0,0);
                $r_date->setTime(0,0);
            }
            return [
                'l_eval' => $l_date,
                'r_eval' => $r_date,
                'pass' => $l_date <= $r_date
            ];
         }
        
         throw new Exceptions\SyntaxErrorException("The 'lessThanEquals' operator accepts only numeric values and dates.");
    }

    /**
     * @return array Evaluation result
     */
    protected function evaluateGreaterThan($l_value, $r_value) : array
    {
        if (is_numeric($r_value))
        {
            return ['pass' => $l_value > $r_value];
        }
        
        // check if we are comparing dates
        $l_date = $this->convertToDateTime($l_value);
        $r_date = $this->convertToDateTime($r_value);
        if($l_date && $r_date)
        {
            if (is_string($r_value) && !preg_match("/\d{1,2}:\d{1,2}(:\d{1,2})?/", $r_value))
            {
                $l_date->setTime(0,0);
                $r_date->setTime(0,0);
            }
            return [
                'l_eval' => $l_date,
                'r_eval' => $r_date,
                'pass' => $l_date > $r_date
            ];
        }
       
        throw new Exceptions\SyntaxErrorException("The 'greaterThan' operator accepts only numeric values and dates.");
    }

    /**
     * @return array Evaluation result
     */
    protected function evaluateGreaterThanEquals($l_value, $r_value) : array
    {
        if (is_numeric($r_value))
        {
            return ['pass' => $l_value >= $r_value];
        }
        
        // check if we are comparing dates
        $l_date = $this->convertToDateTime($l_value);
        $r_date = $this->convertToDateTime($r_value);
        if($l_date && $r_date)
        {
            if (is_string($r_value) && !preg_match("/\d{1,2}:\d{1,2}(:\d{1,2})?/", $r_value))
            {
                $l_date->setTime(0,0);
                $r_date->setTime(0,0);
            }
            return [
                'l_eval' => $l_date,
                'r_eval' => $r_date,
                'pass' => $l_date >= $r_date
            ];
        }
       
        throw new Exceptions\SyntaxErrorException("The 'greaterThanEquals' operator accepts only numeric values and dates.");        
    }

    /**
     * @return array Evaluation result
     */
    protected function evaluateEmpty($payload_value) : array
    {
        // $payload_value = $this->payload[$payload_key];

        if (is_array($payload_value))
        {
            return ['pass' => empty($payload_value)];
        }
        else if(is_string($payload_value))
        {
            $payload_value = trim($payload_value);
            return  ['pass' => empty($payload_value) || $payload_value == 'false'];
        }
        else if(is_bool($payload_value))
        {
            return ['pass' => !$payload_value];
        }       

        return ['pass' => is_null($payload_value)];
    }

    /**
     * @return bool
     */
    protected function _starts_with($haystack, $needle)
    {
        return  strlen($needle) > 0  && strncmp($haystack, $needle, strlen($needle)) === 0;
    }

    /**
     * @return bool
     */
    protected function _ends_with($haystack, $needle)
    {
        return strlen($needle) > 0 && substr($haystack, -strlen($needle)) === (string)$needle;
    }

    /**
     * @return bool
     */
    protected function _contains($haystack, $needle)
    {
        return strlen($needle) > 0 && strpos($haystack, $needle) !== false;
    }

    /**
     * @return string|array
     */
    protected function _lowercaseValues($value)
    {
        if (is_array($value))
        {
            foreach($value as $idx => $val)
            {
                if (is_string($val))    
                {
                    $value[$idx] = strtolower($val);
                }
            }
        }
        else if(is_string($value))
        {
            $value = strtolower($value);
        }

        return $value;
    }
}
