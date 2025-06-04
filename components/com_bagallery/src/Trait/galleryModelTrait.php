<?php
/**
* @package   BaGallery
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

namespace Balbooa\Component\Gallery\Site\Trait;

use Balbooa\Component\Gallery\Site\Helper\GalleryHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Language\Text;

trait galleryModelTrait
{
    public function getTags()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('id, title')
            ->from('#__bagallery_tags');
        $db->setQuery($query);
        $tags = $db->loadObjectList();

        return $tags;
    }

    public function getColors()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('id, title')
            ->from('#__bagallery_colors');
        $db->setQuery($query);
        $tags = $db->loadObjectList();

        return $tags;
    }

    public function checkObj($obj)
    {
        $obj->title = $obj->title ?? '';
        $obj->short = $obj->short ?? '';
        $obj->alt = $obj->alt ?? '';
        $obj->description = $obj->description ?? '';
        $obj->link = $obj->link ?? '';
        $obj->video = $obj->video ?? '';
        $obj->lightboxUrl = $obj->lightboxUrl ?? '';
        $obj->hideInAll = $obj->hideInAll ?? 0;

        return $obj;
    }

    public function checkName($array, $name)
    {
        if (in_array($name, $array)) {
            $name = rand(0, 999999999).'-'.$name;
            $name = $this->checkName($array, $name);
        }

        return $name;
    }

    public function getCategories()
    {
        $id = Factory::getApplication()->input->get('id', 0, 'int');
        GalleryHelper::checkCompatibility($id);
        $query = $this->_db->getQuery(true)
            ->select("*")
            ->from("#__bagallery_category")
            ->where("form_id=" . $id)
            ->order("orders ASC");
        $this->_db->setQuery($query);
        $categories = $this->_db->loadObjectList();
        if (empty($categories)) {
            $categories[] = (object)[
                'title' => Text::_('ALL'),
                'default' => 1,
                'published' => 1,
                'category_all' => 1,
                'category_id' => 0,
                'access' => 1
            ];
        }
         
        return $categories;
    }

    public function getImages()
    {
        $id = Factory::getApplication()->input->get('id', 0, 'int');
        $query = $this->_db->getQuery(true)
            ->select("settings, thumbnail_url, likes, title, short, alt, description, link, video")
            ->from("#__bagallery_items")
            ->where("form_id=" . $id)
            ->order("id ASC");
            $this->_db->setQuery($query);
        $items = $this->_db->loadObjectList();
        
        foreach ($items as $item) {
            $obj = json_decode($item->settings);
            $obj->likes = $item->likes;
            $obj->path = GalleryHelper::replaceLongPath($obj->path);
            $obj->url = GalleryHelper::replaceLongPath($obj->url);
            $obj->thumbnail_url = $item->thumbnail_url;


            $obj->title = $item->title;
            $obj->short = $item->short;
            $obj->alt = $item->alt;
            $obj->description = $item->description;
            $obj->link = $item->link;
            $obj->video = $item->video;


            $item->settings = json_encode($obj);
        }

        return $items;
    }

    public function clearImageDirectory($id, $allCat, $allThumb)
    {
        $dir = GalleryHelper::$thumbnails_base. '/bagallery/gallery-' .$id. '/thumbnail';
        if (!Folder::exists($dir)) {
            return;
        }
        $folders = Folder::folders($dir);
        if (empty($folders)) {
            return;
        }
        foreach ($folders as $folder) {
            if (!in_array($folder, $allCat)) {
                GalleryHelper::deleteFolder($dir.'/'.$folder);
            } else {
                $files = Folder::files($dir .'/'.$folder);
                if (!empty($files)) {
                    foreach ($files as $file) {
                        if (!in_array($file, $allThumb[$folder])) {
                            File::delete($dir .'/'.$folder. '/' .$file);
                        }
                    }
                }
            }
        }
    }

    public function save($data)
    {
        $input = Factory::getApplication()->input;
        $data = $input->post->get('jform', [], 'array');
        $categories = $input->post->get('categories', [], 'array');
        $db = Factory::getDBO();
        if (isset($data['title'])) {
            $data['title'] = strip_tags($data['title']);
        }
        $data['saved_time'] = time();
        $data['gallery_items'] = '';
        if (!parent::save($data)) {
            return false;
        }
        $formId = $this->getState($this->getName() . '.id');
        $dirName = GalleryHelper::$thumbnails_base. '/bagallery/gallery-' .$formId. '/album/';
        $catId = [];
        $catImgs = [];
        $order = 0;
        foreach ($categories as $str) {
            if ($str == '') {
                continue;
            }
            $category = json_decode($str);
            $category->form_id = $formId;
            $category->orders = $order++;
            $category->settings = '';
            $category->description = $category->description ?? '';
            $category->alias =  strtolower(!!$category->alias ? $category->alias : str_replace(' ', '-', $category->title));
            if (!empty($category->image)) {
                $name = explode('/', $category->image);
                $catImgs[] = 'category-'.$category->category_id.'-'.end($name);
            } else if (!in_array('image-placeholder.jpg', $catImgs)) {
                $catImgs[] = 'image-placeholder.jpg';
            }
            if (isset($category->id)) {
                $db->updateObject('#__bagallery_category', $category, 'id');
            } else {
                $db->insertObject('#__bagallery_category', $category);
                $category->id = $db->insertid();
            }
            $catId[] = $category->id;
        }
        if (Folder::exists($dirName)) {
            $albums  = Folder::files($dirName);
            foreach ($albums as $value) {
                if (!in_array($value, $catImgs)) {
                    File::delete($dirName.$value);
                }
            }
        }
        $query = $db->getQuery(true);
        $query->select("id")
            ->from("#__bagallery_category")
            ->where("form_id=" . $db->quote($formId));
        $db->setQuery($query);
        $items = $db->loadColumn();
        foreach ($items as $id) {
            if (!in_array($id, $catId)) {
                $query = $db->getQuery(true)
                    ->delete('#__bagallery_category')
                    ->where('id = '.$db->quote($id));
                $db->setQuery($query)
                    ->execute();
            }
        }
        return true;
    }
}