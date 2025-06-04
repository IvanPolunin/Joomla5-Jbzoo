<?php
/**
 * @package   System - ZOO YOOtheme Pro
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

namespace YOOtheme\Builder\Joomla\Zoo\Type;

use Joomla\CMS\Language\Text;

class TagQueryType
{
    /**
     * @param string $type
     * @param \Application $application
     *
     * @return array
     */
    public static function config($type, $application)
    {
        return [
            'fields' => [

                'tag' => [
                    'type' => $type,
                    'metadata' => [
                        'label' => Text::_('Tag'),
                        'view' => ["com_zoo.{$application->id}.tag"],
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
        if (isset($root['tag'], $root['application'])) {
            return (object) [
                'name' => $root['tag'],
                'application_id' => $root['application']->id,
            ];
        }
    }
}
