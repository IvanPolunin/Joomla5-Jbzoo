<?php
namespace JExtstore\Component\JMap\Administrator\Controller;
/**
 * @package JMAP::SEOSPIDER::administrator::components::com_jmap
 * @subpackage controllers
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use JExtstore\Component\JMap\Administrator\Framework\Controller as JMapController;
use JExtstore\Component\JMap\Administrator\Framework\File\Seospider;

/**
 * Main sitemap Seospider controller manager
 * @package JMAP::SEOSPIDER::administrator::components::com_jmap
 * @subpackage controllers
 * @since 3.8
 */
class SeospiderController extends JMapController {
	/**
	 * Set model state from session userstate
	 * 
	 * @access protected
	 * @param string $scope        	
	 * @return object
	 */
	protected function setModelState($scope = 'default', $ordering = true): object {
		$option = $this->option;
		
		// Get default model
		$defaultModel = $this->getModel ();
		
		// JS Client check and reset userstate
		if($this->app->getInput()->get('jsclient', false)) {
			$postedLang = $this->app->getInput()->get('sitemaplang', null);
			if(!$postedLang) {
				$this->app->setUserState ( "$option.$scope.sitemaplang", null, '' );
			}
			$postedDataset = $this->app->getInput()->get('sitemapdataset', null);
			if(!$postedDataset) {
				$this->app->setUserState ( "$option.$scope.sitemapdataset", null, '' );
			}
			$postedItemid = $this->app->getInput()->get('sitemapitemid', null);
			if(!$postedItemid) {
				$this->app->setUserState ( "$option.$scope.sitemapitemid", null, '' );
			}
		}
		$sitemapLang = $this->getUserStateFromRequest ( "$option.$scope.sitemaplang", 'sitemaplang', '' );
		$sitemapDataset = $this->getUserStateFromRequest ( "$option.$scope.sitemapdataset", 'sitemapdataset', '' );
		$sitemapItemid = $this->getUserStateFromRequest ( "$option.$scope.sitemapitemid", 'sitemapitemid', '' );
		$searchPageWord = $this->getUserStateFromRequest ( "$option.$scope.searchpageword", 'searchpage', '', 'none', false );
		$filter_order = $this->getUserStateFromRequest ( "$option.$scope.filter_order", 'filter_order', '', 'cmd' );
		$filter_order_Dir = $this->getUserStateFromRequest ( "$option.$scope.filter_order_Dir", 'filter_order_Dir', '', 'word' );
		parent::setModelState ( $scope, false );
		
		// Set model state
		$defaultModel->setState ( 'sitemaplang', $sitemapLang );
		$defaultModel->setState ( 'sitemapdataset', $sitemapDataset );
		$defaultModel->setState ( 'sitemapitemid', $sitemapItemid );
		$defaultModel->setState ( 'searchpageword', $searchPageWord );
		$defaultModel->setState ( 'order', $filter_order );
		$defaultModel->setState ( 'order_dir', $filter_order_Dir );
		
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
	function display($cachable = false, $urlparams = false) {
		// Set model state
		$defaultModel = $this->setModelState('seospider');
		 
		// Parent construction and view display
		parent::display($cachable);
	}
	
	/**
	 * Export headings as CSV data
	 *
	 * @access public
	 * @return void
	 */
	public function exportEntities() {
		$option = $this->option;
		// Access check
		if(!$this->allowEdit($option)) {
			$this->setRedirect('index.php?option=com_jmap&task=seospider.display', Text::_('COM_JMAP_ERROR_ALERT_NOACCESS'), 'notice');
			return false;
		}
	
		// Set model state
		$defaultModel = $this->setModelState('seospider');
	
		$viewType = $this->document->getType ();
		$coreName = $this->getName ();
		$viewLayout = $this->app->getInput()->get ( 'layout', 'default' );
	
		$view = $this->getView ( $coreName, $viewType, '', array (
				'base_path' => $this->basePath
		) );
	
		// Push the model into the view (as default)
		$view->setModel ( $defaultModel, true );
	
		// Set the layout
		$view->setLayout ( $viewLayout );
		$view->display ('export');
	}
	
	/**
	 * Import headings from CSV data
	 *
	 * @access public
	 * @return void
	 */
	public function importEntities() {
		$option = $this->option;
		// Access check
		if(!$this->allowEdit($option)) {
			$this->setRedirect('index.php?option=com_jmap&task=seospider.display', Text::_('COM_JMAP_ERROR_ALERT_NOACCESS'), 'notice');
			return false;
		}
	
		// Get the file manager instance with db connector dependency injection
		$filesManager = new Seospider(Factory::getContainer()->get('DatabaseDriver'), $this->app);
	
		if(!$filesManager->import()) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$filesManagerException = $filesManager->getError(null, false);
			$this->app->enqueueMessage($filesManagerException->getMessage(), $filesManagerException->getExceptionLevel());
			$this->setRedirect ( "index.php?option=$option&task=seospider.display", Text::_('COM_JMAP_SEOSPIDER_ERROR_IMPORT'));
			return false;
		}
	
		$this->setRedirect ( "index.php?option=$option&task=seospider.display", Text::_('COM_JMAP_SEOSPIDER_SUCCESS_IMPORT'));
	}
}