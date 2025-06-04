<?php

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\Database\DatabaseInterface;

class pkg_widgetkitInstallerScript
{
    /**
     * @var InstallerAdapter
     */
    protected $adapter;

    /**
     * @var DatabaseInterface
     */
    protected $database;

    public function install()
    {
        $this->enablePlugins();
    }

    public function update()
    {
        $this->enablePlugins();
    }

    public function preflight($type, $adapter)
    {
        $this->adapter = $adapter;
        $this->database = version_compare(JVERSION, '4.0', '<')
            ? Factory::getDbo()
            : Factory::getContainer()->get(DatabaseInterface::class);

        $this->checkVersion();
        $this->checkWarpTheme();
    }

    public function postflight($type)
    {
        if (!in_array($type, ['install', 'update'])) {
            return true;
        }

        $this->patchUpdateSite();
    }

    protected function enablePlugins()
    {
        $query =
            "UPDATE `#__extensions` SET `enabled` = 1 WHERE `element` = 'widgetkit' AND `folder` IN ('content', 'editors-xtd', 'system')";

        $this->database->setQuery($query)->execute();
    }

    protected function checkVersion()
    {
        $query = function ($db, $query) {
            return $query
                ->select($db->qn('manifest_cache'))
                ->from('#__extensions')
                ->where("{$db->qn('element')} = {$db->q('com_widgetkit')}");
        };

        $params = @json_decode($this->createQuery($query)->loadResult(), true);

        if (isset($params['version']) && version_compare($params['version'], '2.0.0', '<')) {
            throw new Exception(
                'Cannot install Widgetkit, please read the <a href="https://yootheme.com/support/widgetkit/migration" target="_blank">Widgetkit migration guide</a>'
            );
        }
    }

    protected function checkWarpTheme()
    {
        $query = function ($db, $query) {
            return $query
                ->select($db->qn('template'))
                ->from('#__template_styles')
                ->where("{$db->qn('client_id')} = 0")
                ->where("{$db->qn('home')} = {$db->q('1')}");
        };

        $templ = $this->createQuery($query)->loadResult();

        if (substr($templ, 0, 4) === 'yoo_') {
            throw new Exception(
                'This website is using a Warp 7 theme, and an update to Widgetkit 3 is not possible. Since <a href="https://yootheme.com/blog/2021/01/11/sunsetting-warp-7-themes" target="_blank">Warp 7 themes are being sunsetted</a>, it\'s strongly recommended to switch to YOOtheme Pro which will work perfectly with Widgetkit 3. Learn more about the <a href="https://yootheme.com/blog/2021/01/26/widgetkit-3.0-completely-rebuilt-with-uikit-3" target="_blank">Widgetkit 3 update</a>.'
            );
        }
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
