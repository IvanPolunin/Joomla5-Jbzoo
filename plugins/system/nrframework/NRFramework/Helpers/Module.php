<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2023 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

namespace NRFramework\Helpers;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

class Module
{
	/**
	 * Get a module data.
	 *
	 * @param  integer  $value
	 * @param  string   $selector
	 *
	 * @return object
	 */
    public static function getData($value, $selector = 'id')
    {
		if (!$value)
		{
			return;
		}

        $db = Factory::getDbo();

        $query = $db->getQuery(true);

        $query
            ->select($db->quoteName(['params']))
            ->from($db->quoteName('#__modules'))
            ->where($db->quoteName($selector) . ' = ' . $db->quote($value))
            ->where($db->quoteName('access') . ' = 1');

        $db->setQuery($query);

        if (!$result = $db->loadResult())
        {
            return;
        }

        return new \Joomla\Registry\Registry($result);
    }
}