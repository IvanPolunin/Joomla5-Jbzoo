<?php

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;

if ($component = ComponentHelper::getComponent('com_widgetkit', true) and $component->enabled) {
    return include(__DIR__ . '/widgetkit.php');
}

return false;