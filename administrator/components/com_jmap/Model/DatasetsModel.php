<?php
namespace JExtstore\Component\JMap\Administrator\Model;
/**
 * @package JMAP::DATASETS::administrator::components::com_jmap
 * @subpackage models
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use JExtstore\Component\JMap\Administrator\Framework\Model as JMapModel;
use JExtstore\Component\JMap\Administrator\Framework\Exception as JMapException;

/**
 * Datasets links model concrete implementation <<testable_behavior>>
 *
 * @package JMAP::DATASETS::administrator::components::com_jmap
 * @subpackage models
 * @since 2.0
 */
class DatasetsModel extends JMapModel {
	/**
	 * Build list entities query
	 * 
	 * @access protected
	 * @return string
	 */
	protected function buildListQuery() {
		// WHERE
		$where = array ();
		$whereString = null;
		$orderString = null;

		// TEXT FILTER
		if ($this->state->get ( 'searchword' )) {
			$where [] = "(s.name LIKE " . $this->dbInstance->quote("%" . $this->state->get ( 'searchword' ) . "%") . ")";
		}
		
		if (count ( $where )) {
			$whereString = "\n WHERE " . implode ( "\n AND ", $where );
		}
		
		// ORDERBY
		if ($this->state->get ( 'order' )) {
			$orderString = "\n ORDER BY " . $this->state->get ( 'order' ) . " ";
		}
		
		// ORDERDIR
		if ($this->state->get ( 'order_dir' )) {
			$orderString .= $this->state->get ( 'order_dir' );
		}
		
		$query = "SELECT s.*, u.name AS editor" . 
				 "\n FROM #__jmap_datasets AS s" .
				 "\n LEFT JOIN #__users AS u" .
				 "\n ON s.checked_out = u.id" . 
				 $whereString . $orderString;
		return $query;
	}

	/**
	 * Main get data methods
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
			
			// Attach names for included data sources
			if(count($result)) {
				foreach ($result as &$row) {
					$subQuery = "SELECT" .
								"\n " . $this->dbInstance->quoteName('name') .
								"\n FROM " . $this->dbInstance->quoteName('#__jmap') .
								"\n WHERE " . $this->dbInstance->quoteName('id') . ' IN ( ' . preg_replace('/\[|\]/i', '', $row->sources) . ' )';
					$subQueryResults = $this->dbInstance->setQuery($subQuery)->loadColumn();
					$row->sourcesNames = $subQueryResults;
				}
			}
		} catch (JMapException $e) {
			$this->app->enqueueMessage($e->getMessage(), $e->getExceptionLevel());
			$result = array();
		} catch (\Exception $e) {
			$jmapException = new JMapException($e->getMessage(), 'error');
			$this->app->enqueueMessage($jmapException->getMessage(), $jmapException->getExceptionLevel());
			$result = array();
		}
		return $result;
	}
	
	/**
	 * Storing entity by ORM table
	 *
	 * @access public
	 * @param bool $updateNulls
	 * @return mixed Object on success or false on failure
	 */
	public function storeEntity($updateNulls = true) {
		return parent::storeEntity($updateNulls);
	}
	
	/**
	 * Return select lists used as filter for editEntity
	 *
	 * @access public
	 * @param Object $record
	 * @return array
	 */
	public function getLists($record = null): array {
		$lists = parent::getLists($record);

		$lists['sources'] = array(); 

		// Select all published data sources
		$query = method_exists ( $this->dbInstance, 'createQuery' ) ? $this->dbInstance->createQuery () : $this->dbInstance->getQuery ( true );
		
		$query->select($this->dbInstance->quoteName('id'));
		$query->select($this->dbInstance->quoteName('name'));
		$query->from($this->dbInstance->quoteName('#__jmap'));
		$query->where($this->dbInstance->quoteName('published') . ' = 1');
		$query->order($this->dbInstance->quoteName('ordering'));
		
		$this->dbInstance->setQuery($query);
		$lists['sources'] = $this->dbInstance->loadObjectList();
		
		return $lists;
	}
}