<?php

/**
 * @author          Tassos.gr <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\Conditions\Conditions;

defined('_JEXEC') or die;

use NRFramework\Conditions\Condition;
use Joomla\CMS\Factory;

class Homepage extends Condition
{
    public static $shortcode_aliases = ['ishomepage'];

    public function value()
	{
		$menu = Factory::getApplication()->getMenu();
		$lang = Factory::getLanguage()->getTag();
		
        return ($menu->getActive() == $menu->getDefault($lang));
    }
}