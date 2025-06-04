<?php
/**
* @package   BaGallery
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Cache\Cache;

class pkg_bagalleryInstallerScript
{
    public function install($parent) {}

	public function uninstall($parent)
    {
    }

	public function update($parent) {}

	public function preflight($type, $parent)
    {
        if (JVERSION < '4.0.0') {
            Jerror::raiseWarning(null, 'Your Joomla version is outdated and incompatible with this extension. Update Joomla!');
			return false;
        }
    }

    public function postflight($type, $parent) {
        $db = Factory::getDbo();
		$query = $db->getQuery(true);
        $query->update('#__extensions')
            ->set('enabled = 1')
            ->where('element='.$db->quote('bagallery'))
            ->where('folder='.$db->quote('editors-xtd'));
        $db->setQuery($query);
		$db->execute();
        $query = $db->getQuery(true);
        $query->update('#__extensions')
            ->set('enabled = 1')
            ->where('element='.$db->quote('bagallery'))
            ->where('folder='.$db->quote('system'));
        $db->setQuery($query);
		$db->execute();
		$conf = Factory::getConfig();
        $options = array(
            'defaultgroup' => '',
            'storage'      => $conf->get('cache_handler', ''),
            'caching'      => true,
            'cachebase'    => $conf->get('cache_path', JPATH_SITE . '/cache')
        );
        $cache = Cache::getInstance('', $options);
        $data = $cache->getAll();
        if ($data) {
            foreach ($data as $item) {
                $cache->clean($item->group);
            }
        }
        $cache = Factory::getCache('');
        $cache->gc();
    }
}