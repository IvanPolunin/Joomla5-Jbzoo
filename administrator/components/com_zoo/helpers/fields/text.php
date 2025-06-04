<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

// set attributes
$attributes = array('type' => 'text', 'name' => "{$control_name}[{$name}]", 'value' => $value, 'class' => isset($class) ? $class : '');

printf('<input %s />', $this->app->field->attributes($attributes, array('label', 'description', 'default')));