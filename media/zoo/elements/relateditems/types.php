<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

// get element from parent parameter form
use Joomla\CMS\Language\Text;

$config      = $parent->element->config;
$application = $parent->application;
$app_id      = $config->get('app_id', '');

$toTypesArray = function ($types) {
    return array_map(function ($type) { return $type->name; }, $types);
};

$applications = $this->app->application->getApplications();
$options[] = $this->app->html->_('select.option', '', Text::_('CURRENT APP'));

$types = array('' => $toTypesArray($application->getTypes()));
foreach ($applications as $app) {
    $options[] = $this->app->html->_('select.option', $app->id, $app->name);
    $types[$app->id] = $toTypesArray($app->getTypes());
}

$appSelect = $this->app->html->_('select.genericlist', $options, $control_name.'[app_id]', [
    'data-types' => htmlspecialchars(json_encode($types)),
    'onchange' => htmlspecialchars('(function ($, el) { var select = $(el).closest(".field").find("[name*=\"selectable_types\"]").empty(); $.each(JSON.parse(el.dataset.types)[el.value], function (id, name) { select.append("<option value=\""+id+"\">"+name+"</option>"); }); })(jQuery, this);')
], 'value', 'text', $app_id);

// init vars
$attributes = array();
$attributes['class'] = (string) $node->attributes()->class ? (string) $node->attributes()->class : 'inputbox';
$attributes['multiple'] = 'multiple';
$attributes['size'] = (string) $node->attributes()->size ? (string) $node->attributes()->size : '';

$options = [];
if (!empty($types[$app_id])) {
    foreach ($types[$app_id] as $id => $name) {
        $options[] = $this->app->html->_('select.option', $id, Text::_($name));
    }
}

$typesSelect =  $this->app->html->_('select.genericlist', $options, $control_name.'[selectable_types][]', $attributes, 'value', 'text', $config->get('selectable_types', array()));

?>

<div class="types">
    <div>
        <?php echo $appSelect ?>
    </div>
    <div>
        <?php echo $typesSelect ?>
    </div>
</div>
