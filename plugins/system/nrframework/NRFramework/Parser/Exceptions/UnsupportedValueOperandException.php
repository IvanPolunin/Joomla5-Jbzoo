<?php

/**
 *  @author          Tassos.gr <info@tassos.gr>
 *  @link            https://www.tassos.gr
 *  @copyright       Copyright © 2024 Tassos All Rights Reserved
 *  @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\Parser\Exceptions;

defined('_JEXEC') or die;

class UnsupportedValueOperandException extends \Exception
{
    public function __construct($operator, $accepts_multi_values)
    {
        $message = 'The Comparison Operator "' . $operator . '" can only be used with ' . ($accepts_multi_values ? 'multiple values.' : 'single values.');
        parent::__construct("006 - Unsupported Value Operand: " . $message);
    }
}