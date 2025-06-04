<?php

$config = [
    'name' => 'framework/joomla',

    'main' => 'YOOtheme\\Framework\\Joomla\\JoomlaPlugin',

    'autoload' => [
        'YOOtheme\\Framework\\Joomla\\' => 'src',
    ],
];

return defined('_JEXEC') ? $config : false;
