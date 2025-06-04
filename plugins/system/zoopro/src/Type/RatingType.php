<?php
/**
 * @package   System - ZOO YOOtheme Pro
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

namespace YOOtheme\Builder\Joomla\Zoo\Type;

use Joomla\CMS\Language\Text;

class RatingType
{
    /**
     * @return array
     */
    public static function config()
    {
        return [

            'fields' => [

                'rating' => [
                    'type' => 'String',
                    'metadata' => [
                        'label' => Text::_('Value'),
                    ],
                ],

                'votes' => [
                    'type' => 'Int',
                    'metadata' => [
                        'label' => Text::_('Votes'),
                    ],
                ],

            ],

        ];
    }
}
