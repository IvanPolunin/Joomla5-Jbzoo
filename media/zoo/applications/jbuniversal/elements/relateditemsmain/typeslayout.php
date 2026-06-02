<?php
/**
* @package   ZOO Component
* @file      typeslayout.php
* @version   2.5.16 April 2012
* @author    -Dima
* @copyright Copyright (C) 2007 - 2010 YOOtheme GmbH
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

	/****************include template from another app or use current********************/
	$config  	 =  $parent->element->config;
	if ($application_id_array = $config->get('type_app', array())){
		$application_id = $application_id_array[0];
		$application = $parent->app->table->application->get($application_id);
		$layout_path = $application->getTemplate()->getPath();
	}
	else {
		$layout_path = $parent->layout_path;
	}
	/************************************************************************************/
	
// init vars
$class      = (string) $node->attributes()->class ? 'class="'.$node->attributes()->class.'"' : 'class="inputbox"';
$constraint = (string) $node->attributes()->constraint;
		
// get renderer
$renderer = $this->app->renderer->create('item')->addPath($layout_path);

// if selectable types isn't specified, get all types
if (empty($parent->selectable_types)) {
	$parent->selectable_types = array('');	
	foreach (JFolder::folders($layout_path.'/'.$renderer->getFolder().'/item') as $folder) {
		$parent->selectable_types[] = $folder;
	}
}

// get layouts
$layouts = array();
foreach ($parent->selectable_types as $type) {
	$path   = 'item';
	$prefix = 'item.';
	if (!empty($type) && $renderer->pathExists($path.DIRECTORY_SEPARATOR.$type)) {
		$path   .= DIRECTORY_SEPARATOR.$type;
		$prefix .= $type.'.';
	}
	foreach ($renderer->getLayouts($path) as $layout) {

		$metadata = $renderer->getLayoutMetaData($prefix.$layout);

		if (empty($constraint) || $metadata->get('type') == $constraint) {
			$layouts[$layout] = $metadata->get('name');
		}
	}
}		
		
// create layout options
$options = array($this->app->html->_('select.option', '', JText::_('Item Name')));
foreach ($layouts as $layout => $layout_name) {
	$text	   = $layout_name;
	$val	   = $layout;
	$options[] = $this->app->html->_('select.option', $val, $text);
}

echo $this->app->html->_('select.genericlist', $options, $control_name.'['.$name.']', $class, 'value', 'text', $value, $control_name.$name);