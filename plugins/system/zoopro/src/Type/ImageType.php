<?php
/**
 * @package   System - ZOO YOOtheme Pro
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

namespace YOOtheme\Builder\Joomla\Zoo\Type;

use Joomla\CMS\Language\Text;

class ImageType
{
    /**
     * @return array
     */
    public static function config()
    {
        return [

            'fields' => [

                'file' => [
                    'type' => 'String',
                    'metadata' => [
                        'label' => Text::_('Url'),
                    ],
                ],

                'title' => [
                    'type' => 'String',
                    'metadata' => [
                        'label' => Text::_('Title'),
                    ],
                ],

            ],

        ];
    }
}
