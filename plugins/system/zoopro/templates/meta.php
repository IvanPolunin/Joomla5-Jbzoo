<?php
/**
 * @package   System - ZOO YOOtheme Pro
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

namespace YOOtheme;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use YOOtheme\Builder\Joomla\Source\UserHelper;

$author = $published = $category = '';

// Author
if ($args['show_author']) {

    $author = $item->created_by_alias;

    if (!$author) {
        $user = app(Zoo::class)->user->get($item->created_by);

        if ($user) {
            $author = $user->name;
        }

    }

    $item->contact_link = UserHelper::getContactLink($item->created_by);

    if (!empty($item->contact_link)) {
        $author = HTMLHelper::_('link', $item->contact_link, $author);
    }
}

// Publish date
if ($args['show_publish_date']) {
    $published = HTMLHelper::_('date', $item->publish_up, $args['date_format'] ?: Text::_('DATE_FORMAT_LC3'));
    $published = '<time datetime="' . HTMLHelper::_('date', $item->publish_up, 'c') . "\">{$published}</time>";
}

// Category
if ($args['show_category']) {

    $categories = [];

    foreach ($item->getRelatedCategories(true) as $category) {
        $categories[] = HTMLHelper::_('link', $category->app->route->category($category), $category->name);
    }

    $category = implode(', ', $categories);
}

if (!$published && !$author && !$category) {
    return;
}

if ($args['link_style']) {
    echo "<span class=\"uk-{$args['link_style']}\">";
}

switch ($args['format']) {

    case 'list':

        echo implode(" {$args['separator']} ", array_filter([$published, $author, $category]));
        break;

    default: // sentence

        if ($author && $published) {
            Text::printf('TPL_YOOTHEME_META_AUTHOR_DATE', $author, $published);
        } elseif ($author) {
            Text::printf('TPL_YOOTHEME_META_AUTHOR', $author);
        } elseif ($published) {
            Text::printf('TPL_YOOTHEME_META_DATE', $published);
        }

        if ($category) {
            echo ' ';
            Text::printf('TPL_YOOTHEME_META_CATEGORY', $category);
        }
}

if ($args['link_style']) {
    echo '</span>';
}
