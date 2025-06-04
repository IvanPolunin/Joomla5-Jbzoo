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

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

extract($displayData);

// Skip if all address details are empty
if (empty(array_filter($address)))
{
	return;
}

$address_details_html = null;

switch ($layout_type)
{
	// Default Layout
	case 'default':
		$address_details_html = \NRFramework\Helpers\Widgets\MapAddress::getDefaultAddressDetailsLayout($address, $showAddressDetails);
		break;

	// Custom Layout
	case 'custom':
		if (!empty(trim($custom_layout)))
		{	
			$st = new \NRFramework\SmartTags();
			$st->add($address, 'address.');

			// Add labels
			foreach ($address as $key => $value)
			{
				$st->add([$key . '.label' => Text::_('NR_' . strtoupper($key))], 'address.');
			}

			// Add map
			if ($map)
			{
				$st->add(['address.map' => $map]);
			}

			$address_details_html = nl2br(\Joomla\CMS\HTML\HTMLHelper::_('content.prepare', $st->replace($custom_layout)));
		}
		break;
}

if (!$address_details_html)
{
	return;
}

echo $map_location === 'above' ? $map_html . $address_details_html : $address_details_html . $map_html;