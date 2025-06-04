<?php
namespace JExtstore\Component\JMap\Site\Controller;
/**
 * @package JMAP::SITEMAP::components::com_jmap
 * @subpackage controllers
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Factory;
use JExtstore\Component\JMap\Administrator\Framework\Controller as JMapController;
use JExtstore\Component\JMap\Administrator\Framework\Http;
use JExtstore\Component\JMap\Administrator\Framework\Language\Multilang as JMapMultilang;

/**
 * Main controller class
 *
 * @package JMAP::SITEMAP::components::com_jmap
 * @subpackage controllers
 * @since 3.5
 */
class GeositemapController extends JMapController {
	/**
	 * Display the Sitemap
	 *
	 * @access public
	 * @return void
	 */
	public function display($cachable = false, $urlparams = false) {
		// Get REQUEST vars all used to makeId for cache handler
		$option = $this->option;
		$format = $this->app->getInput()->get ( 'format', 'xml' );
		$language = JMapMultilang::getCurrentSefLanguage();
		
		// Get sitemap model and view core
		$document = Factory::getApplication()->getDocument ();
		$viewType = $document->getType ();
		$coreName = $this->getName ();
		$viewLayout = 'default';
		
		$view = $this->getView ( $coreName, $viewType, '', array (
				'base_path' => $this->basePath 
		) );
		
		// Get/Create the model
		if ($model = $this->getModel ( $coreName )) {
			// Push the model into the view (as default)
			$view->setModel ( $model, true );
		}
		
		// Set model state
		$model->setState ( 'format', $format );
		$model->setState ( 'language', $language );
		
		// Get an instance of the HTTP client
		$HTTPClient = new Http();
		$view->set('httpclient', $HTTPClient);
		
		// Set the layout
		$view->setLayout ( $viewLayout );
		$view->display ( $format );
	}
}