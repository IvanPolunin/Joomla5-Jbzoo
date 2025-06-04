<?php
/**
* @package   BaGallery
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

namespace Balbooa\Component\Gallery\Site\Controller;

defined('_JEXEC') or die;

use Balbooa\Component\Gallery\Site\Helper\GalleryHelper;
use Balbooa\Plugin\System\Forms\Extension\Baforms;
use gdenhancer\GDEnhancer;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Helper\MediaHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Event\DispatcherInterface;

if (!function_exists('mb_strtolower')) {
    function mb_strtolower($str, $encoding = 'utf-8')
    {
        return strtolower($str);
    }
}

class GalleryController extends FormController
{
    protected $option = 'com_bagallery';

    public function __construct($config)
    {
        if (!empty($_GET)) {
            foreach ($_GET as $key => $value) {
                if (strpos($key, 'amp;') === 0) {
                    $new_key = str_replace('amp;', '', $key);
                    $_GET[$new_key] = $value;
                    unset($_GET[$key]);
                }
            }
        }
        parent::__construct($config);
    }

    public function getLanguage()
    {
        $language = Factory::getLanguage();
        $language->load('com_bagallery', JPATH_ADMINISTRATOR);
        $result = array();
        $path = JPATH_ROOT.'/administrator/components/com_bagallery/language/en-GB/en-GB.com_bagallery.ini';
        if (File::exists($path)) {
            $contents = GalleryHelper::readFile($path);
            $contents = str_replace('_QQ_', '"\""', $contents);
            $data = parse_ini_string($contents);
            foreach ($data as $ind => $value) {
                $result[$ind] = Text::_($ind);
            }
        }
        $str = json_encode($result);
        echo $str;
        exit;
    }
    
    public function matchCategoryPassword()
    {
        $app = Factory::getApplication();
        $id = $app->input->get('id', 0, 'int');
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('password')
            ->from('#__bagallery_category')
            ->where('id = '.$id);
        $db->setQuery($query);
        $password = $db->loadResult();
        if ($password == $_POST['password']) {
            print_r('match');
        } else {
            print_r('notmatch');
        }
        exit;
    }

    public function getGalleryImageRefresh()
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input);
        $id = $data->id;
        $imageIndex = $data->imageIndex;
        $categoryNames = (array)$data->categoryNames;
        $currentUri = $data->currentUri;
        $thumbnail = GalleryHelper::getThumbnailOptions($id);
        $watermark = GalleryHelper::getWatermark($id);
        $compression = GalleryHelper::getCompression($id);
        $general = GalleryHelper::getGeneralOpions($id);
        $pagination = GalleryHelper::getPaginationOptions($id);
        $order = $data->order;
        if (!isset($order[$imageIndex])) {
            exit;
        }
        $imageId = $order[$imageIndex];
        $image = GalleryHelper::getSortImageRefresh($id, $imageId);
        if (empty($image)) {
            print_r('');
            exit;
        }
        $settings = json_decode($image->settings);
        if (isset($settings->video_id)) {
            $image->type = $settings->type;
            $image->video_id = $settings->video_id;
            $image->video_type = $settings->video_type;
        }
        $options = array($image, $categoryNames, false, $id, $currentUri,
            '', $compression, $watermark, $thumbnail, $general, $pagination);
        $html = GalleryHelper::getImageHtml($options);
        print_r($html);
        exit();
    }

    public function getGalleryImageInfinity()
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input);
        if (!isset($data->unpublishCats)) {
            $unpublishCats  = array();
        } else {
            $unpublishCats = $data->unpublishCats;
        }
        $id = $data->id;
        $tagsStr = $data->tags;
        $colorsStr = $data->colors;
        $tags = !empty($tagsStr) ? explode(',', $tagsStr) : array();
        $colors = !empty($colorsStr) ? explode(',', $colorsStr) : array();
        $category = $data->category;
        $imageIndex = $data->imageIndex;
        $currentPage = $data->page;
        $currentUri = $data->currentUri;
        $categoryNames = (array)$data->categoryNames;
        $catImageCount = $data->catImageCount;
        $thumbnail = GalleryHelper::getThumbnailOptions($id);
        $watermark = GalleryHelper::getWatermark($id);
        $compression = GalleryHelper::getCompression($id);
        $general = GalleryHelper::getGeneralOpions($id);
        $pagination = GalleryHelper::getPaginationOptions($id);
        $sorting = GalleryHelper::getSorting($id);
        $order = explode('-_-', $sorting);
        if (!empty($tags) || !empty($colors)) {
            $items = GalleryHelper::getTagsColorsItems($tags, $colors, $general, $category, $unpublishCats, $id);
            $emptyArray = array();
            foreach ($order as $value) {
                if (in_array($value, $items)) {
                    $emptyArray[] = $value;
                }
            }
            $order = $emptyArray;
        } else if ($category != 'category-0') {
            $db = Factory::getDbo();
            $query = $db->getQuery(true);
            $query->select('i.imageId')
                ->from('`#__bagallery_items` AS i')
                ->where('i.form_id = '.$id)
                ->where('i.category = '.$db->quote($db->escape($category, true)));
            $db->setQuery($query);
            $items = $db->loadColumn();
            $emptyArray = array();
            foreach ($order as $value) {
                if (in_array($value, $items)) {
                    $emptyArray[] = $value;
                }
            }
            $order = $emptyArray;
        } else {
            if (!empty($unpublishCats)) {
                $ind = 0;
                foreach ($catImageCount as $key => $value) {
                    if (in_array(str_replace('category-', '', $key), $unpublishCats)) {
                        for ($i = $ind; $i < $value + $ind; $i++) {
                            unset($order[$i]);
                        }
                    }
                    $ind += $value;
                }
                $newSort = array();
                foreach ($order as $value) {
                    $newSort[] = $value;
                }
                $order = $newSort;
            }
        }
        $imageId = $order[$imageIndex];
        $image = GalleryHelper::getSortImage($id, $imageId, $unpublishCats);
        if (empty($image)) {
            print_r('');
            exit;
        }
        $settings = json_decode($image->settings);
        if (isset($settings->video_id)) {
            $image->type = $settings->type;
            $image->video_id = $settings->video_id;
            $image->video_type = $settings->video_type;
        }
        $options = array($image, $categoryNames, false, $id, $currentUri,
            '', $compression, $watermark, $thumbnail, $general, $pagination);
        $html = GalleryHelper::getImageHtml($options);
        print_r($html);
        exit();
    }

    public function getGalleryImages()
    {
        if (!isset($_POST['unpublishCats'])) {
            $unpublishCats  = array();
        } else {
            $unpublishCats = $_POST['unpublishCats'];
        }
        $app = Factory::getApplication();
        $id = $app->input->get('id', 0, 'int');
        $tagsStr = $app->input->get('tags', '', 'string');
        $colorsStr = $app->input->get('colors', '', 'string');
        $tags = !empty($tagsStr) ? explode(',', $tagsStr) : array();
        $colors = !empty($colorsStr) ? explode(',', $colorsStr) : array();
        $category = $app->input->get('category', '', 'string');
        $activeImage = $app->input->get('activeImage', '', 'string');
        $url = urldecode($activeImage);
        $url = explode('?', $url);
        $url = end($url);
        $img = false;
        if (!empty($url)) {
            $img = GalleryHelper::checkImage($url);
        }
        if (!$img && is_numeric($url)) {
            $img = GalleryHelper::getImage($url);
        }
        $currentPage = $app->input->get('page', 0, 'int');
        $currentUri = $app->input->get('currentUri', '', 'string');
        $categoryNames = $app->input->get('categoryNames', array(), 'array');
        $catImageCount = $app->input->get('catImageCount', array(), 'array');
        $thumbnail = GalleryHelper::getThumbnailOptions($id);
        $watermark = GalleryHelper::getWatermark($id);
        $compression = GalleryHelper::getCompression($id);
        $general = GalleryHelper::getGeneralOpions($id);
        $pagination = GalleryHelper::getPaginationOptions($id);
        $sorting = GalleryHelper::getSorting($id);
        $order = explode('-_-', $sorting);
        if (!empty($tags) || !empty($colors)) {
            $items = GalleryHelper::getTagsColorsItems($tags, $colors, $general, $category, $unpublishCats, $id);
            $emptyArray = array();
            foreach ($order as $value) {
                if (in_array($value, $items)) {
                    $emptyArray[] = $value;
                }
            }
            $order = $emptyArray;
        } else if ($category != 'category-0') {
            $db = Factory::getDbo();
            $query = $db->getQuery(true);
            $query->select('i.imageId')
                ->from('`#__bagallery_items` AS i')
                ->where('i.form_id = '.$id)
                ->where('i.category = '.$db->quote($db->escape($category, true)));
            $db->setQuery($query);
            $items = $db->loadColumn();
            $emptyArray = array();
            foreach ($order as $value) {
                if (in_array($value, $items)) {
                    $emptyArray[] = $value;
                }
            }
            $order = $emptyArray;
        } else {
            if (!empty($unpublishCats)) {
                $ind = 0;
                foreach ($catImageCount as $key => $value) {
                    if (in_array(str_replace('category-', '', $key), $unpublishCats)) {
                        for ($i = $ind; $i < $value + $ind; $i++) {
                            unset($order[$i]);
                        }
                    }
                    $ind += $value;
                }
                $newSort = array();
                foreach ($order as $value) {
                    $newSort[] = $value;
                }
                $order = $newSort;
            }
        }
        $catImages = count($order);
        $list = implode(', ', $order);
        $height2 = array();
        $width2 = array();
        $total = GalleryHelper::getImageCount($id, 'category-0');
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
        $items = array();
        $thumbCategory = '';
        $ind = 0;
        $start = 0;
        $end = $pagination->images_per_page + $start;
        $start += $pagination->images_per_page * ($currentPage - 1);
        if ($start > $catImages) {
            $start = $catImages;
        }
        $end = $start + $pagination->images_per_page;
        if ($end > $catImages) {
            $end = $catImages;
        }
        $html = '';
        $flag = false;
        for ($i = $start; $i < $end; $i++) {
            $imageId = $order[$i];
            $image = GalleryHelper::getSortImage($id, $imageId, $unpublishCats);
            if (empty($image)) {
                continue;
            }
            if ($thumbCategory != $image->category) {
                $ind = 0;
                $thumbCategory = $image->category;
            }
            $className = '';
            if (in_array($ind + 1, $height2)) {
                $className .= ' height2';
            }
            if (in_array($ind + 1, $width2)) {
                $className .= ' width2';
            }
            $settings = json_decode($image->settings);
            if (isset($settings->video_id)) {
                $image->type = $settings->type;
                $image->video_id = $settings->video_id;
                $image->video_type = $settings->video_type;
            }
            $options = array($image, $categoryNames, false, $id, $currentUri,
                $className, $compression, $watermark, $thumbnail, $general, $pagination);
            $html .= GalleryHelper::getImageHtml($options);
            $ind++;
            if ($img && $image->id == $img->id) {
                $flag = true;
            }
            if ($img && !$flag && $end == $i + 1) {
                $end = $end + $pagination->images_per_page * 1;
                if ($end > $catImages) {
                    $end = $catImages;
                }
            }
        }
        print_r($html);
        exit;
    }

    public function checkForms()
    {
        $data = $this->input->get('data', '', 'raw');
        if (class_exists('Baforms')) {
            $plugin = new Baforms(
                DispatcherInterface::class,
                (array) PluginHelper::getPlugin('system', 'Baforms')
            );
            $data = $plugin->getContent($data);
            if (JVERSION >= '4.0.0') {
                $src = Uri::root(true).'/media/vendor/jquery/js/jquery.min.js';
            } else {
                $src = Uri::root(true).'/media/jui/js/jquery.min.js';
            }
            $str = '<script type="text/javascript" src="'.$src.'"></script>';
            $data = str_replace($str, '', $data);
        }
        echo $data;
        exit;
    }

    public function getCategories()
    {
        $app = Factory::getApplication();
        $id = $app->input->get('gallery', 0, 'int');
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('id, title, settings')
            ->from('#__bagallery_category')
            ->where('`form_id` = '.$id)
            ->order('orders ASC');
        $db->setQuery($query);
        $result = $db->loadObjectList();
        print_r(json_encode($result));
        exit;
    }

    public function getModel($name = 'gallery', $prefix = 'bagalleryModel', $config = ['ignore_request' => true]) 
	{
        $model = parent::getModel($name, $prefix, $config);
        
        return $model;
	}

    public function setAppLicense()
    {
        GalleryHelper::setAppLicense();
        header('Content-Type: text/javascript');
        echo 'var domainResponse = true;';
        exit();
    }

    public function setAppLicenseForm()
    {
        GalleryHelper::setAppLicense();
        header('Location: https://www.balbooa.com/user/downloads/licenses');
        exit();
    }

    public function save($key = null, $urlVar = null)
    {
        $data = $this->input->post->get('jform', array(), 'array');
        $id = $data['id'];
        /** @var Balbooa\Component\Gallery\Site\Model */
        $model = $this->getModel();
        if ($model->save($data)) {
            $this->setRedirect(
                Route::_(
                    'index.php?option=' . $this->option . '&view=gallery&tmpl=component&id='.$id, false
                ), Text::_('JLIB_APPLICATION_SAVE_SUCCESS')
            );
        }        
    }

    public function formUpload()
    {
        $input = Factory::getApplication()->input;
        $items = $input->files->get('files', '', 'array');
        $dir = GalleryHelper::$thumbnails_base. '/bagallery';
        $contentLength = (int) $_SERVER['CONTENT_LENGTH'];
        $mediaHelper = new MediaHelper;
        $uploadMaxFileSize = $mediaHelper->toBytes(ini_get('upload_max_filesize'));
        if (!Folder::exists($dir)) {
            Folder::create($dir, 0755);
        }
        $dir .= '/original/';
        if (!Folder::exists($dir)) {
            Folder::create($dir, 0755);
        }
        $images = array();
        foreach($items as $item) {
            $flag = true;
            if (($item['error'] == 1) || ($uploadMaxFileSize > 0 && $item['size'] > $uploadMaxFileSize)) {
                $flag = false;
            }
            $ext = strtolower(File::getExt($item['name']));
            if ($this->checkExt($ext) && $flag) {
                $name = str_replace('.'.$ext, '', $item['name']);
                $file = $this->replace($name);
                $file = File::makeSafe($file.'.'.$ext);
                $name = str_replace('-', '', $file);
                $name = str_replace($ext, '', $name);
                $name = str_replace('.', '', $name);
                if ($name == '') {
                    $file = date("Y-m-d-H-i-s").'.'.$ext;
                }
                $file = GalleryHelper::checkFileName($dir, $file);
                if (GalleryHelper::canComress($ext)) {
                    $file = GalleryHelper::compressImage($item['tmp_name'], $dir, $file, $ext, false);
                } else {
                    File::upload($item['tmp_name'], $dir.$file);
                }
                $pos = strlen(JPATH_ROOT);
                $path = substr($dir, $pos);
                $image = new \stdClass;
                $image->name = $file;
                $image->path = $image->url = $path.$file;
                $image->size = filesize(JPATH_ROOT.$image->path);
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

    public function uploadAjax()
    {
        $dir = GalleryHelper::$thumbnails_base.'/bagallery';
        $file = $_GET['file'];
        $ext = strtolower(File::getExt($file));
        $name = str_replace('.'.$ext, '', $file);
        $file = $this->replace($name);
        $file = File::makeSafe($file.'.'.$ext);
        $name = str_replace('-', '', $file);
        $name = str_replace($ext, '', $name);
        $name = str_replace('.', '', $name);
        if ($name == '') {
            $file = date("Y-m-d-H-i-s").'.'.$ext;
        }
        if (!Folder::exists($dir)) {
            Folder::create($dir, 0755);
        }
        $dir .= '/original/';
        if (!Folder::exists($dir)) {
            Folder::create($dir, 0755);
        }
        $file = GalleryHelper::checkFileName($dir, $file);
        if ($this->checkExt($ext)) {
            if (GalleryHelper::canComress($ext)) {
                $file = GalleryHelper::compressImage(file_get_contents('php://input'), $dir, $file, $ext);
            } else {
                file_put_contents($dir.$file, file_get_contents('php://input'));
            }
            $pos = strlen(JPATH_ROOT);
            $dir = substr($dir, $pos);
            $image = new \stdClass;
            $image->name = $file;
            $image->path = $image->url = $dir.$file;
            $image->size = filesize(JPATH_ROOT.$image->path);
            echo json_encode($image);
        }
        exit;
    }

    public function replace($str)
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

    public function getAlias($alias, $table, $name = 'lightboxUrl', $id = 0)
    {
        jimport('joomla.filter.output');
        $alias = $this->replace($alias);
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
            $alias = GalleryHelper::increment($alias);
            $alias = $this->getAlias($alias, $table, $name);
        }
        return $alias;
    }

    public function checkExt($ext)
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

    public function getSession()
    {
        $session = Factory::getSession();
        echo new JsonResponse($session->getState());
        exit;
    }

    public function clearOld()
    {
        $app = Factory::getApplication();
        $itemId = $app->input->get('gallery_items', '', 'string');
        $allThumb = $app->input->get('allThumb', '', 'string');
        $allCat = $app->input->get('allCat', '', 'string');
        $formId = $app->input->get('ba_id', 0, 'int');
        $itemId = json_decode($itemId);
        $allThumb = json_decode($allThumb);
        $allCat = json_decode($allCat);
        $allThumb = get_object_vars($allThumb);
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select("id")
            ->from("#__bagallery_items")
            ->where("form_id=" . $db->Quote($formId));
        $db->setQuery($query);
        $items = $db->loadColumn();
        /** @var Balbooa\Component\Gallery\Site\Model */
        $model = $this->getModel();
        foreach ($items as $id) {
            if (!in_array($id, $itemId)) {
                $query = $db->getQuery(true);
                $thumbnail = GalleryHelper::getThumbnail($id);
                $dir = JPATH_ROOT. '/'.$thumbnail;
                if (File::exists($dir)) {
                    File::delete($dir);
                }
                $conditions = array(
                    $db->quoteName('id'). '=' .$id
                );
                $query->delete($db->quoteName('#__bagallery_items'))
                    ->where($conditions);
                $db->setQuery($query)
                    ->execute();
            }
        }
        $model->clearImageDirectory($formId, $allCat, $allThumb);
        jexit();
    }

    public function checkTag($title)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('id')
            ->from('#__bagallery_tags')
            ->where('title = '.$db->quote($title));
        $db->setQuery($query);
        $id = $db->loadResult();

        return $id;
    }

    public function saveTags($tags, $id, $gallery)
    {
        $db = Factory::getDbo();
        $ids = array();
        foreach ($tags as $tag) {
            if (strpos($tag->id, 'new$') === false) {
                $ids[] = $tag->id;
            }
        }
        if (!empty($ids)) {
            $str = implode(', ', $ids);
            $query = $db->getQuery(true)
                ->delete('#__bagallery_tags_map')
                ->where('image_id = '.$id)
                ->where('tag_id NOT IN ('.$str.')');
            $db->setQuery($query)
                ->execute();
        }
        foreach ($tags as $tag) {
            $tagId = $tag->id;
            if (strpos($tagId, 'new$') !== false) {
                $tagId = $this->checkTag($tag->title);
                if (empty($tagId)) {
                    $object = new \stdClass();
                    $object->title = $tag->title;
                    $object->alias = $object->title;
                    $object->alias = $this->getAlias($object->alias, '#__bagallery_tags', 'alias');
                    $db->insertObject('#__bagallery_tags', $object);
                    $tagId = $db->insertid();
                }
            }
            $query = $db->getQuery(true)
                ->select('id')
                ->from('#__bagallery_tags_map')
                ->where('`image_id` = '.$id)
                ->where('`tag_id` = '.$tagId);
            $db->setQuery($query);
            $item = $db->loadResult();
            if (empty($item)) {
                $obj = new \stdClass();
                $obj->image_id = $id;
                $obj->tag_id = $tagId;
                $obj->gallery_id = $gallery;
                $db->insertObject('#__bagallery_tags_map', $obj);
            }
            $tag->id = $tagId;
        }

        return $tags;
    }

    public function checkColor($title)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('id')
            ->from('#__bagallery_colors')
            ->where('title = '.$db->quote($title));
        $db->setQuery($query);
        $id = $db->loadResult();

        return $id;
    }

    public function saveColors($colors, $id, $gallery)
    {
        $db = Factory::getDbo();
        $ids = array();
        foreach ($colors as $color) {
            if (strpos($color->id, 'new$') === false) {
                $ids[] = $color->id;
            }
        }
        if (!empty($ids)) {
            $str = implode(', ', $ids);
            $query = $db->getQuery(true)
                ->delete('#__bagallery_colors_map')
                ->where('image_id = '.$id)
                ->where('color_id NOT IN ('.$str.')');
            $db->setQuery($query)
                ->execute();
        }
        foreach ($colors as $color) {
            $colorId = $color->id;
            if (strpos($colorId, 'new$') !== false) {
                $colorId = $this->checkColor($color->title);
                if (empty($colorId)) {
                    $object = new \stdClass();
                    $object->title = $color->title;
                    $object->alias = $object->title;
                    $object->alias = $this->getAlias($object->alias, '#__bagallery_colors', 'alias');
                    $db->insertObject('#__bagallery_colors', $object);
                    $colorId = $db->insertid();
                }
            }
            $query = $db->getQuery(true)
                ->select('id')
                ->from('#__bagallery_colors_map')
                ->where('`image_id` = '.$id)
                ->where('`color_id` = '.$colorId);
            $db->setQuery($query);
            $item = $db->loadResult();
            if (empty($item)) {
                $obj = new \stdClass();
                $obj->image_id = $id;
                $obj->color_id = $colorId;
                $obj->gallery_id = $gallery;
                $db->insertObject('#__bagallery_colors_map', $obj);
            }
            $color->id = $colorId;
        }

        return $colors;
    }

    public function saveItems()
    {
        if (!Factory::getUser()->authorise('core.edit', 'com_bagallery')) {
            return false;
        }
        $app = Factory::getApplication();
        $formId = $app->input->get('ba_id', 0, 'int');
        $items = $_POST['gallery_items'];
        $items = json_decode($items);
        /** @var Balbooa\Component\Gallery\Site\Model */
        $model = $this->getModel();
        $id = array();
        $db = Factory::getDbo();
        foreach ($items as $item) {
            $obj = $item;
            $obj = $model->checkObj($obj);
            $obj->path = GalleryHelper::replaceLongPath($obj->path);
            $obj->url = GalleryHelper::replaceLongPath($obj->path);
            if (empty($obj->lightboxUrl)) {
                $obj->lightboxUrl = $obj->title;
            }
            if (isset($obj->tags)) {
                $tags = $obj->tags;
                unset($obj->tags);
            } else {
                $tags = [];
            }
            if (isset($obj->colors)) {
                $colors = $obj->colors;
                unset($obj->colors);
            } else {
                $colors = [];
            }
            $object = new \stdClass();
            $object->form_id = $formId;
            $object->category = $obj->category;
            $object->name = $obj->name;
            $object->path = $obj->path;
            $object->url = $obj->url;
            $object->thumbnail_url = $obj->thumbnail_url;
            $object->title = $obj->title;
            $object->short = $obj->short;
            $object->alt = $obj->alt;
            $object->description = $obj->description;
            $object->link = $obj->link;
            $object->video = $obj->video;
            $object->settings = $item;
            $object->imageId = $obj->imageId;
            $object->target = $obj->target;
            $object->watermark_name = $obj->watermark_name;
            $object->lightboxUrl = $obj->lightboxUrl;
            $object->hideInAll = $obj->hideInAll;
            if (!isset($obj->id)) {
                if (!empty($obj->lightboxUrl)) {
                    $object->lightboxUrl = $this->getAlias($obj->lightboxUrl, '#__bagallery_items', 'lightboxUrl');
                    $obj->lightboxUrl = $object->lightboxUrl;
                }
                $object->settings = '{}';
                $db->insertObject('#__bagallery_items', $object);
                $object->id = $obj->id = $db->insertid();
                $id[] = $object->id;
            } else {
                if (!empty($obj->lightboxUrl)) {
                    $obj->lightboxUrl = $this->getAlias($obj->lightboxUrl, '#__bagallery_items', 'lightboxUrl', $obj->id);
                    $object->lightboxUrl = $obj->lightboxUrl;
                }
                $object->id = $obj->id;
            }
            $obj->tags = $this->saveTags($tags, $obj->id, $formId);
            $obj->colors = $this->saveColors($colors, $obj->id, $formId);
            $object->settings = json_encode($obj);
            $db->updateObject('#__bagallery_items', $object, 'id');
        }
        $id = json_encode($id);
        echo new JsonResponse(true, $id);
        jexit();
    }

    public function emptyAlbums()
    {
        $app = Factory::getApplication();
        $id = $app->input->get('ba_id', 0, 'int');
        if (!empty($id)) {
            $dir = GalleryHelper::$thumbnails_base. '/bagallery/gallery-'.$id.'/album';
            if (Folder::exists($dir)) {
                GalleryHelper::deleteFolder($dir);
            }
        }
    }

    public function emptyThumbnails()
    {
        $app = Factory::getApplication();
        $id = $app->input->get('ba_id', 0, 'int');
        if (!empty($id)) {
            $dir = GalleryHelper::$thumbnails_base.'/bagallery/gallery-'.$id.'/thumbnail';
            if (Folder::exists($dir)) {
                GalleryHelper::deleteFolder($dir);
            }
        }
    }

    public function removeWatermark()
    {
        $app = Factory::getApplication();
        $id = $app->input->get('ba_id', 0, 'int');
        if (!empty($id)) {
            $dir = GalleryHelper::$thumbnails_base.'/bagallery/gallery-'.$id.'/watermark';
            if (Folder::exists($dir)) {
                GalleryHelper::deleteFolder($dir);
            }
        }
    }

    public function compressionImage()
    {
        $app = Factory::getApplication();
        $id = $app->input->get('id', 0, 'int');
        $str = 'g.compression_width, g.compression_quality, g.watermark_upload, g.watermark_position,';
        $str .= ' g.watermark_opacity, g.scale_watermark, g.id';
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('i.watermark_name, i.path')
            ->from('`#__bagallery_items` as i')
            ->where('i.id = '.$id)
            ->leftJoin('`#__bagallery_galleries` as g ON '. $db->quoteName('i.form_id').' = '.$db->quoteName('g.id'))
            ->select($str);
        $db->setQuery($query);
        $item = $db->loadObject();
        $item->path = GalleryHelper::replaceLongPath($item->path);
        $item->watermark_upload = GalleryHelper::replaceLongPath($item->watermark_upload);
        $root = JPATH_ROOT;
        if ($root == '/') {
            $root = '';
        }
        if ($item->path != '/') {
            $item->path = '/'.$item->path;
        }
        if (!empty($item->watermark_upload) && $item->watermark_upload != '/') {
            $item->watermark_upload = '/'.$item->watermark_upload;
        }
        $image = $root.$item->path;
        $name = $item->watermark_name;
        $gallery = $item->id;
        $watermark = $root.$item->watermark_upload;
        $position = $item->watermark_position;
        $opacity = $item->watermark_opacity;
        $scale = $item->scale_watermark;
        $ext = strtolower(File::getExt($image));
        $file_path = GalleryHelper::$params->file_path;
        $dir = $root.'/'.$file_path.'/bagallery/gallery-'.$gallery.'/compression/';
        if (!Folder::exists($dir)) {
            Folder::create($dir, 0755);
        }
        $file = $dir.$name;
        if (empty($name) || !File::exists($dir.$name)) {
            $width = $height = $item->compression_width;
            $quality = $item->compression_quality;
            if (empty($name)) {
                $name = basename($image);
                if (File::exists($dir.$name)) {
                    $name = rand(0, 999999999).'-'.$name;
                }
            }
            $imageCreate = $this->imageCreate($ext);
            $imageSave = $this->imageSave($ext);
            $orig = $imageCreate($image);
            $orig = $this->checkExif($image, $orig, $ext);
            $origWidth = imagesx($orig);
            $origHeight = imagesy($orig);
            $ratio = $origWidth / $origHeight;
            if ($origWidth > $origHeight) {
                if ($origWidth >= $width) {
                    $height = round($width / $ratio);
                } else {
                    $width = $origWidth;
                    $height = $origHeight;
                }
            } else {
                if ($origHeight >= $height) {
                    $width = round($ratio * $height);
                } else {
                    $width = $origWidth;
                    $height = $origHeight;
                }
            }
            $sx = 0;
            $sy = 0;
            $w = $origWidth;
            $h = $origHeight;
            if ($ext == 'gif' && empty($item->watermark_upload)) {
                include_once(JPATH_COMPONENT.'/libraries/gdenhancer/GDEnhancer.php');
                $gd = new GDEnhancer($image);
                $gd->backgroundResize($width, $height);
                $gd->saveTo($dir.$name, 'default', true, $quality * 1);
            } else {
                $out = imagecreatetruecolor($width, $height);
                /*
                if ($ext == 'png') {
                    imagealphablending($out, false);
                    imagesavealpha($out, true);
                    $transparent = imagecolorallocatealpha($out, 255, 255, 255, 127);
                    imagefilledrectangle($out, 0, 0, $width, $height, $transparent);
                }
                */
                imagecopyresampled($out, $orig, 0, 0, $sx, $sy, $width, $height, $w, $h);
                if (!empty($item->watermark_upload) && File::exists($watermark)) {
                    $ex = strtolower(File::getExt($watermark));
                    $imageCreate = $this->imageCreate($ex);
                    $stamp = $imageCreate($watermark);
                    $marge_right = 10;
                    $sx = imagesx($stamp);
                    $sy = imagesy($stamp);
                    $xx = $width;
                    $yy = $height;
                    if ($scale == 1) {
                        $ratio = $sy / $sx;
                        $width = floor($xx * 0.1)  - $marge_right * 2;
                        $height = $width * $ratio;
                        $stamp = $this->resizeImage($stamp, $width, $height);
                        $sx = imagesx($stamp);
                        $sy = imagesy($stamp);
                    }
                    switch ($position) {
                        case 'top_left':
                            $x = $marge_right;
                            $y = $marge_right;
                            break;
                        case 'top_right':
                            $x = $xx - $sx - $marge_right;
                            $y = $marge_right;
                            break;
                        case 'bottm_left':
                        case 'bottom_left':
                            $x = $marge_right;
                            $y = $yy - $sy - $marge_right;
                            break;
                        case 'bottom_right':
                            $x = $xx - $sx - $marge_right;
                            $y = $yy - $sy - $marge_right;
                            break;
                        case 'center':
                            $x = $xx / 2 - $sx / 2;
                            $y = $yy / 2 - $sy / 2;
                    }
                    $this->imagecopymerge_alpha($out, $stamp, $x, $y, 0, 0, $sx, $sy, $opacity, $ext);
                }
                $file = $dir.$name;
                if ($ext == 'png') {
                    $quality = round($quality / 11.111111111111);
                    $imageSave($out, $file, 9 - $quality);
                } else if ($ext == 'gif') {
                    $imageSave($out, $file);
                } else {
                    $imageSave($out, $file, $quality);
                }
            }
            $this->setWatermarkName($id, $name);
        }
        //echo Uri::root().$file_path.'/bagallery/gallery-'.$gallery.'/compression/'.$name;
        header('Location: '.Uri::root().$file_path.'/bagallery/gallery-'.$gallery.'/compression/'.$name);
        exit;
    }

    public function setAppLicenseActivation()
    {
        GalleryHelper::setAppLicenseActivation();
        header('Content-Type: text/javascript');
        echo 'var domainResponse = true;';
        exit();
    }

    public function addWatermark()
    {
        $app = Factory::getApplication();
        $id = $app->input->get('id', 0, 'int');
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('i.watermark_name, i.path')
            ->from('`#__bagallery_items` as i')
            ->where('i.id = '.$id)
            ->leftJoin('`#__bagallery_galleries` as g ON '. $db->quoteName('i.form_id').' = '.$db->quoteName('g.id'))
            ->select('g.watermark_upload, g.watermark_position, g.watermark_opacity, g.scale_watermark, g.id');
        $db->setQuery($query);
        $item = $db->loadObject();
        $item->path = GalleryHelper::replaceLongPath($item->path);
        $item->watermark_upload = GalleryHelper::replaceLongPath($item->watermark_upload);
        $root = JPATH_ROOT;
        if ($root == '/') {
            $root = '';
        }
        if ($item->path != '/') {
            $item->path = '/'.$item->path;
        }
        if ($item->watermark_upload != '/') {
            $item->watermark_upload = '/'.$item->watermark_upload;
        }
        $img = $root.$item->path;
        $watermark = $root.$item->watermark_upload;
        $position = $item->watermark_position;
        $opacity = $item->watermark_opacity;
        $scale = $item->scale_watermark;
        $name = $item->watermark_name;
        $gallery = $item->id;
        $file_path = GalleryHelper::$params->file_path;
        $dir = $root. '/'.$file_path.'/bagallery/gallery-' .$gallery;
        $ext = strtolower(File::getExt($img));
        if (!Folder::exists($dir)) {
            Folder::create($dir, 0755);
        }
        $dir .= '/watermark/';
        if (!Folder::exists($dir)) {
            Folder::create($dir, 0755);
        }
        if (empty($name) || !File::exists($dir.$name)) {
            if (empty($name)) {
                $name = basename($img);
                if (File::exists($dir.$name)) {
                    $name = rand(0, 999999999).'-'.$name;
                }
            }
            $file = $dir.$name;
            if ($ext == 'gif') {
                include_once(JPATH_COMPONENT.'/libraries/gdenhancer/GDEnhancer.php');
                $gd = new GDEnhancer($img);
                $gd->layerImage($watermark);
                $imageCreate = $this->imageCreate($ext);
                $im = $imageCreate($img);
                $ex = strtolower(File::getExt($watermark));
                $imageCreate = $this->imageCreate($ex);
                $stamp = $imageCreate($watermark);
                $marge_right = 10;
                $sx = imagesx($stamp);
                $sy = imagesy($stamp);
                $xx = imagesx($im);
                $yy = imagesy($im);
                if ($scale == 1) {
                    $ratio = $sy / $sx;
                    $width = floor( imagesx($im) * 0.1)  - $marge_right * 2;
                    $height = $width * $ratio;
                    $stamp = $this->resizeImage($stamp, $width, $height);
                    $sx = imagesx($stamp);
                    $sy = imagesy($stamp);
                    $gd->layerImageResize(0, $width, $height, 'shrink');
                }
                switch ($position) {
                    case 'top_left':
                        $x = $marge_right;
                        $y = $marge_right;
                        break;
                    case 'top_right':
                        $x = $marge_right * -1;
                        $y = $marge_right;
                        break;
                    case 'bottm_left':
                    case 'bottom_left':
                        $x = $marge_right;
                        $y = $marge_right * -1;
                        break;
                    case 'bottom_right':
                        $x = $marge_right * -1;
                        $y = $marge_right * -1;
                        break;
                    case 'center':
                        $x = 0;
                        $y = 0;
                        break;
                }
                $position = str_replace('_', '', $position);
                $gd->layerMove(0, $position, $x, $y);
                $gd->saveTo($file, 'default', true);
            } else {
                $imageCreate = $this->imageCreate($ext);
                $imageSave = $this->imageSave($ext);
                $im = $imageCreate($img);
                $im = $this->checkExif($img, $im, $ext);
                $ex = strtolower(File::getExt($watermark));
                $imageCreate = $this->imageCreate($ex);
                $stamp = $imageCreate($watermark);
                $marge_right = 10;
                $sx = imagesx($stamp);
                $sy = imagesy($stamp);
                $xx = imagesx($im);
                $yy = imagesy($im);
                if ($scale == 1) {
                    $ratio = $sy / $sx;
                    $width = floor(imagesx($im) * 0.1)  - $marge_right * 2;
                    $height = $width * $ratio;
                    $stamp = $this->resizeImage($stamp, $width, $height);
                    $sx = imagesx($stamp);
                    $sy = imagesy($stamp);
                }

                switch ($position) {
                    case 'top_left':
                        $x = $marge_right;
                        $y = $marge_right;
                        break;
                    case 'top_right':
                        $x = $xx - $sx - $marge_right;
                        $y = $marge_right;
                        break;
                    case 'bottm_left':
                    case 'bottom_left':
                        $x = $marge_right;
                        $y = $yy - $sy - $marge_right;
                        break;
                    case 'bottom_right':
                        $x = $xx - $sx - $marge_right;
                        $y = $yy - $sy - $marge_right;
                        break;
                    case 'center':
                        $x = $xx / 2 - $sx / 2;
                        $y = $yy / 2 - $sy / 2;
                        break;
                }
                $this->imagecopymerge_alpha($im, $stamp, $x, $y, 0, 0, $sx, $sy, $opacity, $ext);
                if ($ext == 'png') {
                    $imageSave($im, $file, 9);
                } else if ($ext == 'gif') {
                    $imageSave($im, $file);
                } else {
                    $imageSave($im, $file, 100);
                }
            }
            $this->setWatermarkName($id, $name);
            imagedestroy($im);
            imagedestroy($stamp);
        } else {
            $file = $dir.$name;
        }

        //echo Uri::root().$file_path.'/bagallery/gallery-'.$gallery.'/watermark/'.$name;
        header('Location: '.Uri::root().$file_path.'/bagallery/gallery-'.$gallery.'/watermark/'.$name);
        exit;
    }

    public function setWatermarkName($id, $name)
    {
        $db = Factory::getDbo();
        $obj = new \stdClass();
        $obj->id = $id;
        $obj->watermark_name = $name;
        $db->updateObject('#__bagallery_items', $obj, 'id');        
    }

    public function resizeImage($image, $width, $height) {
        $new_image = imagecreatetruecolor($width, $height);
        imagealphablending($image, false);
        imagealphablending($new_image, true);
        $trans_layer_overlay = imagecolorallocatealpha($new_image, 0, 0, 200, 127);
        imagefill($new_image, 0, 0, $trans_layer_overlay);
        imagesavealpha($new_image, true);
        imagecopyresampled($new_image, $image, 0, 0, 0, 0, $width, $height, imagesx($image), imagesy($image));
        imagedestroy($image);

        return $new_image;
    }

    public function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $opacity, $ext)
    {
        if (!isset($opacity)) {
            return false;
        }
        $opacity /= 100;
        $w = imagesx($src_im);
        $h = imagesy($src_im);
        imagealphablending($src_im, false);
        $minalpha = 127;
        for ($x = 0; $x < $w; $x++)
            for ($y = 0; $y < $h; $y++){
                $alpha = (imagecolorat($src_im, $x, $y) >> 24 ) & 0xFF;
                if ($alpha < $minalpha){
                    $minalpha = $alpha;
                }
            }
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $colorxy = imagecolorat( $src_im, $x, $y );
                $alpha = ( $colorxy >> 24 ) & 0xFF;
                if ($minalpha !== 127){
                    $alpha = 127 + 127 * $opacity * ( $alpha - 127 ) / ( 127 - $minalpha );
                } else {
                    $alpha += 127 * $opacity;
                }
                if ($ext == 'png'/* || $ext == 'webp'*/) {
                    $alphacolorxy = imagecolorallocatealpha($src_im, ($colorxy >> 16) & 0xFF, ($colorxy >> 8) & 0xFF, $colorxy & 0xFF, $alpha );
                } else {
                    $alphacolorxy = imagecolorallocatealpha($dst_im, ($colorxy >> 16) & 0xFF, ($colorxy >> 8) & 0xFF, $colorxy & 0xFF, $alpha );
                }
                if (!imagesetpixel($src_im, $x, $y, $alphacolorxy)){
                    return false;
                }
            }
        }
        imagecopy($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h);
    }

    public function showAdminImage()
    {
        $dir = $_GET['image'];
        $dir = GalleryHelper::replaceLongPath($dir);
        if ($dir[0] != '/') {
            $dir = '/'.$dir;
        }
        $dir = JPATH_ROOT.$dir;
        $ext = strtolower(File::getExt($dir));
        $imageCreate = $this->imageCreate($ext);
        $imageSave = $this->imageSave($ext);
        Header("Content-type: image/".$ext);
        $offset = 60 * 60 * 24 * 90;
        $ExpStr = "Expires: " . gmdate("D, d M Y H:i:s", time() + $offset) . " GMT";
        header($ExpStr);
        if (!$im = $imageCreate($dir)) {
            $f = fopen($dir, "r");
            fpassthru($f);
        } else {
            $width = imagesx($im);
            $height = imagesy($im);
            $ratio = $width / $height;
            if ($width > $height) {
                $w = 100;
                $h = floor(100 / $ratio);
            } else {
                $h = 100;
                $w = floor(100 * $ratio);
            }
            $out = imagecreatetruecolor($w, $h);
            if ($ext == 'png' || $ext == 'webp') {
                imagealphablending($out, false);
                imagesavealpha($out, true);
                $transparent = imagecolorallocatealpha($out, 255, 255, 255, 127);
                imagefilledrectangle($out, 0, 0, $w, $h, $transparent);
            }
            imagecopyresampled($out, $im, 0, 0, 0, 0, $w, $h, $width, $height);
            $imageSave($out);
            imagedestroy($im);
            imagedestroy($out);
        }
        exit;
    }
    
    public function imageCreate($type) {
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
    
    public function imageSave($type) {
        switch ($type) {
            case 'webp':
                $imageSave = 'imagewebp';
                break;
            case 'png':
                $imageSave = 'imagepng';
                break;
            case 'gif':
                $imageSave = 'imagegif';
                break;
            default:
                $imageSave = 'imagejpeg';
        }

        return $imageSave;
    }

    public function showCatImage()
    {
        error_reporting(-1);
        ini_set('display_errors', 1);
        $app = Factory::getApplication();
        $id = $app->input->get('id', 0, 'int');
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('`#__bagallery_category` as c')
            ->where('c.id = '.$id)
            ->leftJoin('`#__bagallery_galleries` as g ON '. $db->quoteName('c.form_id').' = '.$db->quoteName('g.id'))
            ->select('g.album_layout, g.album_quality');
        $db->setQuery($query);
        $item = $db->loadObject();
        if (empty($item->image)) {
            $item->image = '/components/com_bagallery';
            $item->image .= '/assets/images/image-placeholder.jpg';
        } else {
            $item->image = GalleryHelper::replaceLongPath($item->image);
        }
        $root = JPATH_ROOT;
        if ($root == '/') {
            $root = '';
        }
        if ($item->image[0] != '/') {
            $root .= '/';
        }
        $image = $root.$item->image;
        $width = $app->input->get('width', 250, 'int');
        $height = $app->input->get('height', 250, 'int');
        $quality = $item->album_quality;
        $category = 'category-'.$item->category_id;
        $gallery = $item->form_id;
        $layout = $item->album_layout;
        $ext = strtolower(File::getExt($image));
        $name = basename($image);
        $file_path = GalleryHelper::$params->file_path;
        $file = $root.'/'.$file_path.'/bagallery/gallery-'.$gallery.'/album/';
        if ($name == 'image-placeholder.jpg') {
            $file .= $name;
        } else {
            $file .= $category.'-'.$name;
        }
        if (!File::exists($file)) {
            $dir = $root. '/'.$file_path.'/bagallery/';
            if (!Folder::exists($dir)) {
                Folder::create($dir, 0755);
            }
            $dir .= 'gallery-'.$gallery;
            if (!Folder::exists($dir)) {
                Folder::create($dir, 0755);
            }
            $dir .= '/album';
            if (!Folder::exists($dir)) {
                Folder::create($dir, 0755);
            }
            $imageCreate = $this->imageCreate($ext);
            $imageSave = $this->imageSave($ext);
            $orig = $imageCreate($image);
            $orig = $this->checkExif($image, $orig, $ext);
            $origWidth = imagesx($orig);
            $origHeight = imagesy($orig);
            $sx = 0;
            $sy = 0;
            $w = $origWidth;
            $h = $origHeight;
            $ratio = $origWidth / $origHeight;
            if ($layout == 'random') {
                if ($origWidth > $origHeight) {
                    $height = round($width / $ratio);
                } else {
                    $width = round($ratio * $height);
                }
            } else if ($layout == 'justified') {
                $width = round($ratio * $height);
            } else {
                if ($origHeight / $origWidth > $height / $width) {
                    $h = round(($height * $origWidth) / $width);
                    $sy = round(($origHeight - $h) / 3);
                } else {
                    $w = round(($origHeight * $width) / $height);
                    $sx = round(($origWidth - $w) / 2);
                }
            }
            if ($ext == 'gif' && ($layout == 'random' || $layout == 'justified')) {
                include_once(JPATH_COMPONENT.'/libraries/gdenhancer/GDEnhancer.php');
                $gd = new GDEnhancer($image);
                $gd->backgroundResize($width, $height);
                $gd->saveTo($file, 'default', true, $quality * 1);
            } else {
                $out = imagecreatetruecolor($width, $height);
                if ($ext == 'png' || $ext == 'webp') {
                    imagealphablending($out, false);
                    imagesavealpha($out, true);
                    $transparent = imagecolorallocatealpha($out, 255, 255, 255, 127);
                    imagefilledrectangle($out, 0, 0, $width, $height, $transparent);
                }            
                imagecopyresampled($out, $orig, 0, 0, $sx, $sy, $width, $height, $w, $h);
                if ($ext == 'png') {
                    $quality = round($quality / 11.111111111111);
                    $imageSave($out, $file, 9 - $quality);
                } else if ($ext == 'gif') {
                    $imageSave($out, $file);
                } else {
                    $imageSave($out, $file, $quality);
                }
            }
        }
        $url = Uri::root().$file_path.'/bagallery/gallery-'.$gallery.'/album/';
        if ($name == 'image-placeholder.jpg') {
            $url .= $name;
        } else {
            $url .= $category.'-'.$name;
        }
        echo $url;
        exit;
    }

    public function showImage()
    {
        error_reporting(-1);
        ini_set('display_errors', 1);
        $app = Factory::getApplication();
        $id = $app->input->get('id', 0, 'int');
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('i.path, i.category')
            ->from('`#__bagallery_items` as i')
            ->where('i.id = '.$id)
            ->leftJoin('`#__bagallery_galleries` as g ON '. $db->quoteName('i.form_id').' = '.$db->quoteName('g.id'))
            ->select('g.gallery_layout, g.image_quality, g.id');
        $db->setQuery($query);
        $item = $db->loadObject();
        $item->path = GalleryHelper::replaceLongPath($item->path);
        $thumbnail = GalleryHelper::getThumbnail($id);
        $root = JPATH_ROOT;
        if ($root == '/') {
            $root = '';
        }
        if ($item->path != '/') {
            $root .= '/';
        }
        $image = $root.$item->path;
        $ext = strtolower(File::getExt($image));
        $file_path = GalleryHelper::$params->file_path;
        $file = $root.'/'.$file_path.'/'.$thumbnail;
        $layout = $item->gallery_layout;
        $width = $app->input->get('width', 250, 'int');
        $height = $app->input->get('height', 250, 'int');
        if (!empty($thumbnail)) {
            $name = explode('/', $thumbnail);
        } else {
            $name = explode('/', $image);
        }            
        $name = end($name);        
        $gallery = $item->id;
        $category = $item->category;
        if (empty($thumbnail) || strlen($thumbnail) < 10 || !File::exists($file)) {
            $quality = $item->image_quality;
            $dir = $root. '/'.$file_path.'/bagallery/';
            if (!Folder::exists($dir)) {
                Folder::create($dir, 0755);
            }
            $dir .= 'gallery-'.$gallery;
            if (!Folder::exists($dir)) {
                Folder::create($dir, 0755);
            }
            $dir .= '/thumbnail/';
            if (!Folder::exists($dir)) {
                Folder::create($dir, 0755);
            }
            $dir .= $category;
            if (!Folder::exists($dir)) {
                Folder::create($dir, 0755);
            }
            $imageCreate = $this->imageCreate($ext);
            $imageSave = $this->imageSave($ext);
            $orig = $imageCreate($image);
            $orig = $this->checkExif($image, $orig, $ext);
            $origWidth = imagesx($orig);
            $origHeight = imagesy($orig);
            $sx = 0;
            $sy = 0;
            $w = $origWidth;
            $h = $origHeight;
            $ratio = $origWidth / $origHeight;
            if ($layout == 'random') {
                if ($origWidth > $origHeight) {
                    $height = round($width / $ratio);
                } else {
                    $width = round($ratio * $height);
                }
            } else if ($layout == 'justified') {
                $width = round($ratio * $height);
            } else {
                if ($origHeight / $origWidth > $height / $width) {
                    $h = round(($height * $origWidth) / $width);
                    $sy = round(($origHeight - $h) / 3);
                } else {
                    $w = round(($origHeight * $width) / $height);
                    $sx = round(($origWidth - $w) / 2);
                }
            }
            $file = $dir. '/' .$name;
            if ($ext == 'gif' && ($layout == 'random' || $layout == 'justified')) {
                include_once(JPATH_COMPONENT.'/libraries/gdenhancer/GDEnhancer.php');
                $gd = new GDEnhancer($image);
                $gd->backgroundResize($width, $height);
                $gd->saveTo($file, 'default', true, $quality * 1);
            } else {
                $out = imagecreatetruecolor($width, $height);
                if ($ext == 'png' || $ext == 'webp') {
                    imagealphablending($out, false);
                    imagesavealpha($out, true);
                    $transparent = imagecolorallocatealpha($out, 255, 255, 255, 127);
                    imagefilledrectangle($out, 0, 0, $width, $height, $transparent);
                }            
                imagecopyresampled($out, $orig, 0, 0, $sx, $sy, $width, $height, $w, $h);
                if ($ext == 'png') {
                    $quality = round($quality / 11.111111111111);
                    $imageSave($out, $file, 9 - $quality);
                } else if ($ext == 'gif') {
                    $imageSave($out, $file);
                } else {
                    $imageSave($out, $file, $quality);
                }
            }
            $this->setThumbnail($id, '/images/bagallery/gallery-'.$gallery.'/thumbnail/'.$category.'/'.$name);
        }
        $url = Uri::root().$file_path.'/bagallery/gallery-'.$gallery.'/thumbnail/'.$category.'/'.$name;
        echo $url;
        exit;
    }

    public function checkExif($src, $img, $ext)
    {
        if (($ext == 'jpg' || $ext == 'jpeg') && function_exists('exif_read_data')) {
            $exif = @exif_read_data($src);
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
        }

        return $img;
    }

    public function setThumbnail($id, $image)
    {
        $db = Factory::getDbo();
        $obj = new \stdClass();
        $obj->id = $id;
        $obj->thumbnail_url = $image;
        $db->updateObject('#__bagallery_items', $obj, 'id');
    }

    public function setTagHit()
    {
        $app = Factory::getApplication();
        $id = $app->input->get('id', 0, 'int');
        $db = Factory::getDbo();
        $query = "UPDATE `#__bagallery_tags` ";
        $query .= "SET `hits` = `hits`+1 ";
        $query .= "WHERE `id`=" .$db->Quote($id);
        $db->setQuery($query)
            ->execute();
        exit();
    }

    public function setColorHit()
    {
        $app = Factory::getApplication();
        $id = $app->input->get('id', 0, 'int');
        $db = Factory::getDbo();
        $query = "UPDATE `#__bagallery_colors` ";
        $query .= "SET `hits` = `hits`+1 ";
        $query .= "WHERE `id`=" .$db->Quote($id);
        $db->setQuery($query)
            ->execute();
        exit();
    }
    
    public function likeIt()
    {
        $app = Factory::getApplication();
        $id = $app->input->get('image_id', 0, 'int');
        $ip = $_SERVER['REMOTE_ADDR'];
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('id')
            ->from("#__bagallery_users")
            ->where('image_id=' .$id)
            ->where('ip=' .$db->Quote($ip));
        $db->setQuery($query);
        $user = $db->loadResult();
        $query = "UPDATE `#__bagallery_items` ";
        if (!$user) {
            $query .= "SET `likes`=`likes`+1 ";
        } else {
            $query .= "SET `likes`=`likes`-1 ";
        }
        $query .= "WHERE `id`=" .$db->Quote($id);
        $db->setQuery($query)
            ->execute();
        if ($user) {
            $query = $db->getQuery(true);
            $conditions = array(
                $db->quoteName('id'). '=' .$user
            );
            $query->delete($db->quoteName('#__bagallery_users'))
                ->where($conditions);
            $db->setQuery($query)
                ->execute();
        } else {
            $query = $db->getQuery(true);
            $columns = array(
                'image_id',
                'ip',
            );
            $values = array(
                $db->quote($id),
                $db->quote($ip)
            );
            $query->insert($db->quoteName('#__bagallery_users'))
                ->columns($db->quoteName($columns))
                ->values(implode(',', $values));
            $db->setQuery($query)
                ->execute();
        }
        $likes = $this->getLikes($id);
        echo $likes;
        exit();
    }
    
    public function getLikes($id)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('likes')
            ->from('#__bagallery_items')
            ->where('id=' .$id);
        $db->setQuery($query);

        return $db->loadResult();
    }
}