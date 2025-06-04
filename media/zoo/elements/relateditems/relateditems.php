<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

/*
	Class: ElementRelatedItems
		The related items element class
*/
class ElementRelatedItems extends Element implements iSubmittable {

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
    Function: getValue
        Returns the element's value.

   Parameters:
        $params - render parameter

    Returns:
        Value
    */
	public function getValue($params = array())
    {
		return $this->_orderItems(
			$this->_getRelatedItems(),
			$this->app->data->create($params)->get('order', $this->config->get('order', array()))
		);
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
		$items    = $this->getValue($params);

		// create output
		$layout   = $params->get('layout');
		$output   = array();
		foreach ($items as $item) {
			$path     = 'item';
			$prefix   = 'item.';
			$type     = $item->getType()->id;
            $renderer = $this->app->renderer->create('item')->addPath(array(
                $this->app->path->path('component.site:'),
                $item->getApplication()->getTemplate()->getPath()
            ));
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

        // get ordering type
        $alphanumeric = false;
        if (($index = array_search('_alphanumeric', $order)) !== false) {
            $alphanumeric = true;
            unset($order[$index]);
        }

        if (!empty($order)) {
        	$self = $this;
            uasort($items, function ($itemA, $itemB) use ($alphanumeric, $order, $self) {
                $valueA = $self->_getItemOrderValue($itemA, $order);
                $valueB = $self->_getItemOrderValue($itemB, $order);
                return $alphanumeric
                    ? strnatcmp($valueA, $valueB)
                    : strcasecmp($valueA, $valueB);
            });
        }

        return $reversed ? array_reverse($items, true) : $items;
	}

	protected function _getItemOrderValue($item, $order) {
        foreach (array_reverse($order) as $identifier) {
            if ($element = $item->getElement($identifier)) {
                return strpos($identifier, '_item') === 0 ? $item->{str_replace('_item', '', $identifier)} : $element->getSearchData();
            }
        }
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
            $params - AppData submission parameters

		Returns:
			String - html
	*/
	public function renderSubmission($params = array()) {

		// load assets
		$this->app->document->addScript('elements:relateditems/relateditems.js');

		return $this->_edit();

	}

	protected function _edit($published = true) {

		$query = array('controller' => 'item', 'task' => 'element', 'tmpl' => 'component', 'func' => 'selectRelateditem', 'object' => $this->identifier);

		// filter app
		$appId = $this->config->get('app_id');
        if (!empty($appId)) {
            $query['app_id'] = $appId;
        } elseif ($type = $this->getType()) {
            $query['app_id'] = $type->getApplication()->id;
        }

		// filter types
		foreach ($this->config->get('selectable_types', array()) as $key => $selectable_type) {
			$query["type_filter[$key]"] = $selectable_type;
		}

		// filter items
		if ($this->getItem()) {
			$query['item_filter'] = $this->_item->id;
		}

		return $this->renderLayout($this->app->path->path("elements:relateditems/tmpl/edit.php"),
            array(
                'data' => $this->_getRelatedItems($published),
				'link' => $this->app->link($query)
            )
        );
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

		$table = $this->app->table->item;
        if ($selectable_types = $this->config->get('selectable_types', array()) and !empty($selectable_types)) {
			foreach ($items as $item) {
				if (!empty($item) && !in_array($table->get($item)->type, $selectable_types)) {
					throw new AppValidatorException('Please choose a correct related item.');
				}
			}
		}

		return array('item' => $items);
	}

	/*
		Function: loadAssets
			Load elements css/js assets.

		Returns:
			Void
	*/
	public function loadAssets() {
		$this->app->document->addScript('elements:relateditems/relateditems.js');
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
        Function: loadAssets
            Load elements css/js config assets.

        Returns:
            Void
    */
    public function loadConfigAssets() {
		$this->app->document->addScript('fields:zooitemorder.js');
		$this->app->document->addStylesheet('fields:zooitemorder.css');
        $this->app->document->addStylesheet('elements:relateditems/types.css');
        return parent::loadConfigAssets();
    }

}
