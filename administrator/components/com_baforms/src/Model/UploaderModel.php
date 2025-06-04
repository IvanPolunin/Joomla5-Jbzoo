<?php
/**
* @package   BaForms
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

namespace Balbooa\Component\Forms\Administrator\Model;

defined('_JEXEC') or die;

use Balbooa\Component\Forms\Administrator\Helper\BaformsHelper;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Component\ComponentHelper;

class UploaderModel extends BaseDatabaseModel
{
    protected $_parent;
    protected $_folders;
    
    public function getParent()
    {
        return $this->_parent;
    }

    public function getFolderList()
    {
        $dir = $this->_folders;
        $name = str_replace(JPATH_ROOT.'/'.BaformsHelper::$image_path, '', $dir);
        $folders = Folder::folders($dir);
        $items = array();
        foreach ($folders as $folder) {
            $obj = new \stdClass();
            $obj->path = $name. '/' .$folder;
            $obj->name = $folder;
            $this->_folders = $dir. '/' .$folder;
            $obj->childs = $this->getFolderList();
            $items[] = $obj;
        }

        return $items;
    }

    public function getBreadcrumb()
    {
        $dir = JPATH_ROOT.'/'.BaformsHelper::$image_path;
        $this->_folders = $dir;
        $input = Factory::getApplication()->input;
        $name = $input->get('folder', '', 'string');
        $dir .= $name;
        $breadcrumb = array();
        if (!empty($name)) {
            $array = explode('/', $name);
            $path = '';
            foreach ($array as $value) {
                if (!empty($value)) {
                    $obj = new \stdClass();
                    $path .= '/'.$value;
                    $obj->path = $path;
                    $obj->title = $value;
                    $breadcrumb[] = $obj;
                }
            }
        }

        return $breadcrumb;
    }
    
    public function getFolders()
    {
        $dir = JPATH_ROOT.'/'.BaformsHelper::$image_path;
        $this->_folders = $dir;
        $input = Factory::getApplication()->input;
        $name = $input->get('folder', '', 'string');
        $dir .= $name;
        $items = array();
        if ($dir != JPATH_ROOT.'/'.BaformsHelper::$image_path) {
            $this->_parent = $dir;
        }
        $folders = Folder::folders($dir);
        if (!empty($folders)) {
            foreach ($folders as $folder) {
                $obj = new \stdClass();
                $obj->path = $name. '/' .$folder;
                $obj->name = $folder;
                $items[] = $obj;
            }
        }

        return $items;
    }

    public function getImages()
    {
        $dir = JPATH_ROOT.'/'.BaformsHelper::$image_path;
        $url = Uri::root().BaformsHelper::$image_path;
        $input = Factory::getApplication()->input;
        $name = $input->get('folder', '', 'string');
        if ($name == "undefined") {
            $name = '';
        }
        if (!empty($name)) {
            $dir .= $name;
            $url .= $name;
        }
        $files = Folder::files($dir);
        $images = [];
        $types = $this->getFiletypes();
        if (!empty($files)) {
            foreach ($files as $file) {
                $ext = strtolower(File::getExt($file));
                if (!in_array($ext, $types)) {
                    continue;
                }
                $image = new \stdClass();
                $image->ext = $ext;
                $image->name = $file;
                $image->path = $name. '/' .$file;
                $image->size = filesize($dir.'/'.$file);
                $image->url = $url. '/' .$file;
                $images[] = $image;
            }
        }
        return $images;
    }

    public function getFiletypes()
    {
        $params = ComponentHelper::getParams('com_baforms');
        $files = $params->get('allowed_file_types', 'csv, doc, gif, ico, jpg, jpeg, pdf, png, txt, xls, svg, mp4, webp');
        $array = explode(',', $files);
        foreach ($array as $key => $value) {
            $value = trim($value);
            $value = strtolower($value);
            $array[$key] = $value;
        }

        return $array;
    }
}
