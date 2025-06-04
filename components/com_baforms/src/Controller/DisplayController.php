<?php
/**
* @package   BaForms
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

namespace Balbooa\Component\Forms\Site\Controller;

defined('_JEXEC') or die;

use Balbooa\Component\Forms\Site\Helper\BaformsHelper;
use Joomla\CMS\MVC\Controller\BaseController;

class DisplayController extends BaseController
{
	protected $default_view = 'forms';
    
    public function display($cachable = false, $urlparams = [])
    {
        BaformsHelper::prepareHelper();
        
        return parent::display($cachable, $urlparams);
    }
}