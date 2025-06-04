<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Balbooa\Component\Gallery\Administrator\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;

abstract class GalleryHtmlJgrid
{
    public static function action($i, $task, $prefix = '', $text = '', $active_title = '',
        $inactive_title = '', $tip = false, $active_class = '',
        $inactive_class = '', $enabled = true, $translate = true, $checkbox = 'cb')
    {
        if (is_array($prefix)) {
            $options = $prefix;
            $active_title = array_key_exists('active_title', $options) ? $options['active_title'] : $active_title;
            $inactive_title = array_key_exists('inactive_title', $options) ? $options['inactive_title'] : $inactive_title;
            $tip = array_key_exists('tip', $options) ? $options['tip'] : $tip;
            $active_class = array_key_exists('active_class', $options) ? $options['active_class'] : $active_class;
            $inactive_class = array_key_exists('inactive_class', $options) ? $options['inactive_class'] : $inactive_class;
            $enabled = array_key_exists('enabled', $options) ? $options['enabled'] : $enabled;
            $translate = array_key_exists('translate', $options) ? $options['translate'] : $translate;
            $checkbox = array_key_exists('checkbox', $options) ? $options['checkbox'] : $checkbox;
            $prefix = array_key_exists('prefix', $options) ? $options['prefix'] : '';
        }
        if ($tip) {
            HTMLHelper::_('bootstrap.tooltip');

            $title = $enabled ? $active_title : $inactive_title;
            $title = $translate ? Text::_($title) : $title;
            $title = HTMLHelper::tooltipText($title, '', 0);
        }
        if ($enabled) {
            $html[] = '<a class="' . ($active_class == 'publish' ? ' active' : '') . ($tip ? ' hasTooltip' : '') . '"';
            $onclick = 'return Joomla.listItemTask(\''.$checkbox.$i.'\',\''.$prefix.$task.'\');';
            $html[] = ' href="javascript:void(0);" onclick="'.$onclick.'"';
            $html[] = $tip ? ' title="' . $title . '"' : '';
            $html[] = '>';
            if ($active_class == 'publish') {
                $html[] = '<i class="zmdi zmdi-eye"></i>';
            } else {
                $html[] = '<i class="zmdi zmdi-eye-off"></i>';
            }            
            $html[] = '</a>';
        } else {
            $html[] = '<a class="disabled jgrid' . ($tip ? ' hasTooltip' : '') . '"';
            $html[] = $tip ? ' title="' . $title . '"' : '';
            $html[] = '>';
            $html[] = '<i class="zmdi zmdi-eye-off"></i>';
            $html[] = '</a>';
        }

        return implode($html);
    }

    public static function state($states, $value, $i, $prefix = '', $enabled = true, $translate = true, $checkbox = 'cb')
    {
        if (is_array($prefix))
        {
            $options = $prefix;
            $enabled = array_key_exists('enabled', $options) ? $options['enabled'] : $enabled;
            $translate = array_key_exists('translate', $options) ? $options['translate'] : $translate;
            $checkbox = array_key_exists('checkbox', $options) ? $options['checkbox'] : $checkbox;
            $prefix = array_key_exists('prefix', $options) ? $options['prefix'] : '';
        }
        $state = ArrayHelper::getValue($states, (int) $value, $states[1]);
        $task = array_key_exists('task', $state) ? $state['task'] : $state[0];
        $text = array_key_exists('text', $state) ? $state['text'] : (array_key_exists(1, $state) ? $state[1] : '');
        $active_title = array_key_exists('active_title', $state) ? $state['active_title'] : (array_key_exists(2, $state) ? $state[2] : '');
        $inactive_title = array_key_exists('inactive_title', $state) ? $state['inactive_title'] : (array_key_exists(3, $state) ? $state[3] : '');
        $tip = array_key_exists('tip', $state) ? $state['tip'] : (array_key_exists(4, $state) ? $state[4] : false);
        $active_class = array_key_exists('active_class', $state) ? $state['active_class'] : (array_key_exists(5, $state) ? $state[5] : '');
        $inactive_class = array_key_exists('inactive_class', $state) ? $state['inactive_class'] : (array_key_exists(6, $state) ? $state[6] : '');

        return static::action(
            $i, $task, $prefix, $text, $active_title, $inactive_title, $tip,
            $active_class, $inactive_class, $enabled, $translate, $checkbox
        );
    }

    public static function published($value, $i, $prefix = '', $enabled = true, $checkbox = 'cb', $publish_up = null, $publish_down = null)
    {
        if (is_array($prefix))
        {
            $options = $prefix;
            $enabled = array_key_exists('enabled', $options) ? $options['enabled'] : $enabled;
            $checkbox = array_key_exists('checkbox', $options) ? $options['checkbox'] : $checkbox;
            $prefix = array_key_exists('prefix', $options) ? $options['prefix'] : '';
        }

        $states = array(1 => array('unpublish', 'JPUBLISHED', 'JLIB_HTML_UNPUBLISH_ITEM', 'JPUBLISHED', true, 'publish', 'publish'),
            0 => array('publish', 'JUNPUBLISHED', 'JLIB_HTML_PUBLISH_ITEM', 'JUNPUBLISHED', true, 'unpublish', 'unpublish'),
            2 => array('unpublish', 'JARCHIVED', 'JLIB_HTML_UNPUBLISH_ITEM', 'JARCHIVED', true, 'archive', 'archive'),
            -2 => array('publish', 'JTRASHED', 'JLIB_HTML_PUBLISH_ITEM', 'JTRASHED', true, 'trash', 'trash'));

        // Special state for dates
        if ($publish_up || $publish_down)
        {
            $nullDate = Factory::getDbo()->getNullDate();
            $nowDate = Factory::getDate()->toUnix();

            $tz = new \DateTimeZone(Factory::getUser()->getParam('timezone', Factory::getConfig()->get('offset')));

            $publish_up = ($publish_up != $nullDate) ? Factory::getDate($publish_up, 'UTC')->setTimeZone($tz) : false;
            $publish_down = ($publish_down != $nullDate) ? Factory::getDate($publish_down, 'UTC')->setTimeZone($tz) : false;

            // Create tip text, only we have publish up or down settings
            $tips = array();

            if ($publish_up)
            {
                $tips[] = Text::sprintf('JLIB_HTML_PUBLISHED_START', $publish_up->format(Date::$format, true));
            }

            if ($publish_down)
            {
                $tips[] = Text::sprintf('JLIB_HTML_PUBLISHED_FINISHED', $publish_down->format(Date::$format, true));
            }

            $tip = empty($tips) ? false : implode('<br />', $tips);

            // Add tips and special titles
            foreach ($states as $key => $state)
            {
                // Create special titles for published items
                if ($key == 1)
                {
                    $states[$key][2] = $states[$key][3] = 'JLIB_HTML_PUBLISHED_ITEM';

                    if ($publish_up > $nullDate && $nowDate < $publish_up->toUnix())
                    {
                        $states[$key][2] = $states[$key][3] = 'JLIB_HTML_PUBLISHED_PENDING_ITEM';
                        $states[$key][5] = $states[$key][6] = 'pending';
                    }

                    if ($publish_down > $nullDate && $nowDate > $publish_down->toUnix())
                    {
                        $states[$key][2] = $states[$key][3] = 'JLIB_HTML_PUBLISHED_EXPIRED_ITEM';
                        $states[$key][5] = $states[$key][6] = 'expired';
                    }
                }

                // Add tips to titles
                if ($tip)
                {
                    $states[$key][1] = Text::_($states[$key][1]);
                    $states[$key][2] = Text::_($states[$key][2]) . '<br />' . $tip;
                    $states[$key][3] = Text::_($states[$key][3]) . '<br />' . $tip;
                    $states[$key][4] = true;
                }
            }

            return static::state($states, $value, $i, array('prefix' => $prefix, 'translate' => !$tip), $enabled, true, $checkbox);
        }

        return static::state($states, $value, $i, $prefix, $enabled, true, $checkbox);
    }

    public static function isdefault($value, $i, $prefix = '', $enabled = true, $checkbox = 'cb')
    {
        if (is_array($prefix))
        {
            $options = $prefix;
            $enabled = array_key_exists('enabled', $options) ? $options['enabled'] : $enabled;
            $checkbox = array_key_exists('checkbox', $options) ? $options['checkbox'] : $checkbox;
            $prefix = array_key_exists('prefix', $options) ? $options['prefix'] : '';
        }

        $states = array(
            0 => array('setDefault', '', 'JLIB_HTML_SETDEFAULT_ITEM', '', 1, 'unfeatured', 'unfeatured'),
            1 => array('unsetDefault', 'JDEFAULT', 'JLIB_HTML_UNSETDEFAULT_ITEM', 'JDEFAULT', 1, 'featured', 'featured'),
        );

        return static::state($states, $value, $i, $prefix, $enabled, true, $checkbox);
    }

    public static function publishedOptions($config = array())
    {
        // Build the active state filter options.
        $options = array();

        if (!array_key_exists('published', $config) || $config['published'])
        {
            $options[] = HTMLHelper::_('select.option', '1', 'JPUBLISHED');
        }

        if (!array_key_exists('unpublished', $config) || $config['unpublished'])
        {
            $options[] = HTMLHelper::_('select.option', '0', 'JUNPUBLISHED');
        }

        if (!array_key_exists('archived', $config) || $config['archived'])
        {
            $options[] = HTMLHelper::_('select.option', '2', 'JARCHIVED');
        }

        if (!array_key_exists('trash', $config) || $config['trash'])
        {
            $options[] = HTMLHelper::_('select.option', '-2', 'JTRASHED');
        }

        if (!array_key_exists('all', $config) || $config['all'])
        {
            $options[] = HTMLHelper::_('select.option', '*', 'JALL');
        }

        return $options;
    }

    public static function checkedout($i, $editorName, $time, $prefix = '', $enabled = false, $checkbox = 'cb')
    {
        HTMLHelper::_('bootstrap.tooltip');

        if (is_array($prefix))
        {
            $options = $prefix;
            $enabled = array_key_exists('enabled', $options) ? $options['enabled'] : $enabled;
            $checkbox = array_key_exists('checkbox', $options) ? $options['checkbox'] : $checkbox;
            $prefix = array_key_exists('prefix', $options) ? $options['prefix'] : '';
        }

        $text = $editorName . '<br />' . HTMLHelper::_('date', $time, Text::_('DATE_FORMAT_LC')) . '<br />' . HTMLHelper::_('date', $time, 'H:i');
        $active_title = HTMLHelper::tooltipText(Text::_('JLIB_HTML_CHECKIN'), $text, 0);
        $inactive_title = HTMLHelper::tooltipText(Text::_('JLIB_HTML_CHECKED_OUT'), $text, 0);

        return static::action(
            $i, 'checkin', $prefix, Text::_('JLIB_HTML_CHECKED_OUT'), html_entity_decode($active_title, ENT_QUOTES, 'UTF-8'),
            html_entity_decode($inactive_title, ENT_QUOTES, 'UTF-8'), true, 'checkedout', 'checkedout', $enabled, false, $checkbox
        );
    }

    public static function orderUp($i, $task = 'orderup', $prefix = '', $text = 'JLIB_HTML_MOVE_UP', $enabled = true, $checkbox = 'cb')
    {
        if (is_array($prefix))
        {
            $options = $prefix;
            $text = array_key_exists('text', $options) ? $options['text'] : $text;
            $enabled = array_key_exists('enabled', $options) ? $options['enabled'] : $enabled;
            $checkbox = array_key_exists('checkbox', $options) ? $options['checkbox'] : $checkbox;
            $prefix = array_key_exists('prefix', $options) ? $options['prefix'] : '';
        }

        return static::action($i, $task, $prefix, $text, $text, $text, false, 'uparrow', 'uparrow_disabled', $enabled, true, $checkbox);
    }

    public static function orderDown($i, $task = 'orderdown', $prefix = '', $text = 'JLIB_HTML_MOVE_DOWN', $enabled = true, $checkbox = 'cb')
    {
        if (is_array($prefix))
        {
            $options = $prefix;
            $text = array_key_exists('text', $options) ? $options['text'] : $text;
            $enabled = array_key_exists('enabled', $options) ? $options['enabled'] : $enabled;
            $checkbox = array_key_exists('checkbox', $options) ? $options['checkbox'] : $checkbox;
            $prefix = array_key_exists('prefix', $options) ? $options['prefix'] : '';
        }

        return static::action($i, $task, $prefix, $text, $text, $text, false, 'downarrow', 'downarrow_disabled', $enabled, true, $checkbox);
    }
}