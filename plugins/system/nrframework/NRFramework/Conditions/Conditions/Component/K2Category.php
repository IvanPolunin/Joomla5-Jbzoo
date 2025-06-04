<?php

/**
 * @author          Tassos.gr
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\Conditions\Conditions\Component;

defined('_JEXEC') or die;

class K2Category extends K2Base
{
    /**
     * Shortcode aliases for this Condition
     */
    public static $shortcode_aliases = ['k2_cats', 'k2category'];

    /**
     *  Pass check
     *
     *  @return bool
     */
    public function pass()
    {
	    return $this->passCategories('k2_categories', 'parent');
	}
}