<?php

/**
 * @package         Advanced Custom Fields
 * @version         2.8.0 Pro
 * 
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Form\Field\TextField;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

class JFormFieldTFPhoneControl extends TextField
{
    /**
     * Returns control input.
     * 
     * @return  string
     */
    protected function getInput()
    {
        $this->assets();
        
        $class = $this->element['class'] ? (string) $this->element['class'] : '';
        $input_class = $this->element['input_class'] ? (string) $this->element['input_class'] : '';
        $inputmask = $this->element['inputmask'] ? (string) $this->element['inputmask'] : '';

        $value = $this->value;

        if (is_object($value))
        {
            $value = (array) $value;
        }

        $decodedValue = is_string($value) ? json_decode($value, true) : false;
        if (is_string($value) && is_array($decodedValue))
        {
            $value = $decodedValue;
        }
        else if (is_scalar($value))
        {
            $value = [
                'code' => '',
                'value' => $value
            ];
        }

        // Enqueue the country data as JS object
        Factory::getDocument()->addScriptOptions('tf_phonecontrol_data', $this->getCountriesData($value['value']));

        $payload = [
            'name' => $this->name,
            'id' => $this->id,
            'value' => $value,
            'class' => $class,
            'input_class' => $input_class,
            'inputmask' => $inputmask,
            'required' => $this->required,
            'readonly' => $this->readonly,
            'placeholder' => (string) $this->element['placeholder'],
            'browserautocomplete' => (string) $this->element['browserautocomplete'] !== '1',
        ];

        $layout = new FileLayout('phonecontrol', JPATH_PLUGINS . '/system/nrframework/layouts/controls');
        return $layout->render($payload);
    }

    /**
     * Load field assets.
     * 
     * @return  void
     */
    private function assets()
    {
        HTMLHelper::stylesheet('plg_system_nrframework/vendor/choices.min.css', ['relative' => true, 'versioning' => 'auto']);
        HTMLHelper::script('plg_system_nrframework/vendor/choices.min.js', ['relative' => true, 'versioning' => 'auto']);
        
        HTMLHelper::stylesheet('plg_system_nrframework/controls/phone.css', ['relative' => true, 'versioning' => 'auto']);
        HTMLHelper::script('plg_system_nrframework/controls/phone.js', ['relative' => true, 'versioning' => 'auto']);
    }

    private function getCountriesData($value = '')
    {
        $countries = NRFramework\Countries::getCountriesData();

        $countries = array_map(function($country) {
            return [
                'name' => $country['name'],
                'calling_code' => $country['calling_code']
            ];
        }, $countries);

        return $countries;
    }
}