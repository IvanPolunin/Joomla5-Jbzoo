<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Text;

/*
   Class: ElementJoomlamodule
       The Joomla module wapper element class
*/
class ElementJoomlamodule extends Element implements iSubmittable {

    /*
       Function: getValue
            Returns the element's value.

       Parameters:
            $params - render parameter

        Returns:
            Value
    */
    public function getValue($params = array()) {
        // get modules
        $modules = $this->app->module->load(true);
        $value   = $this->get('value', $this->config->get('default'));

        if ($value && isset($modules[$value])) {
            return $modules[$value];
        }

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
		$value = $this->getValue($params);

		if ($value) {
			$rendered = ModuleHelper::renderModule($value);

			if (isset($value->params)) {
				$module_params = $this->app->parameter->create($value->params);
				if ($moduleclass_sfx = $module_params->get('moduleclass_sfx')) {
					$html[] = '<div class="'.$moduleclass_sfx.'">';
					$html[] = $rendered;
					$html[] = '</div>';

					return implode("\n", $html);
				}
			}

			return $rendered;
		}

		return null;
	}

	/*
	   Function: edit
	       Renders the edit form field.

	   Returns:
	       String - html
	*/
	public function edit() {

		// init vars
		$options = array($this->app->html->_('select.option', '', '- '.Text::_('Select Module').' -'));

		return '<div>'.$this->app->html->_('zoo.modulelist', $options, $this->getControlName('value'), null, 'value', 'text', $this->get('value', $this->config->get('default'))).'</div>';

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
		return array('value' => $value->get('value'));
	}

}
