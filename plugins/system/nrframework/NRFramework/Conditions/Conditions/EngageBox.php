<?php

/**
 * @author          Tassos.gr <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\Conditions\Conditions;

defined('_JEXEC') or die;

use NRFramework\Conditions\Condition;

class EngageBox extends Condition
{
    /**
     * Shortcode aliases for this Condition
     */
    public static $shortcode_aliases = ['onotherbox'];

    /**
     * Checks if the user viewed any of the given boxes
     * 
     * @return  bool
     */
    public function pass()
    {
        // Skip if the visitorID is not set
        $visitorID = \NRFramework\VisitorToken::getInstance()->get();
        if (empty($visitorID))
        {
            return true;
        }

        $box_ids  = $this->selection;
        if (!is_array($box_ids) || empty($box_ids))
        {
            return true;
        }

        $box_ids = implode(',', $box_ids);
        
        $query = $this->db->getQuery(true);

        $query
            ->select('COUNT(id)')
            ->from($this->db->quoteName('#__rstbox_logs'))
            ->where($this->db->quoteName('event') . ' = 1')
            ->where($this->db->quoteName('box') . " IN ( $box_ids )")
            ->where($this->db->quoteName('visitorid') . ' = '. $this->db->quote($visitorID));
        
        $this->db->setQuery($query);

        $pass = (int) $this->db->loadResult();

        return (bool) $pass;
	}
}