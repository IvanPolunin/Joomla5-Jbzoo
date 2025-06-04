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
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\Archive\Archive;
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Installer;

class FormsController extends AdminController
{
    public function getModel($name = 'Form', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }

    public function pasteDesign()
    {
        /** @var BaBalbooa\Component\Forms\Administrator\Model */
        $model = $this->getModel();
        $id = $this->input->get('id', 0, 'int');
        $str = $this->input->get('design', '{}', 'raw');
        $design = json_decode($str);
        $model->pasteDesign($id, $design);
        echo Text::_('DESIGN_PASTED_SUCCESSFULLY');
        exit;
    }

    public function getFormDesign()
    {
        /** @var BaBalbooa\Component\Forms\Administrator\Model */
        $model = $this->getModel();
        $id = $this->input->get('id', 0, 'int');
        $options = $model->getFormOptions();
        $settings = BaformsHelper::getFormsSettings($id, $options);
        print_r($settings->design);exit;
    }

    public function delete()
    {
        Session::checkToken() or die(Text::_('JINVALID_TOKEN'));
        $cid = $this->input->get('cid', [], 'array');
        /** @var BaBalbooa\Component\Forms\Administrator\Model */
        $model = $this->getModel();
        $model->delete($cid);
        echo Text::_($this->text_prefix.'_N_ITEMS_DELETED');
        exit;
    }

    public function versionCompare()
    {
        $about = BaformsHelper::aboutUs();
        $input = Factory::getApplication()->input;
        $version = $input->get('version', '', 'string');
        $compare = version_compare($about->version, $version);
        echo $compare;
        exit();
    }

    public function checkFormsState()
    {
        $state = BaformsHelper::checkFormsState();
        print_r($state);exit();
    }

    public function getUserLicense()
    {
        $input = Factory::getApplication()->input;
        $data = $input->get('data', '', 'string');
        BaformsHelper::setAppLicense($data);
    }

    public function setFilters()
    {
        $input = Factory::getApplication()->input;
        $view = $input->get('view', '', 'string');
        /** @var BaBalbooa\Component\Forms\Administrator\Model */
        $model = $this->getModel($view);
        $model->populateState();
        exit;
    }

    public function restore()
    {
        $pks = $this->input->getVar('cid', [], 'post', 'array');
        /** @var BaBalbooa\Component\Forms\Administrator\Model */
        $model = $this->getModel();
        $model->restore($pks);
        echo Text::_('ITEMS_RESTORED');
        exit;
    }

    public function trash()
    {
        $pks = $this->input->getVar('cid', [], 'post', 'array');
        /** @var BaBalbooa\Component\Forms\Administrator\Model */
        $model = $this->getModel();
        $model->publish($pks, -2);
        echo Text::_('COM_BAFORMS_N_ITEMS_TRASHED');
        exit;
    }

    public function publish()
    {
        $cid = Factory::getApplication()->input->get('cid', [], 'array');
        $task = $this->getTask();
        switch ($task) {
            case 'trash':
                $value = -2;
                $text = 'COM_BAFORMS_N_ITEMS_TRASHED';
                break;
            case 'unpublish':
                $value = 0;
                $text = 'COM_BAFORMS_N_ITEMS_UNPUBLISHED';
                break;
            default:
                $value = 1;
                $text = 'COM_BAFORMS_N_ITEMS_PUBLISHED';
                break;
        }
        /** @var BaBalbooa\Component\Forms\Administrator\Model */
        $model = $this->getModel();
        $model->publish($cid, $value);
        echo Text::_($text);
        exit;
    }

    public function contextDuplicate()
    {
        $id = $this->input->get('id', 0, 'int');
        /** @var BaBalbooa\Component\Forms\Administrator\Model */
        $model = $this->getModel();
        $model->duplicate([$id]);
        echo Text::_('FORM_DUPLICATED');
        exit;
    }

    public function contextRename()
    {
        $id = $this->input->get('id', 0, 'int');
        $title = $this->input->get('title', '', 'string');
        /** @var BaBalbooa\Component\Forms\Administrator\Model */
        $model = $this->getModel();
        $model->rename($id, $title);
        echo Text::_('ITEM_SUCCESSFULLY_RENAMED');
        exit;
    }

    public function contextTrash()
    {
        $id = $this->input->get('id', 0, 'int');
        /** @var BaBalbooa\Component\Forms\Administrator\Model */
        $model = $this->getModel();
        $pks = [$id];
        $model->publish($pks, -2);
        echo Text::_('COM_BAFORMS_N_ITEMS_TRASHED');
        exit;
    }
    
    public function duplicate()
    {
        $pks = $this->input->getVar('cid', array(), 'post', 'array');
        /** @var BaBalbooa\Component\Forms\Administrator\Model */
        $model = $this->getModel();
        $model->duplicate($pks);
        echo Text::_('FORM_DUPLICATED');
        exit;
    }
    
    public function updateForms()
    {
        $config = Factory::getConfig();
        $path = $config->get('tmp_path').'/pkg_BaForms.zip';
        $data = file_get_contents('php://input');
        $obj = json_decode($data);
        $method = $obj->method;
        $data = $method($obj->package);
        $file = fopen($path, "w+");
        fputs($file, $data);
        fclose($file);
        $this->extract($path, $config->get('tmp_path').'/pkg_BaForms');
        $installer = Installer::getInstance();
        $result = $installer->update($config->get('tmp_path').'/pkg_BaForms');
        File::delete($path);
        BaformsHelper::deleteFolder($config->get('tmp_path').'/pkg_BaForms');
        exit;
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
        $tmp_path = $config->get('tmp_path');
        $path = $tmp_path.'/'.$name;
        $name = explode('.', $name);
        $data = $method($zip);
        $file = fopen($path, "w+");
        fputs($file, $data);
        fclose($file);
        $this->extract($path, $tmp_path.'/'.$name[0]);
        $installer = Installer::getInstance();
        $result = $installer->install($tmp_path.'/'.$name[0]);
        File::delete($path);
        BaformsHelper::deleteFolder($tmp_path.'/'.$name[0]);
        echo Text::_('SUCCESS_INSTALL');
        exit;
    }

    public function addLibrary()
    {
        $input = Factory::getApplication()->input;
        $method = $input->get('method', '', 'string');
        $folder = $input->get('folder', '', 'string');
        $zip = $input->get('zip', '', 'string');
        $package = $input->get('package', '', 'string');
        $data = $method($package);
        $path = JPATH_ROOT.'/components/com_baforms/libraries/';
        $file = fopen($path.$zip, "w+");
        fputs($file, $data);
        fclose($file);
        $this->extract($path.$zip, $path.$folder);
        File::delete($path.$zip);
        echo Text::_('SUCCESS_INSTALL');
        exit;
    }

    public function extract($from, $to)
    {
        $archive = new Archive();
        $archive->extract($from, $to);
    }

    public function exportForms()
    {
        /** @var BaBalbooa\Component\Forms\Administrator\Model */
        $model = $this->getModel();
        $model->exportForms();
    }

    public function exportForm()
    {
        $input = Factory::getApplication()->input;
        $export = $input->get('export_id', '', 'string');
        $cid = explode(';', $export);
        /** @var BaBalbooa\Component\Forms\Administrator\Model */
        $model = $this->getModel();
        $model->exportForm($cid);
    }

    public function download()
    {
        $file = JPATH_ROOT.'/tmp/forms.xml';
        if (file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename='.basename($file));
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            readfile($file);
            exit;
        }
    }

    public function importForms()
    {
        /** @var BaBalbooa\Component\Forms\Administrator\Model */
        $model = $this->getModel();
        $input = Factory::getApplication()->input;
        $files = $input->files->get('ba-files', '', 'array');
        foreach ($files as $item) {
            $name = JPATH_ROOT.'/tmp/'.$item['name'];
            if (!File::upload($item['tmp_name'], $name)) {
                echo Text::_('UPLOAD_ERROR');
                exit;
            }
        }
        $xml = simplexml_load_file($name);
        $model->importForms($xml);
        echo Text::_('SUCCESS_UPLOAD');
        exit;
    }
}