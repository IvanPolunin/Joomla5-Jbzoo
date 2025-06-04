<?php

/**
 * @author          Tassos.gr
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\SmartTags;

use Joomla\Registry\Registry;
use Joomla\CMS\Filter\InputFilter;

defined('_JEXEC') or die('Restricted access');

class QueryString extends SmartTag
{
	/**
	 * Returns the value of a URL query string parameter as found in the $_GET superglobal array. For example, if the page URL is http://example.com/page.php?key1=red&key2=blue, the {querystring.key2} Smart Tag will return blue.
	 * 
	 * @param   string  $key
	 * 
	 * @return  string
	 */
	public function fetchValue($key)
	{
		$query = $this->factory->getURI()->getQuery(true);
		
		if (empty($query))
		{
			return;
		}

		// Convert array keys to lowercase
		$query = array_change_key_case($query);

		// Convert array to registry object so we can access any level with dot notation.
        $queryReg = new Registry($query);

		return InputFilter::getInstance()->clean($queryReg->get(strtolower($key)));
	}
}