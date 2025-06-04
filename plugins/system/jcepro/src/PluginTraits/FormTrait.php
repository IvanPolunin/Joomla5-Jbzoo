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
use Joomla\CMS\Form\Form;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Handles the onDisplay event for the JCE editor.
 *
 * @since  2.9.70
 */
trait FormTrait
{
    public $processed = false;

    /**
     * Update the category id from the form data
     *
     * @param mixed $data The associated data for the form
     *
     * @return void
     */
    protected function updateCategoryId($data = [])
    {
        $app = Factory::getApplication();

        // if the catid is not set, and we have the value in the data array, set a namespaced query value for Custom Query processing. This must happen before any other field processing
        if ($app->input->getInt('catid', 0) === 0) {
            $catid = 0;

            if (is_array($data) && isset($data['catid'])) {
                $catid = (int) $data['catid'];
            }

            if (is_object($data) && isset($data->catid)) {
                $catid = (int) $data->catid;
            }

            $app->input->set('wf_catid', $catid);
        }
    }

    /**
     * Proxy function for PlgContentJce::onContentPrepareForm
     *
     * @param Form $form The form to be altered
     * @param mixed $data The associated data for the form
     * 
     * @return void
     */
    public function onWfContentPrepareForm(Form $form, $data = [])
    {
        // process media fields
        $this->processMediaFieldForm($form, $data);
    }

    /**
     * adds additional fields to the user editing form.
     *
     * @param Form $form The form to be altered
     * @param mixed $data The associated data for the form
     *
     * @return bool
     *
     * @since   2.6.64
     */
    public function onContentPrepareForm(Form $form, $data = [])
    {
        $app = Factory::getApplication();

        $this->updateCategoryId($data);

        // process media fields
        $this->processMediaFieldForm($form, $data);

        // the rest of the form processing is only for admin
        if (!$app->isClient('administrator')) {
            return true;
        }

        $formName = $form->getName();

        // check if the form name is any of the supported forms, and load the language file
        if (in_array($formName, ['com_jce.profile', 'com_jce.config', 'com_config.component'])) {
            // don't process the form if it's already been processed
            if ($this->processed) {
                return;
            }

            $app->getLanguage()->load('com_jce_pro', JPATH_SITE);
        }

        // profile form data
        if ($form->getName() == 'com_jce.profile') {
            // setup manifest - uses "setup" key in params field
            $setup = JPATH_PLUGINS . '/system/jcepro/forms/setup.xml';

            if (is_file($setup)) {
                if ($setup_xml = simplexml_load_file($setup)) {
                    $form->setField($setup_xml, 'config');
                }
            }

            // editor manifest
            $editor = JPATH_PLUGINS . '/system/jcepro/forms/editor.xml';

            if (is_file($editor)) {
                if ($editor_xml = simplexml_load_file($editor)) {
                    $form->setField($editor_xml, 'config');
                }
            }

            // set processed flag
            $this->processed = true;
        }

        if ($form->getName() === 'com_jce.profile.browser') {
            // file browser manifest
            $browser = JPATH_PLUGINS . '/system/jcepro/forms/browser.xml';

            if (is_file($browser)) {
                if ($browser_xml = simplexml_load_file($browser)) {
                    $form->setField($browser_xml);
                }
            }

            // set processed flag
            $this->processed = true;
        }

        // global config form data
        if ($form->getName() == 'com_jce.config') {
            // config manifest
            $config = JPATH_PLUGINS . '/system/jcepro/forms/config.xml';

            if (is_file($config)) {
                if ($config_xml = simplexml_load_file($config)) {
                    $form->setField($config_xml);
                }
            }

            // set processed flag
            $this->processed = true;
        }

        // component options
        if ($form->getName() == 'com_config.component' && $app->input->getCmd('component') == 'com_jce') {
            $options = JPATH_PLUGINS . '/system/jcepro/forms/options.xml';

            if (is_file($options)) {
                if ($options_xml = simplexml_load_file($options)) {
                    $form->setField($options_xml);
                }
            }

            // set processed flag
            $this->processed = true;
        }
    }
}
