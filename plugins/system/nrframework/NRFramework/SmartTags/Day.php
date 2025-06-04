<?php

/**
 * @author          Tassos.gr
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\SmartTags;

defined('_JEXEC') or die('Restricted access');

class Day extends Date
{
    /**
     * Returns the numeric representation of a day of the month without leading zeros. Eg: 22.
     * 
     * @return  string
     */
    public function getDay()
    {
        return $this->date->format('j');
    }
}