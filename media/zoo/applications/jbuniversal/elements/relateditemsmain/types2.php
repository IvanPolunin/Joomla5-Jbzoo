<?php
/**
* @package   ZOO Component
* @file      types2.php
* @version   2.5.16 April 2012
* @author    -Dima-
* @copyright Copyright (C) 2007 - 2010 YOOtheme GmbH
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

	// get element from parent parameter form
	$config		= $parent->element->config;
	$table_app	= $parent->app->table->application;
	$groupapp	= array();
	
	foreach ($table_app->all(array('order' => 'name')) as $applic) {
		if(!in_array($applic->application_group,$groupapp)){

			$path = $applic->getResource() . '/types';
			$filter = '/^.*config$/';

			if ($files = $parent->app->path->files($path, false, $filter)) {
				foreach ($files as $file) {
					$alias = basename($file, '.config');
					$filePath = $applic->getPath() . '/types/' . $file;
					$fileContent = JFile::exists($filePath) ? file_get_contents($filePath) : null;
					$relateds_all_xml[] = array(
						$applic->application_group,
						$alias,
						$parent->app->data->create($fileContent)
					);
				}	
			}
			$groupapp[] = $applic->application_group;
		}
	}

	if(is_array($relateds_all_xml )){
		foreach($relateds_all_xml as $appid=>$relateds_xml){
			if(is_array($relateds_xml[2]->get("elements"))){
				foreach ($relateds_xml[2]->get("elements") as $identifier => $related_xml) {
					if ($related_xml["type"] == "relateditemsmain"){
						$elements[$identifier] = $related_xml;
						$elements[$identifier]["filename"]=$relateds_xml[1];
						$elements[$identifier]["app"]=array('id'=>$appid,'name'=>$relateds_xml[0]);
					}
				}
			}
		}
	}
	// init vars
	$attributes = array();
	$attributes['class'] = (string) $node->attributes()->class ? (string) $node->attributes()->class : 'inputbox';
	$attributes['multiple'] = 'multiple';
	$attributes['size'] = (string) $node->attributes()->size ? (string) $node->attributes()->size : '';
	$options = array();

	if(is_array($elements)){
		foreach ($elements as $identifier=>$type) {
		// value = Appnumber:filename:identifier
			$options[] = $this->app->html->_('select.option', $type["app"]["id"].':'.$type["filename"].':'.$identifier, JText::_($type["app"]["name"].': '.$type["filename"].' - '.$type["name"]));
		}
	}
	echo $this->app->html->_('select.genericlist', $options, $control_name.'[revers_types][]', $attributes, 'value', 'text', $config->get('revers_types', array()));
