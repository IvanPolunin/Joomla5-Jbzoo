<?php
/**
 * @package   System - ZOO YOOtheme Pro
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

namespace YOOtheme\Builder\Joomla\Zoo\Type;

use YOOtheme\Builder\Joomla\Fields\Type\ValueFieldType;

class DateType extends ValueFieldType
{
    /**
     * @return array
     */
    public static function config()
    {
        $config = parent::config();
        $config['fields']['value']['metadata']['filters'][] = 'date';
        $config['metadata']['label'] = 'Date';

        return $config;
    }
}
