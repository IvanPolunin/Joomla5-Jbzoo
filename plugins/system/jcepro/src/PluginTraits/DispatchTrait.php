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
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use Joomla\CMS\Component\ComponentHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Handles the onDisplay event for the JCE editor.
 *
 * @since  2.9.70
 */
trait DispatchTrait
{
    public function onAfterDispatch()
    {
        $app = Factory::getApplication();

        // only in "site"
        if ($app->getClientId() !== 0) {
            return;
        }

        $document = Factory::getDocument();

        // must be an html doctype
        if ($document->getType() !== 'html') {
            return true;
        }

        $editor = PluginHelper::getPlugin('system', 'jce');

        $editorParams = new Registry($editor->params);

        // only if enabled
        if ((int) $editorParams->get('column_styles', 1)) {
            $hash = md5_file(JPATH_SITE . '/media/plg_system_jcepro/site/css/content.min.css');
            $document->addStyleSheet(Uri::root(true) . '/media/plg_system_jcepro/site/css/content.min.css?' . $hash);
        }
    }
}
