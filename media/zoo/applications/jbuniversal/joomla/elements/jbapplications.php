<?php
/**
 * JBZoo Application
 *
 * This file is part of the JBZoo CCK package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package    Application
 * @license    GPL-2.0
 * @copyright  Copyright (C) JBZoo.com, All rights reserved.
 * @link       https://github.com/JBZoo/JBZoo
 * @author     Denis Smetannikov <denis或者其他@jbzoo.com>
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// Use Joomla 6 compatible approach
$app = App::getInstance('zoo');
$appList = $app->table->application->all();
$basketOnly = (int)$node->attributes()->cart_only;

$options = [];
foreach ($appList as $appItem) {
    if ($basketOnly) {
        if ((int)$appItem->getParams()->get('global.jbzoo_cart_config.enable', 0)) {
            $options[$appItem->id] = $appItem->name;
        }
    } else {
        $options[$appItem->id] = $appItem->name;
    }
}

// Create proper select with attributes
$attributes = array(
    'id' => $control_name . '_' . str_replace(array('[]', '[', ']'), array('_', '_', ''), $name),
    'class' => 'inputbox chosen-select',
    'style' => 'width: 250px !important; min-width: 200px !important;'
);

if ((string)$node->attributes()->multiple == '1') {
    $attributes['multiple'] = 'multiple';
    $attributes['size'] = '5';
    $name .= '[]';
}

// Add CSS for Chosen
$css = '<style>
/* Chosen v1.x styles */
#' . $attributes['id'] . '_chzn {
    width: 250px !important;
    min-width: 200px !important;
}
#' . $attributes['id'] . '_chzn .chzn-single,
#' . $attributes['id'] . '_chzn .chzn-choices {
    width: 100% !important;
    box-sizing: border-box !important;
}
#' . $attributes['id'] . '_chzn .chzn-drop {
    width: 100% !important;
    min-width: 200px !important;
    box-sizing: border-box !important;
}

/* Chosen v2.x styles */
#' . $attributes['id'] . ' + .chosen-container {
    width: 250px !important;
    min-width: 200px !important;
}
#' . $attributes['id'] . ' + .chosen-container .chosen-single,
#' . $attributes['id'] . ' + .chosen-container .chosen-choices {
    width: 100% !important;
    box-sizing: border-box !important;
}
#' . $attributes['id'] . ' + .chosen-container .chosen-drop {
    width: 100% !important;
    min-width: 200px !important;
    box-sizing: border-box !important;
}

/* Generic Chosen styles */
.chosen-container {
    width: 250px !important;
    min-width: 200px !important;
}
.chosen-container .chosen-single,
.chosen-container .chosen-choices {
    width: 100% !important;
    box-sizing: border-box !important;
}
.chosen-container .chosen-drop {
    width: 100% !important;
    min-width: 200px !important;
    box-sizing: border-box !important;
}
</style>';

// Build HTML manually for better control
$html = $css . '<select name="' . htmlspecialchars($name) . '"';
foreach ($attributes as $attr => $val) {
    $html .= ' ' . $attr . '="' . htmlspecialchars($val) . '"';
}
$html .= '>';

foreach ($options as $optValue => $optText) {
    $selected = (is_array($value) && in_array($optValue, $value)) || ($optValue == $value) ? ' selected="selected"' : '';
    $html .= '<option value="' . htmlspecialchars($optValue) . '"' . $selected . '>' . htmlspecialchars($optText) . '</option>';
}

$html .= '</select>';

echo $html;
