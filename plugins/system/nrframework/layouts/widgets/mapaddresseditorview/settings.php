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

use Joomla\CMS\Form\Form;

require_once JPATH_SITE . '/plugins/system/nrframework/fields/tfaddresslookup.php';

$form_source = new SimpleXMLElement('
<form>
	<fieldset name="mapeditor_field_settings">
		<field name="address" type="TFAddressLookup"
			label="NR_LOCATION_ADDRESS"
			visible="' . $options['show_address'] . '"
			id="' . $options['id'] . '"
			autocomplete="' . ($options['autocomplete'] ? 'true' : 'false') . '"
		/>
	</fieldset>
</form>
');

$form = Form::getInstance($options['name'], $form_source->asXML(), ['control' => $options['name']]);
$form->bind([
	'address' => [
		'coordinates' => $options['value'],
		'address' => !empty($options['address']) ? $options['address'] : $options['value']
	]
]);

echo $form->renderFieldset('mapeditor_field_settings');