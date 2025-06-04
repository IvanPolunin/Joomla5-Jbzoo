<?php

return [
    'name' => 'widget/grid-slider',

    'main' => 'YOOtheme\\Widgetkit\\Widget\\Widget',

    'config' => [
        'name' => 'grid-slider',
        'label' => 'Grid Slider',
        'core' => true,
        'icon' => 'plugins/widgets/grid-slider/widget.svg',
        'view' => 'plugins/widgets/grid-slider/views/widget.php',
        'item' => ['title', 'content', 'media'],
        'settings' => [
            'grid' => 'default',
            'parallax' => false,
            'parallax_translate' => '',
            'gutter' => 'default',
            'filter' => 'none',
            'filter_tags' => [],
            'filter_align' => 'left',
            'filter_all' => true,
            'columns' => '1',
            'columns_small' => '0',
            'columns_medium' => '0',
            'columns_large' => '0',
            'columns_xlarge' => '0',
            'panel' => 'blank',
            'animation' => 'none',

            'image_width' => 'auto',
            'image_height' => 'auto',
            'media_align' => 'teaser',
            'media_width' => '1-2',
            'media_breakpoint' => 'm',
            'content_align' => true,

            'nav' => 'dotnav',
            'nav_overlay' => true,
            'nav_align' => 'center',
            'thumbnail_width' => '70',
            'thumbnail_height' => '70',
            'slidenav' => 'default',
            'nav_contrast' => true,
            'slide_animation' => 'fade',
            'autoplay' => false,
            'interval' => '7000',
            'autoplay_pause' => true,
            'kenburns' => false,
            'ratio' => '',
            'min_height' => '',

            'title' => true,
            'content' => true,
            'title_size' => 'h3',
            'title_element' => 'h3',
            'text_align' => 'left',
            'link' => true,
            'link_style' => 'button',
            'link_text' => 'Read more',
            'badge' => true,
            'badge_style' => 'badge',
            'badge_position' => 'panel',

            'link_target' => false,
            'class' => '',
        ],
    ],

    'events' => [
        'init.admin' => function ($event, $app) {
            $app['angular']->addTemplate(
                'grid-slider.edit',
                'plugins/widgets/grid-slider/views/edit.php',
                true
            );
        },
    ],
];
