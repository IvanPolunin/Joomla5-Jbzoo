<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Joomla\CMS\Language\Text;

$class = (string) $node->attributes()->class ? 'class="'.$node->attributes()->class.'"' : 'class="inputbox"';

$options = array($this->app->html->_('select.option', '', '- '.Text::_('Select Module').' -'));

echo $this->app->html->_('zoo.modulelist', $options, $control_name.'['.$name.']', $class, 'value', 'text', $value, $control_name.$name);
