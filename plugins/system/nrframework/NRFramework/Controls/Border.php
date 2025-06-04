<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            http://www.tassos.gr
 * @copyright       Copyright © 2022 Tassos Marinos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

namespace NRFramework\Controls;

defined('_JEXEC') or die;

class Border extends Control
{
	protected function generateCSSProperty($value, $unit)
	{
		// We require all border attributes
		if (!isset($value['width']) || !isset($value['style']) || !isset($value['color']))
		{
			return;
		}

		// Ensure the width is > 0
		if (intval($value['width']) === 0)
		{
			return;
		}

		return $this->property . ':' . implode(' ', [
			$value['width'] . $unit,
			$value['style'],
			$value['color']
		]) . ';';
    }
}