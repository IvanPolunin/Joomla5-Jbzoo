<?php
/**
* @package   ZOO Component
* @file      typeapp.php
* @version   2.4.3 April 2011
* @author    -Dima-
* @copyright Copyright (C) 2007 - 2010 YOOtheme GmbH
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

		// get element from parent parameter form
		$config  	 = $parent->element->config;
		$table = $parent->app->table->application;
		
		foreach ($table->all(array('order' => 'name')) as $app) {
				// application option
				$options[] = JHTML::_('select.option', $app->id, JText::_($app->name.' ('.$app->application_group.')'));
		}
		
		// init vars
		$attributes = array();
		$attributes['class'] = (string) $node->attributes()->class ? (string) $node->attributes()->class : 'inputbox';
		$attributes['size'] = (string) $node->attributes()->size ? (string) $node->attributes()->size : '';
		
		echo $this->app->html->_('select.genericlist', $options, $control_name.'[type_app][]', $attributes, 'value', 'text', $config->get('type_app', array()));