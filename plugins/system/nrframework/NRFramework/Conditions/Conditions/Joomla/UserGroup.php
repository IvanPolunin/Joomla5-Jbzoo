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
use Joomla\CMS\Access\Access;
use Joomla\CMS\Language\Text;

class UserGroup extends Condition
{
    /**
     * Shortcode aliases for this Condition
     */
    public static $shortcode_aliases = ['user.group'];

    /**
     *  Returns the ID and the Title of the user's authorized groups
     * 
     *  @return array User groups
     */
	public function value()
	{
		$groups = $this->user->getAuthorisedGroups();

		// Beyond the IDs return also the Titles of the User Groups but only when the User Value includes Titles (Performance-wise). This is mainly used in conditional shortcode to be able to do comparison with Titles.
		if ($this->selection)
		{
			$userValueHasTitles = array_filter((array) $this->selection, function($item) 
			{
				return !is_numeric($item);
			});
	
			if ($userValueHasTitles)
			{
				foreach ($groups as $id)
				{
					$groups[] = Access::getGroupTitle($id);
				}
			}
		}

		return $groups;
	}

	/**
	 * A one-line text that describes the current value detected by the rule. Eg: The current time is %s.
	 *
	 * @return string
	 */
	public function getValueHint()
	{
        $db = $this->db;

        $query = $db->getQuery(true)
            ->select($db->qn('title'))
            ->from('#__usergroups')
            ->where($db->qn('id') . ' IN ' . '(' . implode(',', $this->user->getAuthorisedGroups()) . ')');

        $db->setQuery($query);

        $value = implode(', ', $db->loadColumn());
        
		return Text::sprintf('NR_DISPLAY_CONDITIONS_HINT_' . strtoupper($this->getName()), $value);
	}
}