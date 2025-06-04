<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

/*
	Class: ElementItemPrevNext
		The item prev next element class
*/

use Joomla\CMS\Router\Route;

class ElementItemPrevNext extends Element {

	protected $_items = null;

	/*
		Function: hasValue
			Checks if the element's value is set.

	   Parameters:
			$params - render parameter

		Returns:
			Boolean - true, on success
	*/
	public function hasValue($params = array()) {
		@list($prev, $next) = $this->_getAdjacentItems();
		return $prev || $next;
	}

    /*
       Function: getValue
            Returns the element's value.

       Parameters:
            $params - render parameter

        Returns:
            Value
    */
    public function getValue($params = array()) {
        if (!$this->hasValue($params)) {
            return;
        }

        list($prev, $next) = $this->_getAdjacentItems();

        $category_id = $this->app->request->getInt('category_id');

        return array(
            'prev_id' => $prev ? $prev->id : '',
            'prev_link' => $prev ? Route::_($this->app->route->item($prev, false).($category_id ? '&amp;category_id='.$category_id : '')) : '',
            'next_id' => $next ? $next->id : '',
            'next_link' => $next ? Route::_($this->app->route->item($next, false).($category_id ? '&amp;category_id='.$category_id : '')) : ''
        );
    }

	protected function _getAdjacentItems() {
		if ($this->_items === null) {

			// get category_id
			if (!$category_id = $this->app->request->getInt('category_id')) {
				$category_id = (int) $this->_item->getPrimaryCategoryId();
			}

			if ($category = $this->app->table->category->get((int) $category_id)) {
				$order = $category->getParams('site')->get('config.item_order');
			} else {
				$order = $this->_item->getApplication()->getParams('frontpage')->get('config.item_order');
			}

			$this->_items = $this->app->table->item->getPrevNext($this->_item->getApplication()->id, (int) $category_id, $this->_item->id, true, null, $order);

		}

		return $this->_items;
	}

	/*
	   Function: edit
	       Renders the edit form field.

	   Returns:
	       String - html
	*/
	public function edit() {
		return null;
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

		$links = $this->getValue($params);

		// render layout
		if ($links && $layout = $this->getLayout()) {
			return $this->renderLayout($layout, $links);
		}
	}

}
