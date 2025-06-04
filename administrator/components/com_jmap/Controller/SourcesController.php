<?php
namespace JExtstore\Component\JMap\Administrator\Controller;
/**
 *
 * @package JMAP::SOURCES::administrator::components::com_jmap
 * @subpackage controllers
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use JExtstore\Component\JMap\Administrator\Framework\Controller as JMapController;
use JExtstore\Component\JMap\Administrator\Framework\File\Sources;

/**
 * Main sitemap sources controller manager
 * 
 * @package JMAP::SOURCES::administrator::components::com_jmap
 * @subpackage controllers
 * @since 1.0
 */
class SourcesController extends JMapController {
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
		$filter_state = $this->getUserStateFromRequest ( "$option.sources.filterstate", 'filter_state', '*' );
		$filter_type = $this->getUserStateFromRequest ( "$option.sources.filtertype", 'filter_type', '' );
		$language = $this->getUserStateFromRequest ( "$option.sources.languagejmap", 'languagejmap', '*' );
		
		$defaultModel = parent::setModelState ( $scope, true );
		
		// Set model state
		$defaultModel->setState( 'languagejmap', $language );
		$defaultModel->setState ( 'state', $filter_state );
		$defaultModel->setState ( 'type', $filter_type );
		
		return $defaultModel;
	}
	
	/**
	 * Default listEntities
	 *
	 * @access public
	 * @param $cachable string
	 *        	the view output will be cached
	 * @return void
	 */
	function display($cachable = false, $urlparams = false) {
		// Set model state
		$defaultModel = $this->setModelState ( 'sources' );
		
		// Parent construction and view display
		parent::display ( $cachable );
	}
	
	/**
	 * Edit entity
	 *
	 * @access public
	 * @return bool
	 */
	public function editEntity(): bool {
		$option = $this->option;
		$this->app->getInput()->set ( 'hidemainmenu', 1 );
		$cid = $this->app->getInput()->get ( 'cid', array (
				0 
		), 'array' );
		$idEntity = ( int ) $cid [0];
		$user = $this->user;
		
		$model = $this->getModel ();
		$model->setState ( 'option', $option );
		
		// Try to load record from model
		if (! $record = $model->loadEntity ( $idEntity )) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$modelExceptions = $model->getExceptions ();
			foreach ( $modelExceptions as $exception ) {
				$this->app->enqueueMessage ( $exception->getMessage (), $exception->getExceptionLevel () );
			}
			$this->setRedirect ( 'index.php?option=com_jmap&task=sources.display' );
			return false;
		}
		
		// Check if model record ORM table needs a type swap based on plugin type data source
		if ($this->app->getInput()->get ( 'type', null ) === 'plugin') {
			$record->type = 'plugin';
		}
		
		// Check if model record ORM table needs a type swap based on plugin type data source
		if ($this->app->getInput()->get ( 'type', null ) === 'links') {
			$record->type = 'links';
		}
		
		// Check out control on record
		if ($record->checked_out && $record->checked_out != $user->id) {
			$this->setRedirect ( 'index.php?option=' . $option . '&task=sources.display', Text::_ ( 'COM_JMAP_CHECKEDOUT_RECORD' ), 'notice' );
			return false;
		}
		
		// Access check
		if ($record->id && ! $this->allowEdit ( $model->getState ( 'option' ) )) {
			$this->setRedirect ( 'index.php?option=com_jmap&task=sources.display', Text::_ ( 'COM_JMAP_ERROR_ALERT_NOACCESS' ), 'notice' );
			return false;
		}
		
		if (! $record->id && ! $this->allowAdd ( $model->getState ( 'option' ) )) {
			$this->setRedirect ( 'index.php?option=com_jmap&task=sources.display', Text::_ ( 'COM_JMAP_ERROR_ALERT_NOACCESS' ), 'notice' );
			return false;
		}
		
		// Check out del record
		if ($record->id) {
			$record->checkout ( $user->id );
		}
		
		// Get view and pushing model
		$view = $this->getView ('sources', 'html', '', array('base_path' => $this->basePath, 'layout' => 'default'));
		$view->setModel ( $model, true );
		
		// Call edit view
		$view->editEntity ( $record );
		
		return true;
	}
	
	/**
	 * Manage entity apply/save after edit entity
	 *
	 * @access public
	 * @return bool
	 */
	public function saveEntity(): bool {
		// Ensure magic quotes is not active to preserve SQL Compiler work
		@ini_set ( 'magic_quotes_runtime', 0 );
		
		$task = $this->task;
		$option = $this->option;
		$context = implode ( '.', array (
				$option,
				strtolower ( $this->getName () ),
				'errordataload' 
		) );
		$regenerateQuery = $this->app->getInput()->get ( 'regenerate_query', false );
		$sqlQueryManagedChunks = $this->app->getInput()->get ( 'params', array (), 'array' );
		$targetExtension = $sqlQueryManagedChunks ['datasource_extension'];
		
		// Security layer for tags html outputted fields
		$sanitizedFields = array (
				'name',
				'description',
				'sqlquery'
		);
		foreach ( $sanitizedFields as $field ) {
			if(isset($this->requestArray [$field])) {
				$this->requestArray [$field] = str_replace('<=', '&lt;=', $this->requestArray [$field]);
				$this->requestArray [$field] = strip_tags ( $this->requestArray [$field] );
				$this->requestArray [$field] = str_replace('&lt;=', '<=', $this->requestArray [$field]);
			}
		}
		$this->requestArray ['params'] ['title'] = strip_tags ( $this->requestArray ['params'] ['title'] );
		
		// Get sources model and make dependency injection
		$wizardModel = $this->getModel ( 'Wizard', null, array (
				'extension' => $targetExtension,
				'sourcesModel' => null 
		) );
		
		// Load della model e bind store
		$model = $this->getModel ();
		
		if (! $result = $model->storeEntity ( $regenerateQuery, false, $wizardModel )) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$modelException = $model->getException ( null, false );
			$this->app->enqueueMessage ( $modelException->getMessage (), $modelException->getExceptionLevel () );
			
			// Store data for session recover
			$this->app->setUserState ( $context, $this->requestArray );
			$this->setRedirect ( 'index.php?option=com_jmap&task=sources.editEntity&cid[]=' . $this->app->getInput()->get ( 'id' ), Text::_ ( 'COM_JMAP_ERROR_SAVING' ) );
			return false;
		}
		
		// Security safe if not model record id detected
		if (! $id = $result->id) {
			$id = $this->app->getInput()->get ( 'id' );
		}
		
		$redirects = $task == 'saveEntity' ? array (
				'task' => 'display',
				'msgsufix' => '_SAVING' 
		) : array (
				'task' => 'editEntity&cid[]=' . $id,
				'msgsufix' => '_APPLY' 
		);
		$msg = 'COM_JMAP_SUCCESS' . $redirects ['msgsufix'];
		$controllerTask = $redirects ['task'];
		
		$this->setRedirect ( "index.php?option=$option&task=sources.$controllerTask", Text::_ ( $msg ) );
		
		return true;
	}
	
	/**
	 * Delete a db table entity
	 *
	 * @access public
	 * @return bool
	 */
	public function deleteEntity(): bool {
		$cids = $this->app->getInput()->get ( 'cid', array (), 'array' );
		$option = $this->option;
		$additionalNotice = null;
		// Access check
		if (! $this->allowDelete ( $option )) {
			$this->setRedirect ( 'index.php?option=com_jmap&task=sources.display', Text::_ ( 'COM_JMAP_ERROR_ALERT_NOACCESS' ), 'notice' );
			return false;
		}
		// Load della model e checkin before exit
		$model = $this->getModel ();
		
		// Ensure not deleting Content source if multiple mode is enabled
		$cParams = $model->getComponentParams ();
		if ($cParams->get ( 'multiple_content_sources', 0 )) {
			$foundKey = array_search ( 1, $cids );
			if ($foundKey !== false) {
				array_splice ( $cids, $foundKey, 1, null );
				$additionalNotice = Text::_ ( 'COM_JMAP_NOTALLOWED_CONTENT_DELETE' );
			}
		}
		
		if (! $model->deleteEntity ( $cids )) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$modelException = $model->getException ( null, false );
			$this->app->enqueueMessage ( $modelException->getMessage (), $modelException->getExceptionLevel () );
			$this->setRedirect ( "index.php?option=$option&task=sources.display", Text::_ ( 'COM_JMAP_ERROR_DELETE' ) );
			return false;
		}
		$this->setRedirect ( "index.php?option=$option&task=sources.display", Text::_ ( 'COM_JMAP_SUCCESS_DELETE' ) . $additionalNotice );
		
		return true;
	}
	
	/**
	 * Export sources as db table entities
	 *
	 * @access public
	 * @return void
	 */
	public function exportEntities() {
		$cids = $this->app->getInput()->get ( 'cid', array (), 'array' );
		$option = $this->option;
		// Access check
		if (! $this->allowEdit ( $option )) {
			$this->setRedirect ( 'index.php?option=com_jmap&task=sources.display', Text::_ ( 'COM_JMAP_ERROR_ALERT_NOACCESS' ), 'notice' );
			return false;
		}
		
		// Get the file manager instance with db connector dependency injection
		$filesManager = new Sources ( Factory::getContainer()->get('DatabaseDriver'), $this->app );
		
		if (! $filesManager->export ( $cids )) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$filesManagerException = $filesManager->getError ( null, false );
			$this->app->enqueueMessage ( $filesManagerException->getMessage (), $filesManagerException->getExceptionLevel () );
			$this->setRedirect ( "index.php?option=$option&task=sources.display", Text::_ ( 'COM_JMAP_ERROR_EXPORT' ) );
			return false;
		}
		
		$this->setRedirect ( "index.php?option=$option&task=sources.display", Text::_ ( 'COM_JMAP_SUCCESS_EXPORT' ) );
	}
	
	/**
	 * Import sources as db table entities
	 *
	 * @access public
	 * @return void
	 */
	public function importEntities() {
		$option = $this->option;
		// Access check
		if (! $this->allowEdit ( $option )) {
			$this->setRedirect ( 'index.php?option=com_jmap&task=sources.display', Text::_ ( 'COM_JMAP_ERROR_ALERT_NOACCESS' ), 'notice' );
			return false;
		}
		
		// Get the file manager instance with db connector dependency injection
		$filesManager = new Sources ( Factory::getContainer()->get('DatabaseDriver'), $this->app );
		
		if (! $filesManager->import ()) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$filesManagerException = $filesManager->getError ( null, false );
			$this->app->enqueueMessage ( $filesManagerException->getMessage (), $filesManagerException->getExceptionLevel () );
			$this->setRedirect ( "index.php?option=$option&task=sources.display", Text::_ ( 'COM_JMAP_ERROR_IMPORT' ) );
			return false;
		}
		
		$this->setRedirect ( "index.php?option=$option&task=sources.display", Text::_ ( 'COM_JMAP_SUCCESS_IMPORT' ) );
	}
	
	/**
	 * Export sources as db table entities
	 *
	 * @access public
	 * @return void
	 */
	public function importPlugins() {
		$option = $this->option;
		// Access check
		if (! $this->allowAdd ( $option )) {
			$this->setRedirect ( 'index.php?option=com_jmap&task=sources.display', Text::_ ( 'COM_JMAP_ERROR_ALERT_NOACCESS' ), 'notice' );
			return false;
		}
		
		// Get the file manager instance with db connector dependency injection
		$filesManager = new Sources ( Factory::getContainer()->get('DatabaseDriver'), $this->app );
		
		if (! $filesManager->install ()) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$filesManagerException = $filesManager->getError ( null, false );
			$this->app->enqueueMessage ( $filesManagerException->getMessage (), $filesManagerException->getExceptionLevel () );
			$this->setRedirect ( "index.php?option=$option&task=sources.display", Text::_ ( 'COM_JMAP_ERROR_INSTALLING_PLUGIN' ) );
			return false;
		}
		
		// After importing files forward request to save data source
		$this->app->enqueueMessage ( Text::_ ( 'COM_JMAP_PLUGIN_INSTALL_SUCCESSFUL' ) );
		
		$this->task = 'applyEntity';
		$this->saveEntity ();
	}
	
	/**
	 * Class Constructor
	 *
	 * @access public
	 * @return Object&
	 */
	public function __construct($config = array(), ?MVCFactoryInterface $factory = null, $app = null, $input = null) {
		parent::__construct($config, $factory, $app, $input);
		
		// Register Extra tasks
		$this->registerTask ( 'moveorder_up', 'moveOrder' );
		$this->registerTask ( 'moveorder_down', 'moveOrder' );
		$this->registerTask ( 'applyEntity', 'saveEntity' );
		$this->registerTask ( 'unpublish', 'publishEntities' );
		$this->registerTask ( 'publish', 'publishEntities' );
	}
}