<?php
namespace JExtstore\Component\JMap\Administrator\Framework;
/**
 *
 * @package JMAP::FRAMEWORK::administrator::components::com_jmap
 * @subpackage framework
 * @subpackage model
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Table\Nested as TableNested;
use Joomla\CMS\MVC\Model\BaseDatabaseModel as JBaseModel;
use Joomla\CMS\Cache\Controller\OutputController;
use Joomla\CMS\Cache\Controller\CallbackController;
use Joomla\Registry\Registry;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;
use JExtstore\Component\JMap\Administrator\Framework\Exception\Exceptions;
use JExtstore\Component\JMap\Administrator\Framework\Exception as JMapException;
use JExtstore\Component\JMap\Administrator\Framework\Helpers\Html as JMapHelpersHtml;

/**
 * Base model responsibilities
 *
 * @package JMAP::FRAMEWORK::administrator::components::com_jmap
 * @subpackage framework
 * @subpackage model
 * @since 2.0
 */
interface IJMapModel {
	/**
	 * Main get data method
	 *
	 * @access public
	 * @return Object[]
	 */
	public function getData(): array;
	
	/**
	 * Counter result set
	 *
	 * @access public
	 * @return int
	 */
	public function getTotal(): int;
	
	/**
	 * Load entity from ORM table
	 *
	 * @access public
	 * @param int $id        	
	 * @return Object&
	 */
	public function loadEntity($id);
	
	/**
	 * Cancel editing entity
	 *
	 * @param int $id        	
	 * @access public
	 * @return bool
	 */
	public function cancelEntity($id): bool;
	
	/**
	 * Delete entity
	 *
	 * @param array $ids        	
	 * @access public
	 * @return bool
	 */
	public function deleteEntity($ids): bool;
	
	/**
	 * Storing entity by ORM table
	 *
	 * @access public
	 * @param bool $updateNulls
	 * @return mixed
	 */
	public function storeEntity($updateNulls = false);
	
	/**
	 * Publishing state changer for entities
	 *
	 * @access public
	 * @param int $idEntity        	
	 * @param string $state        	
	 * @return bool
	 */
	public function publishEntities($idEntity, $state): bool;
	
	/**
	 * Change entities ordering
	 *
	 * @access public
	 * @param int $idEntity        	
	 * @param string $state        	
	 * @return boolean
	 */
	public function changeOrder($idEntity, $direction): bool;
	
	/**
	 * Method to move and reorder
	 *
	 * @access public
	 * @return bool
	 */
	function saveOrder($cid, $order): bool;
	
	/**
	 * Copy existing entity
	 *
	 * @param int $id        	
	 * @access public
	 * @return bool
	 */
	public function copyEntity($ids): bool;
	
	/**
	 * Return select lists used as filter for listEntities
	 *
	 * @access public
	 * @return array
	 */
	public function getFilters(): array;
	
	/**
	 * Return select lists used as filter for editEntity
	 *
	 * @access public
	 * @param Object $record        	
	 * @return array
	 */
	public function getLists($record = null): array;
	
	/**
	 * Get the component params width view override/merge
	 *
	 * @access public
	 * @return Object Registry
	 */
	public function getComponentParams(): Registry;
}

/**
 * Base concrete model for business logic
 *
 * @package JMAP::FRAMEWORK::administrator::components::com_jmap
 * @subpackage framework
 * @subpackage model
 * @since 2.0
 */
class Model extends JBaseModel implements IJMapModel {
	use Exceptions;
	
	/**
	 * Application reference
	 *
	 * @access protected
	 * @var Object
	 */
	protected $app;
	
	/**
	 * User reference
	 *
	 * @access protected
	 * @var Object
	 */
	protected $user;
	
	/**
	 * Database reference
	 *
	 * @access protected
	 * @var Object
	 */
	protected $dbInstance;
	
	/**
	 * Component params with view override
	 *
	 * @access protected
	 * @var Object
	 */
	protected $componentParams;
	
	/**
	 * Variables in request array
	 *
	 * @access protected
	 * @var Object
	 */
	protected $requestArray;
	
	/**
	 * Get a cache object specific for this extension models already configured and independant from global config
	 * The cache handler is always callback to cache functions operations and SQL database queries
	 *
	 * @access protected
	 * @return CallbackController
	 */
	protected function getExtensionCache(): CallbackController {
		// Static cache instance
		static $cache;
		if (is_object ( $cache )) {
			return $cache;
		}
		
		$conf = $this->app->getConfig ();
		$componentParams = $this->getComponentParams();
		$options = array (
				'defaultgroup' => $this->option,
				'cachebase' => $conf->get ( 'cache_path', JPATH_CACHE ),
				'lifetime' => ( int ) $componentParams->get ( 'cache_lifetime', 24 ) * 60, // hours to minutes (core cache multiplies by 60 secs), default 24 hours
				'language' => $conf->get ( 'language', 'en-GB' ),
				'storage' => $conf->get ( 'cache_handler', 'file' ) 
		);
		
		$cache = Factory::getContainer()->get(\Joomla\CMS\Cache\CacheControllerFactoryInterface::class)->createCacheController( 'callback', $options );
		$cache->setCaching ( $componentParams->get ( 'caching', false ) );
		return $cache;
	}
	
	/**
	 * Get a cache object specific for this extension models
	 * already configured and independant from global config
	 * The cache handler is always output to cache any arbitrary data based on id
	 *
	 * @access protected
	 * @return OutputController
	 */
	protected function getExtensionOutputCache(): OutputController {
		// Static cache instance
		static $cache;
		if (is_object ( $cache )) {
			return $cache;
		}
	
		$conf = $this->app->getConfig ();
		$options = array (
				'defaultgroup' => $this->option,
				'cachebase' => $conf->get ( 'cache_path', JPATH_CACHE ),
				'lifetime' => (24 * 60), // hours to minutes (core cache multiplies by 60 secs), default 24 hours
				'language' => $conf->get ( 'language', 'en-GB' ),
				'storage' => $conf->get ( 'cache_handler', 'file' )
		);
	
		$cache = Factory::getContainer()->get(\Joomla\CMS\Cache\CacheControllerFactoryInterface::class)->createCacheController( 'output', $options );
		$cache->setCaching ( true );
		return $cache;
	}
	
	/**
	 * Store an action log record into the table xxx__action_logs that is properly formatted and translated
	 *
	 * @access protected
	 * @param string $action
	 * @param string $context
	 * @param string $contextDescription
	 * @param string $link
	 * @param int $itemId
	 * @return boolean
	 */
	protected function storeActionLog($action, $context, $contextDescription, $link, $itemId) {
		$user = $this->app->getIdentity();
		
		$messageObject = new \stdClass();
		$messageObject->action = $action;
		$messageObject->title = 'com_jmap';
		$messageObject->extension_name = 'com_jmap';
		$messageObject->userid = $user->id;
		$messageObject->username = $user->username;
		$messageObject->itemlink = $link;
		$messageObject->accountlink = "index.php?option=com_users&task=user.edit&id=" . $user->id;
		
		$actionLogTable = new \stdClass();
		$actionLogTable->message_language_key = Text::sprintf('COM_JMAP_ACTIONLOGS_STRING', $action, $contextDescription);
		$actionLogTable->message = json_encode($messageObject);
		$actionLogTable->log_date = (string)Factory::getDate();
		$actionLogTable->extension = 'com_jmap.' . $context;
		$actionLogTable->user_id = $user->id;
		$actionLogTable->item_id = $itemId;
		
		$params = ComponentHelper::getComponent('com_actionlogs')->getParams();
		if ($params->get('ip_logging', 0)) {
			// Normal, non-proxied server or server behind a transparent proxy
			if (isset($_SERVER['REMOTE_ADDR']) && filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP)) {
				$ip = $_SERVER['REMOTE_ADDR'];
			} else {
				$ip = 'COM_ACTIONLOGS_IP_INVALID';
			}
		} else {
			$ip = 'COM_ACTIONLOGS_DISABLED';
		}
		$actionLogTable->ip_address = $ip;
		
		try {
			$this->dbInstance->insertObject('#__action_logs', $actionLogTable);
		} catch (\RuntimeException $e) {
			// Ignore it
		}
	}
	
	/**
	 * Create the filename for a resource
	 *
	 * @param string $type
	 *        	The resource type to create the filename for.
	 * @param array $parts
	 *        	An associative array of filename information.
	 *        	
	 * @return string The filename
	 *        
	 * @since 3.0
	 */
	protected static function _createFileName($type, $parts = array()): string {
		$filename = '';
		
		switch ($type) {
			case 'model' :
				$filename = strtolower ( $parts ['name'] ) . '.php';
				break;
		}
		
		return $filename;
	}
	
	/**
	 * Main get data method
	 *
	 * @access public
	 * @return Object[]
	 */
	public function getData(): array {
		// Build query
		$query = $this->buildListQuery ();
		try {
			$dbQuery = method_exists ( $this->dbInstance, 'createQuery' ) ? $this->dbInstance->createQuery () : $this->dbInstance->getQuery ( true );
			$dbQuery->setQuery ( $query )->setLimit ( $this->getState ( 'limit' ), $this->getState ( 'limitstart' ) );
			$this->dbInstance->setQuery ( $dbQuery );
			$result = $this->dbInstance->loadObjectList ();
		} catch ( JMapException $e ) {
			$this->app->enqueueMessage ( $e->getMessage (), $e->getExceptionLevel () );
			$result = array ();
		} catch ( \Exception $e ) {
			$jmapException = new JMapException ( $e->getMessage (), 'error' );
			$this->app->enqueueMessage ( $jmapException->getMessage (), $jmapException->getExceptionLevel () );
			$result = array ();
		}
		return $result;
	}
	
	/**
	 * Counter result set
	 *
	 * @access public
	 * @return int
	 */
	public function getTotal(): int {
		// Build query
		$result = 0;
		$query = $this->buildListQuery ();
		
		try {
			$this->dbInstance->setQuery ( $query );
			$result = count ( $this->dbInstance->loadColumn () );
		} catch ( \Exception $e ) {
			$jmapException = new JMapException ( $e->getMessage (), 'error' );
			$this->app->enqueueMessage ( $jmapException->getMessage (), $jmapException->getExceptionLevel () );
			$result = 0;
		}
		
		return $result;
	}
	
	/**
	 * Load entity from ORM table
	 *
	 * @access public
	 * @param int $id        	
	 * @return mixed Object on success or false on failure
	 */
	public function loadEntity($id) {
		// load table record
		$table = $this->getTable ($this->getName(), 'Administrator');
		
		// Check for previously set post data after errors
		$context = implode ( '.', array (
				$this->getState ( 'option' ),
				$this->getName (),
				'errordataload' 
		) );
		$sessionData = $this->app->getUserState ( $context );
		
		try {
			// Give priority to session recovered data
			if (! $sessionData) {
				// Load normally from database
				if (! $table->load ( $id )) {
					throw new JMapException ( Text::_ ( 'COM_JMAP_ERROR_RECORD_NOT_FOUND' ), 'error' );
				}
			} else {
				// Recover and bind/load from session
				$table->bind ( $sessionData, array (), false, true );
				// Delete session data for next request
				$this->app->setUserState ( $context, null );
			}
		} catch ( JMapException $e ) {
			$this->setException ( $e );
			return false;
		} catch ( \Exception $e ) {
			$jmapException = new JMapException ( $e->getMessage (), 'error' );
			$this->setException ( $jmapException );
			return false;
		}
		return $table;
	}
	
	/**
	 * Cancel editing entity
	 *
	 * @param int $id        	
	 * @access public
	 * @return bool
	 */
	public function cancelEntity($id): bool {
		// New record - do null e return true subito
		if (! $id) {
			return true;
		}
		
		$table = $this->getTable ($this->getName(), 'Administrator');
		try {
			if (! $table->load ( $id )) {
				throw new JMapException ( Text::_ ( 'COM_JMAP_ERROR_RECORD_NOT_FOUND' ), 'error' );
			}
			
			$table->checkin ();
		} catch ( JMapException $e ) {
			$this->setException ( $e );
			return false;
		} catch ( \Exception $e ) {
			$jmapException = new JMapException ( $e->getMessage (), 'error' );
			$this->setException ( $jmapException );
			return false;
		}
		
		return true;
	}
	
	/**
	 * Delete entity
	 *
	 * @param array $ids        	
	 * @access public
	 * @return bool
	 */
	public function deleteEntity($ids): bool {
		$table = $this->getTable ($this->getName(), 'Administrator');
		
		// Ciclo su ogni entity da cancellare
		if (is_array ( $ids ) && count ( $ids )) {
			foreach ( $ids as $id ) {
				try {
					if (! $table->delete ( $id )) {
						throw new JMapException ( $table->getException (), 'error' );
					}
					// Only if table supports ordering
					if (property_exists ( $table, 'ordering' )) {
						$table->reorder ();
					}
				} catch ( JMapException $e ) {
					$this->setException ( $e );
					return false;
				} catch ( \Exception $e ) {
					$jmapException = new JMapException ( $e->getMessage (), 'error' );
					$this->setException ( $jmapException );
					return false;
				}
			}
		}
		
		return true;
	}
	
	/**
	 * Storing entity by ORM table
	 *
	 * @access public
	 * @param bool $updateNulls
	 * @return mixed Object on success or false on failure
	 */
	public function storeEntity($updateNulls = false) {
		$table = $this->getTable ($this->getName(), 'Administrator');
		try {
			// Bind override aware, supports true as second param to distinguish when bind is store/load, has not side effect on original ignore array
			$table->bind ( $this->requestArray, array (), true );
			
			// Run validation server side
			if (! $table->check ()) {
				throw new JMapException ( $table->getException (), 'error' );
			}
			
			// By default, never update nulls
			if (! $table->store ( $updateNulls )) {
				throw new JMapException ( $table->getException (), 'error' );
			}
			// Only if table supports ordering
			if (property_exists ( $table, 'ordering' )) {
				$where = null;
				$catidOrdering = property_exists ( $table, 'catid' );
				if ($catidOrdering) {
					$where = 'catid = ' . $table->catid;
				}
				$table->reorder ( $where );
			}
		} catch ( JMapException $e ) {
			$this->setException ( $e );
			return false;
		} catch ( \Exception $e ) {
			$jmapException = new JMapException ( $e->getMessage (), 'error' );
			$this->setException ( $jmapException );
			return false;
		}
		return $table;
	}
	
	/**
	 * Publishing state changer for entities
	 *
	 * @access public
	 * @param int $idEntity        	
	 * @param string $state        	
	 * @return bool
	 */
	public function publishEntities($idEntity, $state): bool {
		// Table load
		$table = $this->getTable ( $this->getName(), 'Administrator' );
		
		if (isset ( $idEntity ) && $idEntity) {
			try {
				// Ensure treat as array
				if (! is_array ( $idEntity )) {
					$idEntity = array (
							$idEntity 
					);
				}
				$state = $state == 'unpublish' ? 0 : 1;
				if (! $table->publish ( $idEntity, $state, $this->user->id )) {
					throw new JMapException ( $table->getException (), 'notice' );
				}
			} catch ( JMapException $e ) {
				$this->setException ( $e );
				return false;
			} catch ( \Exception $e ) {
				$jmapException = new JMapException ( $e->getMessage (), 'notice' );
				$this->setException ( $jmapException );
				return false;
			}
		}
		return true;
	}
	
	/**
	 * Change entities ordering
	 *
	 * @access public
	 * @param int $idEntity        	
	 * @param int $direction        	
	 * @return bool
	 */
	public function changeOrder($idEntity, $direction): bool {
		$where = null;
		if (isset ( $idEntity ) && $idEntity) {
			try {
				$table = $this->getTable ($this->getName(), 'Administrator');
				if (! $table->load ( ( int ) $idEntity )) {
					throw new JMapException ( Text::_ ( 'COM_JMAP_ERROR_RECORD_NOT_FOUND' ), 'notice' );
				}
				
				// Check if ordering where by cats is required
				if (property_exists ( $table, 'catid' )) {
					$where = 'catid = ' . $table->catid;
				}
				if (! $table->move ( $direction, $where )) {
					throw new JMapException ( $table->getException (), 'notice' );
				}
			} catch ( JMapException $e ) {
				$this->setException ( $e );
				return false;
			} catch ( \Exception $e ) {
				$jmapException = new JMapException ( $e->getMessage (), 'notice' );
				$this->setException ( $jmapException );
				return false;
			}
		}
		return true;
	}
	
	/**
	 * Method to move and reorder
	 *
	 * @access public
	 * @param array $cid        	
	 * @param array $order        	
	 * @return bool
	 */
	public function saveOrder($cid, $order): bool {
		if (is_array ( $cid ) && count ( $cid )) {
			try {
				$table = $this->getTable ($this->getName(), 'Administrator');
				$singleReorder = ! (property_exists ( $table, 'catid' ));
				// If TableNested demand to table class the saveorder algo
				if ($table instanceof TableNested) {
					if (! $table->saveorder ( $cid, $order )) {
						throw new JMapException ( $table->getException (), 'notice' );
					}
				} else {
					// update ordering values
					$conditions = array ();
					for($i = 0; $i < count ( $cid ); $i ++) {
						$table->load ( ( int ) $cid [$i] );
						if ($table->ordering != $order [$i]) {
							$table->ordering = $order [$i];
							if (! $table->store ()) {
								throw new JMapException ( $table->getException (), 'notice' );
							}
						}
						
						if(!$singleReorder) {
							// Remember to reorder within position and client_id
							$condition = 'catid = ' . @$table->catid;
							$found = false;
							
							foreach ( $conditions as $cond ) {
								if ($cond [1] == $condition) {
									$found = true;
									break;
								}
							}
							
							if (! $found) {
								$key = $table->getKeyName ();
								$conditions [] = array (
										$table->$key,
										$condition 
								);
							}
						}
					}
				}
			} catch ( JMapException $e ) {
				$this->setException ( $e );
				return false;
			} catch ( \Exception $e ) {
				$jmapException = new JMapException ( $e->getMessage (), 'notice' );
				$this->setException ( $jmapException );
				return false;
			}
			
			// All went well
			try {
				if (! $table instanceof TableNested && ! $singleReorder) {
					// Execute reorder for each category.
					foreach ( $conditions as $cond ) {
						$table->load ( $cond [0] );
						$table->reorder ( $cond [1] );
					}
				} elseif (! $table instanceof TableNested && $singleReorder) {
					$table->reorder ();
				}
			} catch ( \Exception $e ) {
				$jmapException = new JMapException ( $e->getMessage (), 'notice' );
				$this->setException ( $jmapException );
				return false;
			}
		}
		return true;
	}
	
	/**
	 * Copy existing entity
	 *
	 * @param int $id        	
	 * @access public
	 * @return bool
	 */
	public function copyEntity($ids): bool {
		if (is_array ( $ids ) && count ( $ids )) {
			$table = $this->getTable ($this->getName(), 'Administrator');
			try {
				foreach ( $ids as $id ) {
					if ($table->load ( ( int ) $id )) {
						$table->id = 0;
						$table->name = Text::_ ( 'COM_JMAP_COPYOF' ) . $table->name;
						$table->published = 0;
						$table->params = $table->params->toString ();
						if (! $table->store ()) {
							throw new JMapException ( $table->getException (), 'error' );
						}
					} else {
						throw new JMapException ( Text::_ ( 'COM_JMAP_ERROR_RECORD_NOT_FOUND' ), 'error' );
					}
				}
				$table->reorder ();
			} catch ( JMapException $e ) {
				$this->setException ( $e );
				return false;
			} catch ( \Exception $e ) {
				$jmapException = new JMapException ( $e->getMessage (), 'error' );
				$this->setException ( $jmapException );
				return false;
			}
		}
		return true;
	}
	
	/**
	 * Return select lists used as filter for listEntities
	 *
	 * @access public
	 * @return array
	 */
	public function getFilters(): array {
		$filters = [];
		$filters ['state'] = HTMLHelper::_ ( 'grid.state', $this->getState ( 'state' ) );
		
		return $filters;
	}
	
	/**
	 * Return select lists used as filter for editEntity
	 *
	 * @access public
	 * @param Object $record        	
	 * @return array
	 */
	public function getLists($record = null): array {
		$lists = [];
		// Grid states
		$lists ['published'] = JMapHelpersHtml::booleanlist ( 'published', null, $record->published );
		
		return $lists;
	}
	
	/**
	 * Get the component params width view override/merge
	 *
	 * @access public
	 * @return Object Registry
	 */
	public function getComponentParams(): Registry {
		if (is_object ( $this->componentParams )) {
			return $this->componentParams;
		}
		
		// Manage Site and Admin application instance to call params with view overrides when needed
		if (isset($this->app) && $this->app->isClient('site') && $this->option == 'com_jmap') {
			$this->componentParams = $this->app->getParams ( 'com_jmap' );
		} else {
			$this->componentParams = ComponentHelper::getParams ( 'com_jmap' );
		}
		
		return $this->componentParams;
	}
	
	/**
	 * Class constructor
	 *
	 * @access public
	 * @param $config array        	
	 * @return Object&
	 */
	public function __construct($config = array(), ?MVCFactoryInterface $factory = null) {
		parent::__construct ( $config, $factory );
		
		// Add include paths to the Table class
		Table::addIncludePath ( JPATH_ROOT . '/administrator/components/com_jmap/Table' );
		
		$this->app = Factory::getApplication ();
		$this->user = $this->app->getIdentity();
		$this->requestArray = &$_POST;
		
		// Joomla 4.2+
		if(method_exists($this, 'getDatabase')) {
			$this->dbInstance = $this->getDatabase();
		} else {
			$this->dbInstance = $this->getDbo();
		}
	}
}