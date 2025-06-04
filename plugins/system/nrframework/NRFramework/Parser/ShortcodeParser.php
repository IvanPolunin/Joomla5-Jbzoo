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
use NRFramework\Parser\ShortcodeLexer;

/**
 *  ShortcodeParser
 *  LL(k = 3) recursive-decent parser
 *  Uses ShortcodeLexer as the input/token source
 * 
 *  Parses the following grammar:
 *  ------------------------------
 *  expr        := shortcode*   <-- Top-level expression (i.e. 0 or more shortcodes)
 *  shortcode   := ifexpr content endifexpr
 *  ifexpr      := {sc_open} {if_keyword} condition {sc_close}
 *  endifexpr   := {sc_open} {end_ifkeyword} {sc_close}
 *  content     := any text until endifexpr
 *  condition   := any text with preserved quoted values until {sc_close}
 */
class ShortcodeParser extends Parser
{
    /**
     *  shortcode opening character (e.g.: '{')
     *  @var string
     */
    protected $sc_open;

    /**
     *  shortcode closing character (e.g.: '}')
     *  @var string
     */
    protected $sc_close;

    /**
     *  Log parsing errors?
     *
     *  @var boolean
     */
    protected $log_errors;

    /**
     * The shortcode's position in the input text
     * Used only for error logging
     * 
     * @var int
     */
    protected $shortcode_position;

    /**
     *  Constructor
     *
     *  @param ShortcodeLexer $input
     *  @param Object         $options
     */
    public function __construct(ShortcodeLexer $input, $options = null)
    {
        // k = 3, look 3 tokens ahead at most
        parent::__construct($input, 3);
        $this->sc_open              = $options->tag_open_char ?? '{';
        $this->sc_close             = $options->tag_close_char ?? '}';
        $this->log_errors           = $options->log_errors ?? false;
        $this->shortcode_position   = $options->shortcode_position ?? 0;
    }

    /**
     *  Returns the correct content for replacement
     *  If $pass = null will return an array with [content, else-content]
     * 
     *  Call this method when the conditions have been parsed and 
     *  the result is known
     *
     *  @param  string $content
     *  @param  bool   $pass
     *  @return string
     */
    public function getReplacement($content, $pass)
    {
        //construct the else-tag, e.g. {else}
        $elseTag = $this->sc_open . 'else' . $this->sc_close;

        // split content on the else-tag
        $replacement = $content;
        $elseReplacement = '';

        if (strpos($content, $elseTag) !== false)
        {
            list($replacement, $elseReplacement) = explode($elseTag, $content, 2);
        }

        return $pass === null ? 
            [$replacement, $elseReplacement] :
            ($pass ? $replacement : $elseReplacement);
    }

    /**
     *  Top-level parsing method
     * 
     *  Rule:
     *  expr := shortcode*
     *
     *  @return array
     */
    public function expr()
    {
        $shortcodes = [];
        while ($this->lookahead[0]->type != 'EOF')
        {   
            $position = $this->lookahead[0]->position;
            try
            {
                if ($this->lookahead[0]->type == 'sc_open' &&
                    $this->lookahead[1]->type == 'if_keyword')
                {
                    $shortcodes[] = $this->shortcode();
                }
                else
                {
                    // this token is not part of a shortcode, keep going...
                    $this->consume();
                }
            }
            catch (\Exception $error)
            {
                // something went horribly wrong while parsing a shortcode
                // log the error and continue
                $msg = $error->getMessage();
                $near_text = $this->lookahead[0]->position + $this->shortcode_position;
                $shortcodes[] = (object) [
                    'position' => $position,
                    'parser_error' => $msg,
                    'near_text' => $near_text
                ];
                $this->consume();
            }
        }
        return $shortcodes;
    }

    /**
     *  Rule
     * 
     *  shortcode := ifexpr content endifexpr
     *
     *  @return object
     */
    protected function shortcode()
    {
        $start      = $this->lookahead[0]->position + $this->shortcode_position;
        $conditions = $this->ifexpr();
        $content    = $this->content();
        $length     = $this->lookahead[2]->position + $this->shortcode_position - $start + 1;
        $this->endifexpr();

        return (object) [
            'start'   => $start,
            'length'  => $length,
            'conditions' => $conditions,
            'content' => $content
        ];
    }

    /**
     *  Rule: 
     *  ifexpr : {sc_open} {if_keyword} condition {sc_close}
     *
     *  @return string
     */
    protected function ifexpr()
    {
        $this->match('sc_open');
        $this->match('if_keyword');
        $condition_text = $this->condition();
        $this->match('sc_close');

        return $condition_text;
    }

    /**
     *  Rule: 
     *  endifexpr := {sc_open} {end_ifkeyword} {sc_close}
     *
     *  @return void
     */
    protected function endifexpr()
    {
        $this->match('sc_open');
        $this->match('endif_keyword');
        $this->match('sc_close');
    }

    /**
     *  Rule:
     *  condition := any text with preserved quoted values until {sc_close}
     *
     *  @return string
     *  @throws Exception
     */
    protected function condition()
    {
        $buf = '';
        while ($this->lookahead[0]->type !== 'EOF')
        {
            if ($this->lookahead[0]->type === 'sc_close')
            {
                return htmlspecialchars_decode($buf);
            }
            $buf .= $this->lookahead[0]->text;
            $this->consume();
        }

        throw new Exceptions\SyntaxErrorException('Invalid condition expression.');
    }

    /**
     *  Rule:
     *  content := any text until an endif expression
     *
     *  @return string
     *  @throws Exception
     */
    protected function content()
    {
        $buf = '';
        while ($this->lookahead[0]->type !== 'EOF')
        {
            if ($this->lookahead[0]->type === 'sc_open' &&
                $this->lookahead[1]->type === 'endif_keyword' &&
                $this->lookahead[2]->type === 'sc_close')
            {
                return $buf;
            }
            
            $buf .= $this->lookahead[0]->text;
            $this->consume();
        }

        throw new Exceptions\SyntaxErrorException('Missing shortcode tag character.');
    }
}
