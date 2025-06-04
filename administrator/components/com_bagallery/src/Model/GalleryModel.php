<?php
/**
* @package   BaGallery
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

namespace Balbooa\Component\Gallery\Administrator\Model;

use Balbooa\Component\Gallery\Administrator\Helper\GalleryHelper;
use Balbooa\Component\Gallery\Site\Trait\galleryModelTrait;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;

defined('_JEXEC') or die;
 
class GalleryModel extends AdminModel
{
    use galleryModelTrait;

    public function getTable($type = 'Galleries', $prefix = 'Administrator', $config = [])
    {
        return parent::getTable($type, $prefix, $config);
    }

    public function checkGridbox()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('extension_id ')
            ->from('#__extensions')
            ->where('element = '.$db->quote('com_gridbox'));
        $db->setQuery($query);
        $id = $db->loadResult();

        return $id;
    }

    public function getGridbox()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('title, type, id')
            ->where('type <> '.$db->quote('system_apps'))
            ->from('#__gridbox_app');
        $db->setQuery($query);
        $apps = $db->loadObjectList();
        $obj = new \stdClass();
        $obj->id = 0;
        $obj->title = Text::_('SINGLE_PAGES');
        $obj->type = '';
        $apps[] = $obj;
        usort($apps, function($a, $b){
            return ($a->id < $b->id) ? -1 : 1;
        });
        foreach ($apps as $app) {
            if (empty($app->type) || $app->type == 'single') {
                $query = $db->getQuery(true)
                    ->select('id, title')
                    ->from('#__gridbox_pages')
                    ->where('page_category <> '.$db->quote('trashed'))
                    ->where('app_id = '.$app->id)
                    ->where('published = 1');
                $db->setQuery($query);
                $pages = $db->loadObjectList();
                $app->pages = $this->setPagesLink($pages);
            } else {
                $app->link = 'index.php?option=com_gridbox&view=blog&app='.$app->id.'&id=0';
                $app->childs = $this->getGridboxCategories($app->id, 0);
            }
        }
        
        return $apps;
    }

    public function getGridboxCategories($id, $parent)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('id, title')
            ->from('#__gridbox_categories')
            ->where('published = 1')
            ->where('app_id = '.$id)
            ->where('parent = '.$parent);
        $db->setQuery($query);
        $categories = $db->loadObjectList();
        foreach ($categories as $category) {
            $category->childs = $this->getGridboxCategories($id, $category->id);
            $category->link = 'index.php?option=com_gridbox&view=blog&app='.$id.'&id='.$category->id;
            $query = $db->getQuery(true)
                ->select('id, title, page_category, app_id')
                ->from('#__gridbox_pages')
                ->where('page_category <> '.$db->quote('trashed'))
                ->where('page_category = '.$category->id)
                ->where('published = 1');
            $db->setQuery($query);
            $pages = $db->loadObjectList();
            $category->pages = $this->setPagesLink($pages);
        }

        return $categories;
    }

    public function setPagesLink($pages)
    {
        foreach ($pages as $page) {
            if (isset($page->page_category)) {
                $page->link = 'index.php?option=com_gridbox&view=page&blog='.$page->app_id;
                $page->link .= '&category='.$page->page_category.'&id='.$page->id;
            } else {
                $page->link = 'index.php?option=com_gridbox&view=page&id='.$page->id;
            }
        }

        return $pages;
    }

    public function getMenus()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('title, menutype')
            ->from('#__menu_types')
            ->order('title ASC');
        $db->setQuery($query);
        $menus = $db->loadObjectList();
        foreach ($menus as $key => $menu) {
            $query = $db->getQuery(true)
                ->select('title, link, id')
                ->from('#__menu')
                ->where('published = 1')
                ->where('menutype = '.$db->quote($menu->menutype))
                ->where('parent_id = 1');
            $db->setQuery($query);
            $menu->childs = $db->loadObjectList();
            foreach ($menu->childs as $child) {
                $this->getChilds($child);
            }
        }

        return $menus;
    }

    public function getChilds($obj)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('title, link, id')
            ->from('#__menu')
            ->where('published = 1')
            ->where('parent_id = '.$obj->id);
        $db->setQuery($query);
        $obj->childs = $db->loadObjectList();
        foreach ($obj->childs as $key => $child) {
            $this->getChilds($child);
        }

        return $obj;
    }

    public function getArticles()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('title, id')
            ->from('#__content')
            ->where('(state = 0 OR state = 1)');
        $db->setQuery($query);
        $items = $db->loadObjectList();

        return $items;
    }
 
    public function getForm($data = [], $loadData = true)
    {
        $form = $this->loadForm(
            $this->option . '.gallery', 'gallery', ['control' => 'jform', 'load_data' => $loadData]
        );
        
        if (empty($form)) {
            return false;
        }
 
        return $form;
    }

    public function getThumbnail($id)
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
    
    public function delete(&$pks)
    {
        $pks = (array) $pks;
        foreach ($pks as $i => $pk) {
            $id = $pk;
            if (parent::delete($pk)) {
                $this->_db->setQuery("DELETE FROM #__bagallery_items WHERE `form_id`=". $id);
                $this->_db->execute();
                $this->_db->setQuery("DELETE FROM #__bagallery_category WHERE `form_id`=". $id);
                $this->_db->execute();
                $this->_db->setQuery("DELETE FROM #__bagallery_colors_map WHERE `gallery_id`=". $id);
                $this->_db->execute();
                $this->_db->setQuery("DELETE FROM #__bagallery_tags_map WHERE `gallery_id`=". $id);
                $this->_db->execute();
                if (Folder::exists(GalleryHelper::$thumbnails_base. '/bagallery/gallery_' .$id)) {
                    GalleryHelper::deleteFolder(GalleryHelper::$thumbnails_base. '/bagallery/gallery_' .$id);
                }
                if (Folder::exists(GalleryHelper::$thumbnails_base. '/bagallery/gallery-' .$id)) {
                    GalleryHelper::deleteFolder(GalleryHelper::$thumbnails_base. '/bagallery/gallery-' .$id);
                }
            } else {
                return false;
            }
        }
        return true;
    }

    protected function loadFormData()
    {
        $data = $this->getItem();
        
        return $data;
    }

    protected function getNewTitle($title)
    {
        $table = $this->getTable();
        while ($table->load(['title' => $title])) {
            $title = GalleryHelper::increment($title);
        }

        return $title;
    }
    
    public function duplicate(&$pks)
    {
        $db = $this->getDbo();
        foreach ($pks as $pk) {
            $table = $this->getTable();
            $table->load($pk, true);
            $table->id = 0;
            $table->title = $this->getNewTitle($table->title);
            $table->published = 0;
            $table->store();
            $id = $table->id;
            $query = $db->getQuery(true);
            $query->select("*");
            $query->from("#__bagallery_category");
            $query->where("form_id=" . $pk);
            $query->order("id ASC");
            $db->setQuery($query);
            $items = $db->loadObjectList();
            foreach ($items as $item) {
                $item->id = 0;
                $item->form_id = $id;
                $db->insertObject('#__bagallery_category', $item);
            }
            $query = $db->getQuery(true);
            $query->select("*");
            $query->from("#__bagallery_items");
            $query->where("form_id=" . $pk);
            $query->order("id ASC");
            $db->setQuery($query);
            $items = $db->loadObjectList();
            foreach ($items as $key => $item) {
                $query = $db->getQuery(true)
                    ->select('*')
                    ->from('#__bagallery_colors_map')
                    ->where('`image_id` = '.$item->id);
                $db->setQuery($query);
                $colors = $db->loadObjectList();
                $query = $db->getQuery(true)
                    ->select('*')
                    ->from('#__bagallery_tags_map')
                    ->where('`image_id` = '.$item->id);
                $db->setQuery($query);
                $tags = $db->loadObjectList();
                $item->id = 0;
                $item->form_id = $id;
                if (!empty($item->thumbnail_url)) {
                    $item->thumbnail_url = str_replace('gallery-'.$pk, 'gallery-'.$id, $item->thumbnail_url);
                    $item->thumbnail_url = str_replace('gallery_'.$pk, 'gallery_'.$id, $item->thumbnail_url);
                }
                $db->insertObject('#__bagallery_items', $item);
                $imageId = $db->insertid();
                foreach ($colors as $color) {
                    $color->image_id = $imageId;
                    $color->gallery_id = $id;
                    unset($color->id);
                    $db->insertObject('#__bagallery_colors_map', $color);
                }
                foreach ($tags as $tag) {
                    $tag->image_id = $imageId;
                    $tag->gallery_id = $id;
                    unset($tag->id);
                    $db->insertObject('#__bagallery_tags_map', $tag);
                }
            }
            $query = $db->getQuery(true);
            $query->select("id, settings");
            $query->from("#__bagallery_items");
            $query->where("form_id=" . $id);
            $query->order("id ASC");
            $db->setQuery($query);
            $items = $db->loadObjectList();
            foreach ($items as $item) {
                $obj = $item->settings;
                $obj = json_decode($obj);
                $obj->id = $item->id;
                $obj = json_encode($obj);
                $query = "UPDATE `#__bagallery_items` SET `settings`=";
                $query .= $db->Quote($obj). " WHERE `id`=";
                $query .= $db->Quote($item->id);
                $db->setQuery($query)
                    ->execute();
            }
        }
    }
    
}