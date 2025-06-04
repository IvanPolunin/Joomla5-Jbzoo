<?php

/**
 * @copyright   Copyright (C) 2015 - 2024 Ryan Demmer. All rights reserved
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved
 * @license     GNU General Public License version 2 or later
 */

defined('JPATH_BASE') or die;

require_once __DIR__ . '/editor/includes/constants.php';

JLoader::registerNamespace('Joomla\\Plugin\\System\\JcePro', JPATH_PLUGINS . '/system/jcepro/src', false, false, 'psr4');

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Plugin\System\JcePro\PluginTraits\CustomQueryTrait;
use Joomla\Plugin\System\JcePro\PluginTraits\DispatchTrait;
use Joomla\Plugin\System\JcePro\PluginTraits\EditorTrait;
use Joomla\Plugin\System\JcePro\PluginTraits\FormTrait;
use Joomla\Plugin\System\JcePro\PluginTraits\MediaFieldTrait;

/**
 * JCE Pro
 *
 * @since       2.9.70
 */
class PlgSystemJcePro extends CMSPlugin
{
    use FormTrait;
    use MediaFieldTrait;
    use CustomQueryTrait;
    use EditorTrait;
    use DispatchTrait;

    /**
     * Affects constructor behavior. If true, language files will be loaded automatically.
     *
     * @var    boolean
     */
    protected $autoloadLanguage = true;
}