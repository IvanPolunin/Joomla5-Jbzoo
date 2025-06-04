<?php

defined('_JEXEC') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Plugin\CMSPlugin;

class plgSystemWidgetkit_k2 extends CMSPlugin
{
    public function onAfterInitialise()
    {
        if (!($app = (@include JPATH_ADMINISTRATOR.'/components/com_widgetkit/widgetkit-app.php')
            and file_exists($path = JPATH_ADMINISTRATOR.'/components/com_k2/k2.xml')
            and ComponentHelper::getComponent('com_k2', true)->enabled
            and ($data = ApplicationHelper::parseXMLInstallFile(JPATH_ADMINISTRATOR . '/components/com_k2/k2.xml'))
            and version_compare($data['version'], '2.1', '>='))
        ) {
            return;
        }

        $app['plugins']->addPath(__DIR__.'/plugin/plugin.php');
        $app['locator']->addPath('plugins/content/k2', __DIR__.'/plugin');
    }
}
