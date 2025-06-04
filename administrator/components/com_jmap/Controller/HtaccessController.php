<?php
namespace JExtstore\Component\JMap\Administrator\Controller;
/**
 * @package JMAP::HTACCESS::administrator::components::com_jmap
 * @subpackage controllers
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use JExtstore\Component\JMap\Administrator\Framework\Controller as JMapController;

/**
 * Htaccess manager controller
 *
 * @package JMAP::HTACCESS::administrator::components::com_jmap
 * @subpackage controllers
 * @since 3.0
 */
class HtaccessController extends JMapController {
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
		if(!$htaccessContent = $model->loadEntity(null)) {
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
		$view = $this->getView('htaccess', 'html', '', array('base_path' => $this->basePath, 'layout' => 'default'));
		$view->setModel ( $model, true );
	
		// Call edit view
		$view->editEntity($htaccessContent);
		
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
		$data = $this->app->getInput()->get('htaccess_contents', null, 'raw');
		$restored = $this->app->getInput()->getInt('restored', 0);
	
		//Load della  model e bind store
		$model = $this->getModel ();
	
		if(!$result = $model->storeEntity($data)) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$modelException = $model->getException(null, false);
			$this->app->enqueueMessage($modelException->getMessage(), $modelException->getExceptionLevel());
			$this->setRedirect ("index.php?option=$option&task=htaccess.editEntity");
			return false;
		}

		$saveMsg = $restored ? Text::_('COM_JMAP_SUCCESS_RESTORE_HTACCESS') : Text::_('COM_JMAP_SUCCESS_SAVE_HTACCESS');
		$this->setRedirect ( "index.php?option=$option&task=htaccess.editEntity", $saveMsg);
		
		return true;
	}
	
	/**
	 * Activate the htaccess file entity
	 *
	 * @access public
	 * @return void
	 */
	public function activateEntity() {
		$option = $this->option;

		//Load della  model e bind store
		$model = $this->getModel ();

		if(!$result = $model->activateHtaccessEntity()) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$modelException = $model->getException(null, false);
			$this->app->enqueueMessage($modelException->getMessage(), $modelException->getExceptionLevel());
			$this->setRedirect ("index.php?option=$option&task=htaccess.editEntity");
			return false;
		}

		$this->setRedirect ( "index.php?option=$option&task=htaccess.editEntity", Text::_('COM_JMAP_SUCCESS_ACTIVATE_HTACCESS'));
	}
}