<?php
/**
 * @package   ZOO Category
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

use Joomla\CMS\Helper\ModuleHelper;

// no direct access
defined('_JEXEC') or die('Restricted access');

// load config
require_once(JPATH_ADMINISTRATOR.'/components/com_zoo/config.php');

// get app
$zoo = App::getInstance('zoo');

// load zoo frontend language file
$zoo->system->language->load('com_zoo');

// init vars
$path = dirname(__FILE__);

//register base path
$zoo->path->register($path, 'mod_zoocategory');

// register helpers
$zoo->path->register($path, 'helpers');
$zoo->loader->register('CategoryModuleHelper', 'helpers:helper.php');

if (!$application = $zoo->table->application->get($params->get('application', 0))) {
	return null;
}

// set one or multiple categories
$categories = $application->getCategoryTree(true, null, (bool) $params->get('add_count', false));
$categoryId = $params->get('category', 0);
$category = !empty($categories[$categoryId]) ? $categories[$categoryId] : null ;

if ($category && $category->hasChildren()) {
	include(ModuleHelper::getLayoutPath('mod_zoocategory', $params->get('theme', 'list')));
}
