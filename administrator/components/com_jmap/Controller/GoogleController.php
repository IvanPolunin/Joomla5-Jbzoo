<?php
namespace JExtstore\Component\JMap\Administrator\Controller;
/**
 * @package JMAP::GOOGLE::administrator::components::com_jmap
 * @subpackage controllers
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use JExtstore\Component\JMap\Administrator\Framework\Controller as JMapController;

/**
 * Main controller
 * @package JMAP::GOOGLE::administrator::components::com_jmap
 * @subpackage controllers
 * @since 3.1
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
		
		// Set calendar period for search console stats
		$defaultStartPeriod = date ( "Y-m-01", strtotime ( date ( "Y-m-d" ) ) );
		$defaultEndPeriod = date ( "Y-m-d", strtotime ( "-1 day", strtotime ( "+1 month", strtotime ( date ( "Y-m-01" ) ) ) ) );
		$fromPeriod = $this->getUserStateFromRequest( "$option.$scope.fromperiod", 'fromperiod', strval($defaultStartPeriod));
		$toPeriod = $this->getUserStateFromRequest( "$option.$scope.toperiod", 'toperiod', strval($defaultEndPeriod));
		$pagespeedLink = $this->getUserStateFromRequest( "$option.$scope.pagespeedlink", 'pagespeed_pageurl', $defaultModel->getComponentParams()->get('pagespeed_domain', Uri::root(false)));
		$inspectLink = $this->getUserStateFromRequest( "$option.$scope.inspectlink", 'inspect_pageurl', '');

		// Set model state
		$defaultModel->setState ( 'option', $option );
		$defaultModel->setState ( 'googlestats', $this->app->getInput()->getCmd ( 'googlestats', 'analytics' ) );
		$defaultModel->setState ( 'fromPeriod', $fromPeriod );
		$defaultModel->setState ( 'toPeriod', $toPeriod );
		$defaultModel->setState ( 'pagespeedlink', $pagespeedLink );
		$defaultModel->setState ( 'inspectlink', $inspectLink );
		
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
		// Access check.
		if (!$this->user->authorise ( 'jmap.google', $this->option )) {
			$this->setRedirect('index.php?option=com_jmap&task=cpanel.display', Text::_('COM_JMAP_ERROR_ALERT_NOACCESS'));
			return false;
		}
		
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
		// Load della model e checkin before exit
		$model = $this->getModel ();

		// Origin stats type
		$origin = $this->app->getInput()->get('googlestats', null);
		if($origin == 'webmasters') {
			$this->option .= '&googlestats=webmasters';
		}

		if (! $model->deleteEntity ( null )) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$modelException = $model->getException ( null, false );
			$this->app->enqueueMessage ( $modelException->getMessage (), $modelException->getExceptionLevel () );
			$this->setRedirect ( "index.php?option=" . $this->option . "&task=" . $this->name . ".display", Text::_ ( 'COM_JMAP_GOOGLE_ERROR_' . 'LOGOUT' ) );
			return false;
		}
	
		$this->setRedirect ( "index.php?option=" . $this->option . "&task=" . $this->name . ".display", Text::_ ( 'COM_JMAP_GOOGLE_SUCCESS_LOGOUT' ) );
		
		return true;
	}
	
	/**
	 * Submit a sitemap using the GWT API
	 *
	 * @access public
	 * @return void
	 */
	public function submitSitemap() {
		// Access check.
		if (!$this->user->authorise ( 'core.edit', $this->option )) {
			$this->setRedirect('index.php?option=com_jmap&task=cpanel.display', Text::_('COM_JMAP_ERROR_ALERT_NOACCESS'));
			return false;
		}
		
		// Load della model e checkin before exit
		$model = $this->getModel ();

		// Retrieve the sitemap link
		$sitemapUri = $this->app->getInput()->getString('sitemaplink');
		if(!$sitemapUri) {
			$this->setRedirect ( "index.php?option=" . $this->option . "&task=" . $this->name . ".display&googlestats=webmasters", Text::_ ( 'COM_JMAP_MISSING_DATA' ) );
			return false;
		}
	
		if (! $model->submitSitemap ( $sitemapUri )) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$modelException = $model->getException ( null, false );
			$this->app->enqueueMessage ( $modelException->getMessage (), $modelException->getExceptionLevel () );
			$this->setRedirect ( "index.php?option=" . $this->option . "&task=" . $this->name . ".display&googlestats=webmasters", Text::_ ( 'COM_JMAP_GOOGLE_WEBMASTERS_ERROR_SUBMITTING_SITEMAP' ) );
			return false;
		}
	
		$this->setRedirect ( "index.php?option=" . $this->option . "&task=" . $this->name . ".display&googlestats=webmasters", Text::_ ( 'COM_JMAP_GOOGLE_WEBMASTERS_SITEMAP_SUBMITTED' ) );
	}
	
	/**
	 * Delete a sitemap using the GWT API
	 *
	 * @access public
	 * @return void
	 */
	public function deleteSitemap() {
		// Access check.
		if (!$this->user->authorise ( 'core.edit', $this->option )) {
			$this->setRedirect('index.php?option=com_jmap&task=cpanel.display', Text::_('COM_JMAP_ERROR_ALERT_NOACCESS'));
			return false;
		}
		
		// Load della model e checkin before exit
		$model = $this->getModel ();

		// Retrieve the sitemap link
		$sitemapUri = $this->app->getInput()->getString('sitemapurl');
	
		if (! $model->deleteSitemap ( $sitemapUri )) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$modelException = $model->getException ( null, false );
			$this->app->enqueueMessage ( $modelException->getMessage (), $modelException->getExceptionLevel () );
			$this->setRedirect ( "index.php?option=" . $this->option . "&task=" . $this->name . ".display&googlestats=webmasters", Text::_ ( 'COM_JMAP_GOOGLE_WEBMASTERS_ERROR_DELETING_SITEMAP' ) );
			return false;
		}
	
		$this->setRedirect ( "index.php?option=" . $this->option . "&task=" . $this->name . ".display&googlestats=webmasters", Text::_ ( 'COM_JMAP_GOOGLE_WEBMASTERS_SITEMAP_DELETED' ) );
	}
	
	/**
	 * Avvia il processo di esportazione records
	 *
	 * @access public
	 * @return void
	 */
	public function exportXlsRecord() {
		// Access check.
		if (!$this->user->authorise ( 'core.edit', $this->option )) {
			$this->setRedirect('index.php?option=com_jmap&task=cpanel.display', Text::_('COM_JMAP_ERROR_ALERT_NOACCESS'));
			return false;
		}
		
		// Set model state
		$defaultModel = $this->setModelState('google');
		
		// Get view
		$view = $this->getView('google', 'html', '', array('base_path' => $this->basePath, 'layout' => 'default'));
		$view->setModel($defaultModel, true);
		$view->setLayout('xls');
		$view->sendXlsRecord('webmasters');
	}
	
	/**
	 * Avvia il processo di esportazione records
	 *
	 * @access public
	 * @return void
	 */
	public function exportXlsPagespeed() {
		// Access check.
		if (!$this->user->authorise ( 'core.edit', $this->option )) {
			$this->setRedirect('index.php?option=com_jmap&task=cpanel.display', Text::_('COM_JMAP_ERROR_ALERT_NOACCESS'));
			return false;
		}
		
		// Set model state
		$defaultModel = $this->setModelState('google');
		
		// Get view
		$view = $this->getView('google', 'html', '', array('base_path' => $this->basePath, 'layout' => 'default'));
		$view->setModel($defaultModel, true);
		$view->setLayout('xls');
		if(!$view->sendXlsPagespeed('pagespeed')) {
			$this->setRedirect('index.php?option=com_jmap&task=google.display&googlestats=pagespeedfetch');
			return false;
		}
	}
	
	/**
	 * Class Constructor
	 *
	 * @access public
	 * @return Object&
	 */
	public function __construct($config = array(), ?MVCFactoryInterface $factory = null, $app = null, $input = null) {
		parent::__construct($config, $factory, $app, $input);

		// Composer autoloader
		require_once JPATH_COMPONENT_ADMINISTRATOR. '/Framework/composer/autoload_real.php';
		\ComposerAutoloaderInitcb4c0ac1dedbbba2f0b42e9cdf4d93d7::getLoader();
	}
}