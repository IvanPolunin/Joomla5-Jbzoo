<?php
/**
* @package   BaGallery
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

namespace Balbooa\Component\Gallery\Administrator\Helper;

use Balbooa\Component\Gallery\Site\Trait\CompatibilityTrait;
use Balbooa\Component\Gallery\Site\Trait\ParamsTrait;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

if (!function_exists('mb_strtolower')) {
    function mb_strtolower($str, $encoding = 'utf-8')
    {
        return strtolower($str);
    }
}

abstract class GalleryHelper 
{
    use CompatibilityTrait;
    use ParamsTrait;

    public static function readFile(string $path):string
    {
        $handle = fopen($path, "r");
        $content = fread($handle, filesize($path));
        fclose($handle);

        return $content;
    }
    
    public static function replaceLongPath($path)
    {
        $image_path = self::$params->image_path;
        $pos = strpos($path, '/'.$image_path.'/');
        $pos1 = strpos($path, $image_path.'/');
        if ($pos != 0 && $pos1 != 0) {
            $path = substr($path, $pos);
        }

        return $path;
    }

    public static function getExt($file)
    {
        $dot = strrpos($file, '.');
        if ($dot === false) {
            return '';
        }
        $ext = substr($file, $dot + 1);
        if (strpos($ext, '/') !== false) {
            return '';
        }

        return strtolower($ext);
    }

    public static function checkFileName($dir, $name)
    {
        $file = $dir.$name;
        if (File::exists($file)) {
            $name = rand(0, 10).'-'.$name;
            $name = self::checkFileName($dir, $name);
        }
        return $name;
    }

    public static function makeSafe($file)
    {
        $file = rtrim($file, '.');
        if (function_exists('transliterator_transliterate') && function_exists('iconv')) {
            $file = iconv("UTF-8", "ASCII//TRANSLIT//IGNORE", transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $file));
        }
        $regex = array('#(\.){2,}#', '#[^A-Za-z0-9\.\_\- ]#', '#^\.#');

        return trim(preg_replace($regex, '', $file));
    }

    public static function canComress($ext)
    {
        return self::$params->compress_images == 1 && in_array($ext, self::$params->compress_ext);
    }

    public static function compressImage($source, $dir, $file, $ext, $string = true)
    {
        $endExt = $ext;
        $gd_info = gd_info();
        if (self::$params->compress_to_webp == 1 && $gd_info['WebP Support']) {
            $name = basename($file);
            $name = File::stripExt($name);
            $file = str_replace($name.'.'.$ext, $name.'.webp', $file);
            $file = self::checkFileName($dir, $file);
            $endExt = 'webp';
        }
        $path = $dir.$file;
        $imageSave = self::imageSave($endExt);
        if ($string) {
            $img = imagecreatefromstring($source);
        } else {
            $imageCreate = self::imageCreate($ext);
            $img = $imageCreate($source);
        }
        $img = self::checkExif($source, $file, $string, $img, $ext);
        $width = imagesx($img);
        $height = imagesy($img);
        $size = self::$params->compress_size;
        $quality = self::$params->compress_quality;
        $quality = $quality > 100 ? 100 : $quality;
        if ($width <= $size && $height <= $size) {
            $w = $width;
            $h = $height;
        } else {
            $ratio = $width / $height;
            if ($width > $height) {
                $w = $size;
                $h = $size / $ratio;
            } else {
                $h = $size;
                $w = $size * $ratio;
            }
        }
        $out = imagecreatetruecolor($w, $h);
        if ($ext == 'png' || $ext == 'webp') {
            imagealphablending($out, false);
            imagesavealpha($out, true);
            $transparent = imagecolorallocatealpha($out, 255, 255, 255, 127);
            imagefilledrectangle($out, 0, 0, $w, $h, $transparent);
        }
        imagecopyresampled($out, $img, 0, 0, 0, 0, $w, $h, $width, $height);
        if ($endExt == 'png') {
            $quality = 9 - round($quality / 11.111111111111);
        }
        $imageSave($out, $path, $quality);
        imagedestroy($out);
        imagedestroy($img);

        return $file;
    }

    public static function checkExif($source, $file, $string, $img, $ext)
    {
        if (($ext == 'jpg' || $ext == 'jpeg') && function_exists('exif_read_data')) {
            $path = JPATH_ROOT.'/tmp/'.$file;
            if ($string) {
                file_put_contents($path, $source);
            } else {
                File::upload($source, $path);
            }
            $exif = @exif_read_data($path);
            if (!empty($exif['Orientation'])) {
                switch ($exif['Orientation']) {
                    case 3:
                        $img = imagerotate($img, 180, 0);
                        break;
                    case 6:
                        $img = imagerotate($img, -90, 0);
                        break;
                    case 8:
                        $img = imagerotate($img, 90, 0);
                        break;
                }
            }
            unlink($path);
        }

        return $img;
    }

    public static function deleteFolder($dir)
    {
        if (is_dir($dir)) { 
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir."/".$object) == "dir") {
                        self::deleteFolder($dir."/".$object);
                    } else {
                        unlink($dir."/".$object);
                    }
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }
    
    public static function cleanup()
    {
        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.file');
        $dir = self::$thumbnails_base.'/bagallery/original/';
        $db = Factory::getDbo();
        if (Folder::exists($dir)) {
            $images = Folder::files($dir);
            foreach ($images as $image) {
                $name = '%bagallery/original/'.$image;
                $name1 = '%bagallery/original//'.$image;
                $query = $db->getQuery(true);
                $query->select('COUNT(id)')
                    ->from('`#__bagallery_items`')
                    ->where('`path` like '.$db->quote($name).' OR `path` like '.$db->quote($name1));
                $db->setQuery($query);
                $count = $db->loadResult();
                if ($count == 0) {
                    $name .= '%';
                    $query = $db->getQuery(true);
                    $query->select('COUNT(id)')
                        ->from('`#__bagallery_category`')
                        ->where('`settings` like '.$db->quote($name).' OR `settings` like '.$db->quote($name1));
                    $db->setQuery($query);
                    $count = $db->loadResult();
                    if ($count == 0) {
                        File::delete($dir.$image);
                    }
                }
            }
        }
    }

    public static function getGalleryLanguage()
    {
        $result = array();
        $path = JPATH_ROOT.'/administrator/components/com_bagallery/language/en-GB/en-GB.com_bagallery.ini';
        if (File::exists($path)) {
            $handle = fopen($path, "r");
            $contents = fread($handle, filesize($path));
            $contents = str_replace('_QQ_', '"\""', $contents);
            $data = parse_ini_string($contents);
            foreach ($data as $ind => $value) {
                $result[$ind] = Text::_($ind);
            }
        }
        $data = 'var galleryLanguage = '.json_encode($result).';';

        return $data;
    }

    public static function checkExt($ext)
    {
        switch($ext) {
            case 'jpg':
            case 'png':
            case 'gif':
            case 'jpeg':
            case 'webp':
                return true;
            default:
                return false;
        }
    }

    public static function imageSave($type) {
        switch ($type) {
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

    public static function imageCreate($type) {
        switch ($type) {
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

    public static function aboutUs()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select("manifest_cache");
        $query->from("#__extensions");
        $query->where("type=" .$db->quote('component'))
            ->where('element=' .$db->quote('com_bagallery'));
        $db->setQuery($query);
        $cache = $db->loadResult();
        $about = json_decode($cache);
        $xml = simplexml_load_file(JPATH_ROOT.'/administrator/components/com_bagallery/bagallery.xml');
        $about->tag = (string)$xml->tag;

        return $about;
    }

    public static function getJoomlaCheckboxes($name, $form)
    {
        $input = $form->getField($name);
        $value = $form->getValue($name);
        if ($value === null) {
            $value = $form->getFieldAttribute($name, 'default');
        }
        $class = !empty($input->class) ? ' class="' . $input->class . '"' : '';
        $checked = $input->checked || $value == 1 ? ' checked' : '';
        
        return '<input type="checkbox" name="'.$input->name.'" id="'.$input->id.'" value="1"'.$class.$checked.'>';
    }

    public static function getAccess()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('id, title')
            ->from('#__viewlevels')
            ->order($db->quoteName('ordering') . ' ASC')
            ->order($db->quoteName('title') . ' ASC');
        $db->setQuery($query);
        $array = $db->loadObjectList();
        $access = array();
        foreach ($array as $value) {
            $access[$value->id] = $value->title;
        }

        return $access;
    }

    public static function checkGalleryState()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('`key`')
            ->from('#__bagallery_api')
            ->where('service = '.$db->quote('balbooa'));
        $db->setQuery($query);
        $balbooa = $db->loadResult();
        if (empty($balbooa)) {
            $obj = new \stdClass();
            $obj->key = $balbooa = '{}';
            $obj->service = 'balbooa';
            $db->insertObject('#__bagallery_api', $obj);
            $obj = new \stdClass();
            $obj->key = $balbooa = '{}';
            $obj->service = 'balbooa_activation';
            $db->insertObject('#__bagallery_api', $obj);
        }

        return $balbooa;
    }

    public static function checkGalleryActivation()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('`key`')
            ->from('#__bagallery_api')
            ->where('service = '.$db->quote('balbooa_activation'));
        $db->setQuery($query);
        $balbooa = $db->loadResult();

        return $balbooa;
    }

    public static function setAppLicense($data)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__bagallery_api')
            ->where('service = '.$db->quote('balbooa'));
        $db->setQuery($query);
        $balbooa = $db->loadObject();
        $balbooa->key = json_decode($balbooa->key);
        $balbooa->key->data = $data;
        $balbooa->key = json_encode($balbooa->key);
        $db->updateObject('#__bagallery_api', $balbooa, 'id');
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__bagallery_api')
            ->where('service = '.$db->quote('balbooa_activation'));
        $db->setQuery($query);
        $balbooa = $db->loadObject();
        $balbooa->key = '{"data":"active"}';
        $db->updateObject('#__bagallery_api', $balbooa, 'id');
    }
    
    public static function getContentsCurl($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_URL, $url);
        $data = curl_exec($ch);
        curl_close($ch);
        
        return $data;
    }

    public static function replaceFilename($str)
    {
        $search = array('?', '!', '.', ',', ':', ';', '*', '(', ')', '{', '}', '***91;', '&', '<', '>',
            '***93;', '%', '#', '№', '@', '$', '^', '-', '+', '/', '\\', '=','|', '"', '\'');
        $str = str_replace($search, ' ', $str);
        $str = preg_replace('/\s+/', ' ', $str);
        $search = array('а', 'б', 'в', 'г', 'д', 'е', 'ё', 'з', 'и', 'й',
            'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ъ',
            'ы', 'э', ' ', 'ж', 'ц', 'ч', 'ш', 'щ', 'ь', 'ю', 'я', 'А', 'Б',
            'В', 'Г', 'Д', 'Е', 'Ё', 'З', 'И', 'Й',
            'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Ч', 'Ъ',
            'Ы', 'Э', 'Ж', 'Ц', 'Ч', 'Ш', 'Щ', 'Ь', 'Ю', 'Я');
        $replace = array('a', 'b', 'v', 'g', 'd', 'e', 'e', 'z', 'i', 'y', 'k', 'l', 'm', 'n',
            'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'j', 'i', 'e', '-', 'zh', 'ts',
            'ch', 'sh', 'shch', '', 'yu', 'ya', 'A', 'B', 'V', 'G', 'D', 'E', 'E', 'Z', 'I', 'Y', 'K', 'L', 'M', 'N',
            'O', 'P', 'R', 'S', 'T', 'U', 'F', 'H', 'J', 'I', 'E', 'Zh', 'Ts',
            'Ch', 'Sh', 'Shch', '', 'Yu', 'Ya');
        $str = str_replace($search, $replace, $str);
        $str = trim($str);
        $str = preg_replace("/_{2,}/", "-", $str);

        return $str;
    }

    public static function replace($str)
    {
        $str = mb_strtolower($str, 'utf-8');
        $search = array('?', '!', '.', ',', ':', ';', '*', '(', ')', '{', '}', '***91;',
            '***93;', '%', '#', '№', '@', '$', '^', '-', '+', '/', '\\', '=',
            '|', '"', '\'', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'з', 'и', 'й',
            'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ъ',
            'ы', 'э', ' ', 'ж', 'ц', 'ч', 'ш', 'щ', 'ь', 'ю', 'я');
        $replace = array('-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-',
            '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-',
            'a', 'b', 'v', 'g', 'd', 'e', 'e', 'z', 'i', 'y', 'k', 'l', 'm', 'n',
            'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'j', 'i', 'e', '-', 'zh', 'ts',
            'ch', 'sh', 'shch', '', 'yu', 'ya');
        $str = str_replace($search, $replace, $str);
        $str = trim($str);
        $str = preg_replace("/_{2,}/", "-", $str);

        return $str;
    }

    public static function increment($string)
    {
        if (preg_match('#\((\d+)\)$#', $string, $matches)) {
            $n = $matches[1] + 1;
            $string = preg_replace('#\(\d+\)$#', sprintf('(%d)', $n), $string);
        } else {
            $n = 2;
            $string .= sprintf(' (%d)', $n);
        }

        return $string;
    }

    public static function getAlias($alias, $table, $name = 'lightboxUrl', $id = 0)
    {
        jimport('joomla.filter.output');
        $alias = self::replace($alias);
        $alias = OutputFilter::stringURLSafe($alias);
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('id')
            ->from($table)
            ->where($db->quoteName($name).' = '.$db->Quote($alias))
            ->where('`id` <> ' .$db->Quote($id));
        $db->setQuery($query);
        $id = $db->loadResult();
        if (!empty($id)) {
            $alias = self::increment($alias);
            $alias = self::getAlias($alias, $table, $name);
        }
        return $alias;
    }
}

GalleryHelper::prepareParams();