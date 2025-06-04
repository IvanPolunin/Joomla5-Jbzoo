<?php

/**
 *  @author          Tassos.gr <info@tassos.gr>
 *  @link            https://www.tassos.gr
 *  @copyright       Copyright © 2024 Tassos All Rights Reserved
 *  @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\Parser\Exceptions;

defined('_JEXEC') or die;

class ConditionValueException extends \Exception
{
    public function __construct($condition_name)
    {
        parent::__construct("008 - Condition Value Error: The Condition '" . $condition_name . "' does not return a value.");
    }
}