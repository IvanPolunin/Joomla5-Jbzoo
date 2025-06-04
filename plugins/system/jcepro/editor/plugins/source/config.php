<?php
/**
 * @package     JCE
 * @subpackage  Editor
 *
 * @copyright   Copyright (c) 2009-2024 Ryan Demmer. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

class WFSourcePluginConfig
{
    public static function getConfig(&$settings)
    {
        $wf = WFApplication::getInstance();

        $settings['source_highlight'] = $wf->getParam('source.highlight', 1, 1, 'boolean');
        $settings['source_linenumbers'] = $wf->getParam('source.numbers', 1, 1, 'boolean');
        $settings['source_wrap'] = $wf->getParam('source.wrap', 1, 1, 'boolean');
        $settings['source_format'] = $wf->getParam('source.format', 1, 1, 'boolean');
        $settings['source_tag_closing'] = $wf->getParam('source.tag_closing', 1, 1, 'boolean');
        //$settings['source_selection_match'] = $wf->getParam('source.selection_match', 1, 1, 'boolean');

        $font_size = $wf->getParam('source.font_size', '', '');

        if ($font_size) {
            $font_size = preg_replace('/\D/', '', $font_size);
            $settings['source_font_size'] = (int) $font_size;
        }

        $theme = $wf->getParam('source.theme', 'codemirror');

        // legacy themes that don't have an equivalent in CodeMirror 6
        $legacyThemes = array('custom', 'ambiance', 'blackboard', 'eclipse', 'lesser-dark', 'monokai', 'textmate');

        if (in_array($theme, $legacyThemes)) {
            $theme = 'codemirror';
        }

        $settings['source_theme'] = $theme;
        $settings['source_validate_content'] = $wf->getParam('source.validate_content', 1, 1, 'boolean');
    }
}