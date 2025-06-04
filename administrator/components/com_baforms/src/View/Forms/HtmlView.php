<?php
/**
* @package   BaForms
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

namespace Balbooa\Component\Forms\Administrator\View\Forms;

defined('_JEXEC') or die;

use Balbooa\Component\Forms\Administrator\Helper\BaformsHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
    protected $items;
    protected $pagination;
    protected $state;
    protected $about;
    protected $user;

    public function display($tpl = null) 
    {
        $this->about = BaformsHelper::aboutUs();
        $app = Factory::getApplication();
        $layout = $app->input->get('layout', '', 'string');
        if (empty($layout)) {
            $this->items = $this->get('Items');
        } else {
            $this->items = $this->get('ModalItems');
        }
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
        $this->user = Factory::getUser();
        $this->addToolBar();
        $doc = Factory::getDocument();
        $doc->addStyleSheet(Uri::root().'/components/com_baforms/assets/icons/material/material.css');
        $doc->addStyleSheet('components/com_baforms/assets/css/ba-admin.css?'.$this->about->version);
        if (JVERSION >= '4.0.0') {
            $doc->addScript(Uri::root(true).'/media/vendor/jquery/js/jquery.min.js');
        }
        foreach ($this->items as &$item) {
            $item->order_up = true;
            $item->order_dn = true;
        }
        
        parent::display($tpl);
    }
    
    protected function addToolBar()
    {
        ToolBarHelper::title(Text::_('FORMS'), 'star');
        if ($this->user->authorise('core.create', 'com_baforms')) {
            ToolBarHelper::addNew('form.add');
        }
        if ($this->user->authorise('core.edit', 'com_baforms')) {
            ToolBarHelper::editList('form.edit');
        }
        if ($this->user->authorise('core.duplicate', 'com_baforms')) {
            ToolBarHelper::custom('forms.duplicate', 'copy.png', 'copy_f2.png', 'JTOOLBAR_DUPLICATE', true);
        }
        if ($this->user->authorise('core.edit.state', 'com_baforms')) {
            ToolBarHelper::publish('forms.publish', 'JTOOLBAR_PUBLISH', true);
            ToolBarHelper::unpublish('forms.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        }
        ToolBarHelper::custom('forms.export', 'download.png', 'download.png', 'EXPORT', true);
        if ($this->user->authorise('core.delete', 'com_baforms')) {
            ToolBarHelper::trash('forms.trash');
        }
    }
}