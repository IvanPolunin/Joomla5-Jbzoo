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
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\HTML\HTMLHelper;

class JFormFieldNRResponsiveControl extends TextField
{
    protected $breakpoint = 'desktop';
    
    protected $hide_device_selector = false;

    /**
     *  Method to render the input field
     *
     *  @return  string  
     */
    function getInput()
    {
        return $this->getLayout();
    }

    /**
     * Returns html for all devices
     * 
     * @return  array
     */
    private function getFieldsData()
    {
        if ($this->hasSubform())
        {
            return $this->getSubformFieldsData();
        }
        
        return $this->getSubtypeFieldsData();
    }

    private function getSubtypeFieldsData()
    {
        $name = (string) $this->element['name'];

        $breakpoints = \NRFramework\Helpers\Responsive::getBreakpoints();

        $device = $this->breakpoint;

        // Control default value
        $control_default = json_decode($this->default, true);

        $units = isset($this->element['subtype_units']) ? array_filter(array_unique(array_map('trim', explode(',', (string) $this->element['subtype_units'])))) : [];

        // Default value of the input for breakpoint
        $default = $control_default && isset($control_default[$device]) ? $control_default[$device] : ($device === 'desktop' ? $this->default : null);

        $field_data = $this->getFieldInputByDevice($name, $device, $default);

        // Render layout
        $payload = [
            'device' => $device,
            'breakpoint' => $breakpoints[$device],
            'breakpoints' => $breakpoints,
            'html' => $field_data['html'],
            'name' => $this->name . '[' . $name . '][' . $device . ']',
            'hide_device_selector' => $this->hide_device_selector,
            'units' => $units,
            'unit' => $field_data['unit'] ? $field_data['unit'] : ($units ? $units[0] : null),
            'is_linked' => $field_data['linked'],
        ];

        $layout = new FileLayout('responsive_control_item', JPATH_PLUGINS . '/system/nrframework/layouts');

        return $layout->render($payload);
    }

    /**
     * Returns the field's title and value
     * 
     * @param   string  $field_name     The field name of the field.
     * @param   string  $device         The breakpoint of the field.
     * @param   string  $default        The default value of the field.
     * 
     * @return  array
     */
    private function getFieldInputByDevice($field_name, $device, $default = null)
    {
        $data = [];

        /**
         * TODO: Remove this in the future.
         * 
         * This is for compatibility purposes with versions < 6.0.3 as any previous
         * value set, should now only be visible on the desktop breakpoint.
         */
        $input_value = $this->form->getValue($field_name, $this->group);
        $input_value = is_string($input_value) && json_decode($input_value, true) ? json_decode($input_value, true) : $input_value;

        // Get input value
        $value = $this->getFieldInputValue($field_name, $device);

        // If no value is set, get the default value (if given)
        if (is_null($value) && $default)
        {
            $value = $default;
        }
    
        $field_type = isset($this->element['subtype']) ? (string) $this->element['subtype'] : 'text';

        /**
         * TODO: Remove this in the future.
         * 
         * This is for compatibility purposes, as any previous
         * value set (i.e. 500, 250px, etc...),
         * should now only be set on the desktop device and
         * other devices (tablet, mobile) should inherit the value.
         */
        if (!is_null($input_value) && $input_value !== '' && is_scalar($input_value))
        {
            $value = '';

            // Explicit case for NRToggle and "auto" values. Set the same value across all breakpoints.
            if (strtolower($field_type) === 'nrtoggle' || $input_value === 'auto')
            {
                $value = $input_value;
            }
            else
            {
                if ($device === 'desktop')
                {
                    $is_dimension_control = in_array($field_type, ['TFBorderRadiusControl', 'TFDimensionControl']);

                    if ($is_dimension_control)
                    {
                        $input_type = $field_type === 'TFDimensionControl' ? 'margin_padding' : 'border_radius';
                        $value = \NRFramework\Helpers\Controls\Spacing::parseInputValue($input_value, $input_type);
                        $value['linked'] = '1';
                    }
                    else
                    {
                        $value = \NRFramework\Helpers\Controls\Control::findUnitInValue($input_value);
                    }
                }
            }
        }

        /**
         * Units are set only to these fields:
         * 
         * TFBorderRadiusControl
         * TFDimensionControl
         * TFUnitControl
         */
        $unitBasedControls = ['TFBorderRadiusControl', 'TFDimensionControl', 'TFUnitControl'];
        $value = in_array($field_type, $unitBasedControls) ? $value : (isset($value['value']) ? $value['value'] : $value);

        $data = [
            'html' => $this->getSubtypeHTML($field_name, $field_type, $device, $value),
            'unit' => isset($value['unit']) ? $value['unit'] : null,
            'linked' => isset($value['linked']) ? $value['linked'] : true,
        ];
        
        return $data;
    }

    /**
     * Returns the subtype HTML field.
     * 
     * @param   string  $type
     * @param   string  $type
     * @param   string  $device
     * @param   mixed   $value
     * 
     * @return  string
     */
    protected function getSubtypeHTML($name, $type, $device, $value)
    {
        // Set hint
        $hint = '';
        $subtype_hint = isset($this->element['subtype_hint']) ? (string) $this->element['subtype_hint'] : null;
        if ($subtype_hint)
        {
            $hint = 'hint="' . $subtype_hint . '"';
        }

        // Set format
        $format = '';
        $subtype_format = isset($this->element['subtype_format']) ? (string) $this->element['subtype_format'] : null;
        if ($subtype_format)
        {
            $format = 'format="' . $subtype_format . '"';
        }

        // Set units
        $units = '';
        $subtype_units = isset($this->element['subtype_units']) ? (string) $this->element['subtype_units'] : null;
        if ($subtype_units)
        {
            $units = 'units="' . $subtype_units . '"';
        }

        // Set checked
        $checked = '';
        $subtype_checked = isset($this->element['subtype_checked']) ? (string) $this->element['subtype_checked'] : null;
        if ($subtype_checked)
        {
            $checked = 'checked="' . $subtype_checked . '"';
        }

        // Set keywords
        $keywords = '';
        $subtype_keywords = isset($this->element['subtype_keywords']) ? (string) $this->element['subtype_keywords'] : null;
        if ($subtype_keywords)
        {
            $keywords = 'keywords="' . $subtype_keywords . '"';
        }

        // Set layout
        $layout = '';
        $subtype_layout = isset($this->element['subtype_layout']) ? (string) $this->element['subtype_layout'] : null;
        if ($subtype_layout)
        {
            $layout = 'layout="' . $subtype_layout . '"';
        }

        // Set class
        $class = '';
        $subtype_class = isset($this->element['subtype_class']) ? (string) $this->element['subtype_class'] : null;
        if ($subtype_class)
        {
            $class = 'class="' . $subtype_class . '"';
        }

        // Set min
        $min = '';
        $subtype_min = isset($this->element['subtype_min']) ? (string) $this->element['subtype_min'] : '';
        if ($subtype_min !== '')
        {
            $min = 'min="' . $subtype_min . '"';
        }
        // Set max
        $max = '';
        $subtype_max = isset($this->element['subtype_max']) ? (string) $this->element['subtype_max'] : null;
        if ($subtype_max)
        {
            $max = 'max="' . $subtype_max . '"';
        }

        // Set options
        $options = '';
        $subtype_options = isset($this->element['subtype_options']) ? (string) $this->element['subtype_options'] : null;
        if ($subtype_options)
        {
            $subtype_options = json_decode($subtype_options, true);
            if (is_array($subtype_options))
            {
                // Remove the "Inherit" option from the Desktop breakpoint
                if ($device === 'desktop' && ($inherit_item_key = array_search('NR_INHERIT', $subtype_options)) !== false)
                {
                    unset($subtype_options[$inherit_item_key]);
                }
                
                foreach ($subtype_options as $key => $label)
                {
                    $options .= '<option value="' . $key . '">' . $label . '</option>';
                }
            }
        }

        /**
         * It looks like the MediaField does not accept a null value.
         * 
         * So set it to empty.
         */
        if (strtolower($type) === 'media' && is_null($value))
        {
            $value = '';
        }

        $xml = new SimpleXMLElement('
            <field
                name="' . $name . '"
                type="' . $type . '"
                ' . $format . '
                ' . $keywords . '
                ' . $checked . '
                ' . $hint . '
                ' . $units . '
                ' . $min . '
                ' . $max . '
                ' . $layout . '
                ' . $class . '
            >
                ' . $options . '
            </field>
        ');

        $this->form->setField($xml);

        $field = $this->form->getField($name, null, $value);

        if ($this->group)
        {
            $field->id = $this->formControl . '_' . $this->group . '_' . $this->fieldname;
            $field->name = $this->formControl . '[' . $this->group . '][' . $this->fieldname . ']';
        }
        
        $field->id .= '_' . $device;
        $field->name .= '[' . $device . ']';

        return $field->getInput();
    }

    public function renderField($options = [])
    {
        // Create separate controls for each breakpoint
        $html = '';

        $subtype = isset($this->element['subtype']) ? (string) $this->element['subtype'] : 'text';

        $breakpoints = \NRFramework\Helpers\Responsive::getBreakpoints();

        $origID = $this->id;

        $showon = $this->showon;

        foreach ($breakpoints as $breakpoint => $breakpoint_data)
        {
            $tmpShowon = $showon;
            $this->id = $origID . '_' . $breakpoint;

            if (in_array($subtype, ['TFDimensionControl', 'TFBorderRadiusControl']))
            {
                $this->id .= '_top';
            }

            $options['class'] = 'nr-responsive-control-group device-' . $breakpoint;

            if ($breakpoint === 'desktop')
            {
                $options['class'] .= ' nr-responsive-control-group--active';
            }

            if ($showon)
            {
                // Add group
                if ($this->group)
                {
                    $tmpShowon = preg_replace('/(\[AND\]|\[OR\])/i', '$1' . $this->group . '.', $tmpShowon);
                    $tmpShowon = $this->group . '.' . $tmpShowon;
                }

                // Add breakpoint
                $tmpShowon = str_replace('{breakpoint}', '.' . $breakpoint, $tmpShowon);
            }

            $this->showon = $tmpShowon;

            
            $this->breakpoint = $breakpoint;
            $html .= parent::renderField($options);
        }
        
        return $html;
    }

    /**
     * Returns the field layout
     * 
     * @return  string
     */
    private function getLayout()
    {
        HTMLHelper::stylesheet('plg_system_nrframework/controls/responsive_control.css', ['relative' => true, 'version' => 'auto']);
        HTMLHelper::script('plg_system_nrframework/controls/responsive_control.js', ['relative' => true, 'version' => 'auto']);

        $name = isset($this->element['name']) ? (string) $this->element['name'] : '';
        $width = isset($this->element['width']) ? (string) $this->element['width'] : '';
        $class = isset($this->element['class']) ? ' ' . (string) $this->element['class'] : '';

        $this->hide_device_selector = isset($this->element['hide_device_selector']) && (string) $this->element['hide_device_selector'] === 'true';

        if ($this->hide_device_selector)
        {
            $class .= ' compact';
        }

        if (defined('nrJ4'))
        {
            $class .= ' isJ4';
        }

        $data = [
            'name'  => $name,
            'width'  => $width,
            'class'  => $class,
            'breakpoint' => $this->breakpoint,
            'html' => $this->getFieldsData(),
        ];

        // Render layout
        $layout = new FileLayout('responsive_control', JPATH_PLUGINS . '/system/nrframework/layouts');
        return $layout->render($data);
    }

    /**
     * Finds the field input value
     * 
     * @param   string  $field_name
     * @param   string  $device
     * 
     * @return  string
     */
    private function getFieldInputValue($field_name, $device)
    {
        if (!$values = $this->getValue())
        {
            return;
        }

        // New NRResponsiveControl stores [field_name][desktop][value]
        if (isset($values[$device]))
        {
            return $values[$device];
        }

        // Subform NRResponsiveControl (deprecated) stores [field_control][field_name][device][value]
        if (!isset($values[$field_name][$device]))
        {
            return;
        }

        // Return empty
        if ($values[$field_name][$device] === '')
        {
            return '';
        }
        
        // Return actual value
        return $values[$field_name][$device];
    }

    /**
     * Returns the field value
     * 
     * @return  mixed
     */
    private function getValue()
    {
        if (empty($this->value))
        {
            return;
        }

        return is_string($this->value) ? json_decode($this->value, true) : $this->value;
    }

    /**
     * Checks whether the field uses subform to render its field.
     * 
     * @return  bool
     */
    protected function hasSubform()
    {
        return $this->element->subform;
    }

    /**
     * Used when NRResponsiveControl is used with
     * a Subform field.
     * 
     * Methods related to Subform are deprecated and
     * we should migrate all usage to use the subtype format
     * as it's a cleaner way.
     */

    /**
     * Returns all fields within the subform field.
     * 
     * We must always use a single field as child.
     * 
     * @deprecated
     * 
     * @return  void
     */
    private function getSubformFieldsData()
    {
        if (!$fieldsList = $this->getSubformFieldsList())
        {
            return [];
        }

        $breakpoints = \NRFramework\Helpers\Responsive::getBreakpoints();

        $base_name = (string) $fieldsList['base_name'];

        // Control default value
        $control_default = json_decode($this->default, true);

        $group1 = !empty($this->group) ? '[' . $this->group . ']' : '';
        $group2 = !empty($this->group) ? '_' . $this->group : '';

        $device = $this->breakpoint;

        $field_device_data = $fieldsList['fields'][0];
        
        $name = $field_device_data['name'];

        // Default value of the input for breakpoint
        $default = null;

        if ($control_default && isset($control_default[$name][$device]))
        {
            $default = $control_default[$name][$device];
        }
        
        $field_data = $this->getSubformFieldInputByDevice($name, $device, $default);
        $field_html = $field_data['html'];

        $field_html = str_replace(
            [
                $group1 . '[' . $name . ']',
                $group2 . '_' . $name
            ],
            [
                $group1 . '[' . $base_name . '][' . $name . '][' . $device . ']',
                $group2 . '_' . $base_name . '_' . $name . '_' . $device
            ], $field_html
        );

        $units = isset($field_device_data['units']) ? $field_device_data['units'] : [];

        // Render layout
        $payload = [
            'device' => $device,
            'breakpoint' => $breakpoints[$device],
            'breakpoints' => $breakpoints,
            'html' => $field_html,
            'name' => $this->name . '[' . $name . '][' . $device . ']',
            'units' => $units,
            'unit' => $field_data['unit'] ? $field_data['unit'] : ($units ? $units[0] : null),
            'is_linked' => $field_data['linked'],
            'hide_device_selector' => false
        ];

        $layout = new FileLayout('responsive_control_item', JPATH_PLUGINS . '/system/nrframework/layouts');
        
        return $layout->render($payload);
    }

    /**
     * Returns the field's title and value
     * 
     * @param   string  $field_name     The field name of the field.
     * @param   string  $device         The breakpoint of the field.
     * @param   string  $default        The default value of the field.
     * 
     * @deprecated
     * 
     * @return  array
     */
    private function getSubformFieldInputByDevice($field_name, $device, $default = null)
    {
        $data = [];

        /**
         * TODO: Remove this in the future.
         * 
         * This is for compatibility purposes with versions < 6.0.3 as any previous
         * value set, should now only be visible on the desktop breakpoint.
         */
        $input_value = $this->form->getValue($field_name, $this->group);

        foreach ($this->element->subform->field as $key => $field)
        {
            if ((string) $field->attributes()->name != $field_name)
            {
                continue;
            }

            // Get input value
            $value = $this->getFieldInputValue($field_name, $device);

            // If no value is set, get the default value (if given)
            if (is_null($value) && $default)
            {
                $value = $default;
            }
        
            $field_type = (string) $field->attributes()->type;

            /**
             * TODO: Remove this in the future.
             * 
             * This is for compatibility purposes, as any previous
             * value set (i.e. 500, 250px, etc...),
             * should now only be set on the desktop device and
             * other devices (tablet, mobile) should inherit the value.
             */
            if (!is_null($input_value) && $input_value !== '')
            {
                $value = '';

                if ($device === 'desktop')
                {
                    $is_dimension_control = in_array($field_type, ['TFBorderRadiusControl', 'TFDimensionControl']);

                    if ($is_dimension_control)
                    {
                        $input_type = $field_type === 'TFDimensionControl' ? 'margin_padding' : 'border_radius';
                        $value = \NRFramework\Helpers\Controls\Spacing::parseInputValue($input_value, $input_type);
                        $value['linked'] = '1';
                    }
                    else
                    {
                        $value = \NRFramework\Helpers\Controls\Control::findUnitInValue($input_value);
                    }
                }
            }

            $data = [
                'html' => $this->form->getInput($field_name, $this->group, $value),
                'unit' => isset($value['unit']) ? $value['unit'] : null,
                'linked' => isset($value['linked']) ? $value['linked'] : true
            ];
            break;
        }
        
        return $data;
    }

    /**
     * Returns the list of added fields
     * 
     * @deprecated
     * 
     * @return  array
     */
    private function getSubformFieldsList()
    {
        $data = [
            'base_name' => $this->element['name'],
            'fields' => []
        ];
        
        $fieldset = $this->form->getFieldset();

        foreach ($fieldset as $key => &$value)
        {
            if ($value->fieldname !== (string) $this->element['name'])
            {
                continue;
            }
            
            if (!$value instanceof JFormFieldNRResponsiveControl)
            {
                continue;
            }

            if (!isset($value->element->subform))
            {
                continue;
            }
            
            $atts = $value->element->subform->field->attributes();

            $payload = [
                'name' => (string) $atts['name']
            ];

            $data['fields'][] = $payload;

            break;
        }

        return $data;
    }
}
