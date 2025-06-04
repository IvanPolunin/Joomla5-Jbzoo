<?php

/**
 * @author          Tassos.gr
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\SmartTags;

use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Restricted access');

class Language extends SmartTag
{
	/**
	 * Returns the text of a language string. Replace CONSTANT with the language constant you want to return its text. For instance, to return the text of the language string COM_CONTACT_DETAILS, use {language.COM_CONTACT_DETAILS}. 
	 * 
	 * @param   string  $key
	 * 
	 * @return  string
	 */
	public function fetchValue($key)
	{
		$key = strtolower($key);
		$key_parts = explode('_', $key);

		$lang = $this->factory->getLanguage();

		// Load language overrides: On front-end load administrator's override and vice versa.
		$overridePath = $this->factory->isFrontend() ? JPATH_ADMINISTRATOR : JPATH_SITE;
		$lang->load($lang->getTag() . '.override', $overridePath, 'overrides');
	
		switch ($key_parts[0])
		{
			case 'com':
				if (isset($key_parts[1]) && !empty($key_parts[1]))
				{
					$extension = 'com_' . $key_parts[1];
				}

				$lang->load($extension, JPATH_ADMINISTRATOR);
				$lang->load($extension, JPATH_SITE);
				break;

			case 'plg':
				if (isset($key_parts[1]) && !empty($key_parts[1]) && isset($key_parts[2]) && !empty($key_parts[2]))
				{
					$extension = implode('_', ['plg', $key_parts[1], $key_parts[2]]);
				}

				$path = implode(DIRECTORY_SEPARATOR, [JPATH_PLUGINS, $key_parts[1], $key_parts[2]]);

				$lang->load($extension, $path);
				break;
		}

		return Text::_($key);
	}
}