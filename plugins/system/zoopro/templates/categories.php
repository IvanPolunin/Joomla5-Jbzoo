<?php
/**
 * @package   System - ZOO YOOtheme Pro
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

if ($args['show_link'] && $args['link_style']) {
    echo '<span class="uk-' . $args['link_style'] . '">';
}

echo implode($args['separator'], array_map(function ($category) use ($args) {

    if (empty($args['show_link'])) {
        return $category->name;
    }

    $route = $category->app->route->category($category);

    return "<a href=\"{$route}\">{$category->name}</a>";

}, $categories));

if ($args['show_link'] && $args['link_style']) {
    echo '</span>';
}
