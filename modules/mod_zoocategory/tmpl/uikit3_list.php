<?php
/**
 * @package   ZOO Category
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

echo $zoo->categorymodule->render($category, $params, 0, false, 'class="uk-list uk-list-divider"', true);
