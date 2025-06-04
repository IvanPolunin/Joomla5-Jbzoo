<?php
/**
* @package   BaGallery
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

namespace Balbooa\Component\Gallery\Site\Trait;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Filesystem\Folder;

trait ParamsTrait
{
    public static $params;
    public static $images_base;
    public static $thumbnails_base;

    public static function prepareParams()
    {
        $data = [
            'compress_images' => 0, 'compress_size' => 1920, 'compress_quality' => 60,
            'compress_to_webp' => 0, 'image_path' => 'images', 'file_path' => 'images'
        ];
        $params = ComponentHelper::getParams('com_bagallery');
        self::$params = new \stdClass();
        self::$params->compress_ext = ['png', 'jpg', 'jpeg', 'webp'];
        foreach ($data as $key => $value) {
            self::$params->{$key} = $params->get($key, $value);
        }
        self::$images_base = JPATH_ROOT . '/' . $params->get('image_path', 'images');
        self::$thumbnails_base = JPATH_ROOT . '/' . $params->get('file_path', 'images');
        if (!is_dir(self::$thumbnails_base.'/bagallery') || !is_dir(self::$images_base)) {
            if (!is_dir(self::$thumbnails_base . '/bagallery')) {
                Folder::create(self::$thumbnails_base . '/bagallery', 0755);

            }
            if (!is_dir(self::$images_base)) {
                Folder::create(self::$images_base, 0755);
            }
        }
    }
}