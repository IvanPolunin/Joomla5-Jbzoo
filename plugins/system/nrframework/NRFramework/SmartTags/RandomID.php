<?php

/**
 * @author          Tassos.gr
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\SmartTags;

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Crypt\Crypt;

class RandomID extends SmartTag
{
    /**
     * Returns an 8-character hexadecimal random ID. Example: 03bc431d0d605ce4
     * 
     * @return  string
     */
    public function getRandomID()
    {
        return bin2hex(Crypt::genRandomBytes(8));
    }
}