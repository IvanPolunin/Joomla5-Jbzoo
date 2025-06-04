<?php
/**
* @package   BaGallery
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

namespace Balbooa\Component\Gallery\Site\View\Galleries;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseView;

defined('_JEXEC') or die;

class HtmlView extends BaseView
{
    protected $items;
    protected $pagination;
    protected $state;
    protected $about;
    
    public function display($tpl = null) 
    {
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
        HTMLHelper::_('bootstrap.framework');
        foreach ($this->items as &$item) {
            $item->order_up = true;
            $item->order_dn = true;
        }

        parent::display($tpl);
    }
    
}