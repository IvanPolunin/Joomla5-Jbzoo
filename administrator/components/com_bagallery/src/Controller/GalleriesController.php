<?php
/**
* @package   BaGallery
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

namespace Balbooa\Component\Gallery\Administrator\Controller;

defined('_JEXEC') or die;

use Balbooa\Component\Gallery\Administrator\Helper\GalleryHelper;
use Joomla\Archive\Archive;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;

class GalleriesController extends AdminController
{
    protected $option = 'com_bagallery';
    
    public function getModel($name = 'Gallery', $prefix = 'Administrator', $config = ['ignore_request' => true]) 
	{
        $model = parent::getModel($name, $prefix, $config);
        
        return $model;
	}

    public function versionCompare()
    {
        $about = GalleryHelper::aboutUs();
        $input = Factory::getApplication()->input;
        $version = $input->get('version', '', 'string');
        $compare = version_compare($about->version, $version);
        echo $compare;
        exit();
    }

    public function cleanup()
    {
        GalleryHelper::cleanup();
        echo Text::_('COM_BAGALLERY_N_ITEMS_DELETED');
        exit;
    }

    public function getCategories()
    {
        $id = $this->input->get('id', 0, 'int');
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

    public function duplicate()
    {
        $pks = $this->input->getVar('cid', [], 'post', 'array');
        /** @var BaBalbooa\Component\Gallery\Administrator\Model */
        $model = $this->getModel();
        $model->duplicate($pks);
        $this->setMessage(Text::plural('GALLERY_DUPLICATED', count($pks)));
        $this->setRedirect('index.php?option=com_bagallery&view=galleries');
    }
    
    public function updateGallery()
    {
        $config = Factory::getConfig();
        $path = $config->get('tmp_path').'/pkg_BaGallery.zip';
        $data = file_get_contents('php://input');
        $obj = json_decode($data);
        $method = $obj->method;
        $data = $method($obj->package);
        $file = fopen($path, "w+");
        fputs($file, $data);
        fclose($file);
        $this->extract($path, $config->get('tmp_path').'/pkg_BaGallery');
        $installer = Installer::getInstance();
        $installer->update($config->get('tmp_path').'/pkg_BaGallery');
        File::delete($path);
        GalleryHelper::deleteFolder($config->get('tmp_path').'/pkg_BaGallery');
        exit;
    }

    public function extract($from, $to)
    {
        $archive = new Archive();
        $archive->extract($from, $to);
    }

    public function checkGalleryState()
    {
        $state = GalleryHelper::checkGalleryState();
        print_r($state);exit();
    }

    public function getUserLicense()
    {
        $input = Factory::getApplication()->input;
        $data = $input->get('data', '', 'string');
        GalleryHelper::setAppLicense($data);
    }

    public function addLanguage()
    {
        $input = Factory::getApplication()->input;
        $method = $input->get('method', '', 'string');
        $url = $input->get('url', '', 'string');
        $zip = $input->get('zip', '', 'string');
        $name = explode('/', $url);
        $name = end($name);
        $config = Factory::getConfig();
        $path = $config->get('tmp_path') . '/'. $name;
        $name = explode('.', $name);
        $data = $method($zip);
        $file = fopen($path, "w+");
        fputs($file, $data);
        fclose($file);
        $this->extract($path, $config->get('tmp_path') . '/' .$name[0]);
        $installer = Installer::getInstance();
        $installer->install($config->get('tmp_path') . '/'. $name[0]);
        File::delete($path);
        GalleryHelper::deleteFolder($config->get('tmp_path').'/'.$name[0]);
        echo Text::_('SUCCESS_INSTALL');
        exit;
    }
}