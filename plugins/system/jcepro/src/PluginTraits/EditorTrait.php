<?php
/**
 * @package     JCE
 * @subpackage  Editors.Jce
 *
 * @copyright   Copyright (C) 2005 - 2023 Open Source Matters, Inc. All rights reserved.
 * @copyright   Copyright (c) 2009-2024 Ryan Demmer. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\JcePro\PluginTraits;

use Joomla\CMS\Editor\Editor;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Filesystem\Path;

use WFApplication;
use JLoader;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Handles the onDisplay event for the JCE editor.
 *
 * @since  2.9.70
 */
trait EditorTrait
{
    public function onBeforeWfEditorRender(&$settings)
    {
        $wf = WFApplication::getInstance();

        // no profile set
        if (empty($settings)) {
            return;
        }

        if (isset($settings['readonly'])) {
            return;
        }
        
        // Set up Editor Toggle
        $settings['toggle'] = $wf->getParam('editor.toggle', 0);
        $settings['toggle_label'] = htmlspecialchars($wf->getParam('editor.toggle_label', ''));
        $settings['toggle_state'] = $wf->getParam('editor.toggle_state', 1);

        // Remove Branding
        $settings['plugins'] = implode(',', array_filter(explode(',', $settings['plugins']), static fn($name) => $name !== 'branding'));
    }

    public function onWfContentPreview($context, &$article, &$params, $page)
    {
        $article->text = '<style type="text/css">@import url("' . Uri::root(true) . '/media/plg_system_jcepro/site/css/content.min.css");</style>' . $article->text;
    }

    public function onWfApplicationInit()
    {        
        JLoader::register('WFMediaManager', WF_EDITOR_PRO_LIBRARIES . '/classes/manager.php', true);
        JLoader::register('WFImage', WF_EDITOR_PRO_LIBRARIES . '/classes/image/image.php');
    }

    /**
     * Event called before a plugin is executed via the controller
     *
     * @param [string] $plugin The plugin that is being executed
     * @param [string] $filepath The path to the plugin file
     * @return void
     */
    public function onWfPluginExecute($plugin, &$filepath)
    {
        $language = Factory::getLanguage();
        $language->load('com_jce_pro', JPATH_SITE);

        if (!$filepath) {
            $filepath = Path::find(
                array(
                    WF_EDITOR_PRO_PLUGINS . '/' . $plugin,
                ),
                $plugin . '.php'
            );
        }
    }

    public function onBeforeWfEditorPluginConfig($settings, &$items)
    {
        if (array_key_exists('external_plugins', $settings)) {
            $installed = (array) $settings['external_plugins'];

            foreach ($installed as $plugin => $path) {
                $file = Path::find(array(
                    // pro path
                    JPATH_PLUGINS . '/system/jcepro/editor/plugins/' . $plugin,
                ), 'config.php');

                if ($file) {
                    // add plugin name to array
                    $items[$plugin] = $file;
                }
            }
        }
    }

    public function onWfPluginsHelperGetPlugins(&$plugins)
    {
        // get pro json
        $path = JPATH_PLUGINS . '/system/jcepro';
        
        if (is_file($path . '/editor/pro.json')) {
            $pro = @file_get_contents($path . '/editor/pro.json');

            // decode to object
            if ($pro) {
                $data = json_decode($pro);

                if ($data) {
                    foreach ($data as $name => $attribs) {
                        // set default values
                        if (!isset($attribs->core)) {
                            $attribs->core = 0;
                        }

                        // update attributes
                        $attribs->type = 'plugin';
                        $attribs->path = $path . '/editor/plugins/' . $name;

                        if (!isset($attribs->url)) {
                            $attribs->url = 'media/plg_system_jcepro/editor/plugins/' . $name;
                        }

                        $attribs->manifest = $path . '/editor/plugins/' . $name . '/' . $name . '.xml';

                        $attribs->image = '';

                        if (!isset($attribs->class)) {
                            $attribs->class = '';
                        }

                        // compatability
                        $attribs->name = $name;
                        // pass to array
                        $plugins[$name] = $attribs;
                    }
                }
            }
        }
    }

    /**
     * Updates to the Template Manager when it is initialized
     *
     * @param WFPlugin $instance WFPlugin instance
     * @return void
     */
    public function onWfPluginInit($instance)
    {
        $app = Factory::getApplication();
        $user = Factory::getUser();

        // only in "admin"
        if ($app->getClientId() !== 1) {
            return;
        }

        // set mediatype values for Template Manager parameters
        if ($app->input->getCmd('plugin') == 'browser.templatemanager') {

            // restrict to admin with component manage access
            if (!$user->authorise('core.manage', 'com_jce')) {
                return;
            }

            // check for element and standalone should indicate mediafield
            if ($app->input->getVar('element') && $app->input->getInt('standalone')) {
                $mediatype = $app->input->getVar('mediatype');

                if (!$mediatype) {
                    return;
                }

                $accept = $instance->getParam('templatemanager.extensions', '');

                if ($accept) {
                    $instance->setFileTypes($accept);
                    $accept = $instance->getFileTypes();
                    $mediatype = implode(',', array_intersect(explode(',', $mediatype), $accept));
                }

                $instance->setFileTypes($mediatype);
            }
        }
    }
}
