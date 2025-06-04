<?php

/**
 *  @author          Tassos.gr <info@tassos.gr>
 *  @link            https://www.tassos.gr
 *  @copyright       Copyright © 2024 Tassos All Rights Reserved
 *  @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\Parser\Exceptions;

defined('_JEXEC') or die;

class InvalidConditionException extends \Exception
{
    public function __construct($condition_name)
    {
        parent::__construct("002 - Invalid Condition: The condition '" . $condition_name . "' does not exist.");
    }
}