<?php
/**
 * @package   Smart Search - ZOO
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

defined('JPATH_BASE') or die;

if (version_compare(JVERSION, '4.0', '<')) {
    require_once(__DIR__.'/plugin.joomla3.php');
} else {
    require_once(__DIR__.'/plugin.php');
}
