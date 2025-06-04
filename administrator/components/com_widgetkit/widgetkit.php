<?php

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Session\Session;
use YOOtheme\Framework\Joomla\EditorHelper;
use YOOtheme\Framework\Joomla\Option;
use YOOtheme\Widgetkit\Application;

global $widgetkit;

if ($widgetkit) {
    return $widgetkit;
}

$loader = require __DIR__ . '/vendor/autoload.php';
$config = require __DIR__ . '/config.php';

$app = new Application($config);
$app['autoloader']  = $loader;
$app['path.cache']  = rtrim(JPATH_SITE, '/').'/media/widgetkit';
$app['component']   = 'com_'.$app['name'];
$app['permissions'] = array('core.manage' => 'manage_widgetkit');
$app['templates']   = function () {
    $db = Factory::getDbo();
    $db->setQuery( 'SELECT id,template FROM #__template_styles WHERE client_id=0 AND home=1');
    $template = $db->loadObject()->template;

    return file_exists($path = rtrim(JPATH_ROOT, '/')."/templates/{$template}/widgetkit") ? array($path) : array();
};
$app['option'] = function ($app) {
    return new Option($app['db'], 'pkg_widgetkit');
};

$app['locator']->addPath('assets', rtrim(JPATH_ROOT, '/').'/media/com_widgetkit');

$app->on('init', function ($event, $app) {

    $option = $app['joomla']->input->get('option');
    $controller = $app['joomla']->input->get('controller');

    if ($option == 'com_config' && $controller == 'config.display.modules') {
        $app['scripts']->add('widgetkit-joomla', 'assets/js/joomla.js', array('widgetkit-application'));
    }

    $app['config']->add(ComponentHelper::getParams($app['component'])->toArray());

    $app->on('init.site', function($event, $app) {

        // check theme support for UIkit
        $template = $app['joomla']->getTemplate(true);

        $app['config']->set('theme.support', $app['config']->get('theme_support'));

        if ($template->params->get('uikit3')) {
            $app['config']->set('theme.support', 'uikit3');
            // Legacy Widgetkit 2
        } elseif (Factory::getConfig()->get('widgetkit')
            || Factory::getConfig()->get('widgetkit-noconflict')
            || file_exists(sprintf('%s/%s/warp.php', JPATH_THEMES, $template->template))
        ) {
            $app['config']->set('theme.support', 'noconflict');
        } else if (!$app['config']->get('theme.support')) {
            $app['config']->set('theme.support', 'scoped');
        }

        $app->on('view', function($event, $app) {
            if ($app['config']->get('theme.support') === 'noconflict') {
                $app['locator']->addPath('assets/lib/uikit', rtrim(JPATH_ROOT, '/')."/media/com_widgetkit/lib/wkuikit");
            }
        });

    });

    if ($app['admin'] && $app['component'] === $app['joomla']->input->get('option')) {
        $app->trigger('init.admin', array($app));
    }

});

$app->on('init.admin', function ($event, $app) {

    // don't add assets for editor route
    if ($app['joomla']->input->get('p') === 'editor') {
        $app['controllers']->get('editor', ['YOOtheme\Framework\Joomla\EditorHelper', 'renderEditor']);
        return;
    }

    // load widget manager assets after dispatch
    $app['joomla']->registerEvent('onAfterDispatch', function () use ($app) {
        HTMLHelper::_('behavior.keepalive');
        HTMLHelper::_('jquery.framework');

        // delay loading editor to ensure e.g. bootstrap dependency has been added to the document
        $app['joomla']->registerEvent('onBeforeRender', function () use ($app) {
            if ($editor = EditorHelper::load($app, Factory::getConfig()->get('editor'))) {
                $app['angular']->set('editor', $editor);
                $app['scripts']->add('widgetkit-tinymce', 'https://cdn.jsdelivr.net/npm/tinymce@4.5.12/tinymce.min.js', array(), array('defer' => true));
            }
        });

        $app['angular']->set('token', Session::getFormToken());
        $app['angular']->set('platform', ['name' => 'joomla', 'version' => JVERSION]);
        $app['angular']->addTemplate('media', 'views/media.php', true);
        $app['config']->set('settings-page', 'index.php?option=com_config&view=component&component=com_widgetkit');
        $app['styles']->add('widgetkit-joomla', 'assets/css/joomla.css');
        $app['scripts']->add('widgetkit-joomla', 'assets/js/joomla.js', array('widgetkit-admin'), array('defer' => true));
    });

}, 10);

return $widgetkit = $app->boot();
