<?php
/**
* @package   BaGallery
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

namespace Balbooa\Component\Gallery\Site\Helper;

use Balbooa\Component\Gallery\Site\Trait\CompatibilityTrait;
use Balbooa\Component\Gallery\Site\Trait\ParamsTrait;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die;

abstract class GalleryHelper 
{
    protected static $_id = 0;
    protected static $_currentCat = 0;
    protected static $_activeImage = false;
    protected static $_currentAlias = '';
    protected static $_tags = [];
    protected static $_activeTags = [];
    protected static $_colors = [];
    protected static $_activeColors = [];
    protected static $about;

    use CompatibilityTrait;
    use ParamsTrait;

    public static function renderGalleryHTML($body)
    {
        $regex = '/\[gallery ID=+(.*?)\]/i';
        $array = [];
        preg_match_all($regex, $body, $matches, PREG_SET_ORDER);
        if (!$matches) {
            return $body;
        }
        foreach ($matches as $match) {
            $gallery = explode(',', $match[1]);
            $id = $gallery[0];
            $pos = strpos($id, ' category ID');
            if ($pos !== false) {
                $id = substr($id, 0, $pos);
            }
            if (!isset($id) || !self::checkGallery($id)) {
                continue;
            }
            if (!in_array($id, $array)) {
                $array[] = $id;
            }
            $gallery = self::drawHTMLPage($match[1]);
            $body = @preg_replace("|\[gallery ID=".$match[1]."\]|", addcslashes($gallery, '\\$'), $body, 1);
        }
        if (!empty($array)) {
            $body = self::drawScripts($array).$body;
        }

        return $body;
    }

    public static function replaceLongPath($path)
    {
        $image_path = self::$params->image_path;
        $pos = strpos($path, '/'.$image_path.'/');
        $pos1 = strpos($path, $image_path.'/');
        if ($pos != 0 && $pos1 != 0 && strpos($path, 'components/com_bagallery') === false) {
            $path = substr($path, $pos);
        }

        return $path;
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

    public static function readFile(string $path):string
    {
        $handle = fopen($path, "r");
        $content = fread($handle, filesize($path));
        fclose($handle);

        return $content;
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

    public static function addStyle()
    {
        $doc = Factory::getDocument();
        $url = $_SERVER['REQUEST_URI'];
        $url = urldecode($url);
        $url = explode('?', $url);
        $url = end($url);
        $img = false;
        if (!empty($url) && strpos($url, '=') === false) {
            $img = self::checkImage($url);
        }
        if (!$img && is_numeric($url)) {
            $img = self::getImage($url);
        }
        if ($img) {
            $image = JPATH_ROOT.$img->url;
            $title = $doc->getTitle();
            if (!empty($img->title)) {
                $title = $img->title;
            }
            $description = $doc->getDescription();
            if (!empty($img->description)) {
                $description = $img->description;
            }
            if (file_exists($image)) {
                $ext = File::getExt($image);
                $imageCreate = self::imageCreate($ext);
                $obj = $imageCreate($image);
                $width = imagesx($obj);
                $height = imagesy($obj);
                $doc->setMetaData('og:image:width', $width);
                $doc->setMetaData('og:image:height', $height);
            }
            $pos = strpos($_SERVER['REQUEST_URI'], '?');
            $surl = substr($_SERVER['REQUEST_URI'], $pos);
            $doc->setMetaData('og:title', $title);
            $doc->setMetaData('og:type', "article");
            $doc->setMetaData('og:image:url', Uri::root().substr($img->url, 1));
            $doc->setMetaData('og:url', self::prepareURI($doc->getBase().$surl));
            $doc->setMetaData('og:description', strip_tags($description ?? ''));
        }
    }

    public static function loadJQuery($id)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('load_jquery')
            ->from('#__bagallery_galleries')
            ->where('`id` = '.$id);
        $db->setQuery($query);
        $res = $db->loadResult();
        
        return $res;
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
        $about = $db->loadResult();
        $about = json_decode($about);
        $xml = simplexml_load_file(JPATH_ROOT.'/administrator/components/com_bagallery/bagallery.xml');
        $about->tag = (string)$xml->tag;

        return $about;
    }
    
    public static function drawScripts($cid)
    {
        $doc = Factory::getDocument();
        $scripts = $doc->_scripts;
        $array = [];
        $about = self::aboutUs();
        $v = $about->version;
        $html = '';
        $jquery = true;
        foreach ($scripts as $key => $script) {
            $key = explode('/', $key);
            $array[] = end($key);
        }
        $loadedJquery = false;
        foreach ($cid as $id) {
            if ($loadedJquery) {
                break;
            }
            if (!$jquery || self::loadJQuery($id) == 0) {
                
            } else if (!in_array('jquery.min.js', $array) && !in_array('jquery.js', $array)) {
                $src = Uri::root(true) . '/media/' .
                    (JVERSION >= '4.0.0' ? 'vendor/jquery' : 'jui') .
                    '/js/jquery.min.js';
                $html .= '<script src="' .$src. '"></script>';
                $loadedJquery = true;
            }
        }
        $params = ComponentHelper::getParams('com_bagallery');
        $image_path = $params->get('image_path', 'images');
        $html .= '<script>var JUri = "'.Uri::root().'", gallery_image_path="'.$image_path.'"</script>';
        $src = Uri::root(). 'components/com_bagallery/libraries/modal/ba_modal.js?'.$v;
        $html .= '<script src="' .$src. '"></script>';
        $src = Uri::root(). 'components/com_bagallery/assets/css/ba-style.css?'.$v;
        $html .= '<link rel="stylesheet" href="' .$src. '">';
        $src = Uri::root(). 'components/com_bagallery/assets/css/ba-effects.css?'.$v;
        $html .= '<link rel="stylesheet" href="' .$src. '">';
        $src = Uri::root() . 'components/com_bagallery/libraries/ba_isotope/ba_isotope.js?'.$v;
        $html .= '<script src="'.$src.'"></script>';
        $src = Uri::root(). 'components/com_bagallery/libraries/lazyload/jquery.lazyload.min.js?'.$v;
        $html .= '<script src="' .$src. '"></script>';
        $src = Uri::root(). 'components/com_bagallery/assets/js/ba-gallery.js?'.$v;
        $html .= '<script src="' .$src. '"></script>';
        $html .= "\n";
        
        return $html; 
    }

    public static function getThumbnail($id)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('thumbnail_url')
            ->from('#__bagallery_items')
            ->where('`id` = '.$id);
        $db->setQuery($query);
        $res = $db->loadResult();
        $pos = strpos($res, '/images/');
        $res = substr($res, $pos+8);

        return $res;
    }
    
    public static function checkImage($title)
    {
        $title = strtolower($title);
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select("id, url, title, lightboxUrl, form_id")
            ->from("#__bagallery_items")
            ->where('lightboxUrl = '.$db->quote($db->escape($title, true)));
        if (self::$_id != 0) {
            $query->where('form_id = '.self::$_id);
        }
        $db->setQuery($query);
        $urls = $db->loadObjectList();
        $obj = false;
        foreach ($urls as $url) {
            $search = str_replace(' ', '-', $url->lightboxUrl);
            $search = str_replace('%', '', $search);
            $search = str_replace('?', '', $search);
            $search = strtolower($search);
            if ($search == urldecode($title)) {
                $obj = self::getImage($url->id);
                break;
            }
            $search = str_replace(' ', '-', $url->title);
            $search = str_replace('%', '', $search);
            $search = str_replace('?', '', $search);
            $search = strtolower($search);
            if ($search == urldecode($title)) {
                $obj = self::getImage($url->id);
                break;
            }
        }
        
        return $obj;
    }
    
    public static function getImage($id)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select("form_id, url, watermark_name, id, title, imageId, category, description")
            ->from("#__bagallery_items")
            ->where("id=" . $id);
        if (self::$_id != 0) {
            $query->where('form_id = '.self::$_id);
        }
        $db->setQuery($query);
        $obj = $db->loadObject();
        if (empty($obj)) {
            return false;
        }
        $watermark = self::getWatermark($obj->form_id);
        $compression = self::getCompression($obj->form_id);
        $obj->url = self::replaceLongPath($obj->url);
        if (strpos($obj->url, '/') !== 0) {
            $obj->url = '/'.$obj->url;
        }
        if ($compression->enable_compression == 1) {
            $obj->url = '/images/bagallery/gallery-' .$obj->form_id.'/compression/'.$obj->watermark_name;
        } else if (!empty($watermark->watermark_upload)) {
            $obj->url = '/images/bagallery/gallery-' .$obj->form_id.'/watermark/'.$obj->watermark_name;
        }
        if (!empty($obj->url)) {
            return $obj;
        } else {
            return false;
        }
    }
    
    public static function checkGallery($id)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select("published");
        $query->from("#__bagallery_galleries");
        $query->where("id=" . $id);
        $db->setQuery($query);
        $publish = $db->loadResult();
        if (isset($publish)) {
            if ($publish == 1) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public static function checkShare($lightbox)
    {
        if ($lightbox->twitter_share == 1 || $lightbox->pinterest_share == 1 || $lightbox->facebook_share == 1
            || $lightbox->twitter_share == 1 || $lightbox->linkedin_share == 1
            || $lightbox->vkontakte_share == 1 || $lightbox->odnoklassniki_share == 1) {
            return true;
        } else {
            return false;
        }
    }

    public static function getAlbumAlias($category, $aliasMap, $parentMap)
    {
        $alias = $aliasMap[$category];
        $alias = strtolower($alias);
        $alias = str_replace(' ', '-', $alias);
        $alias = str_replace('%', '', $alias);
        $alias = str_replace('?', '', $alias);
        if (isset($parentMap[$category])) {
            $parent = str_replace('category-', '', $parentMap[$category]);
            if (isset($aliasMap[$parent])) {
                $alias = self::getAlbumAlias($parent, $aliasMap, $parentMap).'&'.$alias;
            }
        }
        
        return $alias;
    }

    public static function getPassword($catPasswords, $parentMap, $value)
    {
        if (isset($catPasswords[$value])) {
            return $catPasswords[$value];
        }
        if (isset($parentMap[$value])) {
            $cat = str_replace('category-', '', $parentMap[$value]);
        } else {
            $cat = '';
        }
        if (!empty($cat)) {
            $password = self::getPassword($catPasswords, $parentMap, $cat);

            return $password;
        } else {
            return '';
        }
    }

    public static function getUnpublish($unpublishCats, $parentMap, $value)
    {
        $cat = str_replace('category-', '', $parentMap[$value]);
        if (in_array($cat, $unpublishCats)) {
            $unpublishCats[] = $value;
        } else if (isset($parentMap[$cat])) {
            $unpublishCats = self::getUnpublish($unpublishCats, $parentMap, $cat);
        }

        return $unpublishCats;
    }

    public static function getChildImages($catImageCount, $parent, $parentMap)
    {
        $count = 0;
        if (in_array($parent, $parentMap)) {
            foreach ($parentMap as $key => $value) {
                if ($value == $parent) {
                    $count += $catImageCount['category-'.$key];
                    $count += self::getChildImages($catImageCount, 'category-'.$key, $parentMap);
                }
            }
        }
        return $count;
    }

    public static function checkGalleryUri()
    {
        $url = $_SERVER['REQUEST_URI'];
        $url = urldecode($url);
        $url = explode('?', $url);
        $url = end($url);
        $img = false;
        if (!empty($url)) {
            $img = self::checkImage($url);
        }
        if (!$img && is_numeric($url)) {
            $img = self::getImage($url);
        }

        return $img;
    }

    public static function getUri($aliasMap, $parentMap)
    {
        if (empty($_SERVER['QUERY_STRING'])) {
            return Uri::current();
        }
        $obj = self::checkGalleryUri();
        if ($obj) {
            $key = str_replace('category-', '', $obj->category);
            self::$_currentCat = $key;
            self::$_activeImage = true;
            return Uri::current();
        }
        $current = Uri::current().'?'.urldecode($_SERVER['QUERY_STRING']);
        $url = $_SERVER['REQUEST_URI'];
        $url = explode('?', $url);
        $url = end($url);
        $pos = strrpos($current, 'root');
        if ($pos !== false) {
            $prev = $current[$pos - 1];
            $len = $pos + strlen('root');
            $flag = false;
            if (isset($current[$len]) &&
                ($current[$len] == '?' || $current[$len] == '&')) {
                $flag = true;
                if ($current[$len] == '&') {
                    if (isset($current[$len+1]) && isset($current[$len+2]) &&
                        isset($current[$len+3]) && isset($current[$len+4]) &&
                        isset($current[$len+4]) && isset($current[$len+6]) && isset($current[$len+7])) {
                        $next = $current[$len+1].$current[$len+2].$current[$len+3].$current[$len+4];
                        $next .= $current[$len+5].$current[$len+6].$current[$len+7];
                        if ($next == 'ba-page') {
                            $flag = true;
                        } else {
                            $flag = false;
                        }
                    } else {
                        $flag = false;
                    }
                }
            }
            if (($prev == '&' || $prev == '?') && (!isset($current[$pos + strlen('root')]) || $flag)) {
                $current = substr($current, 0, $pos - 1);
                return $current;
            }
        }
        foreach (self::$_tags as $tag) {
            $alias = 'tag-'.$tag->alias;
            $pos = strrpos($current, $alias);
            if ($pos !== false) {
                $prev = $current[$pos - 1];
                $len = $pos + strlen($alias);
                if (($prev == '&' || $prev == '?') && (!isset($current[$len]) || $current[$len] == '&')) {
                    self::$_activeTags[] = $tag;
                    $start = substr($current, 0, $pos - 1);
                    $end = substr($current, $len);
                    $current = $start.$end;
                }
            }
        }
        foreach (self::$_colors as $color) {
            $alias = 'color-'.$color->alias;
            $pos = strrpos($current, $alias);
            if ($pos !== false) {
                $prev = $current[$pos - 1];
                $len = $pos + strlen($alias);
                if (($prev == '&' || $prev == '?') && (!isset($current[$len]) || $current[$len] == '&')) {
                    self::$_activeColors[] = $color;
                    $start = substr($current, 0, $pos - 1);
                    $end = substr($current, $len);
                    $current = $start.$end;
                }
            }
        }
        foreach ($aliasMap as $key => $value) {
            $alias = self::getAlbumAlias($key, $aliasMap, $parentMap);
            $pos = strrpos($current, $alias);
            if ($pos !== false) {
                $prev = $current[$pos - 1];
                $len = $pos + strlen($alias);
                if (isset($current[$len])) {
                    $last = substr($current, $len);
                    $lastp = strrpos($last, '=');
                    if ($lastp !== false) {
                        $last = substr($last, 0, $lastp);
                        $last = str_replace('&ba-page', '', $last);
                    }
                } else {
                    $last = '';
                }
                if (($prev == '&' || $prev == '?') && $last == '') {
                    self::$_currentCat = $key;
                    $current = substr($current, 0, $pos - 1);
                    break;
                }
            }
        }

        return $current;
    }

    public static function prepareURI($url)
    {
        $url = strip_tags($url);
        $url = str_replace('>', '', $url);
        $url = str_replace('"', '', $url);
        $url = str_replace("'", '', $url);

        return $url;
    }

    public static function getGalleryTags($id, $cat, $enable, $limit)
    {
        if ($enable != '1') {
            return [];
        }
        $db = Factory::getDbo();
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('DISTINCT t.title, t.alias')
            ->from('`#__bagallery_tags` AS t')
            ->order('t.hits desc')
            ->select('m.tag_id AS id')
            ->leftJoin('`#__bagallery_tags_map` AS m ON t.id = m.tag_id')
            ->leftJoin('`#__bagallery_items` AS i ON i.id = m.image_id')
            ->where('i.category <> '.$db->quote(''))
            ->where('m.gallery_id = '.$id);
        if ($cat) {
            $query->where('i.category = '.$db->quote('category-'.$cat));
        }
        $db->setQuery($query, 0, $limit);
        $items = $db->loadObjectList();

        return $items;
    }

    public static function getTagsOptions($id)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('enable_tags, tags_position, tags_alignment, tags_font_size, tags_font_weight,
                tags_font_color_hover, tags_font_color, tags_border_radius, tags_border_color,
                tags_bg_color_hover, tags_bg_color, max_tags')
            ->from('#__bagallery_galleries')
            ->where('id = '.$id);
        $db->setQuery($query);
        $items = $db->loadObject();

        return $items;
    }

    public static function getGalleryColors($id, $cat, $enable, $limit)
    {
        if ($enable != '1') {
            return [];
        }
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('DISTINCT c.title, c.alias')
            ->from('`#__bagallery_colors` AS c')
            ->order('c.hits desc')
            ->select('m.color_id AS id')
            ->leftJoin('`#__bagallery_colors_map` AS m ON c.id = m.color_id')
            ->leftJoin('`#__bagallery_items` AS i ON i.id = m.image_id')
            ->where('i.category <> '.$db->quote(''))
            ->where('m.gallery_id = '.$id);
        if ($cat) {
            $query->where('i.category = '.$db->quote('category-'.$cat));
        }
        $db->setQuery($query, 0, $limit);
        $items = $db->loadObjectList();

        return $items;
    }

    public static function getColorsOptions($id)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('enable_colors, colors_position, colors_alignment, colors_border_radius, max_colors')
            ->from('#__bagallery_galleries')
            ->where('id = '.$id);
        $db->setQuery($query);
        $items = $db->loadObject();

        return $items;
    }

    public static function getImageHtml($options)
    {
        list($image, $categoryName, $categorySelector, $id, $currentUri,
            $className, $compression, $watermark, $thumbnail, $general, $pagination) = $options;
        $img = Uri::root().'index.php?option=com_bagallery&task=gallery.showImage';
        self::$about = self::aboutUs();
        $state = self::checkGalleryActivation();
        $imgSettings = json_decode($image->settings);
        $image->path = $imgSettings->path;
        $image->settings = null;
        $width = $thumbnail->image_width;
        $height = $thumbnail->image_width;
        $file_path = self::$params->file_path;
        $thumb = $file_path.'/'.self::getThumbnail($image->id);
        if ($general->page_refresh == 1 && $general->gallery_layout == 'metro' &&
            (!empty(self::$_activeTags) || !empty(self::$_activeColors)) &&
            !empty($thumb) && File::exists(JPATH_ROOT.'/'.$thumb)) {
            $ext = strtolower(File::getExt(JPATH_ROOT.'/'.$thumb));
            $imageCreate = self::imageCreate($ext);
            $orig = $imageCreate(JPATH_ROOT.'/'.$thumb);
            $origWidth = $orig ? imagesx($orig) : 250;
            $origHeight = imagesy($orig);
            $className = str_replace('width2', '', $className);
            $className = str_replace('height2', '', $className);
            if ($origWidth > $width) {
                $className .= ' width2';
            }
            if ($origHeight > $height) {
                $className .= ' height2';
            }
        }
        if (strpos($className, 'width2') !== false) {
            $width *= 2;
        }
        if (strpos($className, 'height2') !== false) {
            $height *= 2;
        }
        $image->url = self::replaceLongPath($image->url);
        if ($image->url[0] == '/') {
            $image->url = substr($image->url, 1);
        }
        $alias = $image->lightboxUrl;
        if (empty($alias) && !empty($image->title)) {
            $alias = $image->title;
        } else if (empty($alias)) {
            $alias = $image->id;
        }
        $alias = strtolower($alias);
        $alias = str_replace(' ', '-', $alias);
        $alias = str_replace('%', '', $alias);
        $alias = str_replace('?', '', $alias);
        $alias = urlencode($alias);
        $alias = $currentUri.'?'.$alias;
        if (empty($image->lightboxUrl)) {
            $image->lightboxUrl = $image->title;
        }
        $image->url = Uri::root().$image->url;
        $image->path = self::replaceLongPath($image->path);
        $image->name = htmlspecialchars($image->name, ENT_QUOTES);
        $image->url = htmlspecialchars($image->url, ENT_QUOTES);
        $image->path = htmlspecialchars($image->path, ENT_QUOTES);
        $image->watermark_name = htmlspecialchars($image->watermark_name, ENT_QUOTES);
        if (!empty($image->thumbnail_url)) {
            $image->thumbnail_url = htmlspecialchars($image->thumbnail_url, ENT_QUOTES);
        }
        if (!empty($watermark->watermark_upload) && self::$about->tag == 'pro' && isset($state->data)) {
            if (File::exists(JPATH_ROOT.'/'.$file_path.'/bagallery/gallery-'.$id.'/watermark/'.$image->watermark_name)) {
                $image->url = Uri::root().$file_path.'/bagallery/gallery-'.$id.'/watermark/'.$image->watermark_name;
            } else {
                $image->url = Uri::root().'index.php?option=com_bagallery&task=gallery.addWatermark&id='.$image->id;
            }
        }
        $n = substr($image->category, 9);
        if ($image->description) {
            $image->description = htmlspecialchars($image->description, ENT_NOQUOTES);
            $image->description = str_replace('forms ID=', 'baforms ID=', $image->description);
        }
        if ($image->video) {
            $image->video = htmlspecialchars($image->video, ENT_NOQUOTES);
            $image->video = str_replace('forms ID=', 'baforms ID=', $image->video);
        }
        if ($image->link) {
            $image->link = str_replace("'", '%27', $image->link);
        }
        $image->suffix = '';
        if (isset($imgSettings->suffix)) {
            $image->suffix = $imgSettings->suffix.' ';
        }
        $html = "<div class='ba-gallery-items ";
        $html .= $image->suffix;
        if ($image->hideInAll == 0) {
            $html .= "category-0 ";
        }
        $html .= $image->category;
        $html .= $className;
        if (!isset($imgSettings->tags)) {
            $imgSettings->tags = [];
        }
        if (!isset($imgSettings->colors)) {
            $imgSettings->colors = [];
        }
        foreach ($imgSettings->tags as $key => $tag) {
            $html .= " ba-tag-".$tag->id;
        }
        foreach ($imgSettings->colors as $key => $color) {
            $html .= " ba-color-".$color->id;
        }
        if (isset($image->video_id)) {
            $html .= ' ba-video-thumbnail';
        }
        $html .= "' style='display: none;'>";
        $html .= "<span data-href='".$alias."' class='ba-gallery-image-link'></span>";
        if ($image->link != '') {
            $dir = JPATH_ROOT.'/components/com_gridbox/helpers/gridbox.php';
            if (strpos($image->link, 'option=com_gridbox') && File::exists($dir)) {
                include_once($dir);
                $image->link = gridboxHelper::prepareGridboxLinks($image->link);
            }
            $link = Route::_($image->link);
            $html .= "<a href='" . $link . "' target='_" . $image->target. "'>";
        }
        $html .= "<div class='ba-image'><img ";
        if ($general->lazy_load && (($general->pagination == 1 && $pagination->pagination_type != 'infinity')
            || $general->pagination != 1) && self::$about->tag == 'pro' && isset($state->data)) {
            $html .= "src='".Uri::root()."components/com_bagallery/assets/images/default-lazy-load.webp' ";
            $html .= "data-original='";
        } else {
            $html .= "src='";
        }
        $file = JPATH_ROOT.'/'.$thumb;
        $origWidth = 250;
        $origHeight = 250;
        if (empty($thumb) || strlen($thumb) < 10 || !File::exists($file)) {
            $src = $img.'&width='.$width.'&height='.$height.'&id='.$image->id;
            if (!empty($thumbnail->saved_time)) {
                $src .= '&'.$thumbnail->saved_time;
            }
        } else {
            $thumb = htmlspecialchars($thumb, ENT_QUOTES);
            $src = Uri::root().$thumb;
            if (!empty($thumbnail->saved_time)) {
                $src .= '?'.$thumbnail->saved_time;
            }
            if ($general->gallery_layout == 'justified' || $general->gallery_layout == 'random') {
                $ext = strtolower(File::getExt($file));
                $imageCreate = self::imageCreate($ext);
                $orig = $imageCreate($file);
                $origWidth = $orig ? imagesx($orig) : 250;
                $origHeight = imagesy($orig);
            }
        }
        if (!File::exists(JPATH_ROOT.'/'.$image->path)) {
            $src = $image->url;
        }
        $html .= $src;
        $html .= "'";
        if ($general->gallery_layout == 'justified' || $general->gallery_layout == 'random') {
            $html .= ' data-width="'.$origWidth.'" data-height="'.$origHeight.'"';
        }
        $html .= " alt='" .$image->alt. "'>";
        $html .= "<div class='ba-caption'><div class='ba-caption-content'>";
        if ($thumbnail->display_title && $image->title) {
            $html .= "<h3>" .$image->title. "</h3>";
        }
        if ($thumbnail->display_categoty && $image->category != 'root' && !$categorySelector) {
            $html .= "<p class='image-category'>".$categoryName[$n]."</p>";
        }
        if ($image->short) {
            $html .= "<p class='short-description'>" .$image->short. "</p>";
        }
        $html .= "</div></div>";
        if ($image->title) {
            $image->title = htmlspecialchars($image->title, ENT_NOQUOTES);
        }
        if ($image->short) {
            $image->short = htmlspecialchars($image->short, ENT_NOQUOTES);
        }
        if ($image->alt) {
            $image->alt = htmlspecialchars($image->alt, ENT_NOQUOTES);
        }
        if ($compression->enable_compression == 1 && self::$about->tag == 'pro' && isset($state->data)) {
            if (File::exists(JPATH_ROOT.'/'.$file_path.'/bagallery/gallery-'.$id.'/compression/'.$image->watermark_name)) {
                $image->url = Uri::root().$file_path.'/bagallery/gallery-'.$id.'/compression/'.$image->watermark_name;
            } else {
                $image->url = Uri::root().'index.php?option=com_bagallery&task=gallery.compressionImage&id='.$image->id;
            }
        }
        if (isset($imgSettings->alternative) && !empty($imgSettings->alternative)) {
            $image->url = Uri::root().$imgSettings->alternative;
        }
        $html .= "<textarea style='display: none !important;' class='image-id' data-id='ba-image-";
        $html .= $image->id. "'>" . json_encode($image) . '</textarea>';
        $html .= "</div>";
        if ($image->link != '') {
            $html .= "</a>";
        }
        $html .= "</div>";

        return $html;
    }
    
    public static function drawHTMLPage($id)
    {
        $pos = strpos($id, ' category ID');
        $doc = Factory::getDocument();
        $categorySelector = false;
        self::$about = self::aboutUs();
        $state = self::checkGalleryActivation();
        if ($pos !== false) {
            $categorySelector = substr($id, $pos+strlen(' category ID='));
            $id = substr($id, 0, $pos);
        }
        self::$_id = $id;
        if (isset($_POST['baPasswords'])) {
            $baPasswords = $_POST['baPasswords'];
        } else {
            $baPasswords = [];
        }
        $tagsOptions = self::getTagsOptions($id);
        $colorsOptions = self::getColorsOptions($id);
        $categories = self::getCategories($id);
        $catPassword = [];
        $watermark = self::getWatermark($id);
        $compression = self::getCompression($id);
        $general = self::getGeneralOpions($id);
        $defaultFilter = self::getDefaultFilter($id);
        $defaultCategory = '0';
        $galleryOptions = self::getGalleryOptions($id);
        $thumbnail = self::getThumbnailOptions($id);
        $albums = self::getAlbumsOptions($id);
        $pagination = self::getPaginationOptions($id);
        $lightbox = self::getLightboxOptions($id);
        $sorting = self::getSorting($id);
        $header = self::getHeaderOptions($id);
        $sorting = explode('-_-', $sorting);
        $copyright = self::getCopyrightOptions($id);
        $lightbox->header_icons_color = $header->header_icons_color;
        $html = '';
        $language = Factory::getLanguage();
        $language->load('com_bagallery', JPATH_ADMINISTRATOR);
        if (!$lightbox->enable_alias) {
            $general->enable_disqus = 0;
        }
        if (self::$about->tag != 'pro' || !isset($state->data)) {
            $lightbox->description_position = 'below';
            $lightbox->auto_resize = 1;
            $copyright->disable_right_clk = 0;
            $copyright->disable_shortcuts = 0;
            $copyright->disable_dev_console = 0;
        }
        $file_path = self::$params->file_path;
        $categoryName = [];
        $unpublishCats = [];
        $catImageCount = ['root' => self::getImageCount($id, 'root')];
        $aliasMap = [];
        $parentMap = [];
        $catSel = '';
        $categoryDescription = '';
        $user = Factory::getUser();
        $groups = $user->getAuthorisedViewLevels();
        foreach ($categories as $category) {
            $parent = $category->parent;
            $catId = $category->id;
            $access = $category->access;
            $category->description = str_replace('forms ID=', 'baforms ID=', $category->description);
            if (!empty($category->password)) {
                $catPassword[$category->category_id] = $category->id;
            }
            if (!empty($category->password) && $general->category_list != 1 && $general->album_mode != 1) {
                $category->published = 0;
            }
            if ($category->published == 1 && in_array($access, $groups)) {
                $categoryName[$category->category_id] = $category->title;
                if (isset($category->alias) && !empty($category->alias)) {
                    $alias = $category->alias;
                } else {
                    $alias = $category->title;
                }
                $aliasMap[$category->category_id] = $alias;
                if (!empty($parent)) {
                    $parentMap[$category->category_id] = $parent;
                }
                if ($category->default == 1) {
                    self::$_currentCat = $category->category_id;
                }
                if ($categorySelector && $categorySelector == $catId) {
                    $catSel = $category->category_id;
                    if (isset($category->description)) {
                        $categoryDescription = $category->description;
                    }
                }
            } else {
                $unpublishCats[] = $category->category_id;
            }
            if ($category->category_all != 1) {
                $catImageCount['category-'.$category->category_id] = self::getImageCount($id, 'category-'.$category->category_id);
            }
        }
        foreach ($parentMap as $key => $value) {
            $parent = str_replace('category-', '', $value);
            $unpublishCats = self::getUnpublish($unpublishCats, $parentMap, $key);
        }
        if ($general->album_mode == 1) {
            self::$_currentCat = 'root';
            self::$_tags = [];
            self::$_colors = [];
        } else {
            self::$_tags = self::getGalleryTags($id, $catSel, $tagsOptions->enable_tags, $tagsOptions->max_tags);
            self::$_colors = self::getGalleryColors($id, $catSel, $colorsOptions->enable_colors, $colorsOptions->max_colors);
        }
        $currentUri = self::getUri($aliasMap, $parentMap);
        $currentUri = self::prepareURI($currentUri);
        if (self::$_activeImage) {
            $object = self::checkGalleryUri();
        }
        $rgba = self::hexToRgba($lightbox->lightbox_bg, $lightbox->lightbox_bg_transparency);
        $html .= "<div class='ba-gallery ".$general->class_suffix."' data-gallery='".$id."'";
        if ($categorySelector && !empty($catSel)) {
            $html .= ' data-category=".category-'.$catSel.'"';
        }
        $html .= ' style="--modal-background-color:'.$rgba.'; --modal-title-color: '.$header->header_icons_color;
        $html .= '; --modal-icons-color: '.$header->nav_button_icon.';">';
        if (!empty($catPassword) && self::$about->tag == 'pro' && isset($state->data)) {
            $html .= '<div class="modal-scrollable gallery-password-scrollable" style="display:none;">';
            $html .= '<div class="ba-modal fade hide category-password-modal">';
            $html .= '<div class="ba-modal-body"><h3 class="ba-modal-title">'.$language->_('PASSWORD');
            $html .= '</h3><div class="ba-input-lg"><input type="password" class="category-password" placeholder="';
            $html .= $language->_('PASSWORD').'"><span class="focus-underline"></span></div>';
            $html .= '</div><div class="modal-footer">';
            $html .= '<a href="#" class="ba-btn" data-dismiss="modal">'.$language->_('CANCEL');
            $html .= '</a> <a href="#" class="apply-category-password ba-btn-primary disable-button">'.$language->_('SAVE');
            $html .= '</a></div></div></div>';
        }
        if ($albums->album_enable_lightbox == 1 && self::$about->tag == 'pro' && isset($state->data)) {
            $html .= '<div class="albums-backdrop"></div>';
        }
        if (Factory::getUser()->authorise('core.edit', 'com_bagallery') && self::$about->tag == 'pro' && isset($state->data)) {
            $html .= '<a class="ba-edit-gallery-btn" target="_blank" ';
            $html .= 'href="'.Uri::root().'index.php?option=com_bagallery&view=gallery&tmpl=component&id='.$id.'">';
            $html .= '<i class="zmdi zmdi-settings"></i>';
            $html .= '<span class="ba-tooltip ba-top">'.$language->_('EDIT_GALLERY').'</span>';
            $html .= '</a>';
        }
        $html .= '<div class="modal-scrollable" style="display:none; --modal-background-color: '.$rgba.';">';
        $html .= '<div class="ba-modal gallery-modal '.$lightbox->slide_amimation.' '.$general->class_suffix.'" style="display:none">';
        if ($lightbox->enable_alias && self::checkShare($lightbox) && self::$about->tag == 'pro' && isset($state->data)) {
            $html .= '<div class="ba-share-icons"><div class="ba-share" >';
            $path = JPATH_ROOT . '/components/com_bagallery/assets/images/social-icons/';
            if ($lightbox->twitter_share == 1) {
                $icon = self::readFile($path . 'x.svg');
                $html .= '<span data-service="twitter">' .$icon. '</span>';
            }
            if ($lightbox->facebook_share == 1) {
                $icon = self::readFile($path . 'facebook.svg');
                $html .= '<span data-service="facebook">' .$icon. '</span>';
            }
            if ($lightbox->pinterest_share == 1) {
                $icon = self::readFile($path . 'pinterest.svg');
                $html .= '<span data-service="pinterest">' .$icon. '</span>';
            }
            if ($lightbox->linkedin_share == 1) {
                $icon = self::readFile($path . 'linkedin.svg');
                $html .= '<span data-service="linkedin">' .$icon. '</span>';
            }
            if ($lightbox->vkontakte_share == 1) {
                $icon = self::readFile($path . 'vk.svg');
                $html .= '<span data-service="vk">' .$icon. '</span>';
            }
            if ($lightbox->odnoklassniki_share == 1) {
                $icon = self::readFile($path . 'ok.svg');
                $html .= '<span data-service="ok">' .$icon. '</span>';
            }
            $html .= '</div></div>';
        }
        if ($lightbox->display_header) {
            $html .= '<div class="ba-modal-header row-fluid"><div class="ba-modal-title">';
            if ($lightbox->lightbox_display_title) {
                $html .= '<h3 class="modal-title" style="color:'.$header->header_icons_color.';">';
                if (self::$_activeImage && $object) {
                    $html .= $object->title;
                } else {
                    $html .= $doc->getTitle();
                }
                $html .= '</h3>';
            }
            $html .= '</div><div class="ba-center-icons">';
            if ($lightbox->display_zoom == 1 && self::$about->tag == 'pro' && isset($state->data)) {
                $html .= '<i style="color:';
                $html .= $header->header_icons_color. '" class="ba-zoom-in zmdi zmdi-zoom-in"></i>';
                $html .= '<i class="ba-zoom-out disabled-item zmdi zmdi-fullscreen-exit" style="color:';
                $html .= $header->header_icons_color. '"></i>';
            }
            $html .= '</div><div ';
            $html .= 'class="ba-right-icons"><div class="header-icons">';
            if ($lightbox->display_download == 1 && self::$about->tag == 'pro' && isset($state->data)) {
                $html .= '<a href="#" class="ba-download-img zmdi zmdi-download" style="color:';
                $html .= $header->header_icons_color. '" download></a>';
            }
            if ($lightbox->enable_alias && self::checkShare($lightbox) && self::$about->tag == 'pro' && isset($state->data)) {
                $html .= '<i class="zmdi zmdi-share" style="color:';
                $html .= $header->header_icons_color. '"></i>';
            }
            if ($lightbox->display_likes && self::$about->tag == 'pro' && isset($state->data)) {
                $html .= '<div class="ba-like-wrapper"><div class="ba-like">';
                $html .= '<i class="ba-add-like zmdi zmdi-favorite" style="color:';
                $html .= $header->header_icons_color.'"></i>';
                $html .= '</div></div>';
            }
            if ($lightbox->display_fullscreen && self::$about->tag == 'pro' && isset($state->data)) {
                $html .= '<i class="zmdi zmdi-fullscreen display-lightbox-fullscreen" style="color:';
                $html .= $header->header_icons_color. '"></i>';
            }
            $html .= '<i class="ba-icon-close zmdi zmdi-close" ';
            $html .= 'style="color:'.$header->header_icons_color;
            $html .= '"></i></div></div></div>';
        }
        $html .= '<div class="ba-modal-body active" style="--modal-body-color: '.$lightbox->lightbox_border;
        $html .= '"><div class="modal-image"></div>';
        $html .= '<div class="description-wrapper">';
        if (self::$_activeImage && $object) {
            $html .= '<div class="modal-description">'.$object->description.'</div>';
        }
        if ($general->enable_disqus == 1 && self::$about->tag == 'pro' && isset($state->data)) {
            $html .= '<div id="disqus_thread" class="disqus-container"></div><input type="hidden" class="';
            $html .= 'disqus-subdomen" value="' .$general->disqus_subdomen. '">';
        } else if ($general->enable_disqus == 'vkontakte' && self::$about->tag == 'pro' && isset($state->data)) {
            $html .= '<div id="ba-vk-'.$id.'" class="vk-comments-wrapper"></div><input type="hidden" value="';
            $html .= $general->vk_api_id.'" id="vk-api-id-'.$id.'">';
        }
        $html .= '</div>';
        $html .= "</div></div><input type='hidden' class='lightbox-options' ";
        $html .= "value='" .json_encode($lightbox). "'>";
        $html .= '<div class="modal-nav" style="display:none"><i class="ba-left-action zmdi ';
        $html .= 'zmdi-chevron-left" style="color:' .$header->nav_button_icon. '; ';
        $html .='background-color:' .$header->nav_button_bg. '"></i><i class="';
        $html .= 'ba-right-action zmdi zmdi-chevron-right" style="color:' .$header->nav_button_icon. '; ';
        $html .= 'background-color:' .$header->nav_button_bg. '"></i></div>';
        $html .= "</div>";
        $html .= '<div class="ba-gallery-row-wrapper">';
        if ($albums->album_enable_lightbox == 1 && self::$about->tag == 'pro' && isset($state->data)) {
            $html .= '<i class="zmdi zmdi-close albums-backdrop-close"></i>';
        }
        if (!empty(self::$_tags) || !empty(self::$_colors) && self::$about->tag == 'pro' && isset($state->data)) {
            if ($general->category_list != 1 || $categorySelector) {
                $html .= "<a class='show-filter-modal' href='#'>".$language->_('FILTER')."</a>";
            }
            $html .= "<div class='equal-positions-tags ".$colorsOptions->colors_position;
            $style = "";
            if (!empty(self::$_tags)) {
                $style .= '--tags-color: '.$tagsOptions->tags_font_color.';';
                $style .= '--tags-background-color: '.$tagsOptions->tags_bg_color.';';
                $style .= '--tags-border-radius: '.$tagsOptions->tags_border_radius.'px;';
                $style .= '--tags-border-color: '.$tagsOptions->tags_border_color.';';
                $style .= '--tags-font-weight: '.$tagsOptions->tags_font_weight.';';
                $style .= '--tags-font-size: '.$tagsOptions->tags_font_size.'px;';
                $style .= '--tags-hover-color: '.$tagsOptions->tags_font_color_hover.';';
                $style .= '--tags-hover-background-color: '.$tagsOptions->tags_bg_color_hover.';';
            }
            if (!empty(self::$_colors)) {
                $style .= '--colors-border-radius: '.$colorsOptions->colors_border_radius.'px;';
            }
            $style .= '--tags-text-align: '.$tagsOptions->tags_alignment.';';
            $html .= "'><div class='ba-filter-wrapper' style='".$style."'>";
        }
        if (!empty(self::$_tags) && self::$about->tag == 'pro' && isset($state->data)) {
            $html .= "<div class='row-fluid gallery-tags-wrapper position-".$tagsOptions->tags_position;
            $html .= "'><div class='span12'>";
            $html .= '<h4 class="ba-filter-title">'.$language->_('FILTER_BY_TAG').'</h4>';
            foreach (self::$_tags as $tag) {
                $alias = self::$_currentAlias;
                $flag = true;
                foreach (self::$_activeTags as $value) {
                    if ($tag->id != $value->id) {
                        $alias .= self::getUrlDelimiter($alias);
                        $alias .= 'tag-'.$value->alias;
                    } else {
                        $flag = false;
                    }
                }
                foreach (self::$_activeColors as $value) {
                    $alias .= self::getUrlDelimiter($alias);
                    $alias .= 'color-'.$value->alias;
                }
                if ($flag) {
                    $alias .= self::getUrlDelimiter($alias);
                    $alias .= 'tag-'.$tag->alias;
                }
                $html .= "<a data-href='".$alias."' href='#' class='gallery-tag' data-id='".$tag->id."' data-alias='tag-";
                $html .= $tag->alias."'>".$tag->title."</a>";
            }
            $html .= "</div></div>";
        }
        if (!empty(self::$_colors) && self::$about->tag == 'pro' && isset($state->data)) {
            $html .= "<div class='row-fluid gallery-colors-wrapper position-".$colorsOptions->colors_position;
            $html .= "'><div class='span12'>";
            $html .= '<h4 class="ba-filter-title">'.$language->_('FILTER_BY_COLOR').'</h4>';
            foreach (self::$_colors as $color) {
                $alias = self::$_currentAlias;
                $flag = true;
                foreach (self::$_activeTags as $value) {
                    $alias .= self::getUrlDelimiter($alias);
                    $alias .= 'tag-'.$value->alias;
                }
                foreach (self::$_activeColors as $value) {
                    if ($color->id != $value->id) {
                        $alias .= self::getUrlDelimiter($alias);
                        $alias .= 'color-'.$value->alias;
                    } else {
                        $flag = false;
                    }
                }
                if ($flag) {
                    $alias .= self::getUrlDelimiter($alias);
                    $alias .= 'color-'.$color->alias;
                }
                $html .= "<a data-href='".$alias."' href='#' class='gallery-color' data-id='".$color->id."' data-alias='color-";
                $html .= $color->alias."' style='background-color: ".$color->title.";'></a>";
            }
            $html .= "</div></div>";
        }
        if (!empty(self::$_tags) || !empty(self::$_colors) && self::$about->tag == 'pro' && isset($state->data)) {
            $html .= '<p class="ba-reset-filter"><a href="#">'.$language->_('RESET_FILTER').'</a></p></div>';
            $html .= '<div class="filter-modal-footer"><a class="close-filter-modal" href="#">'.$language->_('CANCEL');
            $html .= '</a><a class="apply-filter-modal" href="#">'.$language->_('SAVE').'</a></div>';
            $html .= "</div>";
            $html .= '<div class="filter-modal-backdrop close-filter-modal"></div>';
        }
        if ($general->category_list == 1 && $general->album_mode != 1 && !$categorySelector
            && self::$about->tag == 'pro' && isset($state->data)) {
            $style = '--bg-color: '.$defaultFilter->bg_color.'; --border-color: '.$defaultFilter->border_color.'; ';
            $style .= '--border-radius: '.$defaultFilter->border_radius.'px; --color: '.$defaultFilter->font_color.'; ';
            $style .= '--font-weight: '.$defaultFilter->font_weight.'; --font-size: '.$defaultFilter->font_size.'px; ';
            $style .= '--active-background-color:'.$defaultFilter->bg_color_hover.'; --text-align:'.$defaultFilter->alignment.'; ';
            $style .= ' --active-color:'.$defaultFilter->font_color_hover.';';
            $html .= "<div class='row-fluid'><div class='span12 category-filter' style='display: none; ".$style."'>";
            foreach ($categories as $category) {
                if (in_array($category->category_id, $unpublishCats)) {
                    continue;
                }
                $alias = $aliasMap[$category->category_id];
                $html .= "<a ";
                $alias = strtolower($alias);
                $alias = str_replace(' ', '-', $alias);
                $alias = str_replace('%', '', $alias);
                $alias = str_replace('?', '', $alias);
                $alias = urlencode($alias);
                $html .= " data-alias='".$alias."'";
                if (!empty($category->password)) {
                    $html .= ' data-password data-id="'.$category->id.'"';
                }
                if (strpos($currentUri, '?') === false) {
                    $alias = $currentUri.'?'.$alias;
                } else {
                    $alias = $currentUri.'&'.$alias;
                }
                if (self::$_currentCat == $category->category_id) {
                    self::$_currentAlias = $alias;
                }
                $html .= ' data-href="'.$alias.'" href="#"';
                $html .= " data-filter='.category-" .$category->category_id;
                $html .= "' class='ba-btn ba-filter";
                if ($category->default == 1) {
                    $defaultCategory = $category->category_id;
                    $html .= "-active";
                }
                $html .= "'>" .$category->title. "</a>";
            }
            $html .= "<select class='ba-select-filter'>";
            foreach ($categories as $category) {
                if (in_array($category->category_id, $unpublishCats)) {
                    continue;
                }
                $html .= "<option value='.category-" .$category->category_id. "'";
                if ($category->default == 1) {
                    $html .= " selected";
                }
                $html .= ">". $category->title. "</option>";
            }
            $html .= "</select>";
            if (!empty(self::$_tags) || !empty(self::$_colors)) {
                $html .= "<a class='show-filter-modal' href='#'>".$language->_('FILTER')."</a>";
            }
            $html .= "</div></div>";
        }
        $height2 = [];
        $width2 = [];
        if ($albums->album_layout == 'masonry') {
            for ($i = 0; $i < 100; $i++) {
                $height2[] = 4 * $i + 2;
            }
        } else if ($albums->album_layout == 'metro') {
            for ($i = 0; $i < 100; $i++) {
                $height2[] = 10 * $i + 2;
                $height2[] = 10 * $i + 5;
                $width2[] = 10 * $i + 4;
                $width2[] = 10 * $i + 7;
                $height2[] = 10 * $i + 7;
                
            }
        } else if ($albums->album_layout == 'square') {
            for ($i = 0; $i < 100; $i++) {
                $height2[] = 5 * $i + 5;
                $width2[] = 5 * $i + 5;
            }
        }
        foreach ($categories as $category) {
            unset($category->password);
        }
        if ($general->album_mode == 1 && !$categorySelector && self::$about->tag == 'pro' && isset($state->data)) {
            $cat = htmlspecialchars(json_encode($categories), ENT_QUOTES);
            $style = '--background-color: '.$pagination->pagination_bg.'; --border-color: '.$pagination->pagination_border.'; ';
            $style .= '--color: '.$pagination->pagination_font.'; --hover-background-color: '.$pagination->pagination_bg_hover;
            $style .= '; --hover-color: '.$pagination->pagination_font_hover.'; ';
            $html .= '<div class="row-fluid"><div class="ba-goback" style="display:none; '.$style.'"><a class="zmdi zmdi-long-arrow-left"';
            $html .= '></a><h2';
            $html .= '></h2>';
            $html .= "<div class='categories-description'>";
            $html .= "<input type='hidden' value='" .$cat;
            $html .= "' class='categories'></div>";
            $html .= '</div><div class="ba-album';
            if ($albums->album_disable_caption != 1) {
                $html .= ' css-style-';
                $html .= $albums->album_thumbnail_layout;
            }
            $style = '--background-color: '.$albums->album_caption_bg.'; --title-size: '.$albums->album_title_size.'px; ';
            $style .= '--title-weight: '.$albums->album_title_weight.'; --title-text-align: '.$albums->album_title_alignment.'; ';
            $style .= '--title-color: '.$albums->album_title_color.'; --count-size: '.$albums->album_img_count_size.'px; ';
            $style .= '--count-weight: '.$albums->album_img_count_weight.'; --count-text-align:'.$albums->album_img_count_alignment.'; ';
            $style .= '--count-color: '.$albums->album_img_count_color.';';
            $html .= '" style="'.$style.'">';
            $img = Uri::root().'index.php?option=com_bagallery&task=gallery.showCatImage';
            $width = $thumbnail->image_width;
            $catIndex = 0;
            $password = self::getPassword($catPassword, $parentMap, self::$_currentCat);
            $currentFlag = !empty($password) && (!isset($baPasswords[$password]) || $baPasswords[$password] == 'false');
            foreach ($categories as $category) {
                $catId = $category->id;
                $parent = $category->parent;
                if (empty($parent)) {
                    $parent = 'root';
                }
                if ($general->page_refresh == 1 && $parent != self::$_currentCat
                    && !$currentFlag && $parent != 'category-'.self::$_currentCat
                    && $category->category_id != self::$_currentCat
                    && isset($parentMap[self::$_currentCat])
                    && $parentMap[self::$_currentCat] != 'category-'.$category->category_id) {
                    continue;
                }
                if ($category->category_all != 1 && !in_array($category->category_id, $unpublishCats)) {
                    $className = '';
                    $i = $catImageCount['category-'.$category->category_id];
                    $i += self::getChildImages($catImageCount, 'category-'.$category->category_id, $parentMap);
                    $file = JPATH_ROOT. '/'.$file_path.'/bagallery/gallery-'.$id.'/album/';
                    if (empty($category->image)) {
                        $category->image = 'components/com_bagallery';
                        $category->image .= '/assets/images/image-placeholder.jpg';
                        $file.= 'image-placeholder.jpg';
                    } else {
                        $category->image = self::replaceLongPath($category->image);
                        $name = explode('/', $category->image);
                        $file .= 'category-'.$category->category_id.'-'.end($name);
                    }
                    $width = $albums->album_width;
                    $height = $albums->album_width;
                    if (in_array($catIndex + 1, $height2)) {
                        $className.= ' height2';
                        $height *= 2;
                    }
                    if (in_array($catIndex + 1, $width2)) {
                        $className.= ' width2';
                        $width *= 2;
                    }
                    $src = $img.'&width='.$width.'&height='.$height.'&id='.$catId;
                    $catIndex++;
                    $origWidth = 250;
                    $origHeight = 250;
                    if (File::exists($file) && File::getExt($category->image) != 'svg') {
                        $ext = strtolower(File::getExt($file));
                        $imageCreate = self::imageCreate($ext);
                        $orig = $imageCreate($file);
                        $origWidth = $orig ? imagesx($orig) : 250;
                        $origHeight = imagesy($orig);
                        $src = str_replace(JPATH_ROOT.'/', Uri::root(), $file);
                    } else {
                        $src = Uri::root() . $category->image;
                    }
                    $html .= '<div class="ba-album-items '.$parent.$className.'"';
                    $alias = $aliasMap[$category->category_id];
                    $alias = strtolower($alias);
                    $alias = str_replace(' ', '-', $alias);
                    $alias = str_replace('%', '', $alias);
                    $alias = str_replace('?', '', $alias);
                    $alias = urlencode($alias);
                    $password = self::getPassword($catPassword, $parentMap, $category->category_id);
                    if (!empty($password)) {
                        $html .= ' data-password data-id="'.$password.'"';
                    }
                    $html .= " style='display:none;' data-alias='".$alias."'";
                    $alias = self::getAlbumAlias($category->category_id, $aliasMap, $parentMap);
                    if (strpos($currentUri, '?') === false) {
                        $alias = $currentUri.'?'.$alias;
                    } else {
                        $alias = $currentUri.'&'.$alias;
                    }
                    if (self::$_currentCat == $category->category_id && self::$_activeImage && $object) {
                        self::$_currentAlias = $alias;
                    }
                    $html .= ' data-filter=".category-';
                    $html .= $category->category_id. '"><a href="#" data-href="'.$alias;
                    $html .= '"></a><div class="ba-image">';
                    $html .='<img src="' .$src.'" data-width="'.$origWidth.'" data-height="'.$origHeight.'"></div>';
                    if ($albums->album_disable_caption != 1) {
                        $html .= '<div class="ba-caption"';
                        $html .= '><div class="ba-caption-content">';
                        if ($albums->album_display_title) {
                            $html .= '<h3>'.$category->title.'</h3>';
                        }
                        if ($albums->album_display_img_count) {
                            $html .= '<p>'.$i. ' ' .$language->_('PHOTOS').'</p>';
                        }
                        $html .= '</div></div>';
                    }
                    $html .= '</div>';
                }
            }
            $html .= "<input type='hidden' value='" .json_encode($albums). "' class='albums-options'>";
            $html .= '<input type="hidden" class="album-mode" value="';
            $html .= $general->album_mode.'"></div></div>';
        }
        if ((($general->album_mode != 1 && $general->category_list == 1) || $categorySelector)
            && self::$about->tag == 'pro' && isset($state->data)) {
            $html .= "<div class='row-fluid'><div class='categories-description'>";
            if (!$categorySelector) {
                $str = json_encode($categories);
                $html .= '<textarea style="display:none !important;" class="categories">' .$str. '</textarea>';
            } else {
                $html .= $categoryDescription;
            }
            $html .= "</div></div>";
        }
        $html .= "<div class='ba-gallery-content-wrapper'>";
        $html .= "<div class='ba-gallery-content'>";
        $html .= "<div class='row-fluid'>";
        $html .= "<div class='span12 ba-gallery-grid";
        if ($thumbnail->disable_caption != 1) {
            $html .= " css-style-" .$galleryOptions->thumbnail_layout;
        }
        if ($lightbox->disable_lightbox == 1) {
            $html .= ' disabled-lightbox';
        }
        if ($thumbnail->disable_caption == 1) {
            $html .= ' disable-caption';
        }
        $rgba = self::hexToRgba($galleryOptions->caption_bg, $galleryOptions->caption_opacity);
        $style = '--background-color: '.$rgba.'; --title-color: '.$galleryOptions->title_color.'; ';
        $style .= '--description-color: '.$galleryOptions->description_color.
            '; --category-color: '.$galleryOptions->category_color.'; ';
        $style .= '--title-size: '.$galleryOptions->title_size.'px; --title-weight: '.$galleryOptions->title_weight.'; ';
        $style .= '--title-text-align: '.$galleryOptions->title_alignment.'; --description-size: ';
        $style .= $galleryOptions->description_size.'px; --description-weight: '.$galleryOptions->description_weight.'; ';
        $style .= '--description-text-align: '.$galleryOptions->description_alignment.'; --category-size: ';
        $style .= $galleryOptions->category_size.'px; --category-weight: '.$galleryOptions->category_weight.'; ';
        $style .= '--category-text-align: '.$galleryOptions->category_alignment.';';
        $html .= "' style='".$style."'>";
        if (($general->page_refresh == 1 && self::$about->tag == 'pro' && isset($state->data)) || $categorySelector) {
            foreach ($categories as $category) {
                $password = self::getPassword($catPassword, $parentMap, $category->category_id);
                if (!empty($password) && (!isset($baPasswords[$password]) || $baPasswords[$password] == 'false')) {
                    if (!in_array($category->category_id, $unpublishCats)) {
                        $unpublishCats[] = $category->category_id;
                    }
                }
            }
            $start = 0;
            if (in_array(self::$_currentCat, $unpublishCats)) {
                self::$_currentCat = $general->album_mode == 1 ? 'root' : $defaultCategory;
            }
            $cat = self::$_currentCat === 'root' ? 'root' : 'category-'.self::$_currentCat;
            if ($categorySelector) {
                $cat = 'category-'.$catSel;
            }
            if (!empty(self::$_activeTags) || !empty(self::$_activeColors)) {
                $tags = [];
                $colors = [];
                foreach (self::$_activeColors as $value) {
                    $colors[] = $value->id;
                }
                foreach (self::$_activeTags as $value) {
                    $tags[] = $value->id;
                }
                $items = self::getTagsColorsItems($tags, $colors, $general, $cat, $unpublishCats, $id);
                $emptyArray = [];
                foreach ($sorting as $value) {
                    if (in_array($value, $items)) {
                        $emptyArray[] = $value;
                    }
                }
                $start = 0;
                $sorting = $emptyArray;
            }
            if ($cat != 'category-0' && empty(self::$_activeTags) && empty(self::$_activeColors)) {
                $db = Factory::getDbo();
                $query = $db->getQuery(true);
                $query->select('i.imageId')
                    ->from('`#__bagallery_items` AS i')
                    ->where('i.form_id = '.$id)
                    ->where('i.category = '.$db->quote($cat));
                $db->setQuery($query);
                $items = $db->loadColumn();
                $emptyArray = [];
                foreach ($sorting as $value) {
                    if (in_array($value, $items)) {
                        $emptyArray[] = $value;
                    }
                }
                $start = 0;
                $sorting = $emptyArray;
            } else if (empty(self::$_activeTags) && empty(self::$_activeColors)) {
                if (!empty($unpublishCats)) {
                    $ind = 0;
                    foreach ($catImageCount as $key => $value) {
                        if (in_array(str_replace('category-', '', $key), $unpublishCats)) {
                            for ($i = $ind; $i < $value + $ind; $i++) {
                                unset($sorting[$i]);
                            }
                        }
                        $ind += $value;
                    }
                    $newSort = [];
                    foreach ($sorting as $value) {
                        $newSort[] = $value;
                    }
                    $sorting = $newSort;
                }
            }
            if (empty(self::$_activeTags) && empty(self::$_activeColors)) {
                $catImages = self::getImageCount($id, $cat, $unpublishCats);
            } else {
                $catImages = count($sorting);
            }
            $end = $start + $catImages;
            $currentPage = 1;
            if ($general->pagination == 1) {
                $end = $pagination->images_per_page + $start;
                if (isset($_GET['ba-page']) && !empty($_GET['ba-page'])) {
                    $currentPage = $_GET['ba-page'];
                    $currentPage = explode('?', $currentPage);
                    $currentPage = $currentPage[0];
                    $start += $pagination->images_per_page * ($currentPage - 1);
                    $end = $start + $pagination->images_per_page;
                }
                if ($end > $start + $catImages) {
                    $end = $start + $catImages;
                }
                $pages = ceil($catImages / $pagination->images_per_page);
                if (self::$_activeImage && $object) {
                    $obj = self::checkGalleryUri();
                    $keys = array_keys($sorting, $obj->imageId);
                    $key = $keys[0];
                    if ($key >= $end) {
                        while ($key >= $end) {
                            $currentPage++;
                            $start += $pagination->images_per_page * ($currentPage - 1);
                            $end = $start + $pagination->images_per_page;
                            $start = 0;
                        }
                        $start = $end - $pagination->images_per_page;
                    }
                }
            }
        }
        $height2 = [];
        $width2 = [];
        $total = self::getImageCount($id, 'category-0');
        if ($general->gallery_layout == 'masonry') {
            for ($i = 0; $i < $total; $i++) {
                $height2[] = 4 * $i + 2;
            }
        } else if ($general->gallery_layout == 'metro') {
            for ($i = 0; $i < $total; $i++) {
                $height2[] = 10 * $i + 2;
                $height2[] = 10 * $i + 5;
                $width2[] = 10 * $i + 4;
                $width2[] = 10 * $i + 7;
                $height2[] = 10 * $i + 7;
                
            }
        } else if ($general->gallery_layout == 'square') {
            for ($i = 0; $i < $total; $i++) {
                $height2[] = 5 * $i + 5;
                $width2[] = 5 * $i + 5;
            }
        }
        $thumbCategory = '';
        $ind = 0;
        foreach ($sorting as $key => $sort) {
            if (empty($sort)) {
                continue;
            }
            if ($general->pagination == 1 && $pagination->pagination_type == 'infinity'
                && self::$about->tag == 'pro' && isset($state->data)) {
                $infinity = new \stdClass();
                $infinity->id = $id;
                $infinity->unpublishCats = $unpublishCats;
                $infinity->currentUri = $currentUri;
                foreach ($categoryName as $ind => $value) {
                    $categoryName[$ind] = str_replace("'", '&#39;', $value);
                }
                $infinity->categoryNames = $categoryName;
                $infinity->catImageCount = $catImageCount;
                $infinityData = json_encode($infinity);
                $html .= "<input type='hidden' class='infinity-data' value='".$infinityData."'>";
                break;
            }
            if ($general->page_refresh == 1 && self::$about->tag == 'pro' && isset($state->data)) {
                if ($key < $start) {
                    continue;
                } else if ($key >= $end) {
                    break;
                }
                $image = self::getSortImageRefresh($id, $sort);
            } else {
                $image = self::getSortImage($id, $sort, $unpublishCats);
                if (empty($image)) {
                    continue;
                }
            }
            if ($thumbCategory != $image->category) {
                $ind = 0;
                $thumbCategory = $image->category;
            }
            $className = '';
            if (in_array($ind + 1, $height2)) {
                $className.= ' height2';
            }
            if (in_array($ind + 1, $width2)) {
                $className.= ' width2';
            }
            $settings = json_decode($image->settings);
            if (isset($settings->video_id)) {
                $image->type = $settings->type;
                $image->video_id = $settings->video_id;
                $image->video_type = $settings->video_type;
            }
            $options = [
                $image, $categoryName, $categorySelector, $id, $currentUri,
                $className, $compression, $watermark, $thumbnail, $general, $pagination
            ];
            $html .= self::getImageHtml($options);
            $ind++;
        }
        $html .= "</div></div>";
        if ($general->page_refresh == 1 && self::$about->tag == 'pro' && isset($state->data)) {
            $refresh = new \stdClass();
            $refresh->id = $id;
            $refresh->order = $sorting;
            $refresh->currentPage = $currentPage;
            $refresh->currentUri = $currentUri;
            foreach ($categoryName as $ind => $value) {
                $categoryName[$ind] = str_replace("'", '&#39;', $value);
            }
            $refresh->categoryNames = $categoryName;
            $refreshData = json_encode($refresh);
            $html .= "<input type='hidden' class='refresh-data' value='".$refreshData."'>";
        }
        $html .= "<input type='hidden' class='gallery-options' value='";
        $html .= json_encode($galleryOptions). "'>";
        $html .= '<input type="hidden" class="current-root" value="'.$currentUri.'">';
        $html .= "<input type='hidden' value='" .$general->gallery_layout. "' class='gallery-layout'>";
        $html .= "<input type='hidden' value='".$general->page_refresh."' class='page-refresh'>";
        $html .= "<input type='hidden' value='" .$language->_('CREATE_THUMBNAILS'). "' class='creating-thumbnails'>";
        $html .= "<input type='hidden' value='" .json_encode($copyright). "' class='copyright-options'>";
        if (self::$_activeImage && $object) {
            if (empty(self::$_currentAlias)) {
                self::$_currentAlias = $currentUri;
            }
            $html .= '<input type="hidden" class="active-category-image" value="'.self::$_currentAlias.'">';
        }
        if ($general->pagination == 1 && self::$about->tag == 'pro' && isset($state->data)) {
            $style = '--background-color: '.$pagination->pagination_bg.'; --border-radius:'.$pagination->pagination_radius.'px; ';
            $style .= '--border-color: '.$pagination->pagination_border.'; --color: '.$pagination->pagination_font.'; ';
            $style .= '--text-align: '.$pagination->pagination_alignment.'; --active-bg-color: '.$pagination->pagination_bg_hover.'; ';
            $style .= '--active-color: '.$pagination->pagination_font_hover.'; ';
            $html .= "<div class='row-fluid'><div class='span12 ba-pagination' style='".$style."'>";
            if (($general->page_refresh == 1) && $pages > 1) {
                if (self::$_currentCat === 'root') {
                    $alias = 'root';
                } else {
                    $alias = self::getAlbumAlias(self::$_currentCat, $aliasMap, $parentMap);
                }
                if (strpos($currentUri, '?') === false) {
                    $alias = $currentUri.'?'.$alias;
                } else {
                    $alias = $currentUri.'&'.$alias;
                }
                if (!empty(self::$_activeTags)) {
                    foreach (self::$_activeTags as $tag) {
                        $alias .= '&tag-'.$tag->alias;
                    }
                }
                if (!empty(self::$_activeColors)) {
                    foreach (self::$_activeColors as $color) {
                        $alias .= '&color-'.$color->alias;
                    }
                }
                if ($pagination->pagination_type == 'default') {
                    $html .= '<a href="#" data-href="'.$alias.'&ba-page=1" class="ba-btn ba-first-page';
                    if ($currentPage == 1) {
                        $html .= ' ba-dissabled';
                    }                    
                    $html .= '" style="display:none;"><span class="zmdi zmdi-skip-previous"></span></a>';
                    $html .= '<a href="#" data-href="'.$alias.'&ba-page='.($currentPage - 1).'" class="ba-btn ba-prev';
                    if ($currentPage == 1) {
                        $html .= ' ba-dissabled';
                    }
                    $html .= '" style="display:none;"><span class="zmdi zmdi-play"></span></a>';
                    for ($i = 0; $i < $pages; $i++) {
                        $html .= '<a href="#" data-href="'.$alias.'&ba-page='.($i + 1).'" class="ba-btn';
                        if ($i == $currentPage - 1) {
                            $html .= ' ba-current';
                        }
                        $html .= '" style="display:none;">';
                        $html .= ($i + 1);
                        $html .= '</a>';
                    }
                    $html .= '<a href="#" data-href="'.$alias.'&ba-page='.($currentPage + 1);
                    $html .= '" class="ba-btn ba-next';
                    if ($currentPage == $pages) {
                        $html .= ' ba-dissabled';
                    }
                    $html .= '" style="display:none;"><span class="zmdi zmdi-play"></span></a>';
                    $html .= '<a href="#" data-href="'.$alias.'&ba-page='.$pages.'" class="ba-btn ba-last-page';
                    if ($currentPage == $pages) {
                        $html .= ' ba-dissabled';
                    }
                    $html .= '" style="display:none;"><span class="zmdi zmdi-skip-next"></span></a>';                    
                } else if ($pagination->pagination_type == 'slider') {
                    $prev = $currentPage - 1;
                    $next = $currentPage + 1;
                    if ($prev == 0) {
                        $prev = $pages;
                    }
                    if ($next > $pages) {
                        $next = 1;
                    }
                    $html .= '<a href="#" data-href="'.$alias.'&ba-page='.$prev.'" class="ba-btn ba-prev';
                    $html .= '" style="display:none;"><span class="zmdi zmdi-play"></span></a>';
                    $html .= '<a href="#" data-href="'.$alias.'&ba-page='.$next;
                    $html .= '" class="ba-btn ba-next';
                    $html .= '" style="display:none;"><span class="zmdi zmdi-play"></span></a>';
                }
            }
            $html .= "<input type='hidden' class='ba-pagination-options' value='";
            $html .= json_encode($pagination). "'>";
            $html .= "<input type='hidden' class='ba-pagination-constant' value='";
            $html .= $language->_('PREV'). "-_-" .$language->_('NEXT'). "-_-";
            $html .= $language->_('LOAD_MORE'). "-_-" .$language->_('SCROLL_TOP');
            $html .= "'></div></div>";
        }
        $html .= "</div></div>";
        $html .= "</div></div>";
        $html .= self::getGalleryFooter();

        return $html;
    }

    public static function getGalleryFooter()
    {
        $html = "<div class='ba-gallery-substrate' style='height: 0;'></div>";
        if (self::$about->tag != 'pro') {
            $html .= '<p style="text-align: center; font-size: 12px; font-style: italic;">';
            $html .= '<a href="http://www.balbooa.com/joomla-gallery">Joomla Gallery</a> makes it better. Balbooa.com</p>';
        }

        return $html;
    }

    public static function setAppLicense()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__bagallery_api')
            ->where('service = '.$db->quote('balbooa'));
        $db->setQuery($query);
        $balbooa = $db->loadObject();
        $balbooa->key = '{}';
        $db->updateObject('#__bagallery_api', $balbooa, 'id');
    }

    public static function setAppLicenseActivation()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__bagallery_api')
            ->where('service = '.$db->quote('balbooa_activation'));
        $db->setQuery($query);
        $balbooa = $db->loadObject();
        $balbooa->key = '{}';
        $db->updateObject('#__bagallery_api', $balbooa, 'id');
    }
    
    protected static function getCategories($id):array
    {
        $db = Factory::getDbo();
        self::checkCompatibility($id);
        $query = $db->getQuery(true)
            ->select("*")
            ->from("#__bagallery_category")
            ->where("form_id=" . $id)
            ->order("orders ASC");
        $db->setQuery($query);
        $items = $db->loadObjectList();

        return $items;
    }

    public static function getImageCount($id, $category, $unpublish = [])
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select("COUNT(i.id)")
            ->from('`#__bagallery_items` AS i')
            ->where('i.form_id = '.$id);
        if ($category !== 'category-0') {
            $query->where("i.category = " . $db->Quote($category));
        } else {
            $query->where("i.hideInAll = " . $db->Quote('0'));
            foreach ($unpublish as $value) {
                $query->where("i.category <>" . $db->Quote('category-'.$value));
            }
        }
        $db->setQuery($query);
        $items = $db->loadResult();
        return $items;
    }

    public static function getSortImageRefresh($id, $imageId)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select("*")
            ->from("#__bagallery_items")
            ->where("`form_id`=" . $id)
            ->where("`imageId`=" . $imageId);
        $db->setQuery($query);
        $items = $db->loadObject();

        return $items;
    }

    public static function getSortImage($id, $imageId, $unpublishCats)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select("*")
            ->from("#__bagallery_items")
            ->where("form_id=" . $id)
            ->where("imageId=" . $imageId);
        foreach ($unpublishCats as $value) {
            $query->where("`category` <>" . $db->Quote('category-'.$value));
        }
        $db->setQuery($query);
        $item = $db->loadObject();

        return $item;
    }
    
    protected static function getImages($id)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select("category, name, url, title, short, thumbnail_url, target,
                        alt, description, link, video, id, likes, path, imageId,
                        lightboxUrl, watermark_name, hideInAll");
        $query->from("#__bagallery_items");
        $query->where("form_id=" . $id);
        $query->order("imageId ASC");
        $db->setQuery($query);
        $items = $db->loadObjectList();
        return $items;
    }

    public static function getWatermark($id)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('watermark_upload, watermark_position, watermark_opacity, scale_watermark')
            ->from("#__bagallery_galleries")
            ->where("id=" . $id);
        $db->setQuery($query);
        $items = $db->loadObject();
        return $items;
    }

    public static function getCompression($id)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('enable_compression, compression_width, compression_quality')
            ->from("#__bagallery_galleries")
            ->where("id=" . $id);
        $db->setQuery($query);
        $items = $db->loadObject();
        return $items;
    }
    
    protected static function getDefaultFilter($id)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select("bg_color, bg_color_hover, border_color,
                        border_radius, font_color, font_color_hover,
                        font_weight, font_size, alignment")
            ->from("#__bagallery_galleries")
            ->where("id=" . $id);
        $db->setQuery($query);
        $items = $db->loadObject();
        return $items;
    }
    
    protected static function getGalleryOptions($id)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select("thumbnail_layout, column_number, image_spacing, caption_bg,
            title_color, title_weight, title_size, title_alignment, tags_method, colors_method,
            category_color, category_weight, category_size, category_alignment,
            description_color, description_weight, description_size, id, pagination_type,
            description_alignment, caption_opacity, sorting_mode, random_sorting,
            tablet_numb, phone_land_numb, phone_port_numb, disable_auto_scroll")
            ->from("#__bagallery_galleries")
            ->where("id=" . $id);
        $db->setQuery($query);
        $items = $db->loadObject();
        return $items;
    }
    
    public static function getGeneralOpions($id)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select("gallery_layout, category_list, pagination, lazy_load,
            class_suffix, album_mode, all_sorting, enable_disqus, tags_method,
            disqus_subdomen, vk_api_id, colors_method, page_refresh")
            ->from("#__bagallery_galleries")
            ->where("id=" . $id);
        $db->setQuery($query);
        $items = $db->loadObject();
        return $items;
    }

    protected static function getAlbumsOptions($id)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select("album_layout, album_width, album_quality, album_image_spacing, album_disable_caption,
            album_thumbnail_layout, album_caption_bg, album_display_title, album_display_img_count,
            album_title_color, album_title_weight, album_title_size, album_title_alignment, album_enable_lightbox,
            album_img_count_color, album_img_count_weight, album_img_count_size, album_img_count_alignment,
            album_phone_port_numb, album_phone_land_numb, album_tablet_numb, album_column_number")
            ->from("#__bagallery_galleries")
            ->where("id=" . $id);
        $db->setQuery($query);
        $items = $db->loadObject();
        return $items;
    }
    
    public static function getThumbnailOptions($id)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select("display_title, display_categoty, disable_caption, image_width, image_quality, saved_time")
            ->from("#__bagallery_galleries")
            ->where("id=" . $id);
        $db->setQuery($query);
        $items = $db->loadObject();
        return $items;
    }
    
    protected static function getLightboxOptions($id)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select("lightbox_border, lightbox_bg, lightbox_bg_transparency,
            display_likes, display_header, display_zoom, lightbox_display_title,
            lightbox_width, auto_resize, disable_lightbox, twitter_share, odnoklassniki_share,
            description_position, facebook_share, pinterest_share, slide_amimation,
            linkedin_share, vkontakte_share, display_download, enable_alias, display_fullscreen")
            ->from("#__bagallery_galleries")
            ->where("id=" . $id);
        $db->setQuery($query);
        $items = $db->loadObject();
        return $items;
    }
    
    public static function getPaginationOptions($id)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select("pagination_type, images_per_page, pagination_bg,
                        pagination_bg_hover, pagination_border, pagination_font,
                        pagination_font_hover, pagination_radius, pagination_alignment")
            ->from("#__bagallery_galleries")
            ->where("id=" . $id);
        $db->setQuery($query);
        $items = $db->loadObject();
        return $items;
    }
    
    public static function getSorting($id)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select("settings")
            ->from("#__bagallery_galleries")
            ->where("id=" . $id);
        $db->setQuery($query);
        $items = $db->loadResult();
        return $items;
    }
    
    protected static function getHeaderOptions($id)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select("header_icons_color, nav_button_bg, nav_button_icon")
            ->from("#__bagallery_galleries")
            ->where("id=" . $id);
        $db->setQuery($query);
        $items = $db->loadObject();
        return $items;
    }

    protected static function getCopyrightOptions($id)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select("disable_right_clk, disable_shortcuts, disable_dev_console")
            ->from("#__bagallery_galleries")
            ->where("id=" . $id);
        $db->setQuery($query);
        $items = $db->loadObject();
        return $items;
    }

    public static function getUrlDelimiter($alias)
    {
        $flag = true;
        $pos = strpos($alias, '?');
        if ($pos === false) {
            $delimiter = '?';
        } else {
            $delimiter = '&';
        }

        return $delimiter;
    }

    public static function getTagsColorsQuery($cat, $unpublishCats)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('DISTINCT i.imageId')
            ->from('`#__bagallery_items` AS i');
        if ($cat != 'category-0') {
            $query->where('i.category = '.$db->quote($cat));
        } else {
            $query->where("i.hideInAll = " . $db->Quote('0'));
            foreach ($unpublishCats as $value) {
                $query->where('i.category <> '.$db->quote('category-'.$value));
            }
        }

        return $query;
    }

    public static function getIncludeItems($array, $table, $column, $cat, $unpublishCats, $id)
    {
        $db = Factory::getDbo();
        $query = self::getTagsColorsQuery($cat, $unpublishCats);
        $where = '';
        foreach ($array as $value) {
            if (!empty($where)) {
                $where .= ' OR ';
            }
            $where .= 'm.'.$column.' = '.$db->quote($db->escape($value, true));
        }
        $query->leftJoin($table.' ON '. $db->quoteName('m.image_id')
            . ' = '. $db->quoteName('i.id'))
            ->where('m.gallery_id = '.$id)
            ->where($where);
        $db->setQuery($query);
        $items = $db->loadColumn();

        return $items;
    }

    public static function getExcludeItems($tags, $colors, $general, $cat, $unpublishCats, $id)
    {
        $db = Factory::getDbo();
        $query = self::getTagsColorsQuery($cat, $unpublishCats);
        if (!empty($tags) && $general->tags_method != 'include') {
            $query->leftJoin('`#__bagallery_tags_map` AS m'
                . ' ON '
                . $db->quoteName('m.image_id')
                . ' = ' 
                . $db->quoteName('i.id')
                )
                ->where('m.gallery_id = '.$id)
                ->where('m.tag_id = '.$tags[0]);
            $lastKey = 'm';
            foreach ($tags as $key => $value) {
                if ($key == 0) {
                    continue;
                }
                $query->leftJoin('`#__bagallery_tags_map` AS m'.$key
                    . ' ON '
                    . $db->quoteName($lastKey.'.image_id')
                    . ' = ' 
                    . $db->quoteName('m'.$key.'.image_id')
                )
                ->where('m'.$key.'.gallery_id = '.$id)
                ->where('m'.$key.'.tag_id = '.$value);
                $lastKey = 'm'.$key;
            }
        }
        if (!empty($colors) && $general->colors_method != 'include') {
            $query->leftJoin('`#__bagallery_colors_map` AS cm'
                . ' ON '
                . $db->quoteName('cm.image_id')
                . ' = ' 
                . $db->quoteName('i.id')
                )
                ->where('cm.gallery_id = '.$id)
                ->where('cm.color_id = '.$colors[0]);
            $lastKey = 'cm';
            foreach ($colors as $key => $value) {
                if ($key == 0) {
                    continue;
                }
                $query->leftJoin('`#__bagallery_colors_map` AS cm'.$key
                    . ' ON '
                    . $db->quoteName($lastKey.'.image_id')
                    . ' = ' 
                    . $db->quoteName('cm'.$key.'.image_id')
                )
                ->where('cm'.$key.'.gallery_id = '.$id)
                ->where('cm'.$key.'.color_id = '.$value);
                $lastKey = 'cm'.$key;
            }
        }
        $db->setQuery($query);
        $items = $db->loadColumn();

        return $items;
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
        $galleryState = json_decode($balbooa);

        return $galleryState;
    }

    public static function getTagsColorsItems($tags, $colors, $general, $cat, $unpublishCats, $id)
    {
        $db = Factory::getDbo();
        if ($general->colors_method == 'include' || $general->tags_method == 'include') {
            $tagsItems = [];
            $colorsItems = [];
            if (!empty($tags) && $general->tags_method == 'include') {
                $tagsItems = self::getIncludeItems($tags, '`#__bagallery_tags_map` AS m', 'tag_id', $cat, $unpublishCats, $id);
            } else if (!empty($tags)) {
                $tagsItems = self::getExcludeItems($tags, $colors, $general, $cat, $unpublishCats, $id);
            }
            if (!empty($colors) && $general->colors_method == 'include') {
                $colorsItems = self::getIncludeItems($colors, '`#__bagallery_colors_map` AS m', 'color_id', $cat, $unpublishCats, $id);
            } else if (!empty($colors)) {
                $colorsItems = self::getExcludeItems($tags, $colors, $general, $cat, $unpublishCats, $id);
            }
            $items = array_merge($tagsItems, $colorsItems);
        } else {
            $items = self::getExcludeItems($tags, $colors, $general, $cat, $unpublishCats, $id);
        }

        return $items;
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

    public static function hexToRgba($hex, $a = 1)
    {
        $hex = str_replace('#', '', $hex);
        $hex = str_split($hex, 2);
        $rgba = [];
        for ($i = 0; $i < 3; $i++) {
            $rgba[] = hexdec($hex[$i]);
        }
        $rgba[] = $a;

        return 'rgba('.implode(', ', $rgba).')';
    }
}

GalleryHelper::prepareParams();