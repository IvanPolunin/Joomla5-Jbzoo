<?php

/**
 * @author          Tassos.gr
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\Conditions\Conditions\Component;

defined('_JEXEC') or die;

class ICagendaBase extends ComponentBase
{
    /**
     * The component's Single Page view name
     *
     * @var string
     */
    protected $viewSingle = 'event';

    /**
     * The component's option name
     *
     * @var string
     */
    protected $component_option = 'com_icagenda';

    /**
     * Get single page's assosiated categories
     *
     * @param   Integer  The Single Page id
	 * 
     * @return  array
     */
	protected function getSinglePageCategories($id)
	{
        $db = $this->db;

        $query = $db->getQuery(true)
            ->select($db->quoteName('catid'))
            ->from('#__icagenda_events')
            ->where($db->quoteName('id') . '=' . $db->q($id));

        $db->setQuery($query);

		return $db->loadColumn();
	}
}