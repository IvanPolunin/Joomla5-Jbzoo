<?php

/**
 *  @author          Tassos.gr <info@tassos.gr>
 *  @link            https://www.tassos.gr
 *  @copyright       Copyright © 2024 Tassos All Rights Reserved
 *  @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

namespace NRFramework\Conditions\Conditions;

defined('_JEXEC') or die;

class URL extends URLBase
{
    /**
     * Returns the assignment's value
     * 
     * @return  string  Current URL
     */
	public function value()
	{
		return $this->factory->getURL();
	}
}