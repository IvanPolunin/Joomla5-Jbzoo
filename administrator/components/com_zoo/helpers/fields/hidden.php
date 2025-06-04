<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

// set attributes
$attributes = array('type' => 'hidden', 'name' => "{$control_name}[{$name}]", 'value' => $value);

printf('<input %s />', $this->app->field->attributes($attributes, array('description', 'default')));