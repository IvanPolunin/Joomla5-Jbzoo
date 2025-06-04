<?php

/**
 * @package         Regular Labs Library
 * @version         24.6.22903
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            https://regularlabs.com
 * @copyright       Copyright © 2024 Regular Labs All Rights Reserved
 * @license         GNU General Public License version 2 or later
 */
namespace RegularLabs\Library\Form\Field;

defined('_JEXEC') or die;
use RegularLabs\Library\Form\FormField as RL_FormField;
use RegularLabs\Library\RegEx as RL_RegEx;
use RegularLabs\Library\ShowOn as RL_ShowOn;
class ShowOnField extends RL_FormField
{
    protected function getInput()
    {
        $value = (string) $this->get('value');
        $class = $this->get('class', '');
        if (!$value) {
            return $this->getControlGroupEnd() . RL_ShowOn::close() . $this->getControlGroupStart();
        }
        $formControl = $this->get('form', $this->formControl);
        $formControl = ($formControl == 'root') ? '' : $formControl;
        while (str_starts_with($value, '../')) {
            $value = substr($value, 3);
            if (str_contains($formControl, '[')) {
                $formControl = RL_RegEx::replace('^(.*)\[.*?\]$', '\1', $formControl);
            }
        }
        return $this->getControlGroupEnd() . RL_ShowOn::open($value, $formControl, $this->group, $class) . $this->getControlGroupStart();
    }
    protected function getLabel()
    {
        return '';
    }
}
