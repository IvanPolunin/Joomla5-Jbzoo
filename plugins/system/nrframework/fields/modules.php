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

require_once JPATH_PLUGINS . '/system/nrframework/helpers/fieldlist.php';

class JFormFieldModules extends NRFormFieldList
{
    /**
     * Method to get a list of options for a list input.
     *
     * @return   array   An array of options.
     */
    protected function getOptions()
    {
        $db = $this->db;
        
        $query = $db->getQuery(true);
        
        $query->select('*')
        ->from('#__modules')
        ->where('published=1')
        ->where('access !=3')
        ->order('title');
        
        $client = isset($this->element['client']) ? (int) $this->element['client'] : false;

        if ($client !== false)
        {
            $query->where('client_id = ' . $client);
        }

        $rows = $db->setQuery($query);
        $results = $db->loadObjectList();

        $options = array();

        if ($this->showSelect())
        {
            $options[] = HTMLHelper::_('select.option', "", '- ' . Text::_("NR_SELECT_MODULE") . ' -');
        }

        foreach ($results as $option)
        {
            $options[] = HTMLHelper::_('select.option', $option->id, $option->title . ' (' . $option->id . ')');
        }

        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }
}