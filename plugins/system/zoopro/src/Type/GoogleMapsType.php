<?php
/**
 * @package   System - ZOO YOOtheme Pro
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

namespace YOOtheme\Builder\Joomla\Zoo\Type;

use Joomla\CMS\Language\Text;

class GoogleMapsType
{
    /**
     * @return array
     */
    public static function config()
    {
        return [

            'fields' => [

                'coordinates' => [
                    'type' => 'String',
                    'metadata' => [
                        'label' => Text::_('Coordinates'),
                    ],
                    'extensions' => [
                        'call' => __CLASS__ . '::coordinates',
                    ],
                ],

            ],

        ];
    }

    public static function coordinates($value)
    {
        return !empty($value['lat']) && !empty($value['lng']) ? "{$value['lat']},{$value['lng']}" : null;
    }
}
