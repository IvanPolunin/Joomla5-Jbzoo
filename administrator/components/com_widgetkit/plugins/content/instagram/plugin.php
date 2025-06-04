<?php

return [
    'name' => 'content/instagram',

    'main' => 'YOOtheme\\Widgetkit\\Content\\instagram\\InstagramType',

    'autoload' => [
        'YOOtheme\\Widgetkit\\Content\\instagram\\' => 'src',
    ],

    'config' => [
        'name' => 'instagram',
        'label' => 'Instagram',
        'icon' => 'plugins/content/instagram/content.svg',
        'item' => ['title', 'content', 'media', 'link'],
        'data' => [
            'limit' => 10,
            'title' => 'username',
        ],
    ],

    'items' => function ($items, $content, $app) {
        // determine api method and parameters
        $params = [
            'limit' => (int) $content['limit'],
            'username' => $content['username'],
            'title' => $content['title'],
        ];
        $posts = $app['instagram']->fetch($params, $content);

        if (is_numeric($content['limit'])) {
            $posts = array_slice($posts, 0, $content['limit']);
        }

        foreach ($posts as $post) {
            $items->add($post);
        }
    },

    'events' => [
        'init.admin' => function ($event, $app) {
            //$app['scripts']->add('widgetkit-instagram-controller', 'plugins/content/Instagram/assets/controller.js');
            $app['angular']->addTemplate(
                'instagram.edit',
                'plugins/content/instagram/views/edit.php'
            );
        },
    ],
];
