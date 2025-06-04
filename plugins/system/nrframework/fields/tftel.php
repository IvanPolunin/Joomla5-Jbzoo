<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\TextField;
use NRFramework\Countries;

class JFormFieldTFTel extends TextField
{
    protected $layout = 'joomla.form.field.tel';
    
    /**
     *  Method to render the input field
     *
     *  @return  string  
     */
    public function getInput()
    {
        $this->maybeParseValue();
        
        return parent::getInput();
    }

    private function maybeParseValue()
    {
        $value = $this->value;

        if (is_string($value) && json_decode($value, true))
        {
            $value = json_decode($value, true);
        }

        if (is_array($value))
		{
			$countryCode = isset($value['code']) ? $value['code'] : '';
			$phoneNumber = isset($value['value']) ? $value['value'] : '';

			$calling_code = Countries::getCallingCodeByCountryCode($countryCode);
			$calling_code = $calling_code !== '' ? '+' . $calling_code : '';

			$this->value = $calling_code . $phoneNumber;
		}
    }
}