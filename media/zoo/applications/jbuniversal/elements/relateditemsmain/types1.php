<?php
/**
* @package   ZOO Component
* @file      types1.php
* @version   2.5.16 April 2012
* @author    -Dima-
* @copyright Copyright (C) 2007 - 2010 YOOtheme GmbH
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

	// get element from parent parameter form
	$config  	 = $parent->element->config;
	$table_app = $parent->app->table->application;
	$groupapp= array();
	
	foreach ($table_app->all(array('order' => 'name')) as $application) {
		if(!in_array($application->application_group,$groupapp)){
			foreach ($application->getTypes() as $type) {
				$options[] = $this->app->html->_('select.option', $type->id, JText::_($type->name.' ('.$application->application_group.')'));
			}
			$groupapp[] = $application->application_group;
		}
	}
		
	// init vars
	$attributes = array();
	$attributes['class'] = (string) $node->attributes()->class ? (string) $node->attributes()->class : 'inputbox';
	$attributes['multiple'] = 'multiple';
	$attributes['size'] = (string) $node->attributes()->size ? (string) $node->attributes()->size : '';

	echo $this->app->html->_('select.genericlist', $options, $control_name.'[selectable_types][]', $attributes, 'value', 'text', $config->get('selectable_types', array()));