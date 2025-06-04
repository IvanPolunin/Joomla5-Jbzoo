<?php
/**
* @package   BaForms
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

namespace Balbooa\Component\Forms\Administrator\View\Trashed;

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
        $this->items = $this->get('Items');
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
        ToolBarHelper::title(Text::_('TRASHED_ITEMS'), 'star');
        if ($this->user->authorise('core.edit.state', 'com_baforms')) {
            ToolBarHelper::custom('forms.restore', 'undo-2.png', 'undo-2.png', 'RESTORE', true);
        }
        if ($this->user->authorise('core.delete', 'com_baforms')) {
            ToolBarHelper::deleteList('', 'forms.delete');
        }
    }
}