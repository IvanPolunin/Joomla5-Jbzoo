<?php

/**
 * @package         Advanced Custom Fields
 * @version         2.8.0 Pro
 * 
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Form\Field\TextField;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\HTML\HTMLHelper;

class JFormFieldTFDimensionControl extends TextField
{
    protected $input_type = 'margin_padding';

    protected $default_units = ['px', '%', 'em', 'rem'];
    
    /**
     * Set the dimensions.
     * 
     * @var  array
     */
    protected $dimensions = [
        'top' => 'NR_TOP',
        'right' => 'NR_RIGHT',
        'bottom' => 'NR_BOTTOM',
        'left' => 'NR_LEFT'
    ];

    /**
     * Set whether the linked button will be enabled or not.
     * 
     * @var  boolean
     */
    protected $linked = true;
    
    /**
     * Method to get a list of options for a list input.
     * @return  array  An array of options.
     */
    protected function getInput()
    {
        if (!$this->dimensions = isset($this->element['dimensions']) ? $this->parseDimensions($this->element['dimensions']) : $this->dimensions)
        {
            return;
        }

        $value = is_scalar($this->value) ? $this->value : (array) $this->value;
        $value = \NRFramework\Helpers\Controls\Spacing::parseInputValue($value, $this->input_type);

        $this->assets();
   
        $this->linked = isset($this->element['linked']) ? (boolean) $this->element['linked'] : (isset($value['linked']) ? (boolean) $value['linked'] : $this->linked);

        $units = $this->getUnits();
        if (count($units) > 0 && isset($value['value']))
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
            if (!in_array($value['unit'], $units))
            {
                $units[] = $unit;
            }
        }

        $payload = [
            'dimensions' => $this->dimensions,
            'dimension_control_locks' => isset($this->element['dimension_control_locks']) ? (bool) $this->element['dimension_control_locks'] === 'true' : true,
            'linked' => $this->linked,
            'units' => $units,
            'name' => $this->name,
            'value' => $value
        ];

        $layout = new FileLayout('dimension', JPATH_PLUGINS . '/system/nrframework/layouts/controls');
        return $layout->render($payload);
    }

    private function getUnits()
    {
        $units = isset($this->element['units']) && !empty($this->element['units']) ? (string) $this->element['units'] : $this->default_units;

        if (!$units)
        {
            return [];
        }

        return is_string($units) ? array_filter(array_unique(array_map('trim', explode(',', $units)))) : $units;
    }

    /**
     * Prepares the given dimensions.
     * 
     * Input:
     * 
	 * - top:NR_TOP,right:NR_RIGHT,bottom:NR_BOTTOM,left:NR_LEFT
	 * - top_left:Top Left,top_right:Top Right,bottom_right:Bottom Right,bottom_left:Bottom Left
     * 
     * @param  array  $dimensions
     * 
     * @return array
     */
    private function parseDimensions($dimensions = [])
    {
        $pairs = explode(',', $dimensions);
        
        $parsed = [];

        if (empty(array_filter($pairs)))
        {
            return [];
        }

        foreach ($pairs as $key => $pair)
        {
            if (!$value = explode(':', $pair))
            {
                continue;
            }

            // We expect 2 key,value pairs
            if (count($value) !== 2)
            {
                continue;
            }

            $parsed[$value[0]] = Text::_($value[1]);
        }

        return $parsed;
    }

    /**
     * Load field assets.
     * 
     * @return  void
     */
    private function assets()
    {
        HTMLHelper::script('plg_system_nrframework/autosize-input.js', ['relative' => true, 'version' => 'auto']);
        HTMLHelper::stylesheet('plg_system_nrframework/controls/dimension.css', ['relative' => true, 'version' => 'auto']);
        HTMLHelper::script('plg_system_nrframework/controls/dimension.js', ['relative' => true, 'version' => true]);
    }
}