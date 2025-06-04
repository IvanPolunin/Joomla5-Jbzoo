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
use YOOtheme\Zoo;
use function YOOtheme\app;
use Joomla\CMS\Language\Text;

class TagType
{
    /**
     * @param \Application $application
     *
     * @return array
     */
    public static function config($application)
    {
        $fields = [

            'name' => [
                'type' => 'String',
                'metadata' => [
                    'label' => Text::_('Name'),
                    'filters' => ['limit'],
                ],
            ],

            'link' => [
                'type' => 'String',
                'metadata' => [
                    'label' => Text::_('Link'),
                ],
                'extensions' => [
                    'call' => __CLASS__ . '::link',
                ],
            ],

        ] +
            array_merge(
                ...array_values(
                array_map(
                    fn($type) => static::configItemType($type),
                    $application->getTypes(),
                ),
            ),
            );;

        $metadata = [
            'type' => true,
            'label' => Text::_('Tag'),
        ];

        return compact('fields', 'metadata');
    }

    public static function configItemType($type)
    {
        $name = Str::camelCase(['zoo', $type->getApplication()->application_group, $type->id], true);

        $orderOptions = [];
        foreach ($type->getElements() as $element) {
            if ($element->getMetaData('orderable') == 'true') {
                $orderOptions[$element->config->name ?: $element->getMetaData('name')] = $element->identifier;
            }
        }

        return [
            StrHelper::toPlural($type->id) => [
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
                    'order' => [
                        'type' => 'String',
                    ],
                    'order_direction' => [
                        'type' => 'String',
                    ],
                    'order_alphanum' => [
                        'type' => 'Boolean',
                    ],
                ],

                'metadata' => [
                    'label' => StrHelper::toPlural($type->getName(), '%s items'),
                    'arguments' => [
                        '_offset' => [
                            'description' => Text::sprintf("Set the starting point and limit the number of %s.", StrHelper::toPlural($type->getName(), '%s items')),
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
                                    'default' => 10,
                                    'attrs' => [
                                        'min' => 1,
                                    ],
                                ],
                            ],
                        ],
                        '_order' => [
                            'type' => 'grid',
                            'width' => '1-2',
                            'fields' => [
                                'order' => [
                                    'label' => Text::_('Order'),
                                    'type' => 'select',
                                    'default' => '_itempublish_up',
                                    'options' => [
                                            Text::_('Published') => '_itempublish_up',
                                            Text::_('Unpublished') => '_itempublish_down',
                                            Text::_('Created') => '_itemcreated',
                                            Text::_('Modified') => '_itemmodified',
                                            Text::_('Alphabetical') => '_itemname',
                                            Text::_('Hits') => '_itemhits',
                                            Text::_('Random') => '_random',
                                        ] + $orderOptions,
                                ],
                                'order_direction' => [
                                    'label' => Text::_('Direction'),
                                    'type' => 'select',
                                    'default' => 'DESC',
                                    'options' => [
                                        Text::_('Ascending') => 'ASC',
                                        Text::_('Descending') => 'DESC',
                                    ],
                                ],
                            ],
                        ],
                        'order_alphanum' => [
                            'text' => Text::_('Alphanumeric Ordering'),
                            'type' => 'checkbox',
                        ],
                    ],
                    'directives' => [],
                ],

                'extensions' => [
                    'call' => [
                        'func' => __CLASS__ . '::resolveItems',
                        'args' => ['type' => $type->id],
                    ],
                ],
            ],
        ];
    }

    public static function link($tag)
    {
        /**
         * @var Zoo $zoo
         */
        $zoo = app(Zoo::class);

         return $zoo->route->tag($tag->application_id, $tag->name);
    }

    public static function resolveItems($tag, array $args)
    {
        return CustomItemsQueryType::resolve(null, $args + ['appid' => $tag->application_id, 'tags' => [$tag->name]]);
    }
}
