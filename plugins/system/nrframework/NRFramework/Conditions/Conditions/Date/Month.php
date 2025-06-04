<?php

/**
 * @author          Tassos.gr <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\Conditions\Conditions\Date;

defined('_JEXEC') or die;

class Month extends DateBase
{
    /**
     * Returns the assignment's value
     * 
     * This returns the month in non-localized strings.
     * 
     * @return string Name of the current month
     */
	public function value()
	{
		return [
            $this->date->format('F', true, false),
            $this->date->format('M', true, false),
            $this->date->format('n', true, false),
            $this->date->format('m', true, false),
        ];
	}
}