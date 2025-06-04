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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\Form;

$form_source = new SimpleXMLElement('
<form>
	<fieldset name="add_marker_modal">
		<field name="address" type="TFAddressLookup"
			label="NR_LOCATION_ADDRESS"
			group_class="mb-0"
			class="tf-marker-repeater-address span12 full-width w-100"
		/>
		<field name="latitude" type="hidden"
			class="tf-marker-repeater-latitude"
		/>
		<field name="longitude" type="hidden"
			class="tf-marker-repeater-longitude"
		/>
	</fieldset>
</form>
');

$form = Form::getInstance($options['name'] . '[add_marker]', $form_source->asXML(), ['control' => $options['name'] . '[add_marker]']);

echo HTMLHelper::_('bootstrap.renderModal', 'tfMapEditorMarkerAddModal', [
	'title'  => Text::_('NR_ADD_MARKER'),
	'modalWidth' => '40',
	'footer' => '<button type="button" class="btn btn-primary tf-mapeditor-save-new-marker tf-modal-btn-primary" data-bs-dismiss="modal" data-dismiss="modal" aria-hidden="true">' . Text::_('NR_ADD_MARKER') . '</button>'
], $form->renderFieldset('add_marker_modal'));