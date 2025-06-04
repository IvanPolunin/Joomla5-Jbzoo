<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\HiddenField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use NRFramework\Conditions\ConditionBuilder;
use NRFramework\Extension;

class JFormFieldConditionBuilder extends HiddenField
{
    /**
     *  Method to render the input field
     *
     *  @return  string
     */
    protected function getInput()
    {
       // Condition Builder relies on com_ajax for AJAX requests.
       if (!Extension::componentIsEnabled('ajax'))
       {
           \Factory::getApplication()->enqueueMessage(Text::_('AJAX Component is not enabled.'), 'error');
           return;
       }

        // This is required on views we don't control such as the Fields or the Modules view page.
        HTMLHelper::_('formbehavior.chosen', '.hasChosen');

        HTMLHelper::stylesheet('plg_system_nrframework/fields.css', ['relative' => true, 'version' => 'auto']);
        HTMLHelper::stylesheet('plg_system_nrframework/joomla' . (defined('nrJ4') ? '4' : '3') . '.css', ['relative' => true, 'version' => 'auto']);

        Text::script('NR_CB_SELECT_CONDITION_GET_STARTED');
        Text::script('NR_ARE_YOU_SURE_YOU_WANT_TO_DELETE_THIS_ITEM');

        // Value must be always be a JSON string.
        if (is_array($this->value))
        {
            $this->value = json_encode($this->value);
        }

        // If field is empty, initialize it with an empty Condition Group

        $payload = [
            'include_rules' => isset($this->getOptions()['include_rules']) ? ConditionBuilder::prepareXmlRulesList($this->getOptions()['include_rules']) : '',
            'exclude_rules' => isset($this->getOptions()['exclude_rules']) ? ConditionBuilder::prepareXmlRulesList($this->getOptions()['exclude_rules']) : '',
            'exclude_rules_pro' => isset($this->getOptions()['exclude_rules_pro']),
            'geo_modal' => ConditionBuilder::getGeoModal() // Out of context
        ];

        return '
            <div class="cb-wrapper">
                ' . parent::getInput() . ConditionBuilder::getLayout('conditionbuilder', $payload) . '
            </div>
        ';
    }

    /**
     * Returns the field options.
     * 
     * @return  array
     */
    protected function getOptions()
    {
        $options = [
            'include_rules' => (string) $this->element['include_rules'],
            'exclude_rules' => (string) $this->element['exclude_rules'],
            'exclude_rules_pro' => (string) $this->element['exclude_rules_pro']
        ];
        
        return $options;
    }
}