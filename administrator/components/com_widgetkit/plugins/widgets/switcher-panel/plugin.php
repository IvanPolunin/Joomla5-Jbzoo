<?php

return [
    'name' => 'widget/switcher-panel',

    'main' => 'YOOtheme\\Widgetkit\\Widget\\Widget',

    'config' => [
        'name' => 'switcher-panel',
        'label' => 'Switcher Panel',
        'core' => true,
        'icon' => 'plugins/widgets/switcher-panel/widget.svg',
        'view' => 'plugins/widgets/switcher-panel/views/widget.php',
        'item' => ['title', 'content', 'media'],
        'settings' => [
            'panel' => 'blank',
            'image' => '',
            'image_hero_width' => 'auto',
            'image_hero_height' => 'auto',
            'image_min_height' => '200',

            'nav' => 'nav',
            'thumbnail_width' => '70',
            'thumbnail_height' => '70',
            'thumbnail_alt' => false,
            'alignment' => 'left',
            'disable_swiping' => false,
            'contrast' => true,

            'animation' => 'none',

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
                'switcher-panel.edit',
                'plugins/widgets/switcher-panel/views/edit.php',
                true
            );
        },
    ],
];
