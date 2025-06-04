<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            http://www.tassos.gr
 * @copyright       Copyright © 2022 Tassos Marinos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

namespace NRFramework\Controls;

defined('_JEXEC') or die;

class Spacing extends Control
{
	protected function generateCSSProperty($value, $unit)
	{
		$value = \NRFramework\Helpers\Controls\Control::getCSSValue($value, $unit);

		if ((is_null($value) || $value === '') && $value != '0')
		{
			return;
		}

		return $this->property . ':' . $value . ';';
    }
}