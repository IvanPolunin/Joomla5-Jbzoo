<?php
/**
 * @package   System - ZOO YOOtheme Pro
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

$zoo = \App::getInstance('zoo');
$view = new \AppView(['name' => 'comments', 'base_path' => __DIR__]);

$view->set('app', $zoo);
$view->set('item', $item);
$view->set('attrs', $attrs);
$view->set('props', $props);

echo $zoo->comment->renderComments($view, $item);
