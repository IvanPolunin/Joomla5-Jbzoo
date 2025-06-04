<?php

return [
    'name' => 'widget/popover',

    'main' => 'YOOtheme\\Widgetkit\\Widget\\Widget',

    'config' => [
        'name' => 'popover',
        'label' => 'Popover',
        'core' => true,
        'icon' => 'plugins/widgets/popover/widget.svg',
        'view' => 'plugins/widgets/popover/views/widget.php',
        'item' => ['title', 'content', 'media'],
        'fields' => [
            [
                'type' => 'text',
                'name' => 'top',
                'label' => 'Top (%)',
            ],
            [
                'type' => 'text',
                'name' => 'left',
                'label' => 'Left (%)',
            ],
        ],
        'settings' => [
            'width' => '',
            'image' => '',
            'image_hero_width' => 'auto',
            'image_hero_height' => 'auto',
            'position' => 'top-center',
            'mode' => 'hover',
            'toggle' => 'plus',
            'contrast' => true,
            'panel' => 'default',
            'panel_link' => false,

            'media' => true,
            'image_width' => 'auto',
            'image_height' => 'auto',
            'media_overlay' => 'icon',
            'overlay_animation' => 'fade',
            'media_animation' => 'scale-up',

            'title' => true,
            'content' => true,
            'title_size' => 'h3',
            'title_element' => 'h3',
            'text_align' => 'left',
            'link' => true,
            'link_style' => 'button',
            'link_text' => 'Read more',

            'link_target' => false,
            'class' => '',
        ],
    ],

    'events' => [
        'init.admin' => function ($event, $app) {
            $app['angular']->addTemplate(
                'popover.edit',
                'plugins/widgets/popover/views/edit.php',
                true
            );
        },
    ],
];
