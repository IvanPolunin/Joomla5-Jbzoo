<?php
/**
 * @package     JCE
 * @subpackage  Editor
 *
 * @copyright   Copyright (c) 2009-2024 Ryan Demmer. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

class WFIframePluginConfig
{
    public static function getConfig(&$settings)
    {
        $wf = WFApplication::getInstance();

        $elements = array('iframe');

        // remove iframe
        $settings['invalid_elements'] = array_diff($settings['invalid_elements'], $elements);

        // add the media plugin if needed
        if (!in_array('media', $settings['plugins'])) {
            $settings['plugins'][] = 'media';

            if (empty($settings['media_valid_elements'])) {
                $settings['media_valid_elements'] = $elements;
            }

            $settings['media_valid_elements'] = array_intersect($settings['media_valid_elements'], $elements);
        }
    }
}
