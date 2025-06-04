<?php
namespace JExtstore\Component\JMap\Site\Controller;
/**
 * @package JMAP::GOOGLE::components::com_jmap
 * @subpackage controllers
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use JExtstore\Component\JMap\Administrator\Framework\Controller as JMapController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

/**
 * Main controller
 * @package JMAP::GOOGLE::components::com_jmap
 * @subpackage controllers
 * @since 3.5
 */
class GoogleController extends JMapController {
	/**
	 * Set model state from session userstate
	 * @access protected
	 * @param string $scope
	 * @return object
	 */
	protected function setModelState($scope = 'default', $ordering = true): object {
		$option = $this->option;
		
		// Get default model
		$defaultModel = $this->getModel();
		
		// Set model state
		$defaultModel->setState ( 'option', $option );
		
		// Retrive component params with view override
		$cParams = $defaultModel->getComponentParams();
		$analyticsService = $cParams->get('analytics_service', 'google');
		
		// Migration path
		if($analyticsService == 'alexa') {
			$cParams->set('analytics_service', 'statscrop');
			$analyticsService = 'statscrop';
		}
		
		$analyticsModelState = $analyticsService == 'google' ? 'analytics' : $analyticsService . 'fetch';
		
		// Override googlestats by query string if the format raw loopback request is placed by the iframe, such as alexarender
		if($googleStatsByQueryString = $this->app->getInput()->get('googlestats', null)) {
			$analyticsModelState = $googleStatsByQueryString;
		}
		
		$defaultModel->setState ( 'googlestats', $analyticsModelState);
		
		return $defaultModel;
	}
	
	/**
	 * Default listEntities
	 * 
	 * @access public
	 * @param $cachable string
	 *       	 the view output will be cached
	 * @return void
	 */
	public function display($cachable = false, $urlparams = false) {
		$this->setModelState('google');
		parent::display($cachable, $urlparams);
	}
	
	/**
	 * Delete a db table entity
	 *
	 * @access public
	 * @return bool
	 */
	public function deleteEntity(): bool {
		// Mixin, add include path for admin side to avoid DRY on model
		$this->addModelPath ( JPATH_COMPONENT_ADMINISTRATOR . '/models', 'JMapModel' );
		
		// Load della model e checkin before exit
		$model = $this->getModel ();

		if (! $model->deleteEntity ( null )) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$modelException = $model->getException ( null, false );
			$this->app->enqueueMessage ( $modelException->getMessage (), $modelException->getExceptionLevel () );
			$this->setRedirect ( "index.php?option=" . $this->option . "&task=" . $this->name . ".display", Text::_ ( 'COM_JMAP_GOOGLEANALYTICS_ERROR_' . 'LOGOUT' ) );
			return false;
		}
	
		$this->setRedirect ( \JMapRoute::_("index.php?option=" . $this->option . "&view=" . $this->name), Text::_ ( 'COM_JMAP_GOOGLEANALYTICS_SUCCESS_LOGOUT' ) );
		
		return true;
	}
	
	/**
	 * Class Constructor
	 *
	 * @access public
	 * @return Object&
	 */
	public function __construct($config = array(), ?MVCFactoryInterface $factory = null, $app = null, $input = null) {
		parent::__construct($config, $factory, $app, $input);

		// Manage partial language translations from the backend side
		$jLang = $app->getLanguage ();
		$jLang->load ( 'com_jmap', JPATH_COMPONENT_ADMINISTRATOR, 'en-GB', true, true );
		if ($jLang->getTag () != 'en-GB') {
			$jLang->load ( 'com_jmap', JPATH_ADMINISTRATOR, null, true, false );
			$jLang->load ( 'com_jmap', JPATH_COMPONENT_ADMINISTRATOR, null, true, false );
		}
		
		// Composer autoloader
		require_once JPATH_COMPONENT_ADMINISTRATOR. '/Framework/composer/autoload_real.php';
		\ComposerAutoloaderInitcb4c0ac1dedbbba2f0b42e9cdf4d93d7::getLoader();
	}
}