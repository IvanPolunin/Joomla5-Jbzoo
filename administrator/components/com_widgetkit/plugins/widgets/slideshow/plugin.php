<?php

return [
    'name' => 'widget/slideshow',

    'main' => 'YOOtheme\\Widgetkit\\Widget\\Widget',

    'config' => [
        'name' => 'slideshow',
        'label' => 'Slideshow',
        'core' => true,
        'icon' => 'plugins/widgets/slideshow/widget.svg',
        'view' => 'plugins/widgets/slideshow/views/widget.php',
        'item' => ['title', 'content', 'media'],
        'settings' => [
            'nav' => 'dotnav',
            'nav_overlay' => true,
            'nav_align' => 'center',
            'thumbnail_width' => '70',
            'thumbnail_height' => '70',
            'thumbnail_alt' => false,
            'slidenav' => 'default',
            'nav_contrast' => true,
            'animation' => 'fade',
            'autoplay' => false,
            'interval' => '7000',
            'autoplay_pause' => true,
            'kenburns' => false,
            'kenburns_animation' => '',
            'height' => '',
            'ratio' => '',
            'min_height' => '300',

            'media' => true,
            'image_width' => 'auto',
            'image_height' => 'auto',
            'overlay' => 'none',
            'overlay_animation' => 'fade',
            'overlay_background' => true,
            'link_media' => false,

            'title' => true,
            'content' => true,
            'title_size' => 'h3',
            'title_element' => 'h3',
            'content_size' => '',
            'link' => true,
            'link_style' => 'button',
            'link_text' => 'Read more',
            'badge' => true,
            'badge_style' => 'badge',

            'link_target' => false,
            'class' => '',
        ],
    ],

    'events' => [
        'init.admin' => function ($event, $app) {
            $app['angular']->addTemplate(
                'slideshow.edit',
                'plugins/widgets/slideshow/views/edit.php',
                true
            );
        },
    ],
];
