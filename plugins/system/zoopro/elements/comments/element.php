<?php
/**
 * @package   System - ZOO YOOtheme Pro
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

return [

	'transforms' => [

		'render' => function ($node, $params) {

			return !empty($params['item']) && $params['item'] instanceof Item;

		}

	]
];
