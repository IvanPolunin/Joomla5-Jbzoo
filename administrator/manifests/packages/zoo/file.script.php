<?php

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\Database\DatabaseInterface;

class pkg_zooInstallerScript extends ZooInstallerScript {}

class ZooInstallerScript {

    /**
     * @var InstallerAdapter
     */
    protected $adapter;

    /**
     * @var DatabaseInterface
     */
    protected $database;

    public function postflight($type, $adapter) {

        $this->adapter = $adapter;
        $this->database = version_compare(JVERSION, '4.0', '<')
            ? Factory::getDbo()
            : Factory::getContainer()->get(DatabaseInterface::class);

        if (!in_array($type, ['install', 'update'])) {
            return true;
        }

        if (class_exists('AppRequirements')) {
            $requirements = new AppRequirements();
            $requirements->checkRequirements();
            $requirements->displayResults();
        }

        if (class_exists('App')) {
            $app = App::getInstance('zoo');
            $app->module->enable('mod_zooquickicon', 'icon');
            $app->plugin->enable('zooshortcode');
            $app->plugin->enable('zoosmartsearch');
            $app->plugin->enable('zoosearch');
            $app->plugin->enable('zooevent');
            $app->plugin->enable('zoopro');
        }

        $this->patchUpdateSite();
   }

    protected function patchUpdateSite()
    {
        $site = $this->getUpdateSite($this->getExtensionId());
        $server = $this->adapter->manifest->updateservers->children()[0];

        if (!$site) {
            return;
        }

        // set name and location
        $site->name = strval($server['name']);
        $site->location = trim(strval($server));

        // set installer api key
        if (!$site->extra_query && ($key = $this->getInstallerApikey())) {
            $site->extra_query = "key={$key}";
        }

        $this->database->updateObject('#__update_sites', $site, 'update_site_id');
    }

    protected function getExtensionId()
    {
        return (function () {
            /** @var InstallerAdapter $this */
            return $this->currentExtensionId;
        })->bindTo($this->adapter, $this->adapter)();
    }

    protected function getInstallerApikey()
    {
        $query = function ($db, $query) {
            return $query
                ->select($db->qn('params'))
                ->from('#__extensions')
                ->where("{$db->qn('type')} = {$db->q('plugin')}")
                ->where("{$db->qn('folder')} = {$db->q('installer')}")
                ->where("{$db->qn('element')} = {$db->q('yootheme')}");
        };

        if ($extension = $this->createQuery($query)->loadObject()) {
            $params = json_decode($extension->params);
        }

        return isset($params->apikey) ? $params->apikey : null;
    }

    protected function getUpdateSite($extensionId)
    {
        $query = function ($db, $query) use ($extensionId) {
            return $query
                ->select('s.*')
                ->from($db->qn('#__update_sites', 's'))
                ->innerJoin(
                    sprintf(
                        '%s ON %s = %s',
                        $db->qn('#__update_sites_extensions', 'se'),
                        $db->qn('se.update_site_id'),
                        $db->qn('s.update_site_id')
                    )
                )
                ->where("{$db->qn('se.extension_id')} = {$extensionId}");
        };

        return $extensionId ? $this->createQuery($query)->loadObject() : null;
    }

    protected function createQuery(callable $callback)
    {
        return $this->database->setQuery(
            $callback($this->database, $this->database->getQuery(true))
        );
    }
}
