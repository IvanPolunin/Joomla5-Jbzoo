<?php
namespace JExtstore\Component\JMap\Administrator\Table;
/**
 *
 * @package JMAP::PINGOMATIC::administrator::components::com_jmap
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
use Joomla\CMS\Component\ComponentHelper;
use JExtstore\Component\JMap\Administrator\Framework\Exception\Exceptions;

/**
 * ORM Table for Pingomatic urls
 *
 * @package JMAP::PINGOMATIC::administrator::components::com_jmap
 * @subpackage tables
 * @since 2.0
 */
class PingomaticTable extends Table {
	use Exceptions;
	
	/**
	 * @var int
	 */
	public $id = 0;
	
	/**
	 * @var string
	 */
	public $title = '';
	
	/**
	 * @var string
	 */
	public $blogurl = '';
	
	/**
	 * @var string
	 */
	public $rssurl = '';
	
	/**
	 * @var string
	 */
	public $services = '{}';
	
	/**
	 * @var datetime
	 */
	public $lastping = null;
	
	/**
	 * @var int
	 */
	public $checked_out = null;
	
	/**
	 * @var datetime
	 */
	public $checked_out_time = null;

	/**
	 * Bind Table override
	 * @override
	 * 
	 * @see Table::bind()
	 */
	public function bind($fromArray, $ignore = array(),  $saveTask = false, $sessionTask = false) {
		parent::bind ( $fromArray, $ignore);
		
		if ($saveTask) {
			$services = array();
			foreach ($fromArray as $key => $value) {
				if (strpos($key, 'chk_') === 0 || strpos($key, 'ajs_') === 0) {
					$services[$key] = $value;
				}
			}
			if (is_array ( $services )) {
				$this->services = json_encode ( $services );
			}
			
			if(!$this->lastping) {
				$this->lastping = null;
			}
		}
		
		// Manage complex attributes during session recovering bind/load
		if($sessionTask) {
			$services = array();
			foreach ($fromArray as $key => $value) {
				if (strpos($key, 'chk_') === 0) {
					$services[$key] = $value;
				}
			}
			$registry = new Registry ( $services );
			$this->services = $registry;
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
		
		// Decoding services on load and wrap into Registry object
		if ($this->services) {
			$this->services = json_decode ( $this->services );
			$servicesRegistry = new Registry();
			$servicesRegistry->loadObject($this->services);
			// New assignment
			$this->services = $servicesRegistry;
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
		// Title required
		if (! $this->title) {
			$this->setException ( Text::_('COM_JMAP_VALIDATION_ERROR' ) );
			return false;
		}
		
		// Check if the validation is enabled and not based on server settings limit management
		if(ComponentHelper::getParams('com_jmap')->get('resources_limit_management', 1)) {
			// Link url required and to be valid
			$blogurl = filter_var($this->blogurl, FILTER_SANITIZE_URL);
			if (! $this->blogurl || !filter_var($blogurl, FILTER_VALIDATE_URL)) {
				$this->setException ( Text::_('COM_JMAP_VALIDATION_ERROR_URL' ) );
				return false;
			}
			
			// LinkRss url to be valid
			$rssurl = filter_var($this->rssurl, FILTER_SANITIZE_URL);
			if ( $this->rssurl && !filter_var($rssurl, FILTER_VALIDATE_URL)) {
				$this->setException ( Text::_('COM_JMAP_VALIDATION_ERROR_URL' ) );
				return false;
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
		parent::__construct ( '#__jmap_pingomatic', 'id', $db, $dispatcher );
		
		// Support null values for datetime field
		$this->_supportNullValue = true;
	}
}