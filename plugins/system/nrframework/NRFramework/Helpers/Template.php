<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

namespace NRFramework\Helpers;

defined('_JEXEC') or die;

use NRFramework\Cache;
use Joomla\CMS\Factory;

class Template
{
	/**
	 * Returns the current template name.
	 * 
	 * @return  string
	 */
	public static function getTemplateName()
	{
        $hash = 'TFGetTemplateName';

        if (Cache::has($hash))
        {
            return Cache::get($hash);
        }

		$template = null;
		
		if (Factory::getApplication()->isClient('site'))
		{
			$template = Factory::getApplication()->getTemplate();
		}
		else
		{
			$db = Factory::getDbo();
			$query = $db->getQuery(true)
				->select($db->quoteName('template'))
				->from($db->quoteName('#__template_styles'))
				->where($db->quoteName('client_id') . ' = 0')
				->where($db->quoteName('home') . ' = 1');
			$db->setQuery($query);
			$template = $db->loadResult();
		}
		
		return Cache::set($hash, $template);
	}
}