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
 *  Tokens
 *  Holds token types and manages creation of new tokens
 */
class Tokens
{
    /**
     *  Token types array
     *
     *  @var array
     */
    protected $types = [];

    public function __construct()
    {
        // default types
        $this->addType('invalid_token');
        $this->addType('EOF');
    }

    /**
     *  Adds a new token type
     *
     *  @param string $type
     *  @return $this
     */
    public function addType($type)
    {
        if (!$this->hasType($type))
        {
            $this->types[] = $type;
        }

        return $this;
    }

    /**
     *  Gets a token type id (i.e. it's array index)
     *
     *  @param string $type
     *  @return int|null
     */
    public function getTypeId($type)
    {
        $id = array_search($type, $this->types);
        $id = $id !== false ? $id : null;
        return $id;
    }

    /**
     *  Returns the token types array
     *
     *  @return array
     */
    public function getTypes()
    {
        return clone $this->types;
    }

    /**
     *  Creates a new token
     *
     *  @param  string  $type
     *  @param  string  $text
     *  @param  integer $position, Position of token in the input stream
     *  @return Token
     */
    public function create($type, $text, $position)
    {
        return new Token($type, $text, $position);
    }
    
    /**
     *  Checks if a type is registered
     *
     *  @param string $type
     *  @return boolean
     */
    public function hasType($type)
    {
        return (bool)array_search($type, $this->types);
    }
}
