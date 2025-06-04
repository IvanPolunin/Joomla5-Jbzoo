<?php

use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Helper\RouteHelper;

$config = [
    'name' => 'content/joomla',

    'main' => 'YOOtheme\\Widgetkit\\Content\\Type',

    'config' => [
        'name' => 'joomla',
        'label' => 'Joomla',
        'icon' => 'plugins/content/joomla/content.svg',
        'item' => ['title', 'content', 'link', 'media'],
        'data' => [
            'number' => 5,
            'category' => '0',
            'subcategories' => '0',
            'featured' => '',
            'content' => 'intro',
            'image' => 'intro',
            'link' => '',
            'order_by' => 'ordering',
            'date' => 'publish_up',
            'author' => 'author',
            'categories' => 'categories',
        ],
    ],

    'items' => function ($items, $content, $app) {
        $args = [
            'items' => $content['number'] ?: 5,
            'catid' => $content['category'] ? (array) $content['category'] : 0,
            'subcategories' => $content['subcategories'] ?: 0,
            'featured' => $content['featured'] ? 'only' : '',
            'order' => $content['order_by'] ?: 'ordering',
        ];

        foreach ($app['joomla.article']->get($args) as $item) {
            $urls = json_decode($item->urls, true);
            $images = json_decode($item->images);

            $data = [
                'title' => $item->title,
                'media' => $images
                    ? ($content['image'] == 'intro'
                        ? $images->image_intro
                        : $images->image_fulltext)
                    : '',
                'media2' => $images
                    ? ($content['image'] == 'full'
                        ? $images->image_intro
                        : $images->image_fulltext)
                    : '',
                'content' => $app['filter']->apply(
                    $content['content'] == 'intro'
                        ? $item->introtext
                        : $item->introtext . $item->fulltext,
                    'content'
                ),
                'link' => html_entity_decode($app['joomla.article']->getUrl($item)),
                'tags' => [],
                'author' => $content['author'] ? $item->author : '',

                'categories' => $content['categories']
                    ? [
                        $item->category_title => Route::_(
                            RouteHelper::getCategoryRoute($item->catid)
                        ),
                    ]
                    : '',
            ];

            if (!empty($content['date'])) {
                $data['date'] = $content['date'] == 'created' ? $item->created : $item->publish_up;
            }

            if ($content['link'] != '' and $urls and !empty($urls["url{$content['link']}"])) {
                $data['link'] = html_entity_decode($urls["url{$content['link']}"]);
            }

            // ignore the joomla setting show tags for the filter function
            if (!isset($item->tags)) {
                $item->tags = new TagsHelper();
            }

            foreach ($item->tags->getItemTags('com_content.article', $item->id) as $tag) {
                $data['tags'][] = $tag->title;
            }

            $items->add($data);
        }
    },

    'events' => [
        'init.admin' => function ($event, $app) {
            $app['angular']->addTemplate('joomla.edit', 'plugins/content/joomla/views/edit.php');
        },
    ],
];

return defined('_JEXEC') ? $config : false;
