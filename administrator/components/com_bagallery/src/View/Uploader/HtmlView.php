<?php
/**
* @package   BaGallery
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/
namespace Balbooa\Component\Gallery\Administrator\View\Uploader;

use Balbooa\Component\Gallery\Administrator\Helper\GalleryHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseView;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die;

class HtmlView extends BaseView
{
    protected $_limit;
    protected $about;
    protected $version;
    protected $uploader;
    
    public function display($tpl = null)
    {
        if (count($errors = $this->get('Errors'))) {
            throw new \Exception(implode('<br />', $errors), 500);
            return false;
        }
        $doc = Factory::getDocument();
        $this->uploader = $this->get('Uploader');
        $this->about = GalleryHelper::aboutUs();
        $this->version = $this->about->version;
        $this->_limit = $this->uploader->limit;
        $this->addToolBar();
        if ($doc->getDirection() == 'rtl') {
            $doc->addStyleSheet('components/com_bagallery/assets/css/rtl-ba-admin.css?'.$this->version);
        }
        $doc = Factory::getDocument();
        if (JVERSION >= '4.0.0') {
            $doc->addScript(Uri::root().'media/vendor/jquery/js/jquery.min.js');
        }
        $doc->addScript('components/com_bagallery/assets/js/ba-uploader.js?'.$this->version);
        $doc->addScript(Uri::root().'components/com_bagallery/assets/js/bootstrap.js?'.$this->version);
        
        parent::display($tpl);
    }

    protected function addToolBar()
    {
        $input = Factory::getApplication()->input;
        $input->set('hidemainmenu', true);
    }
}