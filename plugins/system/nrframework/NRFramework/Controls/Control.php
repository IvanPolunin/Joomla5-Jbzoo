<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            http://www.tassos.gr
 * @copyright       Copyright © 2022 Tassos Marinos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

namespace NRFramework\Controls;

defined('_JEXEC') or die;

class Control
{
	/**
	 * The CSS selector related to this control.
	 * 
	 * @var  string|array
	 */
	protected $selector;

	/**
	 * The CSS property related to this control.
	 * 
	 * @var  mixed
	 */
	protected $property;

	/**
	 * The CSS property used when there are conditions and we fail to use the property, so we override it using this property.
	 * 
	 * @var  mixed
	 */
	protected $fallback_property;

	/**
	 * Some controls may render CSS conditionally, based on the given value.
	 * 
	 * @var  array
	 */
	protected $values;

	/**
	 * The control value.
	 * 
	 * @var  mixed
	 */
	protected $value;

	/**
	 * The raw control value.
	 * 
	 * @var  mixed
	 */
	protected $value_raw;

	/**
	 * The control value unit.
	 * 
	 * @var  string
	 */
	protected $unit;

	/**
	 * Exclude specific breakpoints from the control's CSS.
	 * 
	 * @var  array
	 */
	protected $exclude_breakpoints = [];

	/**
	 * The existing controls we have parsed so far.
	 * 
	 * @var  array
	 */
	protected $parsedControls = [];

	/**
	 * A control may require some conditions to be set.
	 * 
	 * @var  array
	 */
	protected $conditions = [];

	/**
	 * Whether to ignore "inherit" values.
	 * 
	 * @var  bool
	 */
	protected $skip_inherit_value = false;

	/**
	 * The current breakpoint we are checking against to set CSS.
	 * 
	 * @var  string
	 */
	protected $current_breakpoint = 'desktop';

    public function __construct($payload = [])
	{
        $this->parsedControls = isset($payload['parsedControls']) ? $payload['parsedControls'] : null;
        $this->selector = isset($payload['selector']) ? $payload['selector'] : null;
        $this->conditions = isset($payload['conditions']) ? $payload['conditions'] : [];
        $this->property = isset($payload['property']) ? $payload['property'] : null;
        $this->skip_inherit_value = isset($payload['skip_inherit_value']) ? $payload['skip_inherit_value'] : $this->skip_inherit_value;
        $this->fallback_property = isset($payload['fallback_property']) ? $payload['fallback_property'] : null;
        $this->values = isset($payload['values']) ? $payload['values'] : [];
        $this->exclude_breakpoints = isset($payload['exclude_breakpoints']) ? $payload['exclude_breakpoints'] : null;
        $this->value = isset($payload['value']['value']) ? $payload['value']['value'] : (isset($payload['value']) ? $payload['value'] : null);
        $this->value_raw = isset($payload['value']['value']) ? $payload['value']['value'] : (isset($payload['value']) ? $payload['value'] : null);
        $this->unit = isset($payload['value']['unit']) ? $payload['value']['unit'] : (isset($payload['unit']) ? $payload['unit'] : null);

		if (isset($this->value['unit']))
		{
			unset($this->value['unit']);
		}
    }

    public function getCSS()
	{
		if (!$this->isResponsive())
		{
			$this->value = $this->generateCSSProperty($this->value, $this->unit);
			return $this->value;
		}

		// Prepare value for arrays
		foreach ($this->value as $breakpoint => &$value)
		{
			$this->current_breakpoint = $breakpoint;
			
			// If this breakpoint is excluded, skip
			if (is_array($this->exclude_breakpoints) && count($this->exclude_breakpoints) && in_array($breakpoint, $this->exclude_breakpoints))
			{
				unset($this->value[$breakpoint]);
				continue;
			}
			
			if (is_scalar($value))
			{
				$value = $this->generateCSSProperty($value, $this->unit);
			}
			else
			{
				$unit = isset($value['unit']) ? $value['unit'] : $this->unit;

				// Remove "unit" property
				if ($unit)
				{
					unset($value['unit']);
				}
				
				// Remove "linked" property
				if (isset($value['linked']))
				{
					unset($value['linked']);
				}
				
				$value = isset($value['value']) ? $value['value'] : $value;

				// Remove responsive value if no actual value is set
				if (!$value && $value != '0' && $unit != 'auto')
				{
					unset($this->value[$breakpoint]);
					continue;
				}
				
				$value = $this->generateCSSProperty($value, $unit);
			}

			if (is_null($value))
			{
				continue;
			}
		}

		return $this->value;
    }

	protected function generateCSSProperty($value, $unit)
	{
		if ($this->shouldSkipPropertyGeneration($value, $unit))
		{
			return;
		}

		$conditions_pass = $this->conditionsPass($value);
		
		$conditionsNotMet = (!$conditions_pass && !$this->fallback_property);
		$emptyValueWithUnitNotAuto = ($value === '' || is_null($value)) && !$this->fallback_property && $unit !== 'auto';
		
		if ($conditionsNotMet || $emptyValueWithUnitNotAuto)
		{
			return;
		}

		if ($this->values)
		{
			return $this->generateValueCSS($value);
		}

		$properties = $this->conditions && !$conditions_pass && $this->fallback_property ? $this->fallback_property : $this->property;

		if (is_array($properties))
		{
			return $this->generateArrayPropertyCSS($properties, $conditions_pass, $value, $unit);
		}

		if ($value === 'inherit' && $this->skip_inherit_value)
		{
			return;
		}

		return $this->generateSinglePropertyCSS($value, $unit);
	}

	private function shouldSkipPropertyGeneration($value, $unit)
	{
		return !$value && $value != '0' && $unit != 'auto' && !$this->fallback_property;
	}

	private function generateValueCSS($value)
	{
		$css = '';

		$value = explode(' ', $value);

		foreach ($this->values as $key => $css_value)
		{
			if (!in_array($key, $value))
			{
				continue;
			}

			$css .= implode('', $css_value);
		}

		return $css;
	}

	private function generateArrayPropertyCSS($properties, $conditions_pass, $value, $unit)
	{
		$css = '';

		// If the conditions did not pass and we do not have a value, do not set any CSS.
		if ($conditions_pass && !$value)
		{
			return $css;
		}

		foreach ($properties as $prop_key => $prop_value)
		{
			$css_line = $prop_key . ':' . str_replace('%value%', $value . $unit, $prop_value) . ';';
			$css_line = str_replace('%value_raw%', $value , $css_line);

			$css .= $css_line;
		}

		return $css;
	}

	private function generateSinglePropertyCSS($value, $unit)
	{
		$value = \NRFramework\Helpers\Controls\Control::findUnitInValue($value);
		$val = isset($value['value']) ? $value['value'] : $value;
		$unit = isset($value['unit']) && $value['unit'] ? $value['unit'] : $unit;

		return $this->getProperty() . ':' . $val . $unit . ';';
	}

	private function conditionsPass(&$value)
	{
		if (!$this->conditions)
		{
			return true;
		}
	
		foreach ($this->conditions as $conditionItem)
		{
			if (!$this->checkCondition($conditionItem, $value))
			{
				return false;
			}
		}
	
		return true;
	}

	private function checkCondition($conditionItem, &$value)
	{
		$foundCondition = array_search($conditionItem['property'], array_column($this->parsedControls, 'property'));

		if ($foundCondition === false)
		{
			return false;
		}

		$foundConditionControl = isset($this->parsedControls[$foundCondition]) ? $this->parsedControls[$foundCondition] : false;

		if (!$foundConditionControl)
		{
			return false;
		}

		$conditionValue = $conditionItem['property'] . ':' . $conditionItem['value'] . ';';
		$foundConditionControlValues = $foundConditionControl['control']->getValue();
		$breakpointValue = is_array($foundConditionControlValues) ? ($foundConditionControlValues[$this->current_breakpoint] ?? null) : $foundConditionControlValues;

		if (\NRFramework\Functions::endsWith($breakpointValue, ':inherit;'))
		{
			$prevBreakpoint = $this->current_breakpoint === 'tablet' ? 'desktop' : 'tablet';
			$breakpointValue = is_array($foundConditionControlValues) ? ($foundConditionControlValues[$prevBreakpoint] ?? null) : $foundConditionControlValues;

			// Also update the value
			$value = isset($this->value_raw[$prevBreakpoint]['value']) || isset($this->value_raw[$prevBreakpoint]) ? '' : $value;

			if (\NRFramework\Functions::endsWith($breakpointValue, ':inherit;') && $this->current_breakpoint === 'mobile' && $prevBreakpoint === 'tablet')
			{
				$breakpointValue = is_array($foundConditionControlValues) ? ($foundConditionControlValues['desktop'] ?? null) : $foundConditionControlValues;

				// Also update the value
				$value = isset($this->value_raw['desktop']['value']) || isset($this->value_raw['desktop']) ? '' : $value;
			}
		}

		return $conditionValue === $breakpointValue;
	}

	public function getValue()
	{
		return $this->value;
	}

	public function getSelector()
	{
		return $this->selector;
	}

	public function getProperty()
	{
		return $this->property;
	}

	public function isResponsive()
	{
		$keys = ['desktop', 'tablet', 'mobile'];
		return is_array($this->value) && !empty(array_intersect_key(array_flip($keys), $this->value));
	}
}