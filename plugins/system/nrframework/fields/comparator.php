<?php
/**
 * @author          Tassos.gr <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Language\Text;

class JFormFieldComparator extends ListField
{
    private $defaults = [
        'includes' => 'NR_IS',
        'not_includes' => 'NR_IS_NOT'
    ];

    /**
	 * Method to get the field input markup for a generic list.
	 * Use the multiple attribute to enable multiselect.
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
        $this->required = true;
        $this->class .= ' comparator';

        return parent::getInput();
    }

    /**
     * Return the label.
     * 
     * @return  string
     */
    protected function getLabel()
    {
        if (!isset($this->element['label']))
        {
            $this->element['label'] = 'NR_MATCH';
        }

        return parent::getLabel();
    }

    /**
     * Return the options.
     * 
     * @return  string
     */
    protected function getOptions()
    {
        if (!$options = parent::getOptions())
        {
            $options = $this->defaults;

            foreach ($options as $key => &$value)
            {
                $value = Text::_($value);
            }
        }

        return $options;
    }
}
