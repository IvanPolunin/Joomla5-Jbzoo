<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            http://www.tassos.gr
 * @copyright       Copyright © 2021 Tassos Marinos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\TextField;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\HTML\HTMLHelper;

class JFormFieldTFGlobalDevicesSelector extends TextField
{
    /**
     *  Method to render the input field
     *
     *  @return  string  
     */
    public function getInput()
    {
        $this->assets();
        
        $payload = [
            'devices' => \NRFramework\Helpers\Responsive::getBreakpoints()
        ];
        
        $layout = new FileLayout('global_devices_selector', JPATH_PLUGINS . '/system/nrframework/layouts');
        return $layout->render($payload);
    }

    /**
     * Load field assets.
     * 
     * @return  void
     */
    private function assets()
    {
        HTMLHelper::stylesheet('plg_system_nrframework/global_devices_selector.css', ['relative' => true, 'version' => 'auto']);
        HTMLHelper::script('plg_system_nrframework/global_devices_selector.js', ['relative' => true, 'version' => 'auto']);
    }
}