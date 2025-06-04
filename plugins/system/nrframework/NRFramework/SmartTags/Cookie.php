<?php

/**
 * @author          Tassos.gr
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\SmartTags;

defined('_JEXEC') or die('Restricted access');

class Cookie extends SmartTag
{
	/**
     * This is a Pro-only feature
     *
     * @var boolean
     */
    public $proOnly = true;

	/**
	 * Returns the value of a cookie as stored in the visitor’s browser. 
	 * 
	 * @param   string  $key
	 * 
	 * @return  string
	 */
	public function fetchValue($key)
	{
		return $this->factory->getCookie($key);
	}
}