<?php
/**
 * @package   System - ZOO YOOtheme Pro
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

namespace YOOtheme\Builder\Joomla\Zoo\Type;

use YOOtheme\Builder\Joomla\Zoo\StrHelper;
use YOOtheme\Builder\Source;
use Joomla\CMS\Language\Text;
use YOOtheme\Str;

class CategoryType
{
    /**
     * @param Source       $source
     * @param \Application $application
     * @param string       $type
     *
     * @return array
     */
    public static function config(Source $source, $application, $type)
    {
        $fields = [

            'name' => [
                'type' => 'String',
                'metadata' => [
                    'label' => Text::_('Name'),
                    'filters' => ['limit'],
                ],
            ],

            'description' => [
                'type' => 'String',
                'metadata' => [
                    'label' => Text::_('Description'),
                    'filters' => ['limit'],
                ],
            ],

            'children' => [
                'type' => [
                    'listOf' => $type,
                ],
                'metadata' => [
                    'label' => Text::_('Child Categories'),
                ],
                'extensions' => [
                    'call' => __CLASS__ . '::children',
                ],
            ],

            'parent' => [
                'type' => $type,
                'metadata' => [
                    'label' => Text::_('Parent Category'),
                ],
                'extensions' => [
                    'call' => __CLASS__ . '::parent',
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

            'itemCount' => [
                'type' => 'String',
                'metadata' => [
                    'label' => Text::_('Item Count'),
                ],
                'extensions' => [
                    'call' => __CLASS__ . '::itemCount',
                ],
            ],

            'totalItemCount' => [
                'type' => 'String',
                'metadata' => [
                    'label' => Text::_('Total Item Count'),
                ],
                'extensions' => [
                    'call' => __CLASS__ . '::totalItemCount',
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
            );

        $content = ParamsContentType::config($application->getParamsForm()->getXML('category-content'), $application);

        if (!empty($content['fields'])) {

            $fields['content'] = [
                'type' => "{$type}Content",
                'metadata' => [
                    'label' => Text::_('Content'),
                ],
                'extensions' => [
                    'call' => __CLASS__ . '::content',
                ],
            ];

            $source->objectType("{$type}Content", $content);
        }

        $metadata = [
            'type' => true,
            'label' => Text::_('Category'),
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

    public static function children(\Category $category)
    {
        if ($category->hasChildren()) {
            return $category->getChildren();
        }

        return $category->app->table->category->all([
            'conditions' => ['parent = ? AND application_id = ? AND published = 1', $category->id, $category->application_id],
        ]);
    }

    public static function parent(\Category $category)
    {
        if ($category->parent == 0) {
            return;
        }

        return $category->app->table->category->first([
            'conditions' => ['id = ? AND published = 1', $category->parent],
        ]);
    }

    public static function link(\Category $category)
    {
        return $category->app->route->category($category);
    }

    public static function itemCount(\Category $category)
    {
        return $category->itemCount() ?: $category->app->table->item->getItemCountFromCategory($category->application_id, $category->id, true);
    }

    public static function totalItemCount(\Category $category)
    {
        $categories = $category->getApplication()->getCategoryTree(true, $category->app->user->get(), true);

        if (!empty($categories[$category->id])) {
            return $categories[$category->id]->totalItemCount();
        }
    }

    public static function content(\Category $category)
    {
        $result = [];
        $content = $category->getParams('site')->get('content.', []);

        foreach ($content as $key => $value) {
            $result[StrHelper::toFieldName($key)] = $value;
        }

        return $result;
    }

    public static function resolveItems(\Category $category, array $args)
    {
        return CustomItemsQueryType::resolve(null, $args + ['appid' => $category->getApplication()->id, 'categories' => [$category->id]]);
    }
}
