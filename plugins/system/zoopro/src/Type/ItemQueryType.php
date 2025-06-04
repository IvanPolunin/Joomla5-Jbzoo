<?php
/**
 * @package   System - ZOO YOOtheme Pro
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

namespace YOOtheme\Builder\Joomla\Zoo\Type;

use Joomla\CMS\Language\Text;
use YOOtheme\Str;
use YOOtheme\Zoo;
use function YOOtheme\app;

class ItemQueryType
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
        $view = array_map(function ($application) use ($type) {
            return "com_zoo.{$application->id}.{$type->id}";
        }, $applications);

        return [
            'fields' => [

                Str::camelCase($type->id) => [
                    'type' => $name,
                    'metadata' => [
                        'label' => $type->getName(),
                        'view' => $view,
                        'group' => 'Page',
                    ],
                    'extensions' => [
                        'call' => __CLASS__ . '::resolve',
                    ],
                ],
                Str::camelCase(['previous', $type->id]) => [
                    'type' => $name,
                    'metadata' => [
                        'label' => Text::sprintf('Previous %s',$type->getName()),
                        'view' => $view,
                        'group' => 'Page',
                    ],
                    'extensions' => [
                        'call' => __CLASS__ . '::resolvePreviousItem',
                    ],
                ],
                Str::camelCase(['next', $type->id]) => [
                    'type' => $name,
                    'metadata' => [
                        'label' => Text::sprintf('Next %s',$type->getName()),
                        'view' => $view,
                        'group' => 'Page',
                    ],
                    'extensions' => [
                        'call' => __CLASS__ . '::resolveNextItem',
                    ],
                ],

            ],
        ];
    }

    public static function resolve($root)
    {
        if (isset($root['item'])) {
            return $root['item'];
        }
    }

    public static function resolvePreviousItem($root, $args)
    {
        if (isset($root['item'])) {
            list($prev) = self::getAdjacentItems($root['item']);
            return $prev ?: null;

        }
    }

    public static function resolveNextItem($root, $args)
    {
        if (isset($root['item'])) {
            list(, $next) = self::getAdjacentItems($root['item']);
            return $next ?: null;
        }
    }

    /**
     * @param Item $item
     * @return array
     */
    protected static function getAdjacentItems($item)
    {
        $element = $item->getElement('_itemprevnext');

        if ($element && $links = $element->getValue()) {

            /**
             * @var Zoo $zoo
             */
            $zoo = app(Zoo::class);

            return [
                $zoo->table->item->get($links['prev_id']),
                $zoo->table->item->get($links['next_id'])
            ];
        }
    }
}
