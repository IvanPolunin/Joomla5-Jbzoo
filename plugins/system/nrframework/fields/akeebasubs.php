<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

// No direct access to this file
// 
defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;

class JFormFieldAkeebaSubs extends ListField
{
    /**
     * Method to get a list of options for a list input.
     *
     * @return  array  An array of options.
     */
    protected function getOptions() 
    {
        if (!NRFramework\Extension::isInstalled('akeebasubs'))
        {
            return;
        }

        $lists = $this->getLevels();

        if (!count($lists))
        {
            return;
        }

        $options = array();

        foreach ($lists as $option)
        {
            $options[] = HTMLHelper::_('select.option', $option->id, $option->name);
        }

        return array_merge(parent::getOptions(), $options);
    }

    /**
     *  Retrieve all Akeeba Subscription Levels
     *
     *  @return  array  Subscription Levels
     */
    private function getLevels()
    {
        // Get a db connection.
        $db = Factory::getDbo();
        
        $query = $db->getQuery(true)
            ->select('l.akeebasubs_level_id as id, l.title AS name, l.enabled as published')
            ->from('#__akeebasubs_levels AS l')
            ->where('l.enabled > -1')
            ->order('l.title, l.akeebasubs_level_id');
        $db->setQuery($query);
        
        return $db->loadObjectList();
    }
}