<?php

namespace YOOtheme;

return [
    'name' => 'extension/pro',

    'autoload' => [
        'YOOtheme\\Widgetkit\\Pro\\' => 'src',
    ],

    'events' => [
        'init' => function () {
            if (
                class_exists(Application::class, false) &&
                method_exists(Application::class, 'getInstance') &&
                method_exists(Application::class, 'load')
            ) {
                $app = Application::getInstance();
                $app->load(__DIR__ . '/bootstrap.php');
            }
        },
    ],
];
