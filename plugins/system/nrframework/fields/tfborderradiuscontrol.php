<?php

/**
 * @package         Advanced Custom Fields
 * @version         2.8.0 Pro
 * 
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

defined('_JEXEC') or die('Restricted access');

require_once 'tfdimensioncontrol.php';

class JFormFieldTFBorderRadiusControl extends JFormFieldTFDimensionControl
{
    protected $input_type = 'border_radius';

    /**
     * Set the dimensions.
     * 
     * @var  array
     */
    protected $dimensions = [
        'top_left' => 'NR_TOP_LEFT',
        'top_right' => 'NR_TOP_RIGHT',
        'bottom_right' => 'NR_BOTTOM_RIGHT',
        'bottom_left' => 'NR_BOTTOM_LEFT'
    ];
}