<?php
/**
 * @package     JCE
 * @subpackage  Editors.Jce
 *
 * @copyright   Copyright (C) 2005 - 2023 Open Source Matters, Inc. All rights reserved.
 * @copyright   Copyright (c) 2009-2024 Ryan Demmer. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\JcePro\Extension;

require_once JPATH_PLUGINS . '/system/jcepro/editor/includes/constants.php';

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Plugin\System\JcePro\PluginTraits\CustomQueryTrait;
use Joomla\Plugin\System\JcePro\PluginTraits\DispatchTrait;
use Joomla\Plugin\System\JcePro\PluginTraits\EditorTrait;
use Joomla\Plugin\System\JcePro\PluginTraits\FormTrait;
use Joomla\Plugin\System\JcePro\PluginTraits\MediaFieldTrait;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * JCE WYSIWYG Editor Plugin.
 *
 * @since 1.5
 */
final class JcePro extends CMSPlugin
{
    use FormTrait;
    use EditorTrait;
    use DispatchTrait;
    use MediaFieldTrait;
    use CustomQueryTrait;

    /**
     * Affects constructor behavior. If true, language files will be loaded automatically.
     *
     * @var    boolean
     */
    protected $autoloadLanguage = true;
}