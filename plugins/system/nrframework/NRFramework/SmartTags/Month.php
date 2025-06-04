<?php

/**
 * @author          Tassos.gr
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\SmartTags;

defined('_JEXEC') or die('Restricted access');

class Month extends Date
{
    /**
     * Returns the numeric representation of the month without leading zeros. Eg: 10.
     * 
     * @return  string
     */
    public function getMonth()
    {
        return $this->date->format('n');
    }
}