<?php

return [
    'name' => 'content/custom',

    'main' => 'YOOtheme\\Widgetkit\\Content\\Type',

    'config' => [
        'name' => 'custom',
        'label' => 'Custom',
        'icon' => 'assets/images/content-placeholder.svg',
        'item' => ['title', 'content', 'media'],
        'fields' => [
            'email' => [
                'type' => 'text',
                'label' => 'Email',
                'options' => [
                    'icon' => 'mail',
                    'attributes' => ['placeholder' => 'your@email.com'],
                ],
            ],
            'facebook' => [
                'type' => 'text',
                'label' => 'Facebook',
                'options' => ['icon' => 'facebook', 'attributes' => ['placeholder' => 'http://']],
            ],
            'badge' => [
                'type' => 'text',
                'label' => 'Badge',
                'options' => ['icon' => 'bookmark', 'attributes' => ['placeholder' => '']],
            ],
            'location' => ['type' => 'location', 'label' => 'Location'],
            'tags' => ['type' => 'tags', 'label' => 'Tags'],
            'media2' => ['type' => 'media', 'label' => 'Media 2'],
            'twitter' => [
                'type' => 'text',
                'label' => 'Twitter',
                'options' => ['icon' => 'twitter', 'attributes' => ['placeholder' => 'http://']],
            ],
            'date' => ['type' => 'date', 'label' => 'Date'],
        ],
        'data' => [
            'items' => [],
            'random' => 0,
            'parse_shortcodes' => 1,
        ],
    ],

    'items' => function ($items, $content, $app) {
        if (is_array($content['items'])) {
            if ($content['random']) {
                $newitems = $content['items'];
                shuffle($newitems);
                $content['items'] = $newitems;
            }

            if (!isset($content['parse_shortcodes'])) {
                $content['parse_shortcodes'] = 1;
            }

            foreach ($content['items'] as $data) {
                if (isset($data['content']) && $content['parse_shortcodes']) {
                    $data['content'] = $app['filter']->apply($data['content'], 'content');
                }
                $items->add($data);
            }
        }
    },

    'events' => [
        'init.admin' => function ($event, $app) {
            if ($app['config']->get('system_editor')) {
                $app['scripts']->add(
                    'editor',
                    'WK_SYSTEM_EDITOR = "' . $app['config']->get('system_editor') . '";',
                    [],
                    'string'
                );
            }

            $app['scripts']->add(
                'widgetkit-custom-controller',
                'plugins/content/custom/assets/controller.js'
            );
            $app['angular']->addTemplate(
                'custom.edit',
                'plugins/content/custom/views/edit.php',
                true
            );
        },
    ],
];
