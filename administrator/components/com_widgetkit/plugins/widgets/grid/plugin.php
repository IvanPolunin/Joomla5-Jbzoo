<?php

return [
    'name' => 'widget/grid',

    'main' => 'YOOtheme\\Widgetkit\\Widget\\Widget',

    'config' => [
        'name' => 'grid',
        'label' => 'Grid',
        'core' => true,
        'icon' => 'plugins/widgets/grid/widget.svg',
        'view' => 'plugins/widgets/grid/views/widget.php',
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
            'panel_link' => false,
            'animation' => 'none',

            'media' => true,
            'image_width' => 'auto',
            'image_height' => 'auto',
            'media_align' => 'teaser',
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
            'badge' => true,
            'badge_style' => 'badge',
            'badge_position' => 'panel',

            'link_target' => false,
            'date_format' => 'medium',
            'class' => '',
        ],
    ],

    'events' => [
        'init.admin' => function ($event, $app) {
            $app['angular']->addTemplate('grid.edit', 'plugins/widgets/grid/views/edit.php', true);
        },
    ],
];
