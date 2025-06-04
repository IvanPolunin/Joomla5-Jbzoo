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
use Joomla\CMS\Table\Table;
use JExtstore\Component\JMap\Administrator\Framework\Exception as JMapException;
use JExtstore\Component\JMap\Administrator\Framework\Exception\Exceptions;

/**
 * ORM Table for sitemap sources
 *
 * @package JMAP::SOURCES::administrator::components::com_jmap
 * @subpackage tables
 * @since 3.0
 */
class CatsPrioritiesTable extends Table {
	use Exceptions;
	
	/**
	 * @var int
	 */
	public $id = 0;
	
	/**
	 * @var string
	 */
	public $priority = '';
	
	/**
	 * Method to store a row in the database from the Table instance properties.
	 * If a primary key value is set the row with that primary key value will be
	 * updated with the instance property values.  If no primary key value is set
	 * a new row will be inserted into the database with the properties from the
	 * Table instance.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 */
	public function store($updateNulls = false, $currentID = null) {
		// Initialise variables.
		$k = $this->_tbl_key;
		
		// Must be set a primary key and priority to store/update record
		if(!$this->$k && !$currentID || !$this->priority) {
			throw new JMapException(Text::_('COM_JMAP_VALIDATON_ERROR_MISSING_FIELDS'), 'warning');
		}
	
		// If a primary key really exists in DB as numeric and not autoincrement update the object, otherwise insert it.
		if ($this->$k > 0) {
			$stored = $this->_db->updateObject($this->_tbl, $this, $this->_tbl_key, $updateNulls);
		} else {
			$this->id = (int)$currentID;
			$stored = $this->_db->insertObject($this->_tbl, $this, $this->_tbl_key);
		}
	
		return $stored;
	}
	
	/**
	 * Class constructor
	 * @param DatabaseDriver $db DatabaseDriver object.
	 * @param DispatcherInterface  $dispatcher  Event dispatcher for this table
	 *
	 * return Object&
	 */
	public function __construct(DatabaseDriver $db, ?DispatcherInterface $dispatcher = null) {
		parent::__construct ( '#__jmap_cats_priorities', 'id', $db, $dispatcher );
	}
}