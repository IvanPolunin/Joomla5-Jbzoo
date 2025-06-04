<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

// load zoo frontend language file
use Joomla\CMS\Language\Text;

$this->app->system->language->load('com_zoo');

$this->app->html->_('behavior.modal', 'a.modal');
$this->app->document->addStylesheet('fields:zooapplication.css');
$this->app->document->addScript('fields:zooapplication.js');

// init vars
$params	= $this->app->parameterform->convertParams($parent);
$table	= $this->app->table->application;

// set modes
$modes = array();

if ($node->attributes()->allitems) {
	$modes[] = $this->app->html->_('select.option', 'all', Text::_('All Items'));
}

if ($node->attributes()->categories) {
	$modes[] = $this->app->html->_('select.option', 'categories', Text::_('Categories'));
}

if ($node->attributes()->types) {
	$modes[] = $this->app->html->_('select.option', 'types', Text::_('Types'));
}

if ($node->attributes()->items) {
	$modes[] = $this->app->html->_('select.option', 'item', Text::_('Item'));
}

// create application/category select
$cats    = array();
$types   = array();
$options = array($this->app->html->_('select.option', '', '- '.Text::_('Select Application').' -'));

foreach ($table->all(array('order' => 'name')) as $application) {

	// application option
	$options[] = $this->app->html->_('select.option', $application->id, $application->name);

	// create category select
	if ($node->attributes()->categories) {
		$attribs = 'class="category app-'.$application->id.($value != $application->id ? ' hidden' : null).' form-select" data-category="'.$control_name.'[category]"';
		$opts = array();
		if ($node->attributes()->frontpage) {
			$opts[] = $this->app->html->_('select.option', '', '&#8226;	'.Text::_('Frontpage'));
		}
		$cats[]  = $this->app->html->_('zoo.categorylist', $application, $opts, $value == $application->id ? $control_name.'[category]' : '', $attribs, 'value', 'text', $params->get('category'));
	}

	// create types select
	if ($node->attributes()->types) {
		$opts = array();

		foreach ($application->getTypes() as $type) {
			$opts[] = $this->app->html->_('select.option', $type->id, $type->name);
		}

		$attribs = 'class="type app-'.$application->id.($value != $application->id ? ' hidden' : null).' form-select" data-type="'.$control_name.'[type]"';
		$types[] = $this->app->html->_('select.genericlist', $opts, $control_name.'[type]', $attribs, 'value', 'text', $params->get('type'), 'application-type-'.$application->id);
	}
}

// create html
$html[] = '<div id="'.$name.'" class="zoo-application">';
$html[] = $this->app->html->_('select.genericlist', $options, $control_name.'['.$name.']', 'class="application form-select"', 'value', 'text', $value);

// create mode select
if (count($modes) > 1) {
	$html[] = $this->app->html->_('select.genericlist', $modes, $control_name.'[mode]', 'class="mode form-select"', 'value', 'text', $params->get('mode'));
}

// create categories html
if (!empty($cats)) {
	$html[] = '<div class="categories">'.implode("\n", $cats).'</div>';
}

// create types html
if (!empty($types)) {
	$html[] = '<div class="types">'.implode("\n", $types).'</div>';
}

// create items html
$link = '';
if ($node->attributes()->items) {

	$field_name	= $control_name.'[item_id]';
	$item_name  = Text::_('Select Item');

	if ($item_id = $params->get('item_id')) {
		$item = $this->app->table->item->get($item_id);
		$item_name = $item ? $item->name : $item_id;
	}

	$link = $this->app->link(array('controller' => 'item', 'task' => 'element', 'tmpl' => 'component', 'func' => 'selectZooItem', 'object' => $name), false);

	$html[] = '<div class="item">';
	$html[] = '<input type="text" id="'.$name.'_name" value="'.htmlspecialchars($item_name, ENT_QUOTES, 'UTF-8').'" disabled="disabled" />';
	$html[] = '<a '.(version_compare(JVERSION, '4.0', '<') ? 'class="modal"' : '').' title="'.Text::_('Select Item').'"  href="#" rel="{handler: \'iframe\', size: {x: 850, y: 500}}">'.Text::_('Select').'</a>';
	$html[] = '<input type="hidden" id="'.$name.'_id" name="'.$field_name.'" value="'.(int) $item_id.'" />';
	$html[] = '</div>';

}

$html[] = '</div>';

$javascript  = 'jQuery(function($) { jQuery("#'.$name.'").ZooApplication({ url: "'.$link.'", msgSelectItem: "'.Text::_('Select Item').'" }); });';
$javascript  = "<script type=\"text/javascript\">\n// <!--\n$javascript\n// -->\n</script>\n";

echo implode("\n", $html).$javascript;
