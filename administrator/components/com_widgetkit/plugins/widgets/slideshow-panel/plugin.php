<?php

return [
    'name' => 'widget/slideshow-panel',

    'main' => 'YOOtheme\\Widgetkit\\Widget\\Widget',

    'config' => [
        'name' => 'slideshow-panel',
        'label' => 'Slideshow Panel',
        'core' => true,
        'icon' => 'plugins/widgets/slideshow-panel/widget.svg',
        'view' => 'plugins/widgets/slideshow-panel/views/widget.php',
        'item' => ['title', 'content', 'media'],
        'settings' => [
            'panel' => 'blank',
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
            'media_align' => 'top',
            'media_width' => '1-2',
            'media_breakpoint' => 'm',
            'content_align' => true,

            'title' => true,
            'content' => true,
            'title_size' => 'h3',
            'title_element' => 'h3',
            'content_size' => '',
            'text_align' => 'left',
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
                'slideshow-panel.edit',
                'plugins/widgets/slideshow-panel/views/edit.php',
                true
            );
        },
    ],
];
