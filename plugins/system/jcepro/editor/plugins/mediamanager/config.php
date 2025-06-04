<?php
/**
 * @package     JCE
 * @subpackage  Editor
 *
 * @copyright   Copyright (c) 2009-2024 Ryan Demmer. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;

class WFMediamanagerPluginConfig
{
    public static function getConfig(&$settings)
    {
        $wf = WFApplication::getInstance();

        require_once __DIR__ . '/mediamanager.php';

        $plugin = new WFMediaManagerPlugin();

        $config = array(
            'quickmedia' => array(),
        );

        // ensure the media plugin is added
        if (!in_array('media', $settings['plugins'])) {
            $settings['plugins'][] = 'media';
        }

        $allow_iframes = (int) $wf->getParam('media.iframes', 0);

        $supported_media = array();
        $restricted_media = array();

        if ((bool) $plugin->getParam('aggregator.youtube.enable', 1)) {
            $supported_media[] = 'youtube';
        } else {
            $config['quickmedia']['youtube'] = false;

            $restricted_media[] = 'youtube';
        }

        if ((bool) $plugin->getParam('aggregator.vimeo.enable', 1)) {
            $supported_media[] = 'vimeo';
        } else {
            $config['quickmedia']['vimeo'] = false;

            $restricted_media[] = 'vimeo';
        }

        if ((bool) $plugin->getParam('aggregator.dailymotion.enable', 1)) {
            $supported_media[] = 'dailymotion';
        } else {
            $restricted_media[] = 'dailymotion';
        }

        $custom_embed = Factory::getApplication()->triggerEvent('onWfGetCustomEmbedData');

        if (!empty($custom_embed)) {
            $config['custom_embed'] = array();

            foreach ($custom_embed as $item) {
                foreach ($item as $key => $values) {
                    $config['custom_embed'][$key] = $values;
                    $supported_media[] = isset($values['src']) ? $values['src'] : $key;
                }
            }
        }

        // get the list of filetypes supported
        $filetypes = array_values($plugin->getFileTypes());

        // only allow a limited set that are support by the <video> and <audio> tags
        $filetypes_set = array_intersect($filetypes, array('mp3', 'oga', 'm4a', 'mp4', 'm4v', 'ogg', 'webm', 'ogv'));

        if ($plugin->getParam('inline_upload', 1) && $plugin->getParam('upload', 1)) {
            $config['upload'] = array(
                'max_size' => $plugin->getParam('max_size', 1024),
                'filetypes' => array_values($filetypes_set),
                'inline' => true,
            );
        }

        // reset quickmedia if disabled
        if ($plugin->getParam('quickmedia', 1) == 0) {
            $config['quickmedia'] = false;
        }

        if ($plugin->getParam('basic_dialog', 0) == 1) {
            $config['basic_dialog'] = true;

            if ($plugin->getParam('basic_dialog_filebrowser', 1) == 1) {
                $config['basic_dialog_filebrowser'] = true;
                $config['filetypes'] = array_values($filetypes_set);
            }

            $config['attributes'] = $plugin->getDefaultAttributes();
        }

        $settings['mediamanager'] = $config;

        // Media config overrides

        $elements = array('video', 'audio', 'source', 'object', 'embed', 'param');

        if ($allow_iframes == 1) {
            $elements[] = 'iframe';
        }

        // iframes disabled, enable
        if ($allow_iframes == 0) {
            // we have iframes to support
            if (!empty($supported_media)) {
                // iframes are enabled via iframe plugin
                if (!isset($settings['media_valid_elements']) || !in_array('iframe', $settings['media_valid_elements'])) {
                    $elements[] = 'iframe';
                }

                // if iframes is not loaded, allow for supported media only
                if (!in_array('iframe', $settings['plugins'])) {
                    $allow_iframes = 3;
                }
            }
        }

        // iframes are set to local/supported only
        if ($allow_iframes > 1) {
            unset($settings['iframes_allow_local']);

            $settings['iframes_allow_supported'] = true;

            // ensure default array
            if (empty($settings['iframes_supported_media'])) {
                $settings['iframes_supported_media'] = array();
            }

            // add custom supported_media
            $settings['iframes_supported_media'] = array_merge($settings['iframes_supported_media'], $supported_media);

            // remove unsupported media
            $settings['iframes_supported_media'] = array_diff($settings['iframes_supported_media'], array_values($restricted_media));

            // remove duplicates
            $settings['iframes_supported_media'] = array_unique(array_filter($settings['iframes_supported_media']));

            // get values only
            $settings['iframes_supported_media'] = array_values($settings['iframes_supported_media']);

            $elements[] = 'iframe';
        }

        // check if media plugin enabled and has been configured, which would remove these tags from invalid_elements
        $isMediaEnabled = empty(array_intersect($settings['invalid_elements'], array('audio', 'video', 'source', 'embed', 'object', 'param')));

        // allow all elements
        $settings['invalid_elements'] = array_diff($settings['invalid_elements'], array('audio', 'video', 'source', 'embed', 'object', 'param', 'iframe'));

        // allow all if not specified or media plugin is not enabled
        if (empty($settings['media_valid_elements']) || !$isMediaEnabled) {
            $settings['media_valid_elements'] = $elements;
        }

        if ($isMediaEnabled) {
            $settings['media_valid_elements'] = array_values(array_intersect($settings['media_valid_elements'], $elements));
        }

        // ensure iframes is supported if required
        if (in_array('iframe', $elements) && !in_array('iframe', $settings['media_valid_elements'])) {
            $settings['media_valid_elements'][] = 'iframe';
        }
    }
}
