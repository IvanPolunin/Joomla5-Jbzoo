<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

namespace NRFramework\Helpers\Widgets;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

class MapAddress
{
	/**
	 * Returns the default address details layout.
	 * 
	 * @param   array  $address
	 * @param   array  $showAddressDetails
	 * 
	 * @return  string
	 */
	public static function getDefaultAddressDetailsLayout($address = [], $showAddressDetails = [])
	{
		if (empty($address) || empty($showAddressDetails))
		{
			return;
		}
		
		$html = '';

		$template = '<div class="nrf-mapaddress-field-address-detail-item"><strong>%s</strong>: %s</div>';

		foreach ($showAddressDetails as $key)
		{
			$value = isset($address[$key]) ? $address[$key] : '';

			if (empty($value))
			{
				continue;
			}
			
			$html .= sprintf($template, Text::_('NR_' . strtoupper($key)), $value);
		}

		return $html;
	}
}