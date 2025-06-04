<?php

use YOOtheme\Widgetkit\Joomla\Zoo\Transformer;

$config = array(

    'name' => 'content/zoo',

    'main' => 'YOOtheme\\Widgetkit\\Joomla\\Zoo\\Type',

    'autoload' => array(

        'YOOtheme\\Widgetkit\\Joomla\\Zoo\\' => 'src'

    ),

    'config' => array(

        'name'  => 'zoo',
        'label' => 'ZOO',
        'icon'  => 'plugins/content/zoo/content.svg',
        'item'  => array('title', 'content', 'media', 'location'),
        'data'  => array(
            'application'   => 0,
            'mode'          => 'categories',
            'type'          => '',
            'category'      => '',
            'subcategories' => 0,
            'order'         => '_itemname',
            'count'         => 4
        )

    ),

    'events'   => array(

        'init.admin' => function ($event, $app) {
            $app['scripts']->add('widgetkit-zoo-controller', 'plugins/content/zoo/assets/controller.js');
            $app['angular']->addTemplate('zoo.edit', 'plugins/content/zoo/views/edit.php');
        },

        'init.site'  => function ($event, $app) {
            $app['events']->subscribe(new Transformer);
        }

    )

);

return defined('_JEXEC') ? $config : false;
