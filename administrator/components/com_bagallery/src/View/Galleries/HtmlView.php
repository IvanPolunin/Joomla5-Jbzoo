<?php
/**
* @package   BaGallery
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

namespace Balbooa\Component\Gallery\Administrator\View\Galleries;

use Balbooa\Component\Gallery\Administrator\Helper\GalleryHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die;

class HtmlView extends BaseView
{
    protected $items;
    protected $pagination;
    protected $state;
    protected $about;
    protected $count;
    
    public function display($tpl = null) 
    {
        $this->about = GalleryHelper::aboutUs();
        $this->items = $this->get('Items');
        $this->count = $this->get('count');
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
        $this->addToolBar();
        $layout = Factory::getApplication()->input->get('layout', '', 'string');
        $doc = Factory::getDocument();
        if ($doc->getDirection() == 'rtl') {
            $doc->addStyleSheet(Uri::root().'administrator/components/com_bagallery/assets/css/rtl-ba-admin.css?'.$this->about->version);
        }
        if (JVERSION >= '4.0.0') {
            $doc->addScript(Uri::root(true).'/media/vendor/jquery/js/jquery.min.js');
        }
        if (empty($layout)) {
            $doc->addScript('components/com_bagallery/assets/js/ba-about.js?'.$this->about->version);
            $doc->addScript(Uri::root().'components/com_bagallery/assets/js/bootstrap.js?'.$this->about->version);
        }
        foreach ($this->items as &$item) {
            $item->order_up = true;
            $item->order_dn = true;
        }
        
        parent::display($tpl);
    }
    
    protected function addToolBar ()
    {
        ToolbarHelper::title(Text::_('GALLERIES_TITLE'), 'image');
        $user = Factory::getUser();
        if ($user->authorise('core.create', 'com_bagallery')) {
            ToolbarHelper::addNew('gallery.add');
        }
        if ($user->authorise('core.edit', 'com_bagallery')) {
            ToolbarHelper::editList('gallery.edit');
        }
        if ($user->authorise('core.duplicate', 'com_bagallery')) {
            ToolBarHelper::custom('galleries.duplicate', 'copy.png', 'copy_f2.png', 'JTOOLBAR_DUPLICATE', true);
        }
        if ($user->authorise('core.edit.state', 'com_bagallery')) {
            ToolbarHelper::publish('galleries.publish', 'JTOOLBAR_PUBLISH', true);
            ToolbarHelper::unpublish('galleries.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        }
        if ($user->authorise('core.delete', 'com_bagallery')) {
            if ($this->state->get('filter.state') == -2) {
                ToolBarHelper::deleteList('', 'galleries.delete');
            } else {
                ToolbarHelper::trash('galleries.trash');
            }
        }
        if ($user->authorise('core.admin', 'com_bagallery') || $user->authorise('core.options', 'com_bagallery')) {
            ToolBarHelper::preferences('com_bagallery');
        }
    }
    
    protected function getSortFields()
    {
        return [
            'published' => Text::_('JSTATUS'),
            'title' => Text::_('JGLOBAL_TITLE'),
            'id' => Text::_('JGRID_HEADING_ID')
        ];
    }
}