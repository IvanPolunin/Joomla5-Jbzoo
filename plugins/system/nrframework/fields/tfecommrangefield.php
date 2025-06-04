<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

require_once dirname(__DIR__) . '/helpers/field.php';

class JFormFieldTFEcommRangeField extends NRFormField
{
	/**
	 *  Method to render the input field
	 *
	 *  @return  string
	 */
	protected function getInput()
	{
		$prefixLabel = isset($this->element['prefixLabel']) ? (string) $this->element['prefixLabel'] : false;
		$mainName = (string) $this->element['name'];

		$showIsAnyOption = isset($this->element['showIsAnyOption']) ? (string) $this->element['showIsAnyOption'] === 'true' : false;
		$isAnyOption = $showIsAnyOption ? '<option value="any">NR_ANY</option>' : '';

		$showIsNotEqualOption = isset($this->element['showIsNotEqualOption']) ? (string) $this->element['showIsNotEqualOption'] === 'true' : false;
		$isNotEqualOption = $showIsNotEqualOption ? '<option value="not_equal">NR_NOT_EQUAL_TO</option>' : '';
		
		$xml = new SimpleXMLElement('
			<fields name="' . $mainName . '">
				<fieldset name="' . $mainName . '">
					<field name="operator" type="comparator"
						hiddenLabel="true"
						class="noChosen"
						default="any">
							' . $isAnyOption . '
							<option value="equal">NR_EQUAL_TO</option>
							' . $isNotEqualOption . '
							<option value="less_than">NR_FEWER_THAN</option>
							<option value="less_than_or_equal_to">NR_FEWER_THAN_OR_EQUAL_TO</option>
							<option value="greater_than">NR_GREATER_THAN</option>
							<option value="greater_than_or_equal_to">NR_GREATER_THAN_OR_EQUAL_TO</option>
							<option value="range">NR_BETWEEN</option>
					</field>
					<field name="value" type="number"
						hiddenLabel="true"
						class="input-small"
						default="1"
						hint="1"
						min="1"
						showon="operator!:any"
					/>
					<field name="range_note" type="note"
						class="tf-note-and"
						description="NR_AND_LC"
						showon="operator:range"
					/>
					<field name="value2" type="number"
						hiddenLabel="true"
						class="input-small"
						default="1"
						hint="1"
						min="1"
						showon="operator:range"
					/>
				</fieldset>
			</fields>
		');

        $this->form->setField($xml);

        foreach ($xml->field as $key => $field)
        {
            $name = (string) $field->attributes()->name;
            $type = (string) $field->attributes()->type;

			$value = isset($this->value[$name]) ? $this->value[$name] : null;
			$this->form->setValue($name, null, $value);
        }

		$html = $this->form->renderFieldset($mainName);

		HTMLHelper::stylesheet('plg_system_nrframework/tf-ecomm-range-field.css', ['relative' => true, 'version' => 'auto']);

		$prefix = $prefixLabel ? '<span>' . Text::_($prefixLabel) . '</span>' : '';
		
		return '<div class="tf-ecomm-range-extra-settings' . (!empty($this->class) ? ' ' . $this->class : '') . '">' . $prefix . $html . '</div>';
	}
}