<?php
/**
* @package   BaGallery
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Filesystem\Folder;

defined('_JEXEC') or die;

class com_bagalleryInstallerScript
{
    public function install($parent)
    {
    }

    public function deleteFolder($dir)
    {
        if (!is_dir($dir)) { 
            return;
        }
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (filetype($dir."/".$object) == "dir") {
                    $this->deleteFolder($dir."/".$object);
                } else {
                    unlink($dir."/".$object);
                }
            }
        }
        reset($objects);
        rmdir($dir);
    }
    
    public function uninstall($parent)
    {
        $params = ComponentHelper::getParams('com_bagallery');
        $base = JPATH_ROOT . '/' . $params->get('file_path', 'images');
        if (Folder::exists($base. '/bagallery')) {
            $this->deleteFolder($base. '/bagallery');
        }
    }
    public function update($parent)
    {
        $this->updateStructure('/components/com_bagallery/');
        $this->updateStructure('/administrator/components/com_bagallery/');
        $this->updateStructure('/plugins/editors-xtd/bagallery/');
        $this->updateStructure('/plugins/system/bagallery/');
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
                $this->deleteFolder($file);
            } else {
                unlink($file);
            }
        }
    }
    
    public function preflight($type, $parent)
    {
    }
    
    public function postflight($type, $parent)
    {
    }
}