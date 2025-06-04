<?php
/**
 * @package   System - ZOO YOOtheme Pro
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

namespace YOOtheme\Builder\Joomla\Zoo\Type;

use YOOtheme\Builder\Joomla\Zoo\StrHelper;
use YOOtheme\Str;
use Joomla\CMS\Language\Text;

class ItemsQueryType
{
    /**
     * @param string $name
     * @param \Type  $type
     * @param \Application[] $applications
     *
     * @return array
     */
    public static function config($name, \Type $type, $applications)
    {
        $pluralId = StrHelper::toPlural($type->id);
        $pluralLabel = Str::titleCase(StrHelper::toPlural($type->getName(), '%s items'));

        return [
            'fields' => [

                Str::camelCase($pluralId) => [
                    'type' => [
                        'listOf' => $name,
                    ],
                    'args' => [
                        'offset' => [
                            'type' => 'Int',
                        ],
                        'limit' => [
                            'type' => 'Int',
                        ],
                    ],
                    'metadata' => [
                        'label' => $pluralLabel,
                        'view' => array_reduce($applications, function($views, $application) {
                            return array_merge($views, [
                                "com_zoo.{$application->id}.category",
                                "com_zoo.{$application->id}.frontpage",
                                "com_zoo.{$application->id}.tag"
                            ]);
                        }, []),
                        'group' => 'Page',
                        'fields' => [
                            '_offset' => [
                                'description' => Text::_('Set the starting point and limit the number of articles.'),
                                'type' => 'grid',
                                'width' => '1-2',
                                'fields' => [
                                    'offset' => [
                                        'label' => Text::_('Start'),
                                        'type' => 'number',
                                        'default' => 0,
                                        'modifier' => 1,
                                        'attrs' => [
                                            'min' => 1,
                                            'required' => true,
                                        ],
                                    ],
                                    'limit' => [
                                        'label' => Text::_('Quantity'),
                                        'type' => 'limit',
                                        'attrs' => [
                                            'placeholder' => Text::_('No limit'),
                                            'min' => 0,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'extensions' => [
                        'call' => __CLASS__ . '::resolve',
                    ],
                ],

            ],
        ];
    }

    public static function resolve($root, array $args)
    {
        $args += [
            'offset' => 0,
            'limit' => null,
        ];

        if (isset($root['items'])) {

            $items = $root['items'];

            if ($args['offset'] || $args['limit']) {
                $items = array_slice($items, (int) $args['offset'], (int) $args['limit'] ?: null);
            }

            return $items;
        }
    }
}
