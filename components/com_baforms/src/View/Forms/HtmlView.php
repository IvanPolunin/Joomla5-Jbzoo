<?php
/**
* @package   BaForms
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

namespace Balbooa\Component\Forms\Site\View\Forms;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
 
class HtmlView extends BaseHtmlView
{
	protected $items;
    protected $pagination;
    protected $state;
    
    public function display($tpl = null) 
	{
        $this->items = $this->get('ModalItems');
		$this->pagination = $this->get('Pagination');
		$this->state = $this->get('State');
		foreach ($this->items as &$item)
		{
			$item->order_up = true;
			$item->order_dn = true;
		}
        parent::display($tpl);
	}
}