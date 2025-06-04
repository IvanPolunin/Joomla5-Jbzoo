<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

// init vars
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Language\Text;

$class      = (string) $node->attributes()->class ? 'class="'.$node->attributes()->class.' form-select"' : 'class="inputbox form-select"';
$constraint = (string) $node->attributes()->constraint;

$layoutPath = $parent->layout_path;

if (!empty($parent->element->config['app_id'])) {
    $application = $this->app->table->application->get($parent->element->config['app_id']);
    if ($template = $application->getTemplate()) {
        $layoutPath = $template->getPath();
    }
}

// get renderer
$renderer = $this->app->renderer->create('item')->addPath($layoutPath);

// if selectable types isn't specified, get all types
if (empty($parent->selectable_types)) {
	$parent->selectable_types = array('');
	foreach (Folder::folders("{$layoutPath}/{$renderer->getFolder()}/item") as $folder) {
		$parent->selectable_types[] = $folder;
	}
}

// get layouts
$layouts = array();
foreach ($parent->selectable_types as $type) {
	$path   = 'item';
	$prefix = 'item.';
	if (!empty($type) && $renderer->pathExists($path.DIRECTORY_SEPARATOR.$type)) {
		$path   .= DIRECTORY_SEPARATOR.$type;
		$prefix .= $type.'.';
	}
	foreach ($renderer->getLayouts($path) as $layout) {

		$metadata = $renderer->getLayoutMetaData($prefix.$layout);

		if (empty($constraint) || $metadata->get('type') == $constraint) {
			$layouts[$layout] = $metadata->get('name');
		}
	}
}

// create layout options
$options = array($this->app->html->_('select.option', '', Text::_('Item Name')));
foreach ($layouts as $layout => $layout_name) {
	$text	   = $layout_name;
	$val	   = $layout;
	$options[] = $this->app->html->_('select.option', $val, $text);
}

echo $this->app->html->_('select.genericlist', $options, $control_name.'['.$name.']', $class, 'value', 'text', $value, $control_name.$name);
