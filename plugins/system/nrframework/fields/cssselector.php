<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Factory;

class JFormFieldCSSSelector extends FormField
{
	/**
     * Render the Opening Hours
     * 
     * @return string
     */
    protected function getInput()
    {
        $isNew = $this->form->getData()->get('id') == 0;

        if (is_string($this->value))
        {
            $this->value = json_decode($this->value, true);
        }

        $elName = (string) $this->element['name'];
        $groups = explode('.', $this->group);
        $groups[] = $elName;

        $xml = array_map(function($group) {
            return '<fields name="' . $group . '">';
        }, $groups);

        $xml = implode(' ', $xml);

        $fieldsetUniqueName = $this->group . $elName;

        $xml .= '
            <fieldset name="' . $fieldsetUniqueName . '">
                <field name="selector" type="text" 
                    hiddenLabel="true"
                    filter="raw"
                    hint="NR_CSS_SELECTOR_ENTER"
                    default="' . ($isNew && isset($this->value['selector']) ? $this->value['selector'] : '') .'"
                />
                <field name="task" type="list" 
                    hiddenLabel="true"
                    default="' . ($isNew && isset($this->value['task']) ? $this->value['task'] : 'text') .'">
                    <option value="text">NR_CSS_SELECTOR_TEXT</option>
                    <option value="html">NR_CSS_SELECTOR_HTML</option>
                    <option value="innerhtml">NR_CSS_SELECTOR_INNER_HTML</option>
                    <option value="attr">NR_CSS_SELECTOR_ATTR</option>
                    <option value="count">NR_CSS_SELECTOR_TOTAL</option>
                </field>
                <field name="attr" type="text" 
                    showon="task:attr"
                    hiddenLabel="true"
                    hint="NR_CSS_SELECTOR_ATTR_NAME"
                    default="' . ($isNew && isset($this->value['attr']) ? $this->value['attr'] : '') .'"
                />
            </fieldset>
        ';

        $xml .= str_repeat('</fields>', count($groups));

        $this->form->setField(new SimpleXMLElement($xml));

        $html = $this->form->renderFieldSet($fieldsetUniqueName);

        Factory::getDocument()->addStyleDeclaration('
            .css_selector_container {
                display:flex;
                gap:10px;
            }
            .css_selector_container .control-group {
                margin:0;
                width:270px;
            }
        ');

        return '<div class="css_selector_container">' . $html . '</div>';
    }
}