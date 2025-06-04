<?php
/**
 * @package   System - ZOO YOOtheme Pro
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

namespace YOOtheme\Builder\Joomla\Zoo;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Language\Text;
use Joomla\String\Inflector;
use YOOtheme\Str;

class StrHelper
{
    public static function toFieldName($str)
    {
        $languageParams = ComponentHelper::getParams('com_languages');
        $language = $languageParams->get('administrator');

        $str = OutputFilter::stringURLSafe($str, $language);

        return Str::snakeCase($str);
    }

    public static function toPlural($str, $postfix = '%s_s')
	{
		$plural = Inflector::getInstance()->toPlural($str);

		return $plural !== false && strcasecmp($plural, $str) ? $plural : Text::sprintf($postfix, $str);
	}
}
