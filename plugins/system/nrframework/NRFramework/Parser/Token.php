<?php

/**
 *  @author          Tassos.gr <info@tassos.gr>
 *  @link            https://www.tassos.gr
 *  @copyright       Copyright © 2024 Tassos All Rights Reserved
 *  @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\Parser;

defined('_JEXEC') or die;

/**
 *  Token
 *  Represents a single lexer token
 */
class Token
{
    /**
     *  Token type
     * 
     *  @var string
     */
    public $type;

    /**
     *  The token's text 
     *
     *  @var string
     */
    public $text;

    /**
     *  Token position in the input stream
     *  
     *  @var integer
     */
    public $position;
    
    public function __construct($type, $text, $pos)
    {
        $this->type     = $type;
        $this->text     = $text;
        $this->position = $pos;
    }

    /**
     *  __toString magic method (for debugging)
     *
     *  @return string
     */
    public function __toString()
    {
        return '[' . $this->text .', ' . $this->type . ', ' . $this->position .']';
    }
}