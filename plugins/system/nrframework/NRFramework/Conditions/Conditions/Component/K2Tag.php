<?php

/**
 * @author          Tassos.gr
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\Conditions\Conditions\Component;

defined('_JEXEC') or die;

class K2Tag extends K2Base
{
    /**
     * Shortcode aliases for this Condition
     */
    public static $shortcode_aliases = ['k2_tags', 'k2tag'];
    
    /**
     *  Pass check for K2 Tags
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
     *  @return array K2 item tags
     */
	public function value()
	{
		return $this->getK2tags();
	}
}