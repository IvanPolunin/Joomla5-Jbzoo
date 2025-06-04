<?php
/**
* @package   BaGallery
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

namespace Balbooa\Component\Gallery\Site\View\Gallery;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseView;

class HtmlView extends BaseView
{
    protected $_album;
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
        if (!Factory::getUser()->authorise('core.edit', 'com_bagallery')) {
            throw new \Exception(Text::_('NOT_HAVE_PERMISSIONS'), 403);
        }
        $input = Factory::getApplication()->input;
        $id = $input->get('id');
        if (empty($id)) {
            return;
        }
        $form = Form::getInstance(
            'gallery',
            JPATH_COMPONENT.'/forms/gallery.xml',
            ['control' => 'jform', 'load_data' => true]
        );
        $data = $this->get('FormData');
        foreach ($data as $key => $value) {
            $form->setValue($key, null, $value);
        }
        $this->form = $form;
        $this->categories = $this->get('Categories');
        $this->images = $this->get('Images');
        $this->tags = $this->get('Tags');
        $this->colors = $this->get('Colors');
        $this->_album = $this->get('Album');
        
        parent::display($tpl);
    }
}