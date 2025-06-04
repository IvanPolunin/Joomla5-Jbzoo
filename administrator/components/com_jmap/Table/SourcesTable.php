<?php
namespace JExtstore\Component\JMap\Administrator\Table;
/**
 *
 * @package JMAP::SOURCES::administrator::components::com_jmap
 * @subpackage tables
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
// no direct access
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\Database\DatabaseDriver;
use Joomla\Event\DispatcherInterface;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Joomla\CMS\Table\Table;
use JExtstore\Component\JMap\Administrator\Framework\Exception\Exceptions;

/**
 * ORM Table for sitemap sources
 *
 * @package JMAP::SOURCES::administrator::components::com_jmap
 * @subpackage tables
 * @since 1.0
 */
class SourcesTable extends Table {
	use Exceptions;
	
	/**
	 * @var int
	 */
	public $id = 0;
	
	/**
	 * @var string
	 */
	public $type = 'user';
	
	/**
	 * @var string
	 */
	public $name = '';
	
	/**
	 * @var string
	 */
	public $description = '';
	
	/**
	 * @var int
	 */
	public $checked_out = null;
	
	/**
	 * @var datetime
	 */
	public $checked_out_time = null;
	
	/**
	 * @var int
	 */
	public $published = 1;
	
	/**
	 * @var int
	 */
	public $ordering = 0;
	
	/**
	 * @var string
	 */
	public $sqlquery = '';
	
	/**
	 * @var string
	 */
	public $sqlquery_managed = '{}';
	
	/**
	 * @var string
	 */
	public $params = '{}';
	
	/**
	 * Bind Table override
	 * @override
	 * 
	 * @see Table::bind()
	 */
	public function bind($fromArray, $ignore = array(), $saveTask = false, $sessionTask = false) {
		parent::bind ( $fromArray, $ignore);
		
		if ($saveTask) {
			$registry = new Registry ();
			$registry->loadArray ( $this->params );
			$this->params = $registry->toString ();
			
			if (is_array ( $this->sqlquery_managed )) {
				$this->sqlquery_managed = json_encode ( $this->sqlquery_managed );
			}
		}
		
		// Manage complex attributes during session recovering bind/load
		if($sessionTask) {
			$registry = new Registry ( $this->params );
			$this->params = $registry;
				
			// By default convert to plain object this json serialized field, later convertable in Registry if needed
			if ($this->sqlquery_managed) {
				$this->sqlquery_managed = (object) ( $this->sqlquery_managed );
			}
		}
		
		return true;
	}
	
	/**
	 * Load Table override
	 * @override
	 * 
	 * @see Table::load()
	 */
	public function load($idEntity = null, $reset = true) {
		// If not $idEntity set return empty object
		if($idEntity) {
			if(!parent::load ( $idEntity )) {
				return false;
			}
		}

		$registry = new Registry ($this->params);
		$this->params = $registry;
		
		// By default convert to plain object this json serialized field, later convertable in Registry if needed
		if ($this->sqlquery_managed) {
			$this->sqlquery_managed = json_decode ( $this->sqlquery_managed );
		}
		
		return true;
	}
	
	/**
	 * Check Table override
	 * @override
	 * 
	 * @see Table::check()
	 */
	public function check() {
		// Name required
		if (! $this->name) {
			$this->setException ( Text::_('COM_JMAP_VALIDATION_ERROR' ) );
			return false;
		}
		
		// Validate sql query managed chunks
		if($this->type == 'user') {
			if(isset($this->sqlquery_managed)) {
				$sqlQuerymanagedObject = json_decode($this->sqlquery_managed);
				if(	!($sqlQuerymanagedObject->option) ||
					!($sqlQuerymanagedObject->table_maintable) ||
					!($sqlQuerymanagedObject->titlefield) ||
					!($sqlQuerymanagedObject->id)) {
						$this->setException ( Text::_('COM_JMAP_ERROR_DATASOURCE_VALIDATION' ) );
						return false;
				}
			}
		}
		return true;
	}
	
	/**
	 * Class constructor
	 * @param DatabaseDriver $db DatabaseDriver object.
	 * @param DispatcherInterface  $dispatcher  Event dispatcher for this table
	 *
	 * return Object&
	 */
	public function __construct(DatabaseDriver $db, ?DispatcherInterface $dispatcher = null) {
		parent::__construct ( '#__jmap', 'id', $db, $dispatcher );
		
		// Support null values for datetime field
		$this->_supportNullValue = true;
	}
}