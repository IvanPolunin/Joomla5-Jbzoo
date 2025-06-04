<?php

/**
 * @author          Tassos.gr <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\Conditions\Conditions\Joomla;

defined('_JEXEC') or die;

use NRFramework\Conditions\Condition;
use NRFramework\Cache;
use Joomla\CMS\Language\Text;

class AccessLevel extends Condition
{
    /**
     * Shortcode aliases for this Condition
     */
    public static $shortcode_aliases = ['user.access'];
    
    /**
     *  Get the user's authorized view levels
     * 
     *  @return array User groups
     */
	public function value()
	{
		$viewLevels = $this->user->getAuthorisedViewLevels();

        // Beyond the IDs return also the Titles of the User Access Levels but only when the User Value includes Titles (Performance-wise). This is mainly used in conditional shortcode to be able to do comparison with Titles.
        if ($this->selection)
        {
            $userValueHasTitles = array_filter((array) $this->selection, function($item) 
            {
                return !is_numeric($item);
            });

            if ($userValueHasTitles)
            {
                $viewLevels = array_merge($viewLevels, $this->getAuthorisedViewLevelTitles());
            }
        }

        return $viewLevels;
	}

	/**
	 * A one-line text that describes the current value detected by the rule. Eg: The current time is %s.
	 *
	 * @return string
	 */
	public function getValueHint()
	{
		return Text::sprintf('NR_DISPLAY_CONDITIONS_HINT_' . strtoupper($this->getName()), implode(', ', $this->getAuthorisedViewLevelTitles()));
	}

    /**
     * Return a list with user access level titles
     *
     * @return array
     */
    private function getAuthorisedViewLevelTitles()
    {
        $callback = function()
        { 
            $db = $this->db;
    
            $query = $db->getQuery(true)
                ->select($db->qn('title'))
                ->from('#__viewlevels')
                ->where($db->qn('id') . ' IN ' . '(' . implode(',', $this->user->getAuthorisedViewLevels()) . ')');
    
            $db->setQuery($query);
    
            return $db->loadColumn();
        };

        return Cache::memo('getAuthorisedViewLevelTitles', $callback);
    }
}