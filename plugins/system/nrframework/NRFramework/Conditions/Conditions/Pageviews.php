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

class Pageviews extends Condition
{
    /**
     * Shortcode aliases for this Condition
     */
    public static $shortcode_aliases = ['user.pageviews'];
    
    /**
     *  Returns the assignment's value
     * 
     *  @return int Number of page visits
     */
    public function value()
    {
        return $this->factory->getSession()->get('session.counter', 0);
    }
}