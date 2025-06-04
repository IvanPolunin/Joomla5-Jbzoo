<?php
namespace JExtstore\Component\JMap\Administrator\Framework;
/**
 *
 * @package JMAP::administrator::components::com_jmap
 * @subpackage framework
 * @subpackage controller
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Controller\BaseController as JBaseController;
use Joomla\CMS\Cache\Controller\ViewController;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

/**
 * Base controller class
 *
 * @package JMAP::administrator::components::com_jmap
 * @subpackage framework
 * @subpackage controller
 * @since 1.0
 */
class Controller extends JBaseController {
	/**
	 * Dispatch option
	 *
	 * @access protected
	 * @var string
	 */
	protected $option;
	
	/**
	 * Main application reference
	 *
	 * @access protected
	 * @var Object
	 */
	protected $app;
	
	/**
	 * User object for ACL authorise check
	 *
	 * @access protected
	 * @var Object
	 */
	protected $user;
	
	/**
	 * Document object, needed by controllers to instantiate
	 * the right view object based on document format
	 *
	 * @access protected
	 * @var Object
	 */
	protected $document;
	
	/**
	 * Variables in request array
	 *
	 * @access protected
	 * @var Object
	 */
	protected $requestArray;
	
	/**
	 * Method override to check if you can add a new record.
	 *
	 * @param array $data
	 *        	An array of input data.
	 *        	
	 * @return bool
	 *
	 * @since 1.6
	 */
	protected function allowAdmin($assetName): bool {
		// Initialise variables.
		$allow = $this->user->authorise ( 'core.admin', $assetName );
		
		return $allow;
	}
	
	/**
	 * Method override to check if you can add a new record.
	 *
	 * @param array $data
	 *        	An array of input data.
	 *        	
	 * @return bool
	 *
	 * @since 1.6
	 */
	protected function allowAdd($assetName): bool {
		// Initialise variables.
		$allow = $this->user->authorise ( 'core.create', $assetName );
		
		return $allow;
	}
	
	/**
	 * Method override to check if you can edit an existing record.
	 *
	 * @param array $data
	 *        	An array of input data.
	 * @param string $key
	 *        	The name of the key for the primary key.
	 *        	
	 * @return bool
	 *
	 * @since 1.6
	 */
	protected function allowEdit($assetName): bool {
		// Initialise variables.
		$allow = $this->user->authorise ( 'core.edit', $assetName );
		
		return $allow;
	}
	
	/**
	 * Method override to check if you can edit an existing record.
	 *
	 * @param array $data
	 *        	An array of input data.
	 * @param string $key
	 *        	The name of the key for the primary key.
	 *        	
	 * @return bool
	 *
	 * @since 1.6
	 */
	protected function allowEditState($assetName): bool {
		// Initialise variables.
		$allow = $this->user->authorise ( 'core.edit.state', $assetName );
		
		return $allow;
	}
	
	/**
	 * Method override to check if you can edit an existing record.
	 *
	 * @param array $data
	 *        	An array of input data.
	 * @param string $key
	 *        	The name of the key for the primary key.
	 *        	
	 * @return bool
	 *
	 * @since 1.6
	 */
	protected function allowDelete($assetName): bool {
		// Initialise variables.
		$allow = $this->user->authorise ( 'core.delete', $assetName );
		
		return $allow;
	}
	
	/**
	 * Get a cache object specific for this extension models already configured and independant from global config
	 * The cache handler is always view to cache the entire component view response
	 *
	 * @access protected
	 * @return ViewController
	 */
	protected function getExtensionCache(): ViewController {
		// Static cache instance
		static $cache;
		if (is_object ( $cache )) {
			return $cache;
		}
		
		$conf = $this->app->getConfig ();
		$componentParams = ComponentHelper::getParams ( $this->option );
		
		// days to hours to minutes (core cache multiplies by 60 secs), default 1 day
		$lifeTimeMinutes = ( int ) $componentParams->get ( 'lifetime_view_cache', 1 ) * 24 * 60;
		
		// Check for an RSS feed lifetime override
		$format = $this->app->getInput()->get ( 'format', 'html' );
		if ($format == 'rss') {
			$lifeTimeMinutes = ( int ) $componentParams->get ( 'rss_lifetime_view_cache', 60 );
		}
		if ($format == 'gnews') {
			$lifeTimeMinutes = ( int ) $componentParams->get ( 'gnews_lifetime_view_cache', 60 );
		}

		$options = array (
				'defaultgroup' => $this->option,
				'cachebase' => $conf->get ( 'cache_path', JPATH_CACHE ),
				'lifetime' => $lifeTimeMinutes,
				'language' => $conf->get ( 'language', 'en-GB' ),
				'storage' => $conf->get ( 'cache_handler', 'file' ) 
		);
		
		$cache = Factory::getContainer()->get(\Joomla\CMS\Cache\CacheControllerFactoryInterface::class)->createCacheController( 'view', $options );
		$cache->setCaching ( $componentParams->get ( 'enable_view_cache', false ) );
		return $cache;
	}
	
	/**
	 * Setta il model state a partire dallo userstate di sessione
	 *
	 * @access protected
	 * @param string $scope        	
	 * @return object
	 */
	protected function setModelState($scope = 'default', $ordering = true): object {
		$option = $this->option;
		$componentParams = ComponentHelper::getParams ( $this->option );
		
		$search = $this->getUserStateFromRequest ( "$option.$scope.searchword", 'search', '' );
		
		$limit = $this->getUserStateFromRequest ( "$option.$scope.limit", 'limit', $componentParams->get ( 'lists_limit_pagination', 10 ), 'int' );
		$limitStart = $this->getUserStateFromRequest ( "$option.$scope.limitstart", 'limitstart', 0, 'int' );
		// Round del limit al change proof
		$limitStart = ($limit != 0 ? (floor ( $limitStart / $limit ) * $limit) : 0);
		
		// Check for ordering support
		if ($ordering) {
			$filter_order = $this->getUserStateFromRequest ( "$option.$scope.filter_order", 'filter_order', 's.ordering', 'cmd' );
			$filter_order_Dir = $this->getUserStateFromRequest ( "$option.$scope.filter_order_Dir", 'filter_order_Dir', 'asc', 'word' );
		}
		
		// Get default model
		$defaultModel = $this->getModel ();
		
		// Set model state
		$defaultModel->setState ( 'option', $option );
		$defaultModel->setState ( 'limit', $limit );
		$defaultModel->setState ( 'limitstart', $limitStart );
		$defaultModel->setState ( 'searchword', $search );
		
		// Check for ordering support
		if ($ordering) {
			$defaultModel->setState ( 'order', $filter_order );
			$defaultModel->setState ( 'order_dir', $filter_order_Dir );
		}
		
		return $defaultModel;
	}
	
	/**
	 * Gets the value of a user state variable and sets it in the session
	 *
	 * This is the same as the method in JApplication except that this also can optionally
	 * force you back to the first page when a filter has changed
	 *
	 * @param string $key
	 *        	The key of the user state variable.
	 * @param string $request
	 *        	The name of the variable passed in a request.
	 * @param string $default
	 *        	The default value for the variable if not found. Optional.
	 * @param string $type
	 *        	Filter for the variable, for valid values see {@link \Joomla\CMS\Filter\InputFilter::clean()}. Optional.
	 * @param boolean $resetPage
	 *        	If true, the limitstart in request is set to zero
	 *        	
	 * @return mixed The request user state, could be string or integer
	 * @since 2.0
	 */
	protected function getUserStateFromRequest($key, $request, $default = null, $type = 'none', $resetPage = true) {
		$app = Factory::getApplication ();
		$old_state = $app->getUserState ( $key );
		$cur_state = (! is_null ( $old_state )) ? $old_state : $default;
		$new_state = $this->app->getInput()->get ( $request, null, $type );
		
		if ($new_state && ($cur_state != $new_state) && ($resetPage)) {
			$this->app->getInput()->set ( 'limitstart', 0 );
		}
		
		// Save the new value only if it is set in this request.
		if ($new_state !== null) {
			$app->setUserState ( $key, $new_state );
		} else {
			$new_state = $cur_state;
		}
		
		return $new_state;
	}
	
	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  Model|boolean  Model object on success; otherwise false on failure.
	 *
	 * @since   3.0
	 */
	public function getModel($name = '', $prefix = '', $config = array()) {
		static $models = array ();
		
		if (empty($name)) {
			$name = $this->getName();
		}
		
		if (array_key_exists ( $name, $models )) {
			return $models [$name];
		}
		
		$model = parent::getModel($name, $prefix, $config);
		$models[$name] = $model;
		
		return $model;
	}
	
	/**
	 * Edit entity
	 *
	 * @access public
	 * @return bool
	 */
	public function editEntity(): bool {
		$this->app->getInput()->set ( 'hidemainmenu', 1 );
		$cid = $this->app->getInput()->get ( 'cid', array (
				0 
		), 'array' );
		$idEntity = ( int ) $cid [0];
		$model = $this->getModel ();
		$model->setState ( 'option', $this->option );
		
		// Try to load record from model
		if (! $record = $model->loadEntity ( $idEntity )) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$modelExceptions = $model->getExceptions ();
			foreach ( $modelExceptions as $exception ) {
				$this->app->enqueueMessage ( $exception->getMessage (), $exception->getExceptionLevel () );
			}
			$this->setRedirect ( "index.php?option=" . $this->option . "&task=" . $this->name . ".display", Text::_ ( 'COM_JMAP_ERROR_EDITING' ) );
			return false;
		}
		
		// Additional model state setting
		$model->setState ( 'option', $this->option );
		
		// Check out control on record
		if ($record->checked_out && $record->checked_out != $this->user->id) {
			$this->setRedirect ( "index.php?option=" . $this->option . "&task=" . $this->name . ".display", Text::_ ( 'COM_JMAP_CHECKEDOUT_RECORD' ), 'notice' );
			return false;
		}
		
		// Access check
		if ($record->id && ! $this->allowEdit ( $this->option )) {
			$this->setRedirect ( "index.php?option=" . $this->option . "&task=" . $this->name . ".display", Text::_ ( 'COM_JMAP_ERROR_ALERT_NOACCESS' ), 'notice' );
			return false;
		}
		
		if (! $record->id && ! $this->allowAdd ( $this->option )) {
			$this->setRedirect ( "index.php?option=" . $this->option . "&task=" . $this->name . ".display", Text::_ ( 'COM_JMAP_ERROR_ALERT_NOACCESS' ), 'notice' );
			return false;
		}
		
		// Check out del record
		if ($record->id) {
			$record->checkout ( $this->user->id );
		}
		
		// Get view and pushing model
		$viewType = $this->document->getType();
		$viewName = $this->input->get('view', $this->default_view);
		$viewLayout = $this->input->get('layout', 'default', 'string');
		$view = $this->getView($viewName, $viewType, '', array('base_path' => $this->basePath, 'layout' => $viewLayout));
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
		$context = implode ( '.', array (
				$this->option,
				strtolower ( $this->getName () ),
				'errordataload' 
		) );
		
		// Security layer for tags html outputted fields
		$sanitizedFields = array (
				'name',
				'description' 
		);
		foreach ( $sanitizedFields as $field ) {
			$this->requestArray [$field] = strip_tags ( $this->requestArray [$field] );
		}
		
		// Load della model e bind store
		$model = $this->getModel ();
		
		if (! $result = $model->storeEntity ()) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$modelException = $model->getException ( null, false );
			$this->app->enqueueMessage ( $modelException->getMessage (), $modelException->getExceptionLevel () );
			
			// Store data for session recover
			$this->app->setUserState ( $context, $this->requestArray );
			$this->setRedirect ( "index.php?option=" . $this->option . "&task=" . $this->name . ".editEntity&cid[]=" . $this->app->getInput()->get ( 'id' ), Text::_ ( 'COM_JMAP_ERROR_SAVING' ) );
			return false;
		}
		
		// Security safe if not model record id detected
		if (! $id = $result->id) {
			$id = $this->app->getInput()->get ( 'id' );
		}
		
		// Redirects switcher
		switch ($this->task) {
			case 'saveEntity' :
				$redirects = array (
						'task' => 'display',
						'msgsufix' => '_SAVING' 
				);
				break;
			
			case 'saveEntity2New' :
				$redirects = array (
						'task' => 'editEntity',
						'msgsufix' => '_STORING' 
				);
				
				break;
			
			default :
			case 'applyEntity' :
				$redirects = array (
						'task' => 'editEntity&cid[]=' . $id,
						'msgsufix' => '_APPLY' 
				);
				break;
		}
		
		$msg = 'COM_JMAP_SUCCESS' . $redirects ['msgsufix'];
		$controllerTask = $redirects ['task'];
		
		$this->setRedirect ( "index.php?option=" . $this->option . "&task=" . $this->name . "." . $controllerTask, Text::_ ( $msg ) );
		
		return true;
	}

	/**
	 * Manage cancel edit for entity and unlock record checked out
	 *
	 * @access public
	 * @return void
	 */
	public function cancelEntity(): void {
		$id = $this->app->getInput()->get ( 'id' );
		// Load della model e checkin before exit
		$model = $this->getModel ();

		if (! $model->cancelEntity ( $id )) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$modelException = $model->getException ( null, false );
			$this->app->enqueueMessage ( $modelException->getMessage (), $modelException->getExceptionLevel () );
		}

		$this->setRedirect ( "index.php?option=" . $this->option . "&task=" . $this->name . ".display", Text::_ ( 'COM_JMAP_CANCELED_OPERATION' ) );
	}

	/**
	 * Copies one or more items
	 *
	 * @access public
	 * @return bool
	 */
	public function copyEntity(): bool {
		$cids = $this->app->getInput()->get ( 'cid', array (), 'array' );
		// Load della model e checkin before exit
		$model = $this->getModel ();

		if (! $model->copyEntity ( $cids )) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$modelException = $model->getException ( null, false );
			$this->app->enqueueMessage ( $modelException->getMessage (), $modelException->getExceptionLevel () );
			$this->setRedirect ( "index.php?option=" . $this->option . "&task=" . $this->name . ".display", Text::_ ( 'COM_JMAP_ERROR_DUPLICATING' ) );
			return false;
		}

		$this->setRedirect ( "index.php?option=" . $this->option . "&task=" . $this->name . ".display", Text::_ ( 'COM_JMAP_SUCCESS_DUPLICATING' ) );
		
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
		// Access check
		if (! $this->allowDelete ( $this->option )) {
			$this->setRedirect ( "index.php?option=" . $this->option . "&task=" . $this->name . ".display", Text::_ ( 'COM_JMAP_ERROR_ALERT_NOACCESS' ), 'notice' );
			return false;
		}
		// Load della model e checkin before exit
		$model = $this->getModel ();
		
		if (! $model->deleteEntity ( $cids )) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$modelException = $model->getException ( null, false );
			$this->app->enqueueMessage ( $modelException->getMessage (), $modelException->getExceptionLevel () );
			$this->setRedirect ( "index.php?option=" . $this->option . "&task=" . $this->name . ".display", Text::_ ( 'COM_JMAP_ERROR_DELETE' ) );
			return false;
		}
		
		$this->setRedirect ( "index.php?option=" . $this->option . "&task=" . $this->name . ".display", Text::_ ( 'COM_JMAP_SUCCESS_DELETE' ) );
		
		return true;
	}
	
	/**
	 * Moves the order of a record
	 *
	 * @access public
	 * @param
	 *        	integer The increment to reorder by
	 * @return bool
	 */
	public function moveOrder(): bool {
		// Set model state
		$this->setModelState ( $this->name );
		// ID Entity
		$cid = $this->app->getInput()->get ( 'cid', array (
				0 
		), 'array' );
		$idEntity = $cid [0];
		// Task direction
		$model = $this->getModel ();
		$orderDir = $model->getState ( 'order_dir' );
		
		switch ($orderDir) {
			case 'desc' :
				$orderUp = 1;
				$orderDown = - 1;
				break;
			
			case 'asc' :
			default :
				$orderUp = - 1;
				$orderDown = 1;
				break;
		}
		
		$direction = $this->task == 'moveorder_up' ? $orderUp : $orderDown;
		
		if (! $model->changeOrder ( $idEntity, $direction )) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$modelException = $model->getException ( null, false );
			$this->app->enqueueMessage ( $modelException->getMessage (), $modelException->getExceptionLevel () );
			$this->setRedirect ( "index.php?option=" . $this->option . "&task=" . $this->name . ".display", Text::_ ( 'COM_JMAP_ERROR_REORDER' ) );
			return false;
		}
		
		$this->setRedirect ( "index.php?option=" . $this->option . "&task=" . $this->name . ".display", Text::_ ( 'COM_JMAP_SUCCESS_REORDER' ) );
		
		return true;
	}
	
	/**
	 * Save ordering
	 *
	 * @access public
	 * @return bool
	 */
	public function saveOrder(): bool {
		$cids = $this->app->getInput()->get ( 'cid', array (), 'array' );
		$order = $this->app->getInput()->get ( 'order', array (), 'array' );
		$isAjax = $this->app->getInput()->get ( 'ajax', null );
		ArrayHelper::toInteger ( $cids );
		ArrayHelper::toInteger ( $order );
		
		$model = $this->getModel ();
		
		if (! $model->saveOrder ( $cids, $order )) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$modelException = $model->getException ( null, false );
			$this->app->enqueueMessage ( $modelException->getMessage (), $modelException->getExceptionLevel () );
			$this->setRedirect ( "index.php?option=" . $this->option . "&task=" . $this->name . ".display", Text::_ ( 'COM_JMAP_ERROR_REORDER' ) );
			return false;
		}
		
		// Manage the ajax call without a redirect HTTP
		if ($isAjax) {
			echo "1";
			$this->app->close ();
		}
		
		$this->setRedirect ( "index.php?option=" . $this->option . "&task=" . $this->name . ".display", Text::_ ( 'COM_JMAP_SUCCESS_REORDER' ) );
		
		return true;
	}
	
	/**
	 * Publishing entities
	 *
	 * @access public
	 * @return bool
	 */
	public function publishEntities(): bool {
		// Access check
		if (! $this->allowEditState ( $this->option )) {
			$this->setRedirect ( "index.php?option=" . $this->option . "&task=" . $this->name . ".display", Text::_ ( 'COM_JMAP_ERROR_ALERT_NOACCESS' ), 'notice' );
			return false;
		}
		
		$cid = $this->app->getInput()->get ( 'cid', array (
				0 
		), 'array' );
		$idEntity = ( int ) $cid [0];
		
		$model = $this->getModel ();
		
		if (! $model->publishEntities ( $idEntity, $this->task )) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$modelException = $model->getException ( null, false );
			$this->app->enqueueMessage ( $modelException->getMessage (), $modelException->getExceptionLevel () );
			$this->setRedirect ( "index.php?option=" . $this->option . "&task=" . $this->name . ".display", Text::_ ( 'COM_JMAP_ERROR_STATE_CHANGE' ) );
			return false;
		}
		
		$this->setRedirect ( "index.php?option=" . $this->option . "&task=" . $this->name . ".display", Text::_ ( 'COM_JMAP_SUCCESS_STATE_CHANGE' ) );
		
		return true;
	}
	
	/**
	 * Checkin entities
	 *
	 * @access public
	 * @return bool
	 */
	public function checkin(): bool {
		// Access check
		if (! $this->user->authorise ( 'core.manage', 'com_checkin' )) {
			$this->setRedirect ( "index.php?option=" . $this->option . "&task=" . $this->name . ".display", Text::_ ( 'COM_JMAP_ERROR_ALERT_NOACCESS' ), 'notice' );
			return false;
		}
		
		$cid = $this->app->getInput()->get ( 'cid', array (
				0 
		), 'array' );
		$id = ( int ) $cid [0];
		
		// Load della model e checkin before exit
		$model = $this->getModel ();
		
		if (! $model->cancelEntity ( $id )) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$modelException = $model->getException ( null, false );
			$this->app->enqueueMessage ( $modelException->getMessage (), $modelException->getExceptionLevel () );
		}
		
		$this->setRedirect ( "index.php?option=" . $this->option . "&task=" . $this->name . ".display", Text::_ ( 'COM_JMAP_CHECKEDIN_RECORD' ) );
		
		return true;
	}
	
	/**
	 * Constructor.
	 *
	 * @access protected
	 * @param
	 *        	array An optional associative array of configuration settings.
	 *        	Recognized key values include 'name', 'default_task',
	 *        	'model_path', and
	 *        	'view_path' (this list is not meant to be comprehensive).
	 * @since 1.5
	 */
	public function __construct($config = array(), ?MVCFactoryInterface $factory = null, $app = null, $input = null) {
		parent::__construct($config, $factory, $app, $input);
		
		$this->user = $app->getIdentity ();
		$this->document = $app->getDocument();
		$this->option = $this->app->getInput()->get ( 'option' );
		$this->requestArray = &$_POST;
	}
}