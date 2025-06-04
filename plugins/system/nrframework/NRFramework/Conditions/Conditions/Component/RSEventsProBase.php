<?php

/**
 * @author          Tassos.gr
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\Conditions\Conditions\Component;

defined('_JEXEC') or die;

class RSEventsProBase extends ComponentBase
{
    /**
     * The component's Single Page view name
     *
     * @var string
     */
    protected $viewSingle = 'rseventspro';

    /**
     * The component's option name
     *
     * @var string
     */
    protected $component_option = 'com_rseventspro';

    /**
     * Get single events's assosiated categories
     *
     * @param   Integer  The Single Event id
	 * 
     * @return  array
     */
	protected function getSinglePageCategories($id)
	{
        $db = $this->db;

        $query = $db->getQuery(true)
                    ->select('id')
                    ->from('#__rseventspro_taxonomy')
                    ->where($db->quoteName('ide') . '=' . $db->q($id))
                    ->where($db->quoteName('type') . '=' . $db->q('category'));
        
        $db->setQuery($query);
		return $db->loadColumn();
	}
}