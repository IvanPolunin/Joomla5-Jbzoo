<?php

use Joomla\CMS\Factory;

class com_widgetkitInstallerScript
{
    protected $oldVersion;

    public function install($parent)
    {
        $this->init()->install();
    }

    public function uninstall($parent)
    {
        $this->init()->uninstall();
    }

    public function update($parent)
    {
        $app = $this->init();

        $app->install();

        if ($this->oldVersion && version_compare($this->oldVersion, '2.2.0', '<')) {
            $update = require($app['path'].'/updates/2.2.0.php');
            $update->run($app);
        }

        if ($this->oldVersion && version_compare($this->oldVersion, '3.0.0-beta.0.2', '<')) {
            $update = require($app['path'].'/updates/3.0.0-beta.0.2.php');
            $update->run($app);
        }
    }

    public function preflight($type, $parent)
    {
        $params = Factory::getDBO()->setQuery("SELECT manifest_cache FROM `#__extensions` WHERE `element` = 'com_widgetkit'")->loadResult();

        if ($params = @json_decode($params, true)) {
            $this->oldVersion = @$params['version'];
        }
    }

    public function postflight($type, $parent)
    {
    }

    protected function init()
    {
        return require(JPATH_ADMINISTRATOR.'/components/com_widgetkit/widgetkit.php');
    }
}
