<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\NumberField;
use Joomla\CMS\Language\Text;

class JFormFieldNRNumber extends NumberField
{
    /**
     *  Method to render the input field
     *
     *  @return  string  
     */
    function getInput()
    {   
        $parent = parent::getInput();
        $addon  = (string) $this->element['addon'];

        if (empty($addon))
        {
            return $parent;
        }

        return '
            <div class="input-append input-group">
                ' . $parent . '
                <span class="add-on input-group-append">
                    <span class="input-group-text" style="font-size:inherit;">' . Text::_($addon) . '</span>
                </span>
            </div>
        ';
    }
}
