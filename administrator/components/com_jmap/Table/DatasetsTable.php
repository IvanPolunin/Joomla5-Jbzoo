<?php
namespace JExtstore\Component\JMap\Administrator\Table;
/**
 *
 * @package JMAP::DATASETS::administrator::components::com_jmap
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
use JExtstore\Component\JMap\Administrator\Framework\Exception\Exceptions;

/**
 * ORM Table for Datasets
 *
 * @package JMAP::DATASETS::administrator::components::com_jmap
 * @subpackage tables
 * @since 2.0
 */
class DatasetsTable extends Table {
	use Exceptions;
	
	/**
	 *
	 * @var int
	 */
	public $id = 0;
	
	/**
	 *
	 * @var string
	 */
	public $name = '';
	
	/**
	 *
	 * @var string
	 */
	public $description = '';
	
	/**
	 *
	 * @var int
	 */
	public $checked_out = null;
	
	/**
	 *
	 * @var datetime
	 */
	public $checked_out_time = null;
	
	/**
	 * @var int
	 */
	public $published = 1;
	
	/**
	 *
	 * @var string
	 */
	public $sources = '[]';
	
	/**
	 * Check Table override
	 * @override
	 *
	 * @see Table::check()
	 */
	public function check() {
		// Title required
		if (! $this->name) {
			$this->setException ( Text::_ ( 'COM_JMAP_VALIDATION_ERROR' ) );
			return false;
		}
		
		return true;
	}
	
	/**
	 * Store Table override
	 * @override
	 *
	 * @see Table::store()
	 */
	public function store($updateNulls = false) {
		$result = parent::store($updateNulls);
		
		// If store sucessful go on to popuplate relations table for sources/datasets
		if($result) {
			// Clear table from previous records
			$queryDelete = "DELETE" .
						   "\n FROM " . $this->_db->quoteName('#__jmap_dss_relations') .
						   "\n WHERE" .
						   "\n " . $this->_db->quoteName('datasetid') . " = " .
						   "\n " . (int)$this->id;
			$this->_db->setQuery($queryDelete)->execute();
			
			// Manage multiple tuples to be inserted using single query
			$selectedSources = json_decode($this->sources);
			if(count($selectedSources)) {
				$insertTuples = array();
				foreach ($selectedSources as $source) {
					$insertTuples[] = '(' . (int)$this->id . ',' . $source . ')';
				}
				$insertTuples = implode(',', $insertTuples);
				
				$queryMultipleInsert = "INSERT" .
									   "\n INTO " . $this->_db->quoteName('#__jmap_dss_relations') .
									   "\n (" . 
									   $this->_db->quoteName('datasetid') . "," .
									   $this->_db->quoteName('datasourceid') . ")" .
									   "\n VALUES " . $insertTuples;
				$this->_db->setQuery($queryMultipleInsert)->execute();
			}
		}
		
		return $result;
	}
	
	/**
	 * Delete Table override
	 * @override
	 *
	 * @see Table::delete()
	 */
	public function delete($pk = null) {
		$result = parent::delete($pk);
		
		// If store sucessful go on to popuplate relations table for sources/datasets
		if($result) {
			// Clear table from previous records
			$queryDelete = "DELETE" .
						   "\n FROM " . $this->_db->quoteName('#__jmap_dss_relations') .
						   "\n WHERE" .
						   "\n " . $this->_db->quoteName('datasetid') . " = " .
						   "\n " . (int)$this->id;
			$this->_db->setQuery($queryDelete)->execute();
		}
		
		
		return $result;
	}
	
	/**
	 * Class constructor
	 * @param DatabaseDriver $db DatabaseDriver object.
	 * @param DispatcherInterface  $dispatcher  Event dispatcher for this table
	 *
	 * return Object&
	 */
	public function __construct(DatabaseDriver $db, ?DispatcherInterface $dispatcher = null) {
		parent::__construct ( '#__jmap_datasets', 'id', $db, $dispatcher );

		// Support null values for datetime field
		$this->_supportNullValue = true;
	}
}