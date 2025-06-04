<?php
/**
 * @package         Modules Anywhere
 * @version         8.1.2
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            https://regularlabs.com
 * @copyright       Copyright © 2024 Regular Labs All Rights Reserved
 * @license         GNU General Public License version 2 or later
 */

use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\EditorButtonPlugin as RL_EditorButtonPlugin;
use RegularLabs\Library\Extension as RL_Extension;

defined('_JEXEC') or die;

if ( ! is_file(JPATH_LIBRARIES . '/regularlabs/regularlabs.xml')
    || ! class_exists('RegularLabs\Library\Parameters')
    || ! class_exists('RegularLabs\Library\DownloadKey')
    || ! class_exists('RegularLabs\Library\EditorButtonPlugin')
)
{
    return;
}

if ( ! RL_Document::isJoomlaVersion(4))
{
    RL_Extension::disable('modulesanywhere', 'plugin', 'editors-xtd');

    return;
}

if (true)
{
    class PlgButtonModulesAnywhere extends RL_EditorButtonPlugin
    {
        protected $button_icon = '<svg viewBox="0 0 24 24" style="fill:none;" width="24" height="24" fill="none" stroke="currentColor">'
        . '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14v6m-3-3h6M6 10h2a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v2a2 2 0 002 2zm10 0h2a2 2 0 002-2V6a2 2 0 00-2-2h-2a2 2 0 00-2 2v2a2 2 0 002 2zM6 20h2a2 2 0 002-2v-2a2 2 0 00-2-2H6a2 2 0 00-2 2v2a2 2 0 002 2z" />'
        . '</svg>';

        protected function loadScripts()
        {
            RL_Document::script('modulesanywhere.button');
        }
    }
}
