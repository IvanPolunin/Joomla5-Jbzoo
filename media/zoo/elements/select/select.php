<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

// no direct access
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Restricted access');

// register ElementOption class
App::getInstance('zoo')->loader->register('ElementOption', 'elements:option/option.php');

/*
	Class: ElementSelect
		The select element class
*/
class ElementSelect extends ElementOption {

	/*
	   Function: edit
	       Renders the edit form field.

	   Returns:
	       String - html
	*/
	public function edit(){

		// init vars
		$options_from_config = $this->config->get('option', array());
		$multiple 			 = $this->config->get('multiple');
		$default			 = $this->config->get('default');
        $name   			 = $this->config->get('name');

		if (count($options_from_config)) {

			// set default, if item is new
			if ($default != '' && $this->_item != null && $this->_item->id == 0) {
				$this->set('option', $default);
			}

			$hasEmptyValue = false;
			$options = array();
            foreach ($options_from_config as $option) {
				$hasEmptyValue = $hasEmptyValue || $option['value'] == '';
				$options[] = $this->app->html->_('select.option', $option['value'], $option['name']);
			}
			if (!$multiple && !$hasEmptyValue) {
				array_unshift($options, $this->app->html->_('select.option', '', '-' . Text::sprintf('Select %s', $name) . '-'));
			}

			$style = $multiple ? 'multiple="multiple" size="5"' : 'class="form-select"';

			$html[] = $this->app->html->_('zoo.genericlist', $options, $this->getControlName('option', true), $style, 'value', 'text', $this->get('option', array()));

			// workaround: if nothing is selected, the element is still being transfered
			$html[] = '<input type="hidden" name="'.$this->getControlName('select').'" value="1" />';

			return implode("\n", $html);
		}

		return Text::_("There are no options to choose from.");
	}

}
