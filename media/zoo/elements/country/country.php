<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

/*
   Class: ElementCountry
       The country element class
*/

use Joomla\CMS\Language\Text;

class ElementCountry extends Element implements iSubmittable {

	/*
		Function: getSearchData
			Get elements search data.

		Returns:
			String - Search data
	*/
	public function getSearchData() {
        $countries = $this->getValue();

		return implode (' ', $countries);
	}

	/*
		Function: hasValue
			Checks if the element's value is set.

	    Parameters:
			$params - render parameter

		Returns:
			Boolean - true, on success
	*/
	public function hasValue($params = array()) {
		foreach ($this->get('country', array()) as $country) {
            if (!empty($country)) {
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
        $countries = $this->get('country', array());
        $keys = array_flip($countries);
        $countries = array_intersect_key($this->app->country->getIsoToNameMapping(), $keys);

        return $countries;
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

		$params = $this->app->data->create($params);
		$countries = $this->getValue();

		$countries = array_map(function($a) { return Text::_($a); }, $countries);

		return $this->app->element->applySeparators($params->get('separated_by'), $countries);
	}

	/*
	   Function: edit
	       Renders the edit form field.

	   Returns:
	       String - html
	*/
	public function edit(){

		//init vars
		$selectable_countries = $this->config->get('selectable_country', array());

		if (count($selectable_countries)) {

			$multiselect = $this->config->get('multiselect');

			$countries = $this->app->country->getIsoToNameMapping();
			$keys = array_flip($selectable_countries);
			$countries = array_intersect_key($countries, $keys);

			return '<div>'.$this->app->html->_('zoo.countryselectlist', $countries, $this->getControlName('country', true), $this->get('country', array()), $multiselect).'</div>';
		}

		return Text::_("There are no countries to choose from.");
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

        $options     = array('required' => $params->get('required'));
		$multiselect = $this->config->get('multiselect');
		$messages    = $multiselect ? array('required' => 'Please select at least one country.') : array('required' => 'Please select a country.');

        $clean = (array) $this->app->validator
				->create('foreach', $this->app->validator->create('string', $options, $messages), $options, $messages)
				->clean($value->get('country'));

        foreach ($clean as $country) {
            if (!empty($country) && !in_array($country, $this->config->get('selectable_country', array()))) {
                throw new AppValidatorException('Please choose a correct country.');
            }
        }

		return array('country' => $clean);
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

}
