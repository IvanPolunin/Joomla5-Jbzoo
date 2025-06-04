<?php

/**
 *  @author          Tassos.gr <info@tassos.gr>
 *  @link            https://www.tassos.gr
 *  @copyright       Copyright © 2024 Tassos All Rights Reserved
 *  @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\Parser;

use DateTime;
use DateTimeImmutable;

defined('_JEXEC') or die;

class ShortcodeParserHelper {
    /**
     *  Input text buffer
     *
     *  @var string
     */
    protected $text;

    /**
     *  ShortcodeParser options
     *
     *  @var object
     */
    protected $parser_options;

    /**
     * Parsing payload, associative array
     * 
     * @var array
     */
    protected $payload;

    /**
     * Parsing context
     * 
     * @var string
     */
    protected $context;

    /**
	 * List of areas in the content that should not be parsed for Smart Tags.
	 *
	 * @var array
	 */
	private $protectedAreas = [];
    
    /**
     *  @param string       $text               Text buffer (stored as a reference)
     *  @param object       $parser_options     ShortcodeParser options
     *  @param array|null   $payload            Parser payload
     *  @param string|null  $context            Parser context
     */
    public function __construct(&$text, $payload = null, $parser_options = null, $context = null)
    {
        $this->text             =&  $text;
        $this->payload          =   $payload;
        $this->context          =   $context;

        if (!$parser_options)
        {
            $this->parser_options                   = new \stdClass();
            $this->parser_options->tag_open_char    = '{';
            $this->parser_options->tag_close_char   = '}';
            $this->parser_options->if_keyword       = 'if';
            $this->parser_options->log_errors       = 'false';
        }
        else
        {
            $this->parser_options = $parser_options;
        }
    }

    /**
     * The text being parsed may contain sensitive information and areas where parsing of shortcodes, such as <script> tags, must be skipped. 
     * This method aids in protecting these areas by replacing the sensitive content with a hash, which can be restored later.
     *
     * @return void
     */
    private function protectAreas()
    {
		$reg = '/<script[\s\S]*?>[\s\S]*?<\/script>/';
		preg_match_all($reg, $this->text, $protectedAreas);
        
		if (!$protectedAreas[0])
		{
            return;
        }

        foreach ($protectedAreas[0] as $protectedArea)
        {
            $hash = md5($protectedArea);

            $this->protectedAreas[] = [$hash, $protectedArea];

            $this->text = str_replace($protectedArea, $hash, $this->text);
        }
    }

    /**
     * Restore protected areas in the result text.
     *
     * @return void
     */
    private function restoreProtectedAreas()
    {
        if (empty($this->protectedAreas))
        {
            return;
        }

        foreach ($this->protectedAreas as $protectedArea)
        {
            $this->text = str_ireplace($protectedArea[0], $protectedArea[1], $this->text);
        }			
    }

    /**
     * 
     */
    public function parseAndReplace()
    {
        $this->protectAreas();

        $replacements    = [];
        $shortcodes_text = [];

        $shortcode_lexer  = new ShortcodeLexer($this->text, $this->parser_options);
        $shortcode_parser = new ShortcodeParser($shortcode_lexer, $this->parser_options);
        $shortcodes       = $shortcode_parser->expr();

        foreach ($shortcodes as $shortcode)
        {
            // check if the shortcode has errors
            if (\property_exists($shortcode, 'parser_error'))
            {
                // we cannot remove the shortcode at this point because it's 'content' could not be parsed
                // error message is added before the shortode
                $this->text = substr_replace($this->text, $shortcode->parser_error, $shortcode->position, 0);
                continue;
            }

            $cond_lexer  = new ConditionLexer(htmlspecialchars_decode($shortcode->conditions));
            $cond_parser = new ConditionParser($cond_lexer);
            
            // parse the shortcode's 'conditions' expression
            $conditions  = [];
            try
            {
                $conditions = $cond_parser->expr();

                // check if the shortcode has the correct context
                // if ($conditions['context'] !== $this->context)
                // {
                //     continue;
                // }

                // get the parsed logical operator (and/or)
                $logic_op = array_key_exists('logic_op', $conditions) ? $conditions['logic_op'] : 'and';

                // check if the debug param is set and we are logged in as a Super User
                $debug_enabled = array_key_exists('debug', $conditions['global_params']) && $conditions['global_params']['debug'] &&
                                \Joomla\CMS\Factory::getUser()->authorise('core.admin');
                
                // check for the noPrepareContent global param
                $prepare_content = !array_key_exists('nopreparecontent', $conditions['global_params']);

                // evaluate conditions
                $evaluator  = new ConditionsEvaluator($conditions['conditions'], $this->payload, $debug_enabled);
                $results    = $evaluator->evaluate();

                // get the final result
                $pass = $logic_op === 'and' && !empty($results);
                foreach($results as $result)
                {
                    if ($logic_op === 'and')
                    {
                        $pass &= $result['pass'];
                    }
                    else
                    {
                        $pass |= $result['pass'];
                    }
                }
                //
                $replacement = $shortcode_parser->getReplacement($shortcode->content, $pass);
                if ($debug_enabled)
                {
                    list($content, $alt_content) = $shortcode_parser->getReplacement($shortcode->content, null);
                    $replacement .= $this->prepareDebugInfo($shortcode->conditions, $conditions, $results, $pass, $content, $alt_content);
                }
            }
            catch (\Exception $error)
            {
                // Log the error and remove the shortcode from the input text
                $replacement = $error->getMessage();
            }

            // fire the onContentPrepare event for the replacement text
            // if ($prepare_content)
            // {
            //     $replacement = \Joomla\CMS\HTML\Helpers\Content::prepare($replacement);
            // }

            // store 'replacement' text
            $replacements[] = $replacement;
            
            // store the original shortcode text
            $shortcodes_text[] = substr($this->text, $shortcode->start, $shortcode->length);
        }

        // replace all shortcodes
        $this->replaceContent($shortcodes_text, $replacements);

        $this->restoreProtectedAreas();
    }

    /**
     *  Performs content replacement in the input text buffer
     *
     *  @param  array  $shortcodes_text  Array containing the shortcodes text
     *  @param  array  $replacements     Array containng the shortcode replacements
     *  @param  array  $debug_info       Contains debug info for each shortcode
     *  @return void
     */
    protected function replaceContent($shortcodes_text, $replacements)
    {
        $this->text = \str_replace($shortcodes_text, $replacements, $this->text);
    }

    /**
     * 
     */
    protected function prepareDebugInfo($conditions_text, $conditions, $results, $pass, $content, $alt_content)
    {
        $format_value = function($value)
        {
            if (is_array($value))
            {
                return implode(', ', $value);
            }
            
            if ($value instanceof \DateTime || $value instanceof \DateTimeImmutable)
            {
                return $value->format(\DateTimeInterface::RFC1036);
            }

            return $value;
        };

        $format_condition = function($conditions) use ($results, $format_value, $conditions_text)
        {
            $res = [];
            foreach ($conditions['conditions'] as $idx => $condition) 
            {
                $params = count($condition['params']) ?
                    array_reduce(array_keys($condition['params']), function($acc, $key) use($condition) {
                        return $acc . "{$key}: " . $condition['params'][$key] . "<br>";
                    }, '') :
                    null;

                $res[] = [
                    'title' => ($condition['alias'] ?? $results[$idx]['l_func_name'] . '('. $format_value($results[$idx]['l_func_args']) . ') ')  . ($results[$idx]['pass'] ? '<span style="color: green;"> &#10003;</span>' : '<span style="color: red;"> &#10007;</span>'),
                    'body'  => '<ul style="margin-bottom: 0">' . implode('', 
                                array_filter([
                                    '<li>Condition Operand: ' . (array_key_exists('l_func_name', $results[$idx]) ? $format_value($results[$idx]['l_func_val']) : $format_value($results[$idx]['actual_value'])) .
                                    (array_key_exists('l_eval', $results[$idx]) && ($results[$idx]['l_eval'] != $results[$idx]['l_func_val']) ? ' (evaluated as "' . $format_value($results[$idx]['l_eval']) . '")': '') .'</li>',

                                    $condition['values'] ? '<li>Value Operand: ' . $format_value($condition['values']) .
                                    (array_key_exists('r_evbal', $results[$idx]) && ($results[$idx]['r_eval'] != $condition['values']) ? ' (evaluated as "' . $format_value($results[$idx]['r_eval']) .'")' : '') . '</li>' : null,

                                    array_key_exists('r_func_name', $results[$idx]) ? '<li>Value Operand: ' . $results[$idx]['r_func_name'] . '('. $format_value($results[$idx]['r_func_args']) . '): ' . $format_value($results[$idx]['r_func_val']) .'</li>': null,

                                    '<li>Operator: ' . $this->operatorToString($condition['operator']) .'</li>',

                                    $params ? '<li style="list-style-type: none; margin-left: -1rem;">' . $this->debugInfoHTML(['title' => 'Parameters', 'body' => $params]) . '</li>': null
                                ])
                                ) . '</ul>'
                ];
            }
            return $res;
        };

        $title = $pass ? '<span style="color: green;"> &#10003;</span>' : '<span style="color: red;"> &#10007;</span>';
        $info = [
            'title'     => str_replace('--debug', '', $conditions_text) . ' ' . $title,
            'body'      => '',
            'children'  => array_filter([
                ['title' => 'Conditions', 'body' => '', 'children' => $format_condition($conditions)],
                count($conditions['conditions']) > 1 ? ['title' => 'Logical Operator: ' . $conditions['logic_op'], 'children' => []] : null,
                ['title' => 'Content', 'body' => $content, 'children' => []],
                !empty($alt_content) ? ['title' => 'Alt. Content', 'body' => $alt_content, 'children' => []] : null
                
            ])
        ];
        
        return '<div style="display: flex; justify-content: center; text-align: start;">' . $this->debugInfoHTML($info) . '</div>';
    }

    /**
     * 
     */
    protected function debugInfoHTML($info)
    {
        $title = \array_key_exists('title', $info) ? $info['title'] : '';
        $body =  \array_key_exists('body', $info) ? $info['body'] : '';
        $children = '';
        if (\array_key_exists('children', $info))
        {
            foreach ($info['children'] as $c)
            {
                $children .= $this->debugInfoHTML($c);
            }
        }
        return '<details style=""><summary style="cursor: pointer; ">' . $title . '</summary><div style="margin: 0.2em 0.5em;">' . $body . $children . '</div></details>';
    }

    /**
     * Converts shortcode operators to a human readable string
     * 
     */
    protected function operatorToString($op)
    {
        switch($op)
        {
            case 'equals':
                return 'equals';
            case 'starts_with':
                return 'startsWith';
            case 'ends_with':
                return 'endsWith';
            case 'contains':
                return 'contains';
            case 'contains_any':
                return 'containsAny';
            case 'contains_all':
                return 'containsAll';
            case 'contains_only':
                return 'containsOnly';
            case 'lt':
                return 'lessThan';
            case 'lte':
                return 'lessThanEquals';
            case 'gt':
                return 'greaterThan';
            case 'gte':
                return 'greaterThanEquals';
            case 'empty':
                return 'empty';
            default:
                throw new Exceptions\UnknownOperatorException($op);
        }
    }
}