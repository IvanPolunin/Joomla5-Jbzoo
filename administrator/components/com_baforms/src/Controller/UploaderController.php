<?php
/**
* @package   BaForms
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

namespace Balbooa\Component\Forms\Administrator\Controller;

defined('_JEXEC') or die;

use Balbooa\Component\Forms\Administrator\Helper\BaformsHelper;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Helper\MediaHelper;
use Joomla\CMS\Response\JsonResponse;

class UploaderController extends FormController
{
    public function checkFileExists()
    {
        BaformsHelper::checkUserEditLevel();
        $content = file_get_contents('php://input');
        $obj = json_decode($content);
        $name = $obj->title;
        $file = baformsHelper::replace($name);
        $file = File::makeSafe($file.'.'.$obj->ext);
        $name = str_replace('-', '', $file);
        $name = str_replace($obj->ext, '', $name);
        $name = str_replace('.', '', $name);
        if ($name == '') {
            $file = date("Y-m-d-H-i-s").'.'.$obj->ext;
        }
        $obj->path = str_replace($obj->name, '', $obj->path).$file;
        echo File::exists(JPATH_ROOT.'/'.BaformsHelper::$image_path.$obj->path);exit;
    }

    public function savePhotoEditorImage()
    {
        baformsHelper::checkUserEditLevel();
        $content = file_get_contents('php://input');
        $obj = json_decode($content);
        if (isset($obj->title)) {
            $name = $obj->title;
            $file = baformsHelper::replace($name);
            $file = File::makeSafe($file.'.'.$obj->ext);
            $name = str_replace('-', '', $file);
            $name = str_replace($obj->ext, '', $name);
            $name = str_replace('.', '', $name);
            if ($name == '') {
                $file = date("Y-m-d-H-i-s").'.'.$obj->ext;
            }
            $obj->path = str_replace($obj->name, '', $obj->path).$file;
        }
        $data = explode(',', $obj->image);
        $method = $obj->method;
        $str = $method($data[1]);
        if ($obj->ext == 'png') {
            $imageSave = $this->imageSave($obj->ext);
            $imageCreate = $this->imageCreate($obj->ext);
            $img = imagecreatefromstring($str);
            $width = imagesx($img);
            $height = imagesy($img);
            $out = imagecreatetruecolor($width, $height);
            imagealphablending($out, false);
            imagesavealpha($out, true);
            $transparent = imagecolorallocatealpha($out, 255, 255, 255, 127);
            imagefilledrectangle($out, 0, 0, $width, $height, $transparent);          
            imagecopyresampled($out, $img, 0, 0, 0, 0, $width, $height, $width, $height);
            $imageSave($out, JPATH_ROOT.'/'.BaformsHelper::$image_path.$obj->path, 9);
        } else {
            File::write(JPATH_ROOT.'/'.BaformsHelper::$image_path.$obj->path, $str);
        }
        echo JPATH_ROOT.'/'.BaformsHelper::$image_path.$obj->path;
        exit();
    }
    
    public function moveTo()
    {
        baformsHelper::checkUserEditLevel();
        $input = Factory::getApplication()->input;
        $image = $input->get('ba_image', '', 'string');
        $folder = $input->get('ba_folder', '', 'string');
        $file = JPATH_ROOT. '/'.BaformsHelper::$image_path.$image;
        $target = JPATH_ROOT. '/'.BaformsHelper::$image_path.$folder;
        if (Folder::exists($file)) {
            $name = explode('/', $file);
            $name = end($name);
            Folder::move($file, $target.'/'.$name);
        } else if (File::exists($file)) {
            $name = basename($file);
            File::move($file, $target.'/'.$name);
        }        
        echo new JsonResponse(true, Text::_('SUCCESS_MOVED'));
        jexit();
    }

    public function moveTarget()
    {
        baformsHelper::checkUserEditLevel();
        $input = Factory::getApplication()->input;
        $ba_target = $input->get('ba_target', '', 'string');
        $ba_path = $input->get('ba_path', '', 'string');
        $target = JPATH_ROOT. '/'.BaformsHelper::$image_path.$ba_target;
        $path = JPATH_ROOT. '/'.BaformsHelper::$image_path.$ba_path;
        if ($ba_path == JPATH_ROOT.'/'.BaformsHelper::$image_path) {
            $path = JPATH_ROOT.'/'.BaformsHelper::$image_path;
        }
        $flag = $input->get('ba_flag', '', 'string');
        if ((bool)$flag == false) {
            if (!empty($target)) {
                if (Folder::exists($target)) {
                    $name = explode('/', $target);
                    $name = end($name);
                    Folder::move($target, $path.'/'.$name);
                } else if (File::exists($target)) {
                    $name = basename($target);
                    File::move($target, $path.'/'.$name);
                }
            }
        } else {
            $target = explode(';', $target);
            foreach ($target as $key => $item) {
                if (!empty($item)) {
                    if (Folder::exists($item)) {
                        $name = explode('/', $item);
                        $name = end($name);
                        Folder::move($item, $path.'/'.$name);
                    } else if (File::exists($item)) {
                        $name = basename($item);
                        File::move($item, $path.'/'.$name);
                    }
                }
            }
        }
        echo new JsonResponse($path.'/'.$name, Text::_('SUCCESS_MOVED'));
        jexit();
    }

    public function renameTarget()
    {
        baformsHelper::checkUserEditLevel();
        $input = Factory::getApplication()->input;
        $ba_target = $input->get('ba_target', '', 'string');
        $target = JPATH_ROOT. '/'.BaformsHelper::$image_path.$ba_target;
        $name = $input->get('ba_name', '', 'string');
        $name = str_replace(' ', '-', $name);
        $dir = explode('/', $target);
        $n = count($dir) - 1;
        unset($dir[$n]);
        $dir = implode('/', $dir);
        if (!empty($target)) {
            if (Folder::exists($target)) {
                Folder::move($target, $dir.'/'.$name);
            } else if (File::exists($target)) {
                $ext = File::getExt($target);
                $name .= '.'.$ext;
                File::move($target, $dir.'/'.$name);
            }
        }
        echo new JsonResponse($dir.'/'.$name, Text::_('SUCCESS_RENAME'));
        jexit();
    }

    public function deleteTarget()
    {
        baformsHelper::checkUserEditLevel();
        $input = Factory::getApplication()->input;
        $ba_target = $input->get('ba_target', '', 'string');
        $target = JPATH_ROOT. '/'.BaformsHelper::$image_path.$ba_target;
        $result = Text::_('COM_BAFORMS_N_ITEMS_DELETED');
        $flag = true;
        if (!empty($target)) {
            if (Folder::exists($target)) {
                baformsHelper::deleteFolder($target);
            } else if (File::exists($target)) {
                File::delete($target);
            }
        }
        echo new JsonResponse($flag, $result);
        jexit();
    }

    public function addFolder()
    {
        baformsHelper::checkUserEditLevel();
        $location = $this->getDir();
        $dir = $location[0];
        $input = Factory::getApplication()->input;
        $nfolder = $input->get('new-folder', '', 'string');
        $nfolder = str_replace(' ', '-', $nfolder);
        if (Folder::create($dir.'/'.$nfolder)) {
            $result = Text::_('FOLDER_IS_CREATED');
        } else {
            $result = Text::_('FOLDER_IS_NOT_CREATED');
        }
        echo '<input type="hidden" id="ba-message-data" value="'.$result.'">';
        ?>
            <script type="text/javascript">
                var msg = document.getElementById("ba-message-data").value;
                window.parent.postMessage(msg, "*");
            </script>

        <?php
        exit;
    }
    
    public function getDir()
    {
        baformsHelper::checkUserEditLevel();
        $redirect = 'index.php?option=com_baforms&view=uploader&tmpl=component';
        $dir = JPATH_ROOT. '/'.BaformsHelper::$image_path;
        $input = Factory::getApplication()->input;
        $folder = $input->get('current-dir', '', 'string');
        if (!empty($folder)) {
            $dir = $folder;
            $redirect .= '&folder=' .$dir;
        }
        $array = array ($dir, $redirect);

        return $array;
        
    }
    
    public function delete()
    {
        baformsHelper::checkUserEditLevel();
        $location = $this->getDir();
        $dir = $location[0];
        $redirect = $location[1];
        $input = Factory::getApplication()->input;
        $items = $input->get('ba-rm', '', 'array');
        $result = Text::_('COM_BAFORMS_N_ITEMS_DELETED');
        foreach ($items as $item) {
            if ($item != '') {
                if (Folder::exists($dir. '/' .$item)) {
                    baformsHelper::deleteFolder($dir. '/' .$item);
                }
                if (File::exists($dir. '/' .$item)) {
                    File::delete($dir. '/' .$item);
                }
            }
        }
        echo '<input type="hidden" id="ba-message-data" value="'.$result.'">';
        ?>
            <script type="text/javascript">
                var msg = document.getElementById("ba-message-data").value;
                window.parent.postMessage(msg, "*");
            </script>

        <?php
        exit;
    }

    public function uploadAjax()
    {
        baformsHelper::checkUserEditLevel();
        $input = Factory::getApplication()->input;
        $folder = $input->getVar('folder', '', 'get', 'string');
        $file = $input->getVar('file', '', 'get', 'string');
        $ext = strtolower(File::getExt($file));
        $name = str_replace('.'.$ext, '', $file);
        $file = baformsHelper::replace($name);
        $file = File::makeSafe($file.'.'.$ext);
        $name = str_replace('-', '', $file);
        $name = str_replace($ext, '', $name);
        $name = str_replace('.', '', $name);
        if ($name == '') {
            $file = date("Y-m-d-H-i-s").'.'.$ext;
        }
        if (empty($folder)) {
            $folder = JPATH_ROOT. '/'.BaformsHelper::$image_path;
        }
        $url = Uri::root().BaformsHelper::$image_path;
        /** @var BaBalbooa\Component\Forms\Administrator\Model */
        $model = $this->getModel();
        $curent = str_replace(JPATH_ROOT.'/'.BaformsHelper::$image_path, '', $folder);
        $url .= $curent;
        $types = $model->getFiletypes();
        if (in_array($ext, $types)) {
            file_put_contents(
                $folder. '/'. $file,
                file_get_contents('php://input')
            );
            $image = new \stdClass;
            $image->name = $file;
            $image->path = $curent.'/'.$file;
            $image->size = filesize($folder. '/'. $file);
            $image->ext = $ext;
            $image->url = $url. '/' .$file;
            echo json_encode($image);
        }        
        exit;
    }

    public function formUpload()
    {
        baformsHelper::checkUserEditLevel();
        $input = Factory::getApplication()->input;
        $items = $input->files->get('files', '', 'array');
        $dir = $input->get('current_folder', '', 'string');
        if (empty($dir) || $dir == '/') {
            $dir = JPATH_ROOT. '/'.BaformsHelper::$image_path.'/';
        }
        $mediaHelper = new MediaHelper;
        $uploadMaxFileSize = $mediaHelper->toBytes(ini_get('upload_max_filesize'));
        $url = Uri::root().BaformsHelper::$image_path;
        /** @var BaBalbooa\Component\Forms\Administrator\Model */
        $model = $this->getModel();
        $curent = str_replace(JPATH_ROOT.'/'.BaformsHelper::$image_path, '', $dir);
        $url .= $curent;
        $images = array();
        $types = $model->getFiletypes();
        foreach($items as $item) {
            $flag = true;
            if (($item['error'] == 1) || ($uploadMaxFileSize > 0 && $item['size'] > $uploadMaxFileSize)) {
                $flag = false;
            }
            $ext = strtolower(File::getExt($item['name']));
            if (in_array($ext, $types) && $flag) {
                $name = str_replace('.'.$ext, '', $item['name']);
                $file = baformsHelper::replace($name);
                $file = File::makeSafe($file.'.'.$ext);
                $name = str_replace('-', '', $file);
                $name = str_replace($ext, '', $name);
                $name = str_replace('.', '', $name);
                if ($name == '') {
                    $file = date("Y-m-d-H-i-s").'.'.$ext;
                }
                File::upload($item['tmp_name'], $dir. $file);
                $image = new \stdClass;
                $image->name = $file;
                $image->ext = $ext;
                $image->path = $curent.'/'.$file;
                $image->size = filesize($dir. $file);
                $image->url = $url. '/' .$file;
                $images[] = $image;
            }
        }
        $images = json_encode($images);
?>
    <script type="text/javascript">
        var images = <?php echo $images; ?>;
        window.parent.uploadCallback(images);
    </script>
<?php
    exit();
    }

    public function showImage()
    {
        baformsHelper::checkUserEditLevel();
        $input = Factory::getApplication()->input;
        $image = $input->getVar('image', '', 'get', 'string');
        $dir = JPATH_ROOT. '/'.BaformsHelper::$image_path.$image;
        $ext = strtolower(File::getExt($dir));
        $imageCreate = $this->imageCreate($ext);
        $imageSave = $this->imageSave($ext);
        Header("Content-type: image/".$ext);
        if (!$img = $imageCreate($dir)) {
            $file = fopen($dir, "r");
            fpassthru($file);
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

    public function imageSave($type) {
        switch ($type) {
            case 'jpeg':
                $imageSave = 'imagejpeg';
                break;
            case 'png':
                $imageSave = 'imagepng';
                break;
            case 'gif':
                $imageSave = 'imagegif';
                break;
            case 'webp':
                $imageSave = 'imagewebp';
                break;
            default:
                $imageSave = 'imagejpeg';
        }

        return $imageSave;
    }

    public function imageCreate($type) {
        switch ($type) {
            case 'jpeg':
            case 'jpg':
                $imageCreate = 'imagecreatefromjpeg';
                break;
            case 'png':
                $imageCreate = 'imagecreatefrompng';
                break;
            case 'gif':
                $imageCreate = 'imagecreatefromgif';
                break;
            case 'webp':
                $imageCreate = 'imagecreatefromwebp';
                break;
            default:
                $imageCreate = 'imagecreatefromjpeg';
        }
        return $imageCreate;
    }
}