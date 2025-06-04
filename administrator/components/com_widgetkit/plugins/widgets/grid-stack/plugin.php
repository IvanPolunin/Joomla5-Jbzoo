<?php

return [
    'name' => 'widget/grid-stack',

    'main' => 'YOOtheme\\Widgetkit\\Widget\\Widget',

    'config' => [
        'name' => 'grid-stack',
        'label' => 'Grid Stack',
        'core' => true,
        'icon' => 'plugins/widgets/grid-stack/widget.svg',
        'view' => 'plugins/widgets/grid-stack/views/widget.php',
        'item' => ['title', 'content', 'media'],
        'settings' => [
            'width' => '1-2',
            'align' => 'left',
            'breakpoint' => 'm',
            'alternate' => true,
            'gutter' => true,
            'gutter_vertical' => 'default',
            'divider' => false,
            'panel' => true,
            'content_align' => true,
            'animation_media' => 'none',
            'animation_content' => 'none',

            'media' => true,
            'image_width' => 'auto',
            'image_height' => 'auto',
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
            'class' => '',
        ],
    ],

    'events' => [
        'init.admin' => function ($event, $app) {
            $app['angular']->addTemplate(
                'grid-stack.edit',
                'plugins/widgets/grid-stack/views/edit.php',
                true
            );
        },
    ],
];
