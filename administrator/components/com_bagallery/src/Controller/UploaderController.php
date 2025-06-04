<?php
/**
* @package   BaGallery
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

namespace Balbooa\Component\Gallery\Administrator\Controller;

use Balbooa\Component\Gallery\Administrator\Helper\GalleryHelper;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\MVC\Controller\FormController;

defined('_JEXEC') or die;

class UploaderController extends FormController
{
    protected $option = 'com_bagallery';
    
    public function executeAction()
    {
        $action = $this->input->get('action', '', 'string');
        $path = $this->input->get('path', '', 'raw');
        /** @var BaBalbooa\Component\Gallery\Administrator\Model */
        $model = $this->getModel();
        $uploader = $model->getUploader($path);
        $response = call_user_func(array($uploader, $action));
        $str = json_encode($response);
        print_r($str);exit();
    }

    public function regenerateThumbnails()
    {
        $id = $this->input->get('id', 0, 'int');
        $dir = GalleryHelper::$thumbnails_base.'/bagallery/gallery-'.$id.'/thumbnail';
        if (Folder::exists($dir)) {
            GalleryHelper::deleteFolder($dir);
        }
        $dir = GalleryHelper::$thumbnails_base.'/bagallery/gallery-'.$id.'/album';
        if (Folder::exists($dir)) {
            GalleryHelper::deleteFolder($dir);
        }
        exit();
    }

    public function checkOriginalFolder()
    {
        $dir = GalleryHelper::$thumbnails_base.'/bagallery';
        if (!Folder::exists($dir)) {
            Folder::create($dir);
        }
        $dir .= '/original';
        if (!Folder::exists($dir)) {
            Folder::create($dir);
        }
    }

    public function getVideoImage()
    {
        /** @var BaBalbooa\Component\Gallery\Administrator\Model */
        $model = $this->getModel();
        $this->checkOriginalFolder();
        $path = GalleryHelper::$params->file_path.'/bagallery/original';
        $uploader = $model->getUploader($path);
        $response = $uploader->uploadVideoImage();
        $str = json_encode($response);
        print_r($str);
        exit;
    }

    public function uploadOriginal()
    {
        $this->checkOriginalFolder();
        $path = GalleryHelper::$params->file_path.'/bagallery/original';
        /** @var BaBalbooa\Component\Gallery\Administrator\Model */
        $model = $this->getModel();
        $uploader = $model->getUploader($path);
        $response = $uploader->uploadFile();
        $str = json_encode($response);
        print_r($str);exit();
    }

    public function checkFileExists()
    {
        $content = file_get_contents('php://input');
        $obj = json_decode($content);
        $name = $obj->title;
        $file = GalleryHelper::replace($name);
        $file = File::makeSafe($file.'.'.$obj->ext);
        $name = str_replace('-', '', $file);
        $name = str_replace($obj->ext, '', $name);
        $name = str_replace('.', '', $name);
        if ($name == '') {
            $file = date("Y-m-d-H-i-s").'.'.$obj->ext;
        }
        $obj->path = str_replace($obj->name, '', $obj->path).$file;
        echo File::exists(JPATH_ROOT.$obj->path);exit;
    }

    public function savePhotoEditorImage()
    {
        $content = file_get_contents('php://input');
        $obj = json_decode($content);
        if (isset($obj->title) && !empty($obj->title)) {
            $name = $obj->title;
            $file = GalleryHelper::replace($name);
            $file = File::makeSafe($file.'.'.$obj->ext);
            $name = str_replace('-', '', $file);
            $name = str_replace($obj->ext, '', $name);
            $name = str_replace('.', '', $name);
            if ($name == '') {
                $file = date("Y-m-d-H-i-s").'.'.$obj->ext;
            }
            $obj->path = str_replace($obj->name, '', $obj->path).$file;
        }
        if (strpos($obj->path, '/') != 0) {
            $obj->path = '/'.$obj->path;
        }
        $data = explode(',', $obj->image);
        $method = $obj->method;
        $str = $method($data[1]);
        if ($obj->ext == 'png') {
            $imageSave = GalleryHelper::imageSave($obj->ext);
            $imageCreate = GalleryHelper::imageCreate($obj->ext);
            $img = imagecreatefromstring($str);
            $width = imagesx($img);
            $height = imagesy($img);
            $out = imagecreatetruecolor($width, $height);
            imagealphablending($out, false);
            imagesavealpha($out, true);
            $transparent = imagecolorallocatealpha($out, 255, 255, 255, 127);
            imagefilledrectangle($out, 0, 0, $width, $height, $transparent);          
            imagecopyresampled($out, $img, 0, 0, 0, 0, $width, $height, $width, $height);
            $imageSave($out, JPATH_ROOT.$obj->path, 9);
        } else {
            File::write(JPATH_ROOT.$obj->path, $str);
        }
        echo JPATH_ROOT.$obj->path;
        exit();
    }

    public function showImage()
    {
        $dir = urldecode($_GET['image']);
        $dir = JPATH_ROOT.'/'.$dir;
        $ext = strtolower(File::getExt($dir));
        $imageCreate = GalleryHelper::imageCreate($ext);
        $imageSave = GalleryHelper::imageSave($ext);
        header("Content-type: image/".$ext);
        $offset = 60 * 60 * 24 * 90;
        $ExpStr = "Expires: " . gmdate("D, d M Y H:i:s", time() + $offset) . " GMT";
        header($ExpStr);
        if (!$img = $imageCreate($dir)) {
            $f = fopen($dir, "r");
            fpassthru($f);
        } else {
            $width = imagesx($img);
            $height = imagesy($img);
            $ratio = $width / $height;
            if ($width > $height) {
                $w = 100;
                $h = round(100 / $ratio);
            } else {
                $h = 100;
                $w = round(100 * $ratio);
            }
            $out = imagecreatetruecolor($w, $h);
            if ($ext == 'png' || $ext == 'webp') {
                imagealphablending($out, false);
                imagesavealpha($out, true);
                $transparent = imagecolorallocatealpha($out, 255, 255, 255, 127);
                imagefilledrectangle($out, 0, 0, $w, $h, $transparent);
            }
            imagecopyresampled($out, $img, 0, 0, 0, 0, $w, $h, $width, $height);
            $imageSave($out);
            imagedestroy($img);
            imagedestroy($out);
        }
        exit;
    }
}