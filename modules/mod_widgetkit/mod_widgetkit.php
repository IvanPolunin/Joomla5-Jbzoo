<?php

use Joomla\CMS\Helper\ModuleHelper;

defined('_JEXEC') or die;

if (!$widgetkit = @include(JPATH_ADMINISTRATOR . '/components/com_widgetkit/widgetkit-app.php')) {
    return;
}

require ModuleHelper::getLayoutPath('mod_widgetkit', $params->get('layout', 'default'));
