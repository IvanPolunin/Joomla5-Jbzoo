<?php

$config = array(

    'name' => 'content/k2',

    'main' => 'YOOtheme\\Widgetkit\\Joomla\\K2\\Type',

    'autoload' => array(

        'YOOtheme\\Widgetkit\\Joomla\\K2\\' => 'src'

    ),

    'config' => array(

        'name'  => 'k2',
        'label' => 'K2',
        'icon'  => 'plugins/content/k2/content.svg',
        'item'  => array('title', 'content', 'media', 'location'),
        'data'  => array(
            'content'       => 'intro',
            'category'      => array(),
            'subcategories' => 0,
            'number'        => 5,
            'ordering'      => ''
        )

    ),

    'events' => array(

        'init.admin' => function($event, $app) {
            $app['scripts']->add('widgetkit-k2-controller', 'plugins/content/k2/assets/controller.js');
            $app['angular']->addTemplate('k2.edit', 'plugins/content/k2/views/edit.php');
        },

        'init.site'  => function ($event, $app) {
            $app['events']->subscribe(new YOOtheme\Widgetkit\Joomla\K2\Transformer);
        }

    )

);

return defined('_JEXEC') ? $config : false;