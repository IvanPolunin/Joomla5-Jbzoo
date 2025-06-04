<?php

return [
    'name' => 'widget/accordion',

    'main' => 'YOOtheme\\Widgetkit\\Widget\\Widget',

    'config' => [
        'name' => 'accordion',
        'label' => 'Accordion',
        'core' => true,
        'icon' => 'plugins/widgets/accordion/widget.svg',
        'view' => 'plugins/widgets/accordion/views/widget.php',
        'item' => ['title', 'content', 'media'],
        'settings' => [
            'collapse' => true,
            'first_item' => true,

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
                'accordion.edit',
                'plugins/widgets/accordion/views/edit.php',
                true
            );
        },
    ],
];
