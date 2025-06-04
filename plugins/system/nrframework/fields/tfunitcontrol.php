<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            http://www.tassos.gr
 * @copyright       Copyright © 2021 Tassos Marinos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\TextField;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\HTML\HTMLHelper;

class JFormFieldTFUnitControl extends TextField
{
    private $units = ['px', '%', 'em', 'rem'];

    private $default_unit = 'px';

    private $min = 0;
    private $max;
    private $step;

    /**
     *  Method to render the input field
     *
     *  @return  string  
     */
    public function getInput()
    {
        $wrapper_class = $dropdown_class = '';
        
        $initial_name = $this->name;

        $units = $this->getUnits();

        $hint = $this->hint;

        $parsedValue = \NRFramework\Helpers\Controls\Control::findUnitInValue($this->value);
        $value = isset($parsedValue['value']) ? $parsedValue['value'] : $this->value;
        $unit = isset($parsedValue['unit']) && $parsedValue['unit'] ? $parsedValue['unit'] : (isset($units[0]) && $units[0] ? $units[0] : $this->default_unit);
        if ($value === 'auto' || $unit === 'auto')
        {
            $unit = 'auto';
            $this->hint = '';
            $this->readonly = true;
            $wrapper_class .= ' has-value';
        }
        else if (count($units) > 0 && $unit)
        {
            /**
             * If the value unit is not in the $units list,
             * this means that the value most probably was a string that had a specific unit set,
             * such as: 50em
             * 
             * We add the "em" to the units list in order to use the existing value unit
             * and later on allow the user to switch to the new units. The new units wont allow the
             * user to use the previous unit once they change to the new one.
             */
            if (!in_array($unit, $units))
            {
                $units[] = $unit;
            }
        }

        if (count($units) > 0)
        {
            $this->assets();
        }

        if (count($units) > 1)
        {
            $wrapper_class .= ' has-multiple-units';
        }

        if ($value !== '')
        {
            $wrapper_class .= ' has-value';
        }

        // Update value
        $this->value = $value;

        // Append [value] to name
        $this->name .= '[value]';

        $this->min = isset($this->element['min']) ? (int) $this->element['min'] : $this->min;
        $this->max = isset($this->element['max']) ? (int) $this->element['max'] : $this->max;
        $this->step = isset($this->element['step']) ? (int) $this->element['step'] : $this->step;

        $this->layout = 'joomla.form.field.number';

        $this->class .= (!empty($this->class) ? ' ' : '') . 'tf-unit-control--value';

        $html = parent::getInput();
        $html = str_replace('form-control', '', $html);

        $payload = [
            // Default values
            'value' => $value,
            'unit' => $unit,

            'wrapper_class' => $wrapper_class,
            'dropdown_class' => $dropdown_class,
            'name' => $initial_name,
            'input' => $html,
            'hint' => $hint,
            'form_field_name' => $this->name,
            'units' => $units
        ];

        $layout = new FileLayout('unit', JPATH_PLUGINS . '/system/nrframework/layouts/controls');
        return $layout->render($payload);
    }

    private function getUnits()
    {
        $units = isset($this->element['units']) ? (string) $this->element['units'] : $this->units;

        return is_string($units) ? array_filter(array_unique(array_map('trim', explode(',', $units)))) : $units;
    }

    /**
     * Method to get the data to be passed to the layout for rendering.
     *
     * @return  array
     */
    protected function getLayoutData()
    {
        $data = parent::getLayoutData();

        $extraData = [
            'max'   => $this->max,
            'min'   => $this->min,
            'step'  => $this->step,
            'value' => $this->value
        ];

        return array_merge($data, $extraData);
    }

    /**
     * Load field assets.
     * 
     * @return  void
     */
    private function assets()
    {
        HTMLHelper::script('plg_system_nrframework/autosize-input.js', ['relative' => true, 'version' => 'auto']);
        HTMLHelper::stylesheet('plg_system_nrframework/controls/unit.css', ['relative' => true, 'version' => 'auto']);
    }
}