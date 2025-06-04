<?php

/**
 * @author          Tassos.gr
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\Conditions\Conditions\Component;

defined('_JEXEC') or die;

class K2Pagetype extends K2Base
{
    /**
     * Shortcode aliases for this Condition
     */
    public static $shortcode_aliases = ['k2_pagetypes', 'k2pagetype'];

    /**
     *  Pass check for K2 page types
     *
     *  @return bool
     */
    public function pass()
    {
        if (empty($this->selection) || !$this->passContext())
        {
            return false;
        }

        return parent::pass();
    }

    /**
     *  Returns the assignment's value
     * 
     *  @return string Pagetype
     */
	public function value()
	{
		return $this->getPageType();
    }
}