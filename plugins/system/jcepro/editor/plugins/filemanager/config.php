<?php
/**
 * @package     JCE
 * @subpackage  Editor
 *
 * @copyright   Copyright (c) 2009-2024 Ryan Demmer. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

 defined('JPATH_PLATFORM') or die;

class WFFilemanagerPluginConfig
{
    public static function getConfig(&$settings)
    {
        $wf = WFApplication::getInstance();
        
        require_once __DIR__ . '/filemanager.php';

        $plugin = new WFFileManagerPlugin();

        $config = array();

        if ($plugin->getParam('inline_upload', 1) && $plugin->getParam('upload', 1)) {
            $config['upload'] = array(
                'max_size'  => $plugin->getParam('max_size', 1024),
                'filetypes' => $plugin->getFileTypes(),
                'inline' => true
            );
        }

        $allow_iframes = (int) $wf->getParam('media.iframes', 0);

        // allow all if not specified or media plugin is not enabled
        if (empty($settings['media_valid_elements'])) {
            $settings['media_valid_elements'] = array();
        }

        // if iframes is explicitly disabled, allow for local and supported media only
        if (!$allow_iframes) {
            $settings['iframes_allow_local'] = true;
            $settings['iframes_allow_supported'] = true;

            if (empty($settings['iframes_supported_media_custom'])) {
                $settings['iframes_supported_media_custom'] = array();
            }

            $supported_media = array(
                'https://docs.google.com/viewer',
                'https://view.officeapps.live.com/op/view.aspx'
            );

            $settings['iframes_supported_media_custom'] = array_merge($settings['iframes_supported_media_custom'], $supported_media);

            $settings['media_valid_elements'] = array_merge($settings['media_valid_elements'], array('iframe'));
        }

        $allow_object = (int) $wf->getParam('media.object', 0);

        // if object is explicitly disabled, allow for local only
        if (!$allow_object) {
            $settings['media_object_allow_local'] = true;
            $settings['media_valid_elements'] = array_merge($settings['media_valid_elements'], array('object', 'param'));
        }

        $settings['filemanager'] = $config;
    }
}
