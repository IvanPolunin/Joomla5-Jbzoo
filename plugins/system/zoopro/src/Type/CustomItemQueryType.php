<?php
/**
 * @package   System - ZOO YOOtheme Pro
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

namespace YOOtheme\Builder\Joomla\Zoo\Type;

use YOOtheme\Builder\Joomla\Zoo\StrHelper;
use function YOOtheme\app;
use YOOtheme\Str;
use YOOtheme\Zoo;
use Joomla\CMS\Language\Text;

class CustomItemQueryType
{
    /**
     * @param \Type          $type
     * @param mixed          $name
     * @param \Application[] $applications
     *
     * @return array
     */
    public static function config($name, \Type $type, $applications)
    {
        $application = $type->getApplication();
        $appOptions = array_column($applications, 'id', 'name');

        $orderOptions = [];
        foreach ($type->getElements() as $element) {
            if ($element->getMetaData('orderable') == 'true') {
                $orderOptions[$element->config->name ?: $element->getMetaData('name')] = $element->identifier;
            }
        }

        $singularLower = Str::lower($type->getName());
		$pluralName = StrHelper::toPlural($type->getName(), '%s items');
        $pluralLower = Str::lower($pluralName);
        $pluralUpper = Str::titleCase($pluralName);

        return [
            'fields' => [

                Str::camelCase(['custom', $type->id]) => [
                    'type' => $name,
                    'args' => [
                        'appid' => [
                            'type' => 'String',
                        ],
                        'id' => [
                            'type' => 'String',
                        ],
                        'categories' => [
                            'type' => [
                                'listOf' => 'String',
                            ],
                        ],
                        'tags' => [
                            'type' => [
                                'listOf' => 'String',
                            ],
                        ],
                        'frontpage' => [
                            'type' => 'Boolean',
                        ],
                        'offset' => [
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
                        'label' => Text::sprintf('Custom ZOO %1$s %2$s', $application->getMetaData('name'), $type->getName()),
                        'group' => 'ZOO',
                        'fields' => [
                            'appid' => [
                                'label' => Text::_('Application'),
                                'description' => Text::_('Only items from the selected application are loaded.'),
                                'type' => 'select',
                                'options' => $appOptions,
								'defaultIndex' => 0,
                                'attrs' => [
                                    'class' => 'uk-height-small',
                                ],
                                'show' => count($appOptions) > 1,
                            ],
                            'id' => [
                                'label' => Text::_('Select Manually'),
                                'description' => Text::sprintf('Pick a %1$s manually or use filter options to specify which %2$s should be loaded dynamically.', $singularLower, $singularLower),
                                'type' => 'select-item',
                                'module' => 'zoo',
                                'item_type' => $type->id,
                                'labels' => [
                                    'type' => $type->getName(),
                                ],
                            ],
                            'categories' => [
                                'label' => Text::_('Limit by Categories'),
                                'description' => Text::sprintf('The %1$s is only loaded from the selected categories. %2$s from child categories are not included. Use the <kbd>shift</kbd> or <kbd>ctrl/cmd</kbd> key to select multiple categories.', $singularLower, $pluralUpper),
                                'type' => 'select',
                                'default' => [],
								'options' => [
									['evaluate' => '((config.zoo || {})[appid] || {})["categories"]'],
									['evaluate' => '((api.builder.zoo || {})[appid] || {})["categories"]'],
								],
                                'attrs' => [
                                    'multiple' => true,
                                    'class' => 'uk-height-small uk-resize-vertical',
                                ],
                                'enable' => '!id',
                            ],
                            'tags' => [
                                'label' => Text::_('Limit by Tags'),
                                'description' => Text::sprintf("The %s is only loaded from the selected tags. Use the <kbd>shift</kbd> or <kbd>ctrl/cmd</kbd> key to select multiple tags.", $singularLower),
								'type' => 'select',
								'default' => [],
								'options' => [
									['evaluate' => '((config.zoo || {})[appid] || {})["tags"]'],
									['evaluate' => '((api.builder.zoo || {})[appid] || {})["tags"]'],
								],
                                'attrs' => [
                                    'multiple' => true,
                                    'class' => 'uk-height-small uk-resize-vertical',
                                ],
                                'enable' => '!id',
                            ],
                            'frontpage' => [
                                'label' => Text::sprintf("Limit by Frontpage %s", $pluralUpper),
                                'type' => 'checkbox',
                                'text' => Text::sprintf("Load frontpage %s only", $pluralLower),
                                'enable' => '!id',
                            ],
                            'offset' => [
                                'label' => Text::_('Start'),
                                'description' => Text::sprintf("Set the starting point and limit the number of %s.", $pluralUpper),
                                'type' => 'number',
                                'default' => 0,
                                'modifier' => 1,
                                'attrs' => [
                                    'min' => 1,
                                    'required' => true,
                                ],
                                'enable' => '!id',
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
                                        'enable' => '!id',
                                    ],
                                    'order_direction' => [
                                        'label' => Text::_('Direction'),
                                        'type' => 'select',
                                        'default' => 'DESC',
                                        'options' => [
                                            Text::_('Ascending') => 'ASC',
                                            Text::_('Descending') => 'DESC',
                                        ],
                                        'enable' => '!id',
                                    ],
                                ],
                            ],
                            'order_alphanum' => [
                                'text' => Text::_('Alphanumeric Ordering'),
                                'type' => 'checkbox',
                                'enable' => '!id',
                            ],
                        ],
                    ],
                    'extensions' => [
                        'call' => [
                            'func' => __CLASS__ . '::resolve',
                            'args' => [
                                'type' => $type->id,
                            ],
                        ],
                    ],
                ],

            ],
        ];
    }

    public static function resolve($root, array $args)
    {
        $args += ['id' => 0, 'limit' => 1];

        /**
         * @var Zoo $zoo
         */
        $zoo = app(Zoo::class);

        if (!empty($args['id'])) {
            return $zoo->table->item->get($args['id']);
        }

        $items = CustomItemsQueryType::resolve($root, $args);
        return array_pop($items);
    }
}
