<?php

/**
 * @author          Tassos.gr
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\SmartTags;

defined('_JEXEC') or die('Restricted access');

class Year extends Date
{
    /**
     * Returns a 4-digit numeric representation of the year. Eg: 2023.
     * 
     * @return  string
     */
    public function getYear()
    {
        return $this->date->format('Y');
    }
}