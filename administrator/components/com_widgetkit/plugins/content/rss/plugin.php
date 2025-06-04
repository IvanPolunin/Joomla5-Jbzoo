<?php

return [
    'name' => 'content/rss',

    'main' => 'YOOtheme\\Widgetkit\\Content\\rss\\RSSType',

    'autoload' => [
        'YOOtheme\\Widgetkit\\Content\\rss\\' => 'src',
    ],

    'config' => [
        'name' => 'rssfeed',
        'label' => 'RSSFeed',
        'icon' => 'plugins/content/rss/content.svg',
        'item' => ['title', 'content', 'link', 'date'],
        'data' => [
            'limit' => 10,
            'src' => '',
        ],
    ],

    'items' => function ($items, $content, $app) {
        // determine api method and parameters
        $params = ['limit' => (int) $content['limit'], 'source' => $content['src']];
        $posts = $app['rss']->fetch($params, $content);

        foreach ($posts as $post) {
            $items->add($post);
        }
    },

    'events' => [
        'init.admin' => function ($event, $app) {
            $app['angular']->addTemplate('rssfeed.edit', 'plugins/content/rss/views/edit.php');
        },
    ],
];
