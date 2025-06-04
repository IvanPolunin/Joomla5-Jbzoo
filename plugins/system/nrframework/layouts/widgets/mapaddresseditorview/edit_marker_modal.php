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
	<fieldset name="edit_marker_modal">
		<field name="" type="hidden" class="tf-marker-repeater-id" />
		<field name="" type="TFAddressLookup"
			label="NR_LOCATION_ADDRESS"
			group_class="mb-0"
			class="tf-marker-repeater-address span12 full-width w-100"
		/>
		<field name="" type="text"
			label="NR_COORDINATES"
			hint="NR_LATITUDE"
			class="tf-marker-repeater-latitude span12 full-width w-100"
		/>
		<field name="" type="text"
			hiddenLabel="true"
			hint="NR_LONGITUDE"
			class="tf-marker-repeater-longitude span12 full-width w-100"
		/>
		<field name="" type="spacer" label="NR_INFO_WINDOW" class="tf-bold" />
		<field name="" type="text"
			label="NR_LABEL"
			class="tf-marker-repeater-label span12 full-width w-100"
			filter="safehtml"
		/>
		<field name="" type="textarea"
			label="NR_DESCRIPTION"
			class="tf-marker-repeater-description span12 full-width w-100"
			rows="4"
			filter="safehtml"
		/>
	</fieldset>
</form>
');

$form = Form::getInstance($options['name'], $form_source->asXML(), ['control' => $options['name']]);

echo HTMLHelper::_('bootstrap.renderModal', 'tfMapEditorMarkerEditModal', [
	'title'  => Text::_('NR_EDIT_MARKER'),
	'modalWidth' => '50',
	'footer' => '<button type="button" class="btn btn-primary tf-mapeditor-save-marker tf-modal-btn-primary" data-bs-dismiss="modal" data-dismiss="modal" aria-hidden="true">' . Text::_('JAPPLY') . '</button>'
], $form->renderFieldset('edit_marker_modal'));