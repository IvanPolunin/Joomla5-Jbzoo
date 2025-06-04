<?php
/**
* @package   BaGallery
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

namespace Balbooa\Component\Gallery\Administrator\Helper;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;

defined('_JEXEC') or die;

class UploaderHelper
{
    public $dir;
    public $limit;
    public $page;
    public $input;
    public $pages;
    public $search;

    public function __construct($dir = '')
    {
        $session = Factory::getSession();
        $this->dir = empty($dir) ? GalleryHelper::$params->image_path : $dir;
        $this->limit = $session->get('gallery-limit', 25);
        $this->input = Factory::getApplication()->input;
        $this->page = 0;
        $this->pages = 1;
    }

    public function prepareFilename($name, $ext)
    {
        $dir = JPATH_ROOT.'/'.$this->dir.'/';
        $name = str_replace('.'.$ext, '', $name);
        $fileName = GalleryHelper::replaceFilename($name);
        $fileName = GalleryHelper::makeSafe($fileName);
        $name = str_replace('-', '', $fileName);
        $name = str_replace('.', '', $name);
        if ($name == '') {
            $fileName = date("Y-m-d-H-i-s").'.'.$ext;
        }
        $i = 2;
        $name = $fileName;
        while (File::exists($dir.$name.'.'.$ext)) {
            $name = $fileName.'-'.($i++);
        }
        $fileName = $name.'.'.$ext;

        return $fileName;
    }

    public function uploadFile()
    {
        $file = $this->input->files->get('file', array(), 'array');
        $response = new \stdClass();
        if (isset($file['error']) && $file['error'] == 0 && ($ext = GalleryHelper::getExt($file['name']))) {
            $dir = JPATH_ROOT.'/'.$this->dir.'/';
            $fileName = $this->prepareFilename($file['name'], $ext);
            if (GalleryHelper::canComress($ext)) {
                $fileName = GalleryHelper::compressImage($file['tmp_name'], $dir, $fileName, $ext, false);
                $ext = GalleryHelper::getExt($fileName);
            } else {
                File::upload($file['tmp_name'], $dir.$fileName);
            }
            $response = $this->getImageObject($this->dir, $ext, $fileName);
        }

        return $response;
    }

    protected function checkYoutubeTumb($id)
    {
        $resolution = 'hqdefault';
        $array = ['maxresdefault', 'sddefault', 'hqdefault'];
        foreach ($array as $img) {
            $url = 'http://img.youtube.com/vi/'.$id.'/'.$img.'.jpg';
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($curl, CURLOPT_TIMEOUT, 80);
            curl_setopt($curl, CURLOPT_HEADER, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $body = curl_exec($curl);
            $header = explode("\n", $body);
            curl_close($curl);
            if (strpos($header[0], '200') !== false) {
                $resolution = $img;
                break;
            }
        }

        return $resolution;
    }

    public function uploadVideoImage()
    {
        $id = $this->input->get('id', '', 'string');
        $type = $this->input->get('type', '', 'string');
        $ext = 'jpg';
        if ($type == 'youtube') {
            $resolution = $this->checkYoutubeTumb($id);
            $url = 'http://img.youtube.com/vi/'.$id.'/'.$resolution.'.jpg';
        } else {
            $url = 'https://vumbnail.com/'.$id.'.jpg';
        }
        $fileName = $this->prepareFilename($id, $ext);
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($curl, CURLOPT_TIMEOUT, 80);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $body = curl_exec($curl);
        $dir = JPATH_ROOT.'/'.$this->dir.'/';
        if (GalleryHelper::canComress($ext)) {
            $fileName = GalleryHelper::compressImage($body, $dir, $fileName, $ext);
            $ext = GalleryHelper::getExt($fileName);
        } else {
            $file = fopen($dir.$fileName, 'wb');
            fwrite($file, $body);
            fclose($file);
        }
        curl_close($curl);
        $response = $this->getImageObject($this->dir, $ext, $fileName);

        return $response;
    }

    public function multipleMove()
    {
        $array = $this->input->get('array', array(), 'array');
        foreach ($array as $path) {
            $name = basename($path);
            if (is_dir(JPATH_ROOT.'/'.$path)) {
                Folder::move(JPATH_ROOT.'/'.$path, JPATH_ROOT.'/'.$this->dir.'/'.$name);
            } else {
                File::move(JPATH_ROOT.'/'.$path, JPATH_ROOT.'/'.$this->dir.'/'.$name);
            }
        }
        $response = $this->setTree();
        
        return $response;
    }

    public function multipleDelete()
    {
        $array = $this->input->get('array', array(), 'array');
        foreach ($array as $path) {
            if (is_dir(JPATH_ROOT.'/'.$path)) {
                GalleryHelper::deleteFolder(JPATH_ROOT.'/'.$path);
            } else {
                unlink(JPATH_ROOT.'/'.$path);
            }
        }
        $response = $this->setTree();
        
        return $response;
    }

    public function contextDelete()
    {
        if (is_dir(JPATH_ROOT.'/'.$this->dir)) {
            GalleryHelper::deleteFolder(JPATH_ROOT.'/'.$this->dir);
        } else {
            unlink(JPATH_ROOT.'/'.$this->dir);
        }
        $response = $this->setTree();
        
        return $response;
    }

    public function createFolder()
    {
        $name = $this->input->get('name', '', 'string');
        mkdir(JPATH_ROOT.'/'.$this->dir.'/'.$name, 0755);
        $response = $this->setTree();
        
        return $response;
    }

    public function setTree()
    {
        $this->dir = GalleryHelper::$params->image_path;
        $response = new \stdClass();
        $response->tree = $this->getFoldersTree();
        
        return $response;
    }

    public function rename()
    {
        $name = $this->input->get('name', '', 'string');
        if (file_exists(JPATH_ROOT.'/'.$this->dir)) {
            rename(JPATH_ROOT.'/'.$this->dir, JPATH_ROOT.'/'.$name);
        }
        
        return [];
    }

    public function loadFolder()
    {
        $response = new \stdClass();
        $response->breadcrumb = $this->getbreadcrumb();
        $response->table = $this->getItemsTable();
        $response->paginator = $this->getPaginator();

        return $response;
    }

    public function setPage()
    {
        $this->page = $this->input->get('page', 0, 'int');
        $this->search = $this->input->get('search', '', 'string');
        $response = $this->loadFolder();

        return $response;
    }

    public function setLimit()
    {
        $limit = $this->input->get('limit', 1, 'int');
        $session = Factory::getSession();
        $session->set('gallery-limit', $limit);
        $this->limit = $limit;
        $response = $this->setPage();

        return $response;
    }

    public function getFileSize($size)
    {
        $size = floor($size / 1024);
        if ($size >= 1024) {
            $size = floor($size / 1024);
            $filesize = (string)$size .' MB';
        } else {
            $filesize = (string)$size .' KB';
        }

        return $filesize;
    }

    public function searchItems($directory)
    {
        $dir = JPATH_ROOT.'/'.$directory.'/';
        $files = scandir($dir);
        $data = new \stdClass();
        $data->folders = [];
        $data->images = [];
        foreach ($files as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $ext = GalleryHelper::getExt($dir.$file);
            $isDir = is_dir($dir.$file);
            if ($isDir && strpos($file, $this->search) !== false) {
                $data->folders[] = $this->getFolderObject($directory, $file);
            } else if (!$isDir && GalleryHelper::checkExt($ext) && strpos($file, $this->search) !== false) {
                $data->images[] = $this->getImageObject($directory, $ext, $file);
            }
            if ($isDir) {
                $object = $this->searchItems($directory.'/'.$file);
                $data->folders = array_merge($data->folders, $object->folders);
                $data->images = array_merge($data->images, $object->images);
            }
        }
        
        return $data;
    }

    public function getFolderObject($dir, $file)
    {
        $folder = new \stdClass();
        $folder->path = $dir.'/'.$file;
        $folder->name = $file;

        return $folder;
    }

    public function getImageObject($dir, $ext, $file)
    {
        $image = new \stdClass;
        $image->ext = $ext;
        $image->name = $file;
        $image->folder = $dir.'/';
        $image->path = $dir.'/'.$file;
        $image->url = $image->path;
        $image->size = filesize(JPATH_ROOT.'/'.$image->path);

        return $image;
    }

    public function scanDirectory()
    {
        $dir = JPATH_ROOT.'/'.$this->dir.'/';
        $files = scandir($dir);
        $data = new \stdClass();
        $data->folders = [];
        $data->images = [];
        foreach ($files as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $ext = GalleryHelper::getExt($dir.$file);
            if (is_dir($dir.$file)) {
                $data->folders[] = $this->getFolderObject($this->dir, $file);
            } else if (GalleryHelper::checkExt($ext)) {
                $data->images[] = $this->getImageObject($this->dir, $ext, $file);
            }
        }

        return $data;
    }

    public function getItems()
    {
        if (!empty($this->search)) {
            $data = $this->searchItems($this->dir);
        } else {
            $data = $this->scanDirectory();
        }
        $items = array_merge($data->folders, $data->images);
        
        return $items;
    }

    public function getFolders($dir = '')
    {
        if (empty($dir)) {
            $dir = $this->dir;
        }
        $files = scandir(JPATH_ROOT.'/'.$dir);
        $items = array();
        foreach ($files as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $path = $dir.'/'.$file;
            if (is_dir(JPATH_ROOT.'/'.$path)) {
                $folder = new \stdClass();
                $folder->path = $path;
                $folder->name = $file;
                $folder->childs = $this->getFolders($path);
                $items[] = $folder;
            }
        }

        return $items;
    }

    public function getItemsTable()
    {
        $items = $this->getItems();
        if ($this->limit != 1) {
            $this->pages = ceil(count($items) / $this->limit);
            $items = array_slice($items, $this->page * $this->limit, $this->limit);
        }
        include JPATH_COMPONENT.'/tmpl/layout/uploader/table.php';

        return $out;
    }

    public function getPaginator()
    {
        include JPATH_COMPONENT.'/tmpl/layout/uploader/paginator.php';

        return $out;
    }

    public function getFoldersTree($folders = null)
    {
        if (!$folders) {
            $folders = $this->getFolders();
        }
        include JPATH_COMPONENT.'/tmpl/layout/uploader/folders-tree.php';

        return $out;
    }

    public function getbreadcrumb()
    {
        $folders = explode('/', $this->dir);
        $parts = [];
        $n = count($folders) - 1;
        include JPATH_COMPONENT.'/tmpl/layout/uploader/breadcrumb.php';

        return $out;
    }
}