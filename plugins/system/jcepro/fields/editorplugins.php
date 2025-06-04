<?php

/**
 * JCE Pro Editor
 *
 * @copyright  (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @copyright  (C) 2009-2024 Ryan Demmer. All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Form\Field\PluginsField;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Form Field class for the Joomla Framework.
 *
 * @since  2.5.0
 */
class JFormFieldEditorPlugins extends PluginsField
{
    /**
     * The field type.
     *
     * @var    string
     * @since  2.5.0
     */
    protected $type = 'EditorPlugins';

    /**
     * Method to attach a Form object to the field.
     *
     * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
     * @param   mixed              $value    The form field value to validate.
     * @param   string             $group    The field name group control value. This acts as an array container for the field.
     *                                       For example if the field has name="foo" and the group value is set to "bar" then the
     *                                       full field name would end up being "bar[foo]".
     *
     * @return  boolean  True on success.
     *
     * @see     FormField::setup()
     * @since   3.2
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $return = parent::setup($element, $value, $group);

        if ($return) {
            $this->folder = 'editors';
        }

        return $return;
    }

    /**
     * Method to get a list of options for a list input.
     *
     * @return  object[]  An array of JHtml options.
     *
     * @since   2.5.0
     */
    protected function getOptions()
    {
        $parentOptions = parent::getOptions();

        $this->folder = 'editors';

        $exclude = $this->element['exclude'] ?? '';

        if ($exclude) {
            $exclude = explode(',', $exclude);

            // remove exclude options from the list
            foreach ($parentOptions as $key => $option) {
                if (in_array($option->value, $exclude)) {
                    unset($parentOptions[$key]);
                }
            }
        }

        return $parentOptions;
    }
}