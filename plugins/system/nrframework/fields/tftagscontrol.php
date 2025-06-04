<?php

/**
 * @package         Advanced Custom Fields
 * @version         2.8.0 Pro
 * 
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            http://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos Marinos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Form\Field\TagField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

class JFormFieldTFTagsControl extends TagField
{
    /**
     * Name of the layout being used to render the field
     *
     * @var    string
     * @since  4.0.0
     */
    protected $layout = 'joomla.form.field.tag';
    
    protected function getInput()
    {
        if (!defined('nrJ4'))
        {
            return parent::getInput();
        }
        
        $data = $this->getLayoutData();

        $data['value']         = $this->value;
        $data['options']       = $this->getOptions();
        $data['multiple']      = true;
        $data['allowCustom']   = true;
        $data['remoteSearch']  = false;
        $data['dataAttribute'] = ' allow-custom';

        return $this->getRenderer($this->layout)->render($data);
    }
    
    /**
     * Method to get a list of options for a list input.
     *
     * @return  array
	 */
	protected function getOptions()
	{
        // Get all dropdown choices
        $options = [];

        if (is_array($this->value))
        {
            foreach ($this->value as $key => $value)
            {
                $options[] = HTMLHelper::_('select.option', $key, $value);
            }
        }

        return $options;
    }
}