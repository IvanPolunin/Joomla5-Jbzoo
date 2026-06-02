<?php
/**
* @package   ZOO 
* @file      relateditemsmain.php
* @version   2.5.16 April 2012
* @author    -Dima-
* @copyright Copyright (C) 2007 - 2010 YOOtheme GmbH
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

/*
	Class: ElementRelatedItems
		The related items element class
*/
class ElementRelatedItemsMain extends Element implements iSubmittable {

	protected $_related_items;

	/*
		Function: hasValue
			Checks if the element's value is set.

	   Parameters:
			$params - render parameter

		Returns:
			Boolean - true, on success
	*/		
	public function hasValue($params = array()) {
		$items = $this->_getRelatedItems();
		return !empty($items);
	}
	
	/*
		Function: render
			Renders the element.

	   Parameters:
            $params - render parameter

		Returns:
			String - html
	*/
	public function render($params = array()) {
		
		// init vars
		$params   = $this->app->data->create($params);
		
		/****************include template from another app or use current********************/
		$config  	 = $this->getConfig();
		
		if ($application_id_array = $config->get('type_app', array())){
		
			$application_id = $application_id_array[0];
			$application = $this->app->table->application->get($application_id);
			
			$layout_path = $application->getTemplate()->getPath();
		}
		else {
			$layout_path = $this->_item->getApplication()->getTemplate()->getPath();
		}
		/************************************************************************************/

		$renderer = $this->app->renderer->create('item')->addPath(array($this->app->path->path('component.site:'), $layout_path));
		$items    = $this->_orderItems($this->_getRelatedItems($params), $params->get('order'));
		
		// create output
		$layout   = $params->get('layout');
		$output   = array();
		
		// init vars
		
		$limit	= $params->get('limit');
		$count = 1;
		
		foreach($items as $item) {
			
			if ($limit && ($count > $limit)){
				break;
			}
			$count++;
			
			$path   = 'item';
			$prefix = 'item.';
			$type   = $item->getType()->id;
			if ($renderer->pathExists($path.DIRECTORY_SEPARATOR.$type)) {
				$path   .= DIRECTORY_SEPARATOR.$type;
				$prefix .= $type.'.';
			}

			if (in_array($layout, $renderer->getLayouts($path))) {
				$output[] = $renderer->render($prefix.$layout, array('item' => $item));
			} elseif ($params->get('link_to_item', false) && $item->getState()) {
				$output[] = '<a href="'.$this->app->route->item($item).'" title="'.$item->name.'">'.$item->name.'</a>';
			} else {
				$output[] = $item->name;
			}
		}
		
		return $this->app->element->applySeparators($params->get('separated_by'), $output);
	}
	
	protected function _orderItems($items, $order) {
		
		// if string, try to convert ordering
		if (is_string($order)) {
			$order = $this->app->itemorder->convert($order);
		}
		
		$items = (array) $items;
		$order = (array) $order;
		$sorted = array();
		$reversed = false;

		// remove empty values
		$order = array_filter($order);

		// if random return immediately
		if (in_array('_random', $order)) {
			shuffle($items);
			return $items;
		}

		// get order dir
		if (($index = array_search('_reversed', $order)) !== false) {
			$reversed = true;
			unset($order[$index]);
		} else {
			$reversed = false;
		}

		// order by default
		if (empty($order)) {
			return $reversed ? array_reverse($items, true) : $items;
		}

		// if there is a none core element present, ordering will only take place for those elements
		if (count($order) > 1) {
			$order = array_filter($order, create_function('$a', 'return strpos($a, "_item") === false;'));
		}

		if (!empty($order)) {

			// get sorting values
			foreach ($items as $item) {
				foreach ($order as $identifier) {
					if ($element = $item->getElement($identifier)) {
						$sorted[$item->id] = strpos($identifier, '_item') === 0 ? $item->{str_replace('_item', '', $identifier)} : $element->getSearchData();
						break;
					}
				}
			}

			// do the actual sorting
			$reversed ? arsort($sorted) : asort($sorted);

			// fill the result array
			foreach (array_keys($sorted) as $id) {
				if (isset($items[$id])) {
					$sorted[$id] = $items[$id];
				}
			}

			// attach unsorted items
			$sorted += array_diff_key($items, $sorted);

		// no sort order provided
		} else {
			$sorted = $items;
		}

		return $sorted;
	}
	
	protected function _getRelatedItems($published = true) {

		if ($this->_related_items == null) {

			// init vars
			$table = $this->app->table->item;
			$this->_related_items = array();
			$related_items = array();
			
			// get items
			$items = $this->get('item', array());

			// check if items have already been retrieved
			foreach ($items as $key => $id) {
				if ($table->has($id)) {
					$related_items[$id] = $table->get($id);
					unset($items[$key]);
				}
			}
			
			if (!empty($items)) {
				// get dates
				$db   = $this->app->database;
				$date = $this->app->date->create();
				$now  = $db->Quote($date->toSQL());
				$null = $db->Quote($db->getNullDate());
				$items_string = implode(', ', $items);
				$conditions = $table->key.' IN ('.$items_string.')'
							. ($published ? ' AND state = 1'
							.' AND '.$this->app->user->getDBAccessString()
							.' AND (publish_up = '.$null.' OR publish_up <= '.$now.')'
							.' AND (publish_down = '.$null.' OR publish_down >= '.$now.')' : '');
				$order = 'FIELD('.$table->key.','.$items_string.')';
				$related_items += $table->all(compact('conditions', 'order'));
			}
			
			foreach ($this->get('item', array()) as $id) {
				if (isset($related_items[$id])) {
					$this->_related_items[$id] = $related_items[$id];
				}
			}
			
		}
		
		return $this->_related_items;
	}
	
	/*
	   Function: edit
	       Renders the edit form field.

	   Returns:
	       String - html
	*/
	public function edit() {
		return $this->_edit(false);
	}
	
	/*
		Function: renderSubmission
			Renders the element in submission.

	   Parameters:
            $params - submission parameters

		Returns:
			String - html
	*/
	public function renderSubmission($params = array()) {

		// load assets
		$this->app->document->addScript('elements:relateditemsmain/relateditemsmain.js');

		return $this->_edit();

	}
	
	protected function _edit($published = true) {

		$query = array(
			'controller' => 'item',
			'task'       => 'element',
			'tmpl'       => 'component',
			'func'       => 'selectRelateditem',
			'object'     => $this->identifier
		);
		
		// filter types
		$selectable_types = $this->config->get('selectable_types', array());
		foreach ($selectable_types as $key => $selectable_type) {
			$query["type_filter[$key]"] = $selectable_type;
		}

		// filter items
		if ($this->_item) {
			$query['item_filter'] = $this->_item->id;
		}
		
		$changeapp = $this->config->get('type_app');
		if ($changeapp){
			$query['app_id'] = $changeapp[0];
		}
		
		if ($layout = $this->getLayout('edit.php')) {
            return $this->renderLayout($layout,
                array(
                    'element' => $this->identifier,
                    'data' => $this->_getRelatedItems($published),
					'link' => $this->app->link($query)
                )
            );
        }
	}

	/*
		Function: validateSubmission
			Validates the submitted element

	   Parameters:
            $value  - AppData value
            $params - AppData submission parameters

		Returns:
			Array - cleaned value
	*/
	public function validateSubmission($value, $params) {

        $options     = array('required' => $params->get('required'));
		$messages    = array('required' => 'Please select at least one related item.');

		$items = (array) $this->app->validator
				->create('foreach', $this->app->validator->create('integer'), $options, $messages)
				->clean($value->get('item'));
		$previous = (array) $this->app->validator
				->create('foreach', $this->app->validator->create('integer'), $options, $messages)
				->clean($value->get('previous'));

		$table = $this->app->table->item;
		$selectable_types = $this->config->get('selectable_types', array());
		if (!empty($selectable_types)) {
			foreach ($items as $item) {
				if (!empty($item) && !in_array($table->get($item)->type, $selectable_types)) {
					throw new AppValidatorException('Please choose a correct related item.');
				}
			}
		}

		return array('item' => $items, 'previous' => $previous);
	}

	/*
		Function: loadAssets
			Load elements css/js assets.

		Returns:
			Void
	*/
	public function loadAssets() {
		$this->app->document->addScript('elements:relateditemsmain/relateditemsmain.js');
	}

	/*
		Function: getConfigForm
			Get parameter form object to render input form.

		Returns:
			Parameter Object
	*/
	public function getConfigForm() {
		return parent::getConfigForm()->addElementPath(dirname(__FILE__));
	}
	
	
	/*
		Function: bindData
			Set data through data array.

		Parameters:
			$data - array

		Returns:
			Void
	*/
	public function bindData($data = array()) {
			
		$zoo = App::getInstance('zoo');

		// register event
		$zoo->event->dispatcher->connect('submission:saved', array('ReversItems', 'SubmissionSaved'));
		$zoo->event->dispatcher->connect('item:saved', array('ReversItems', 'ItemSaved'));
		
		if (isset($this->_item)) {
				
			$cleared_data = array();
			if($data['item']){
				
				$cleared_data['item'] = array_unique($data['item']);
				if($data['previous']){
					if($deleteditem = array_diff($data['previous'], $data['item'])){;				
						$cleared_data['deleted'] = array_unique($deleteditem);
					}
				}
			}else{
				if($data['previous']){
					$cleared_data['deleted'] = array_unique($data['previous']);
				}
			}
			
			$this->_item->elements->set($this->identifier, $cleared_data);
		}	
	}
}

class ReversItems  {
	
	public static function SubmissionSaved($event) {

		$item = $event->offsetGet('item');
		$new = $event['new'];
		
		ReversItems::SaveRevers($item);

	}
	
	public static function ItemSaved($event) {

		$item = $event->getSubject();
		$new = $event['new'];

		ReversItems::SaveRevers($item);

	}
	
	public static function SaveRevers($item){
			
		$app = App::getInstance('zoo');
		$table = $app->table->item;
		
		$mainitem_id = $item->id;
		$elements = $item->getElements();
		
		foreach ($elements as $key => $element){
			if ($element->getElementType() == 'relateditemsmain'){
				$data[$key]['data'] = $element->data();
				$data[$key]['revers'] = $element->config->get('revers_types', array());
			}
		}

		foreach ($data as $identifier => $dat){
		
			$types = array();
			$ident = array();
			$result = array();
			$releated = array();
			$new_releated = array();
			
			foreach ($dat['revers'] as $conf){
				list(, $types[], $ident[])=explode(':',$conf);
			}
			
			// delete current item from items, which was deleted from this one
			if(isset($ident)){
				$typesunique = array_unique($types);
				
				foreach($typesunique as $unique){
					$keytype = array_keys ($types, $unique);

					foreach($keytype as $key){
						$result[$unique][] = $ident[$key];
					}
				}
				
				if (isset($dat['data']['deleted'])) {				
					
					if(count($dat['data']['deleted'])){
						$deleted_uniq	=	array_unique($dat['data']['deleted']);
						foreach ($deleted_uniq as $item){
							$item_deleted = $table->get($item);

							if (array_key_exists($item_deleted->type, $result)) {

								foreach($result[$item_deleted->type] as $revers_identifier){
									$element = $item_deleted->elements[$revers_identifier];
									$element['item'] = array_diff($element['item'], array($mainitem_id));	//delete current id from related item
									
									if (is_array($element['item'])){
										$new_releated[$revers_identifier] = $element;
									}else{
										$new_releated[$revers_identifier]['item'] = array();
									}
								}
								if($new_releated){
									$item_deleted->releated = $new_releated;

									$app->table->ElementRelatedSave->save($item_deleted);
								}
							}
						}
					}	
				}
				// get and save releated item

				if (isset($dat['data']['item'])) {

					$item_uniq	=	array_unique($dat['data']['item']);
				
					foreach($item_uniq as $item) {
				
						$item_releated = $table->get($item);
						if (array_key_exists($item_releated->type, $result)) {
						
							foreach($result[$item_releated->type] as $revers_identifier){
									
								if(is_array($item_releated->elements[$revers_identifier]['item'])){
									$relateditems = $item_releated->elements[$revers_identifier]['item'];
								}else{
									$relateditems = array();
								}
								if ($mainitem_id){
									$itemexist = in_array($mainitem_id, $relateditems);
									if(!$itemexist){
										array_push($relateditems, $mainitem_id); 
										$releated[$revers_identifier]['item'] = $relateditems;
									}
								}
							}
									
							if($releated){
									$item_releated->releated = $releated;
									
									$app->table->ElementRelatedSave->save($item_releated);
							}
						}
					}
				}
			}
		}
	}
}

class ElementRelatedSaveTable extends AppTable{
	
	public function __construct($app) {
		parent::__construct($app, ZOO_TABLE_ITEM);
		
	}

	public function Save($object) {

		$releated = $object->releated;

		// init vars
		$element_data = array();

		foreach($releated as $identifier => $value){
				$object->elements[$identifier] = $value;
		}
		
		return parent::Save($object);

	}

}
