<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Selectcity
 * @author     Ivan Polynin <ipolynin@gmail.com>
 * @copyright  2024 Ivan Polynin
 * @license    GNU General Public License версии 2 или более поздней; Смотрите LICENSE.txt
 */

namespace Selectcity\Component\Selectcity\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;

/**
 * Selectcity master display controller.
 *
 * @since  1.0.0
 */
class DisplayController extends BaseController
{
	/**
	 * The default view.
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected $default_view = 'selectcitys';

	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link InputFilter::clean()}.
	 *
	 * @return  BaseController|boolean  This object to support chaining.
	 *
	 * @since   1.0.0
	 */
	public function display($cachable = false, $urlparams = array())
	{
		return parent::display();
	}
}
