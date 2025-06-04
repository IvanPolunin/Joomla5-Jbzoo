<?php

namespace YOOtheme\Framework\Joomla;

use Joomla\CMS\Editor\Editor;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use YOOtheme\Framework\Application;

class EditorHelper
{
    public static function load(Application $app, $element)
    {
        $root = Uri::root();
        $editor = Editor::getInstance();
        $document = Factory::getApplication()->getDocument();
        $language = Factory::getApplication()->getLanguage();
        $language->load("plg_editors_{$element}");

        // skip visual editor
        if (in_array($element, ['none', 'codemirror'])) {
            return;
        }

        // current editor plugin
        $plugin = Table::getInstance('Extension');
        $plugin->load([
            'folder' => 'editors',
            'element' => $element,
        ]);

        // Prevent xtd editor buttons from adding assets to the current document
        if (version_compare(JVERSION, '4.0', '<')) {
            Factory::$document = clone $document;
        } else {
            $manager = $document->getWebAssetManager();

            // Add media select to allow Media (button)
            $manager->useScript('webcomponent.media-select');

            if (version_compare(JVERSION, '5.0', '>=')) {
                $manager->useScript('editors');
            }
        }

        // create editor config
        $config = [
            'id' => 'editor-xtd',
            'title' => isset($plugin->name) ? $language->_($plugin->name) : 'Editor',
            'iframe' => $app['url']->route('editor', ['format' => 'html', 'tmpl' => 'component']),
            'buttons' => static::getButtons($editor),
            'settings' => static::getSettings() + [
                'branding' => false,
                'content_css' => version_compare(JVERSION, '4.0', '<')
                    ? "{$root}templates/system/css/editor.css"
                    : "{$root}media/system/css/editor.min.css",
                'directionality' => $language->get('rtl') ? 'rtl' : 'ltr',
                'document_base_url' => $root,
                'entity_encoding' => 'raw',
                'insert_button_items' => '', // e.g. 'hr charmap',
                'plugins' => 'link autolink hr lists charmap paste',
                'toolbar1' =>
                    'formatselect bold italic bullist numlist blockquote alignleft aligncenter alignright link strikethrough hr pastetext removeformat charmap outdent indent insert',
            ],
        ];

        if (version_compare(JVERSION, '4.0', '<')) {
            // Recover document
            Factory::$document = $document;
        }

        return $config;
    }

    public static function getSettings()
    {
        $tinymce = PluginHelper::getPlugin('editors', 'tinymce');
        $params = $tinymce ? json_decode($tinymce->params, true) : [];

        if (!empty($params['newlines'])) {
            $settings = [
                'forced_root_block' => '',
                'force_p_newlines' => false,
                'force_br_newlines' => true,
            ];
        } else {
            $settings = [
                'forced_root_block' => 'p',
                'force_p_newlines' => true,
                'force_br_newlines' => false,
            ];
        }

        return $settings;
    }

    public static function renderEditor()
    {
        $type = Factory::getConfig()->get('editor');
        $editor = Editor::getInstance($type);
        $exclude = ['pagebreak', 'readmore', 'widgetkit'];

        // core.js needs to initialize Joomla.editors early
        HTMLHelper::_('behavior.core');

        ob_start();

        echo "<form>{$editor->display('content', '', '100%', '100%', '', '30', $exclude)}</form>";

        return ob_get_clean();
    }

    protected static function getButtons(Editor $editor): array
    {
        $buttons = $editor->getButtons('editor-xtd', ['pagebreak', 'readmore', 'widgetkit']);

        if (version_compare(JVERSION, '5.0', '>=')) {
            $buttons = array_map(
                fn($button) => [
                    'text' => $button->get('text'),
                    'link' => $button->get('link'),
                    'options' => $button->getOptions(),
                ],
                array_filter($buttons, fn($button) => $button->get('action') === 'modal'),
            );
        } else {
            $buttons = array_filter($buttons, fn($button) => !empty($button->modal));
        }

        return array_values($buttons);
    }
}
