<?php

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

class JFormFieldTemplates extends FormField
{

    /**
     * The form field type.
     *
     * @var    string
     *
     * @since  2.8
     */
    protected $type = 'Templates';

    /**
     * Method to attach a JForm object to the field.
     *
     * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the <field /> tag for the form field object.
     * @param   mixed             $value    The form field value to validate.
     * @param   string            $group    The field name group control value. This acts as as an array container for the field.
     *                                      For example if the field has name="foo" and the group value is set to "bar" then the
     *                                      full field name would end up being "bar[foo]".
     *
     * @return  boolean  True on success.
     *
     * @since   2.8
     */
    public function setup(SimpleXMLElement $element, $value, $group = null)
    {
        $return = parent::setup($element, $value, $group);

        return $return;
    }

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @since   11.1
     */
    protected function getInput()
    {
        $values = $this->value;

        if (is_string($values)) {
            $values = json_decode(htmlspecialchars_decode($this->value), true);
        }

        // cast to array
        $values = (array) $values;

        // default
        if (empty($values)) {
            $values = array(
                array(
                    'name' => '',
                    'url' => '',
                    'html' => '',
                ),
            );
        }

        $subForm = new Form($this->name, array('control' => $this->formControl));
        $children = $this->element->children();
        $subForm->load($children);
        $subForm->setFields($children);

        // And finaly build a main container
        $str = array();

        $str[] = '<div class="form-field-repeatable">';

        $n = 0;

        foreach ($values as $value) {

            $fields = $subForm->getFieldset();
            
            $str[] = '<div class="form-field-repeatable-item wf-templatemanager-templates">';
            $str[] = '  <div class="form-field-repeatable-item-group well well-small p-3 card">';

            foreach ($fields as $field) {
                // huh?
                if (!$field->element instanceof SimpleXMLElement) {
                    continue;
                }

                $tmpField = clone $field;

                $tmpField->element['multiple'] = true;

                $name = (string) $tmpField->element['name'];

                $val = is_array($value) && isset($value[$name]) ? $value[$name] : '';

                if ($name == "url") {
                    $val = htmlspecialchars($val, ENT_COMPAT, 'UTF-8');
                }

                if ($name == "html") {
                    $val = htmlspecialchars_decode($val);
                }

                if ($name == "thumbnail") {
                    $val = htmlspecialchars($val, ENT_COMPAT, 'UTF-8');
                }
 
                // escape value
                $tmpField->value = $val;

                $tmpField->setup($tmpField->element, $tmpField->value, $this->group);

                // reset id
                $tmpField->id .= '_' . $n;

                // reset name
                $tmpField->name = $name;

                $str[] = $tmpField->renderField();
            }

            $n++;

            $str[] = '  </div>';

            $str[] = '  <div class="form-field-repeatable-item-control">';
            $str[] = '      <button class="btn btn-link form-field-repeatable-add" aria-label="' . Text::_('JGLOBAL_FIELD_ADD') . '"><i class="icon icon-plus pull-right float-right"></i></button>';
            $str[] = '      <button class="btn btn-link form-field-repeatable-remove" aria-label="' . Text::_('JGLOBAL_FIELD_REMOVE') . '"><i class="icon icon-trash pull-right float-right"></i></button>';
            $str[] = '  </div>';

            $str[] = '</div>';
        }

        $str[] = '<input type="hidden" name="' . $this->name . '" value="" />';

        $str[] = '</div>';

        Factory::getDocument()->addScript(Uri::root(true) . '/media/plg_system_jcepro/editor/plugins/templatemanager/js/templates.js');

        return implode("", $str);
    }
}