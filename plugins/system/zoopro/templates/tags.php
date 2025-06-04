<?php
/**
 * @package   System - ZOO YOOtheme Pro
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

use Joomla\CMS\Router\Route;

if ($args['show_link'] && $args['link_style']) {
    echo '<span class="uk-' . $args['link_style'] . '">';
}

echo implode($args['separator'], array_map(function ($tag) use ($args, $item) {

    if (empty($args['show_link'])) {
        return $tag;
    }

    $route = Route::_($item->app->route->tag($item->application_id, $tag));

    return "<a href=\"{$route}\">{$tag}</a>";

}, $tags));

if ($args['show_link'] && $args['link_style']) {
    echo '</span>';
}
