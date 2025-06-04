<?php

/**
 * @author          Tassos.gr
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\Conditions\Conditions\Component;

defined('_JEXEC') or die;

class RSBlogCategory extends RSBlogBase
{
    /**
     * Shortcode aliases for this Condition
     */
    public static $shortcode_aliases = ['rsblog.category'];

    /**
     *  Pass check
     *
     *  @return bool
     */
    public function pass()
    {
        return $this->passCategories('rsblog_categories', 'parent_id');
	}

}