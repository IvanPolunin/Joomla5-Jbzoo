<?php
/**
 * @package   System - ZOO YOOtheme Pro
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

namespace YOOtheme;

use YOOtheme\Builder\BuilderConfig;
use YOOtheme\Builder\Joomla\Zoo\SourceController;
use YOOtheme\Builder\Joomla\Zoo\SourceListener;
use YOOtheme\Builder\Joomla\Zoo\TemplateListener;

return [

    'routes' => [

        ['get', '/zoo/items', [SourceController::class, 'items']],

    ],

    'events' => [

        'source.init' => [
            SourceListener::class => ['initSource', -20],
        ],

        'customizer.init' => [
            SourceListener::class => ['initCustomizer', -5],
        ],

        'builder.template' => [
            TemplateListener::class => [
                ['matchItemTemplate'],
                ['matchCategoryTemplate'],
                ['matchFrontpageTemplate'],
                ['matchTagTemplate'],
            ],
        ],

        'builder.template.load' => [
            TemplateListener::class => 'loadTemplateUrl'
        ],

        BuilderConfig::class => [
            SourceListener::class => 'loadBuilderConfig',
        ],

    ],

    'extend' => [

        Builder::class => function (Builder $builder) {
            $builder->addTypePath(Path::get('./elements/*/element.json'));
        },

    ],

];
