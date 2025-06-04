<?php

return [
    'name' => 'widget/switcher',

    'main' => 'YOOtheme\\Widgetkit\\Widget\\Widget',

    'config' => [
        'name' => 'switcher',
        'label' => 'Switcher',
        'core' => true,
        'icon' => 'plugins/widgets/switcher/widget.svg',
        'view' => 'plugins/widgets/switcher/views/widget.php',
        'item' => ['title', 'content', 'media'],
        'settings' => [
            'nav' => 'nav',
            'thumbnail_width' => '70',
            'thumbnail_height' => '70',
            'thumbnail_alt' => false,
            'position' => 'top',
            'alignment' => 'left',
            'width' => '1-4',
            'panel' => false,
            'animation' => 'none',
            'disable_swiping' => false,

            'media' => true,
            'image_width' => 'auto',
            'image_height' => 'auto',
            'media_align' => 'top',
            'media_width' => '1-2',
            'media_breakpoint' => 'm',
            'content_align' => true,
            'media_border' => 'none',
            'media_overlay' => 'icon',
            'overlay_animation' => 'fade',
            'media_animation' => 'scale-up',

            'title' => true,
            'content' => true,
            'social_buttons' => true,
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
                'switcher.edit',
                'plugins/widgets/switcher/views/edit.php',
                true
            );
        },
    ],
];
