<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            http://www.tassos.gr
 * @copyright       Copyright © 2022 Tassos Marinos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

namespace NRFramework\Controls;

defined('_JEXEC') or die;

class ControlFactory
{
	public function createControl($value = [])
	{
		if (!isset($value['value']))
		{
			return;
		}
		
        $type = isset($value['type']) ? $value['type'] : 'Control';

		switch ($type)
		{
			case 'Control':
				return new \NRFramework\Controls\Control($value);
				break;
			case 'Border':
				return new \NRFramework\Controls\Border($value);
				break;
			case 'Spacing':
				return new \NRFramework\Controls\Spacing($value);
				break;
		}
		
		return;
    }
}