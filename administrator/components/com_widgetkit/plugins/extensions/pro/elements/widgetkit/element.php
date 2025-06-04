<?php

return [
    'transforms' => [
        'render' => function ($node, $params) {
            return !empty($node->props['widget']);
        },
    ],
];
