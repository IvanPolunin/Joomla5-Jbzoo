<?php
/**
 * @package   System - ZOO YOOtheme Pro
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

namespace YOOtheme\Builder\Joomla\Zoo\Type;

use YOOtheme\Builder\Source;
use YOOtheme\Str;

class AppQueryType
{
    /**
     * @param Source         $source
     * @param \Application   $application
     * @param \Application[] $applications
     *
     * @return array
     */
    public static function config(Source $source, $application, $applications)
    {
        $field = Str::camelCase(['zoo', $application->application_group]);
        $query = Str::camelCase([$field, 'Query'], true);

        $source->objectType($name = Str::camelCase([$field, 'Category'], true), CategoryType::config($source, $application, $name));
        $source->objectType($query, CategoryQueryType::config($name, $applications));
        $source->objectType($query, CustomCategoryQueryType::config($name, $application, $applications));
        $source->objectType($query, CustomCategoriesQueryType::config($name, $application, $applications));
        $catName = $name;

        $source->objectType($name = Str::camelCase([$field, 'Tag'], true), TagType::config($application));
        $source->objectType($query, TagQueryType::config($name, $application));

        $source->objectType($name = Str::camelCase([$field, 'Application'], true), ApplicationType::config($source, $application, $name, $catName));
        $source->objectType($query, ApplicationQueryType::config($name, $applications));

        foreach ($application->getTypes() as $type) {
            $source->objectType($name = Str::camelCase([$field, $type->id], true), ItemType::config($source, $name, $type));
            $source->objectType($query, ItemQueryType::config($name, $type, $applications));
            $source->objectType($query, ItemsQueryType::config($name, $type, $applications));
            $source->objectType($query, CustomItemQueryType::config($name, $type, $applications));
            $source->objectType($query, CustomItemsQueryType::config($name, $type, $applications));
        }

        return [
            'fields' => [
                $field => [
                    'type' => $query,
                    'extensions' => [
                        'call' => __CLASS__ . '::resolve',
                    ],
                ],
            ],
        ];
    }

    public static function resolve($root)
    {
        return $root;
    }
}
