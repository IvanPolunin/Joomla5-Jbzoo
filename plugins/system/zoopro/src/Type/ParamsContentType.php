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

class ParamsContentType
{
    /**
     * @param \SimpleXMLElement $group
     * @param \Application      $application
     *
     * @return array|void
     */
    public static function config($group, $application)
    {
        if (empty($group)) {
            return;
        }

        $fields = [];
        foreach ($group->param as $param) {

            $config = [
                'type' => 'String',
                'args' => [],
                'metadata' => [
                    'label' => (string) $param->attributes()->label,
                    'filters' => ['limit'],
                ],
            ];

            if ($param->attributes()->type == 'zoorelateditems') {
                $typeFilter = (string) $param->attributes()->type_filter;
                if (!$typeFilter) {
                    $types = $application->getTypes();
                    $type = array_shift($types);
                } else {
                    $types = explode(',', $typeFilter);
                    $type = $application->getType($types[0]);
                }

                if (!$type) {
                    continue;
                }

                $config = [
                    'type' => ['listOf' => Str::camelCase(['Zoo', $application->application_group, $type->id], true)],
                    'filter' => [],
                    'extensions' => [
                        'call' => __CLASS__ . '::resolveRelatedItems',
                    ],
                ] + $config;
            }

            $fields[StrHelper::toFieldName($param->attributes()->name)] = $config;

        }

        return [
            'fields' => $fields
        ];
    }

    /**
     * @depecated call directive on object type in CategoryType, ApplicationType
     */
    public static function resolve($object, $args, $ctx, $info)
    {
        return isset($object[$info->fieldName]) ? $object[$info->fieldName] : null;
    }

    public static function resolveRelatedItems($object, $args, $ctx, $info)
    {
        /**
         * @var Zoo $zoo
         */
        $zoo = app(Zoo::class);

        $ids = static::resolve($object, $args, $ctx, $info);
        return $zoo->table->item->getByIds($ids, true, null, 'ids');
    }
}
