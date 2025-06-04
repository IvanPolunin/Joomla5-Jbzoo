<?php

/**
 * @author          Tassos.gr
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\SmartTags;

defined('_JEXEC') or die('Restricted access');

class Time extends Date
{
    /**
     * Returns a 24-hour format of an hour with leading zeros. Eg: 20:30.
     * 
     * @return  string
     */
    public function getTime()
    {
        return $this->date->format('H:i', true);
    }
}