<?php
/**
* @package   BaForms
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Cache\Cache;

class pkg_baformsInstallerScript
{
    public function cleardir($dir)
    { 
        if (!is_dir($dir)) { 
            return;
        }
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                filetype($dir."/".$object) == "dir" ? $this->cleardir($dir."/".$object) : unlink($dir."/".$object);
            }
        }
        reset($objects);
        rmdir($dir);
    }

    public function install($parent)
    {
        
    }

	public function uninstall($parent)
    {
        $dir = JPATH_ROOT.'/images/baforms';
        $this->cleardir($dir);
    }

	public function update($parent)
    {
        $dir = JPATH_ROOT.'/components/com_baforms/libraries/pdf-submissions/font/unifont';
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != ".." && $object != 'ttfonts.php') {
                unlink($dir."/".$object);
            }
        }
        $this->updateStructure('/components/com_baforms/');
        $this->updateStructure('/administrator/components/com_baforms/');
        $this->updateStructure('/plugins/editors-xtd/baforms/');
        $this->updateStructure('/plugins/system/baforms/');
    }

    private function updateStructure($path)
    {
        $path = JPATH_ROOT . $path;
        $files = ['controllers', 'helpers', 'models', 'views', 'baforms.php', 'controller.php'];
        foreach ($files as $fileName) {
            $file = $path . $fileName;
            if (!file_exists($file)) {
                continue;
            }
            if (filetype($file) == "dir") {
                $this->cleardir($file);
            } else {
                unlink($file);
            }
        }
    }

	public function preflight($type, $parent)
    {
        if (JVERSION < '4.0.0') {
            Jerror::raiseWarning(null, 'Your Joomla version is outdated and incompatible with this extension. Update Joomla!');
			return false;
        }
    }

    public function postflight($type, $parent)
    {
        $db = Factory::getDbo();
		$query = $db->getQuery(true);
        $query->update('#__extensions')
            ->set('enabled = 1')
            ->where('element='.$db->quote('baforms'))
            ->where('folder='.$db->quote('editors-xtd'));
        $db->setQuery($query);
		$db->execute();
        $query = $db->getQuery(true);
        $query->update('#__extensions')
            ->set('enabled = 1')
            ->where('element='.$db->quote('baforms'))
            ->where('folder='.$db->quote('system'));
        $db->setQuery($query);
		$db->execute();
		$conf = Factory::getConfig();
		$options = ['defaultgroup' => '', 'storage' => $conf->get('cache_handler', ''),
            'caching' => true, 'cachebase' => $conf->get('cache_path', JPATH_SITE . '/cache')];
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