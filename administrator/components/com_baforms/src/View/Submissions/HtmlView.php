<?php
/**
* @package   BaForms
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

namespace Balbooa\Component\Forms\Administrator\View\Submissions;

defined('_JEXEC') or die;

use Balbooa\Component\Forms\Administrator\Helper\BaformsHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Component\ComponentHelper;

class HtmlView extends BaseHtmlView
{
    protected $items;
    protected $pagination;
    protected $state;
    protected $about;
    protected $uploaded_path;
    protected $user;
    protected $submission;
    protected $titles;

    public function display($tpl = null) 
    {
        $input = Factory::getApplication()->input;
        $layout = $input->get('layout', '');
        $this->user = Factory::getUser();
        $this->items = $this->get('Items');
        $this->about = BaformsHelper::aboutUs();
        if ($layout == 'pdf' || $layout == 'print') {
            $this->submission = $this->get('Submission');
        }
        if (empty($layout)) {
            $this->pagination = $this->get('Pagination');
            $this->titles = $this->get('SubmissionForms');
            $this->state = $this->get('State');
            $this->addToolBar();
            foreach ($this->items as &$item) {
                $item->order_up = true;
                $item->order_dn = true;
            }
        }
        $params = ComponentHelper::getParams('com_baforms');
        $this->uploaded_path = $params->get('uploaded_path', 'images');
        $doc = Factory::getDocument();
        $doc->addStyleSheet(Uri::root().'/components/com_baforms/assets/icons/material/material.css');
        $doc->addStyleSheet('components/com_baforms/assets/css/ba-admin.css?'.$this->about->version);
        $doc->addScriptDeclaration('var uploaded_path = "'.$this->uploaded_path.'";');
        if (JVERSION >= '4.0.0') {
            $doc->addScript(Uri::root(true).'/media/vendor/jquery/js/jquery.min.js');
        }

        parent::display($tpl);
    }
    
    protected function addToolBar()
    {
        ToolbarHelper::title(Text::_('SUBMISSIONS'), 'star');
        ToolbarHelper::custom('submissions.export', 'download.png', 'download.png', 'EXPORT', true);
        if ($this->user->authorise('core.delete', 'com_baforms')) {
            ToolbarHelper::deleteList('', 'submissions.delete');
        }
        ToolbarHelper::custom('submissions.readAll', 'eye.png', 'eye.png', 'MARK_ALL_AS_READ', false);
        ToolbarHelper::custom('submissions.unread', 'eye-slash.png', 'eye-slash.png', 'MARK_AS_UNREAD', true);
    }
}