<?php
/**
* @package   BaGallery
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

namespace Balbooa\Component\Gallery\Administrator\View\Gallery;

use Balbooa\Component\Gallery\Administrator\Helper\GalleryHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die;

class HtmlView extends BaseView
{
    protected $about;
    protected $access;
    protected $tags;
    protected $colors;
    protected $categories;
    protected $images;

    public function display ($tpl = null)
    {
        if (count($errors = $this->get('Errors'))) {
            throw new \Exception(implode('<br />', $errors), 500);
            return false;
        }
        $this->categories = $this->get('Categories');
        $this->images = $this->get('Images');
        $this->tags = $this->get('Tags');
        $this->colors = $this->get('Colors');
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        $this->access = GalleryHelper::getAccess();
        $this->about = GalleryHelper::aboutUs();
        $this->addToolBar();
        $doc = Factory::getDocument();
        if (JVERSION >= '4.0.0') {
            $doc->addScript(Uri::root(true).'/media/vendor/jquery/js/jquery.min.js');
        }
        $doc->addScript(Uri::root().'components/com_bagallery/assets/js/bootstrap.js?'.$this->about->version);
        $doc->addScript('//cdn.ckeditor.com/4.12.1/full/ckeditor.js');
        

        parent::display($tpl);
    }

    protected function addToolBar()
    {
        $input = Factory::getApplication()->input;
        $input->set('hidemainmenu', true);
        $isNew = ($this->item->id == 0);
        ToolbarHelper::title($isNew ? Text::_('BAGALLERY_NEW') : Text::_('BAGALLERY_EDIT'), 'image');
        ToolbarHelper::apply('gallery.apply', 'JTOOLBAR_APPLY');
        ToolBarHelper::save('gallery.save');
        ToolBarHelper::cancel('gallery.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
    }
}