<?php

namespace YOOtheme;

use YOOtheme\Widgetkit\Pro\SourceListener;

return [
    'events' => [
        'customizer.init' => [
            SourceListener::class => 'initCustomizer',
        ],
    ],

    'extend' => [
        Builder::class => function (Builder $builder) {
            $builder->addTypePath(Path::get('./elements/*/element.json'));
        },
    ],
];
