<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

/*
	Class: ElementOption
		The option elements base class
*/
abstract class ElementOption extends Element implements iSubmittable {

	/*
		Function: hasValue
			Checks if the element's value is set.

	    Parameters:
			$params - render parameter

		Returns:
			Boolean - true, on success
	*/
	public function hasValue($params = array()) {
		foreach ($this->get('option', array()) as $option) {
            if (strlen($option) > 0) {
                return true;
            }
        }
        return false;
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

        $selected_options = $this->get('option', array());
        return array_filter($this->config->get('option', array()), function ($option) use ($selected_options) {
            return in_array($option['value'], $selected_options);
        });

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
		$options = array_map(function ($option) { return $option['name']; }, $this->getValue($params));
		$params = $this->app->data->create($params);

		return $this->app->element->applySeparators($params->get('separated_by'), $options);

	}

	/*
		Function: getSearchData
			Get elements search data.

		Returns:
			String - Search data
	*/
	public function getSearchData() {
		$options = $this->get('option', array());
		$result = array();
		foreach ($this->config->get('option', array()) as $option) {
			if (in_array($option['value'], $options)) {
				$result[] = $option['name'];
			}
		}
		return (empty($result) ? null : implode("\n", $result));

	}

	/*
	   Function: editOption
	      Renders elements options for form input.

	   Parameters:
	      $var - form var name
	      $num - option order number

	   Returns:
		  Array
	*/
	public function editOption($var, $num, $name = null, $value = null){
		return $this->renderLayout($this->app->path->path("elements:option/tmpl/editoption.php"), compact('var', 'num', 'name', 'value'));
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
		$this->app->document->addScript('elements:option/option.js');
		$this->app->document->addStylesheet('elements:option/option.css');
		return parent::loadConfigAssets();
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
        return $this->edit();
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
        $options = array('required' => $params->get('required'));
		$messages = array('required' => 'Please choose an option.');
        $option = $this->app->validator
				->create('foreach', $this->app->validator->create('string', $options, $messages), $options, $messages)
				->clean($value->get('option'));

        $config_options = array_map(function($o) { return @$o['value']; }, $this->config->get('option', array()));
        foreach ($option as $key => $value) {
            if (!in_array($value, $config_options)) {
                unset($option[$key]);
            }
        }

		return compact('option');
	}

}
