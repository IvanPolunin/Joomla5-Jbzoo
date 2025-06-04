<?php

/**
 * @package         Regular Labs Library
 * @version         24.6.22903
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            https://regularlabs.com
 * @copyright       Copyright © 2024 Regular Labs All Rights Reserved
 * @license         GNU General Public License version 2 or later
 */
namespace RegularLabs\Library;

defined('_JEXEC') or die;
class FieldHelper
{
    public static function correctFieldValue(int|string $field_name, mixed &$field_value): void
    {
        if (is_array($field_value) && (count($field_value) > 1 || !isset($field_value[0]))) {
            foreach ($field_value as $key => &$value) {
                self::correctFieldValue($key, $value);
            }
            return;
        }
        $field_type = self::getTypeByName($field_name);
        if ($field_type != 'articles') {
            return;
        }
        $field_value = (array) $field_value;
        if (count($field_value) == 1 && str_contains($field_value[0], ',')) {
            $field_value = explode(',', $field_value[0]);
        }
    }
    public static function getTypeByName(string $name): string
    {
        $types = self::getTypes();
        return $types[$name] ?? '';
    }
    public static function getTypes(): array
    {
        $db = \RegularLabs\Library\DB::get();
        $query = \RegularLabs\Library\DB::getQuery()->select([\RegularLabs\Library\DB::quoteName('f.name'), \RegularLabs\Library\DB::quoteName('f.type')])->from(\RegularLabs\Library\DB::quoteName('#__fields', 'f'));
        $db->setQuery($query);
        $by_name = $db->loadAssocList('name', 'type');
        $db = \RegularLabs\Library\DB::get();
        $query = \RegularLabs\Library\DB::getQuery()->select([\RegularLabs\Library\DB::quoteName('f.id'), \RegularLabs\Library\DB::quoteName('f.type')])->from(\RegularLabs\Library\DB::quoteName('#__fields', 'f'));
        $db->setQuery($query);
        $by_id = $db->loadAssocList('id', 'type');
        $by_id = \RegularLabs\Library\ArrayHelper::addPrefixToKeys($by_id, 'field');
        return array_merge($by_name, $by_id);
    }
}
