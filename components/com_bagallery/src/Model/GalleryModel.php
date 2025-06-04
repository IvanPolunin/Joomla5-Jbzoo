<?php
/**
* @package   BaGallery
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

namespace Balbooa\Component\Gallery\Site\Model;

use Balbooa\Component\Gallery\Site\Helper\GalleryHelper;
use Balbooa\Component\Gallery\Site\Trait\galleryModelTrait;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\MVC\Model\AdminModel;

defined('_JEXEC') or die;
 
class GalleryModel extends AdminModel
{
    use galleryModelTrait;

    public function getTable($type = 'Galleries', $prefix = 'Administrator', $config = [])
    {
        return parent::getTable($type, $prefix, $config);
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

    public function getFormData()
    {
        $input = Factory::getApplication()->input;
        $id = $input->get('id');
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('id, settings, all_sorting, sorting_mode')
            ->from('#__bagallery_galleries')
            ->where('`id` = '.$id);
        $db->setQuery($query);
        $data = $db->loadObject();
        
        return $data;
    }
    
    public function getAlbum()
    {
        $input = Factory::getApplication()->input;
        $id = $input->get('id');
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('album_mode')
            ->from('#__bagallery_galleries')
            ->where('`id` = '.$id);
        $db->setQuery($query);

        return $db->loadResult();
    }
    
    protected function loadFormData()
    {
        $input = Factory::getApplication()->input;
        $id = $input->get('id');
        $data = $this->getItem($id);
        
        return $data;
    }
}