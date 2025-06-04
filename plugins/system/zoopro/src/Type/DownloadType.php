<?php
/**
 * @package   System - ZOO YOOtheme Pro
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

namespace YOOtheme\Builder\Joomla\Zoo\Type;

use Joomla\CMS\Language\Text;

class DownloadType
{
    /**
     * @return array
     */
    public static function config()
    {
        return [

            'fields' => [

                'filename' => [
                    'type' => 'String',
                    'metadata' => [
                        'label' => Text::_('File Name'),
                    ],
                ],

                'download_link' => [
                    'type' => 'String',
                    'metadata' => [
                        'label' => Text::_('Link'),
                    ],
                ],

                'size' => [
                    'type' => 'String',
                    'metadata' => [
                        'label' => Text::_('Size'),
                    ],
                ],

                'hits' => [
                    'type' => 'String',
                    'metadata' => [
                        'label' => Text::_('Hits'),
                    ],
                ],

                'limit_reached' => [
                    'type' => 'String',
                    'metadata' => [
                        'label' => Text::_('Limit Reached'),
                    ],
                ],

                'download_limit' => [
                    'type' => 'String',
                    'metadata' => [
                        'label' => Text::_('Download Limit'),
                    ],
                ],

            ],

        ];
    }
}
