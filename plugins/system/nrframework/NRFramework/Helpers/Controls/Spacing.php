<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

namespace NRFramework\Helpers\Controls;

defined('_JEXEC') or die;

use NRFramework\Helpers\Controls\Control;

class Spacing
{
    /**
     * Parses the given value and returns the value expected by Spacing Control.
     * 
     * @param   mixed   $value
     * @param   string  $type    This can be margin_padding or border_radius.
     * 
     * @return  array
     */
    public static function parseInputValue($value = '', $type = 'margin_padding')
    {
        if (!$value)
        {
            return [];
        }

        $linked = isset($value['linked']) ? $value['linked'] : '0';
        $unit = isset($value['unit']) ? $value['unit'] : 'px';
        $value = isset($value['value']) ? $value['value'] : $value;

        $positions = Control::getPositions($type);
        
        // If it's a string of values, prepare it to be an array and continue
        if (is_scalar($value))
        {
            $value = array_filter(explode(' ', $value), function($value) {
                return $value !== '';
            });

            // Get the unit from the first found value
            foreach ($value as $val)
            {
                $_value = Control::findUnitInValue($val);
                if (!isset($_value['unit']))
                {
                    continue;
                }

                $unit = !empty($_value['unit']) ? $_value['unit'] : $unit;
                break;
            }
            
            // Ensure only ints are in the array
            $value = array_map('intval', $value);

            // If only a single value is given, apply the value to all positions
            if (count($value) === 1)
            {
                $value = array_merge($value, $value, $value, $value);
            }

            if (count($value) === 2)
            {
                $value = [$value[0], $value[1], $value[0], $value[1]];
            }
            
            $tmp_value = [];

            foreach ($positions as $index => $pos)
            {
                $tmp_value[$pos] = isset($value[$index]) ? $value[$index] : '';
            }

            $value = $tmp_value;
        }

        // Return value
        $return = [];

        foreach ($positions as $pos)
        {
            $return[$pos] = isset($value[$pos]) && $value[$pos] !== '' ? intval($value[$pos]) : '';
        }

        if (empty($return))
        {
            return [];
        }

        $return['linked'] = $linked;
        $return['unit'] = $unit;

        return $return;
    }
}