<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

namespace NRFramework\Helpers\Controls;

defined('_JEXEC') or die;

class Control
{
    /**
     * Finds the value and unit in the given subject.
     * 
     * @param   string/array  $subject
     * 
     * @return  array
     */
	public static function findUnitInValue($subject = '')
	{
        if (is_null($subject))
        {
            return;
        }
        
        if (is_string($subject) && $subject === '')
        {
            return;
        }

        if (is_array($subject) && count($subject) === 0)
        {
            return;
        }

        if ($subject === 'auto')
        {
            return [
                'value' => '',
                'unit' => 'auto'
            ];
        }
        
        if (is_array($subject) && isset($subject['value']))
        {
            $return = [
                'value' => $subject['value']
            ];

            if (isset($subject['unit']))
            {
                $return['unit'] = $subject['unit'];
            }

            return $return;
        }

		$pattern = '/^([\d.]+)(\D+)?$/';
		if (is_scalar($subject) && preg_match($pattern, $subject, $matches, PREG_OFFSET_CAPTURE) === 1)
		{
			return [
				'value' => $matches[1][0],
				'unit' => isset($matches[2][0]) ? $matches[2][0] : ''
			];
		}

		return [
            'value' => $subject,
            'unit' => ''
        ];
	}

    /**
     * Parses the given value to a CSS value.
     * 
     * @param   mixed   $value
     * @param   string  $unit
     * 
     * @return  string
     */
    public static function getCSSValue($value = '', $unit = '')
    {
        if (is_null($value) || $value === '')
        {
            return;
        }

        // Is scalar, transform to array
        if (is_scalar($value))
        {
            $value = array_filter(explode(' ', $value), function($value) {
                return $value !== '';
            });
        }

        if (!$value)
        {
            return;
        }
        
        if (is_array($value))
        {
            if (empty($value))
            {
                return;
            }
            
			// If all values are empty, abort
			$isEmptyArray = array_filter($value, function($str) {
                return $str === null || $str === false || $str === '' || (is_array($str) && empty($str));
            });
			if (count($isEmptyArray) === 4)
			{
				return;
			}

			// Apply spacing positions
			if ($positions = self::findSpacingPositions($value))
			{
				$return = [];
				foreach ($positions as $pos)
				{
					$return[$pos] = isset($value[$pos]) && $value[$pos] !== '' ? $value[$pos] : 0;
				}
				if (empty($return))
				{
					return;
				}
				$value = $return;
			}

            /**
             * All values are duplicates, return only 1 number with their unit.
             * 
             * Example: Given [5, 5, 5, 5] to print the margin in pixels, do not return `margin: 5px 5px 5px 5px`.
             * Rather return `margin: 5px`
             */
            if (count($value) === 4 && count(array_unique($value)) === 1)
            {
				$value = reset($value);
                if ($value_data = self::findUnitInValue($value))
                {
                    $value = $value_data['value'];
                    $unit = !empty($value_data['unit']) ? $value_data['unit'] : $unit;
                }

				if (is_array($value))
				{
					return;
				}
				
                return $value . ($value > 0 ? $unit : '');
            }

            /**
             * If we were given 4 values and first/third & second/forth values are the same then return these only.
             * 
             * Example: Given[5, 10, 5, 10] to print the margin in pixels, do not return `margin: 5px 10px 5px 10px`.
             * Rather return `margin: 5px 10px`
             */
            $keys = array_keys($value);
            if (count($value) === 4 && $value[$keys[0]] === $value[$keys[2]] && $value[$keys[1]] === $value[$keys[3]])
            {
                $value1 = $value[$keys[0]];
                $suffix1 = $suffix2 = $unit;
                $value2 = $value[$keys[1]];
                
                if ($value_1 = self::findUnitInValue($value1))
                {
                    $value1 = $value_1['value'];
                    $suffix1 = !empty($value_1['unit']) ? $value_1['unit'] : $unit;
                }
                if ($value_2 = self::findUnitInValue($value2))
                {
                    $value2 = $value_2['value'];
                    $suffix2 = !empty($value_2['unit']) ? $value_2['unit'] : $unit;
                }
                
                return $value1 . ($value1 > 0 ? $suffix1 : '') . ' ' . $value2 . ($value2 > 0 ? $suffix2 : '');
            }

            // Different values
            $data = [];
            foreach ($value as $key => $_value)
            {
                $val = $_value;
                if ($value_data = self::findUnitInValue($val))
                {
                    $val = $value_data['value'];
                    $unit = !empty($value_data['unit']) ? $value_data['unit'] : $unit;
                }
                $data[] = $val . ($val > 0 ? $unit : '');
            }

            return implode(' ', $data);
        }

        return;
    }

    /**
     * Finds an array of positions of the given value that
     * relates to margin/padding or border radius.
     * 
     * @param   array  $value
     * 
     * @return  array
     */
    public static function findSpacingPositions($value = [])
    {
		if (!is_array($value) || !count($value))
		{
			return;
		}
		
		$keys = array_keys($value);
        
		// Is margin/padding
        $margin_padding = self::getPositions();
		if (in_array($keys[0], $margin_padding, true))
		{
			return $margin_padding;
		}

		// Is border radius
        $border_radius = self::getPositions('border_radius');
		if (in_array($keys[0], $border_radius, true))
		{
			return $border_radius;
		}

		return;
    }

    /**
     * Return the position keys based on the control type.
     * 
     * @param   string  $type
     * 
     * @return  array
     */
    public static function getPositions($type = 'margin_padding')
    {
        if (!$type)
        {
            return [];
        }

        $margin_padding = [
            'top',
            'right',
            'bottom',
            'left'
        ];

        $border_radius = [
            'top_left',
            'top_right',
            'bottom_right',
            'bottom_left'
        ];

        return $type === 'margin_padding' ? $margin_padding : $border_radius;
    }
}