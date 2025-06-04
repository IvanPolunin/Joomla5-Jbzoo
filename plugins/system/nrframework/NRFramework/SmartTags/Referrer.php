<?php

/**
 * @author          Tassos.gr
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\SmartTags;

defined('_JEXEC') or die('Restricted access');

class Referrer extends SmartTag
{
    /**
     * Returns the URL of the webpage where a person clicked a link that sent them to your site.
     * 
     * @return  string
     */
    public function getReferrer()
    {
        return $this->app->input->server->get('HTTP_REFERER', '', 'RAW');
    }
}