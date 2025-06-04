<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

// No direct access to this file
defined('_JEXEC') or die;

require_once dirname(__DIR__) . '/helpers/field.php';

class JFormFieldNRRangeSlider extends NRFormField
{
    /**
     *  Method to render the input field
     *
     *  @return  string  
     */
    protected function getInput()
    {
        $min = isset($this->element['min']) ? (float) $this->element['min'] : null;
        $max = isset($this->element['max']) ? (float) $this->element['max'] : null;
        $step = isset($this->element['step']) ? (float) $this->element['step'] : null;

        $payload = [
            'name' => $this->name,
            'value' => (float) $this->value
        ];

        if ($min)
        {
            $payload['min'] = $min;
        }
        if ($max)
        {
            $payload['max'] = $max;
        }
        if ($step)
        {
            $payload['step'] = $step;
        }

        if ($this->class)
        {
            $payload['css_class'] = $this->class;
        }
        
        $slider = \NRFramework\Widgets\Helper::render('RangeSlider', $payload);
        
        return $slider;
    }
}