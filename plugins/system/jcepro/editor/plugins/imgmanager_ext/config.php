<?php
/**
 * @package     JCE
 * @subpackage  Editor
 *
 * @copyright   Copyright (c) 2009-2024 Ryan Demmer. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

class WFImgManagerExtPluginConfig
{
    public static function getConfig(&$settings)
    {
        require_once __DIR__ . '/imgmanager_ext.php';

        $plugin = new WFImgManagerExtPlugin();

        $config = $plugin->getImageProperties();

        $config['filetypes'] = $plugin->getFileTypes();

        if ($plugin->getParam('inline_upload', 1) && $plugin->getParam('upload', 1)) {

            $config['upload'] = array(
                'max_size' => $plugin->getParam('max_size', 1024),
                'filetypes' => $plugin->getFileTypes(),
                'inline' => true,
            );
        }

        $config['always_include_dimensions'] = (bool) $plugin->getParam('always_include_dimensions', 1);

        $settings['imgmanager_ext'] = $config;
    }
}
