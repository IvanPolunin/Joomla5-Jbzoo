<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::stylesheet('plg_system_nrframework/addresslookup.css', ['relative' => true, 'version' => 'auto']);
HTMLHelper::script('plg_system_nrframework/addresslookup.js', ['relative' => true, 'version' => 'auto']);

extract($displayData);

$coordinates = isset($value['coordinates']) ? $value['coordinates'] : '';
$address = isset($value['address']) ? $value['address'] : '';
?>
<div class="tf-address-lookup-container <?php echo $group_class; ?> is-<?php echo $visible ? 'visible' : 'hidden'; ?>">
	<input type="hidden" name="<?php echo $name; ?>[coordinates]" class="tf-address-lookup-field-coordinates-value" value="<?php echo $coordinates; ?>" />
	<input type="text"  name="<?php echo $name; ?>[address]" id="<?php echo $id; ?>-field-address-field-address" class="form-control span12 full-width w-100 tf-address-lookup-field-address" placeholder="<?php echo Text::_('NR_ADDRESS_ADDRESS_HINT'); ?>" value="<?php echo $address; ?>" autocomplete="off" data-autocomplete="<?php echo $autocomplete ? 'true' : 'false'; ?>">
	<div class="tf-address-lookup-field-autocomplete-results"></div>
	<svg width="22" height="22" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
		<circle cx="14" cy="14" r="8.48528" transform="rotate(-45 14 14)" stroke-width="2"></circle>
		<path d="M19.9995 20L24.4995 24.5" stroke-width="2" stroke-linecap="round"></path>
	</svg>
</div>