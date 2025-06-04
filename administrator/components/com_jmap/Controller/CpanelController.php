<?php
namespace JExtstore\Component\JMap\Administrator\Controller;
/**
 * @package JMAP::CPANEL::administrator::components::com_jmap
 * @subpackage controllers
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use JExtstore\Component\JMap\Administrator\Framework\Controller as JMapController;
use JExtstore\Component\JMap\Administrator\Framework\Http;

/**
 * CPanel controller
 *
 * @package JMAP::CPANEL::administrator::components::com_jmap
 * @subpackage controllers
 * @since 1.0
 */
class CpanelController extends JMapController {
	/**
	 * Show Control Panel
	 * @access public
	 * @return void
	 */
	function display($cachable = false, $urlparams = false) {
		$defaultModel = $this->getModel();
		$defaultModel->setState('option', $this->option);
		// Auto-Refresh menu sources
		if(!$defaultModel->syncMenuSources()) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$modelExceptions = $defaultModel->getExceptions();
			foreach ($modelExceptions as $exception) {
				$this->app->enqueueMessage($exception->getMessage(), $exception->getExceptionLevel());
			}
		}
		
		// Retrieve status and version informations
		$view = $this->getView('cpanel', 'html', '', array('base_path' => $this->basePath, 'layout' => 'default'));
		$HTTPClient = new Http();
		$view->set('httpclient', $HTTPClient);
		
		parent::display ($cachable); 
	}
	
	/**
	 * Edit entity
	 *
	 * @access public
	 * @return bool
	 */
	public function editEntity(): bool {
		$this->app->getInput()->set('tmpl', 'component');
		$option = $this->option;
		
		$model = $this->getModel();
		$model->setState('option', $option);
	
		// Try to load record from model
		if(!$robotsContent = $model->loadEntity(null)) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$modelExceptions = $model->getExceptions();
			foreach ($modelExceptions as $exception) {
				$this->app->enqueueMessage($exception->getMessage(), $exception->getExceptionLevel());
			}
			return false;
		}
	
		// Access check
		if(!$this->allowEdit($model->getState('option'))) {
			$this->app->enqueueMessage(Text::_('COM_JMAP_ERROR_ALERT_NOACCESS'), 'notice');
			return false;
		}
	
		// Get view and pushing model
		$view = $this->getView('cpanel', 'html', '', array('base_path' => $this->basePath, 'layout' => 'default'));
		$view->setModel ( $model, true );
	
		// Call edit view
		$view->editEntity($robotsContent);
		
		return true;
	}
	
	/**
	 * Manage entity apply/save after edit entity
	 *
	 * @access public
	 * @return bool
	 */
	public function saveEntity(): bool {
		$option = $this->option;
		$data = $this->app->getInput()->getString('robots_contents', null);
	
		//Load della  model e bind store
		$model = $this->getModel ();
	
		if(!$result = $model->storeEntity($data)) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$modelException = $model->getException(null, false);
			$this->app->enqueueMessage($modelException->getMessage(), $modelException->getExceptionLevel());
			$this->setRedirect ("index.php?option=$option&task=cpanel.editEntity");
			return false;
		}

		$this->setRedirect ( "index.php?option=$option&task=cpanel.editEntity", Text::_('COM_JMAP_SUCCESS_SAVE_ROBOTS'));
		
		return true;
	}
		
	/**
	 * Show suggestions
	 * @access public
	 * @return void
	 */
	public function installerApp() {
		$view = $this->getView('cpanel', 'html', '', array('base_path' => $this->basePath, 'layout' => 'default'));
	
		$view->showInstallerApp();
	}
	
	/**
	 * Retrieve status info from external module
	 * @access public
	 * @return void
	 */
	public function getUpdates() {
		$HTTPClient = new Http();

		// Model instance
		$model = $this->getModel();
		$model->setState('option', 'com_jmap');
		$jsonObject = $model->getUpdates($HTTPClient);

		header('Content-type: application/json');
		echo json_encode($jsonObject);
	}
}