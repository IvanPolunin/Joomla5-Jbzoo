<?php
/**
* @package   BaForms
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

namespace Balbooa\Component\Forms\Administrator\View\Form;

defined('_JEXEC') or die;

use Balbooa\Component\Forms\Administrator\Helper\BaformsHelper;
use Balbooa\Component\Forms\Site\Helper\CompatibleCheck;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
    public $about;
    public $item;
    public $pages;
    public $templates;
    public $googleFont;
    public $formOptions;
    public $formSettings;
    public $integrations;
    public $user;
    public $formTemplates;

    public function display($tpl = null)
    {
        $this->about = BaformsHelper::aboutUs();
        $this->item = $this->get('Item');
        $this->user = Factory::getUser();
        if (JVERSION >= '4.0.0') {
            $doc = Factory::getDocument();
            $doc->addScript(Uri::root(true).'/media/vendor/jquery/js/jquery.min.js');
        }
        if (empty($this->item) || empty($this->item->id)) {
            if (!Factory::getUser()->authorise('core.create', 'com_baforms')) {
                throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'), 404);
            }
            $this->setLayout('create');
        } else {
            if (!Factory::getUser()->authorise('core.edit', 'com_baforms')) {
                throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'), 404);
            }
            $this->formOptions = $this->get('FormOptions');
            CompatibleCheck::checkForm($this->item->id, $this->formOptions);
            $this->integrations = BaformsHelper::getIntegrations($this->item->id);
            $this->pages = $this->get('Pages');
            $this->templates = BaformsHelper::getTemplates($this->formOptions);
            $this->formSettings = BaformsHelper::getFormsSettings($this->item->id, $this->formOptions);
            $this->googleFont = $this->get('GoogleFonts');
            $this->form = $this->get('Form');
            $this->formTemplates = $this->get('FormTemplates');
        }
        $input = Factory::getApplication()->input;
        $input->set('hidemainmenu', true);
        $isNew = ($this->item && $this->item->id == 0);
        ToolbarHelper::title($isNew ? Text::_('FORMS_NEW') : Text::_('FORMS_EDIT'), 'star');

        parent::display($tpl);
    }
}