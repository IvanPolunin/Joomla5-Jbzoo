<?php
/**
 * @package   System - ZOO YOOtheme Pro
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

namespace YOOtheme\Builder\Joomla\Zoo\Type;

use Joomla\CMS\Language\Text;

class ApplicationQueryType
{
    /**
     * @param string         $type
     * @param \Application[] $applications
     *
     * @return array
     */
    public static function config($type, $applications)
    {
        return [
            'fields' => [
                'application' => [
                    'type' => $type,
                    'metadata' => [
                        'label' => Text::_('Application'),
                        'view' => array_map(function ($application) {
                            return "com_zoo.{$application->id}.frontpage";
                        }, $applications),
                        'group' => 'Page',
                    ],
                    'extensions' => [
                        'call' => __CLASS__ . '::resolve',
                    ],
                ],
            ],
        ];
    }

    public static function resolve($root)
    {
        if (isset($root['application'])) {
            return $root['application'];
        }
    }
}
