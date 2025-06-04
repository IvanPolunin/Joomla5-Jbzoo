<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

// Initialize variables.
use Joomla\CMS\Language\Text;

$html = array();
$link = 'index.php?option=com_users&amp;view=users&amp;layout=modal&amp;tmpl=component&amp;ismoo=0&amp;field='.$name;

// Initialize some field attributes.
$attr = (string) $node->attributes()->class ? ' class="field-user-input-name '.(string) $node->attributes()->class.'"' : ' class="field-user-input-name"';
$attr .= (string) $node->attributes()->size ? ' size="'.(int) $node->attributes()->size.'"' : '';

// Initialize JavaScript field attributes.
$onchange = (string) $node->attributes()->onchange;

// Load the modal behavior script.
$this->app->html->_('behavior.modal', 'a.modal_'.$name);

if (version_compare(JVERSION, '4.0', '<')) {
    $this->app->html->script('jui/fielduser.min.js', false, true, false, false, true);
} else {
    $this->app->document->addScript('fields:zooitemauthor.js');
}

// Load the current username if available.
$username = $value == 'NO_CHANGE' ? Text::_( 'No Change' ) : (($user = $this->app->user->get($value)) && $user->id ? $user->name : Text::_('JLIB_FORM_SELECT_USER'));

//Create js wrapper
$html[] = '<div class="field-user-wrapper"
	data-url="'.$link.'"
	data-modal=".modal"
	data-modal-width="100%"
	data-modal-height="400px"
	data-input=".field-user-input"
	data-input-name=".field-user-input-name"
	data-button-select=".js-select-button"
	>';

// Create a dummy text field with the user name.
$html[] = '<div class="pull-left">';
$html[] = '	<input type="text" id="'.$name.'_name"' .
			' value="'.htmlspecialchars($username, ENT_COMPAT, 'UTF-8').'"' .
			' readonly="readonly"'.$attr.' />';
$html[] = '</div>';

// Create the user select button.
$html[] = '<div style="float: left; margin-left: 5px">';
if ((string) $node->attributes()->readonly != 'true') {
	$html[] = '		<a class="btn btn-small js-select-button" title="'.Text::_('JLIB_FORM_CHANGE_USER').'">';
	$html[] = '			'.Text::_('JLIB_FORM_CHANGE_USER').'</a>';
}
$html[] = '</div>';

//Add modal
$html[] = $this->app->html->_(
	'bootstrap.renderModal',
	'modal_' . $name,
	array(
		'title'  => Text::_('JLIB_FORM_CHANGE_USER'),
		'closeButton' => false,
		'footer' => '<button type="button" class="btn" data-dismiss="modal" data-bs-dismiss="modal">' . Text::_('JCANCEL') . '</button>',
        'height'      => '100%',
        'width'       => '100%',
        'modalWidth'  => 80,
        'bodyHeight'  => 60,
	)
);

// Create the real field, hidden, that stored the user id.
$html[] = '<input type="hidden" id="'.$name.'_id" name="'.$name.'" class="field-user-input" value="'.$value.'" data-onchange="'.$onchange.'"/>';

//Close wrapper
$html[] = '</div>';

echo implode("\n", $html);
