<?php
namespace JExtstore\Component\JMap\Administrator\Model;
/**
 * @package JMAP::PATTERNS::administrator::components::com_jmap
 * @subpackage models
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use JExtstore\Component\JMap\Administrator\Framework\Model as JMapModel;
use JExtstore\Component\JMap\Administrator\Framework\Helpers\Html as JMapHelpersHtml;
use JExtstore\Component\JMap\Administrator\Framework\Exception as JMapException;

/**
 * Patterns model concrete implementation <<testable_behavior>>
 *
 * @package JMAP::PATTERNS::administrator::components::com_jmap
 * @subpackage models
 * @since 1.8
 */
class PatternsModel extends JMapModel {
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

		// STATE FILTER
		if ($filter_state = $this->state->get ( 'state' )) {
			if ($filter_state == 'P') {
				$where [] = 's.published = 1';
			} elseif ($filter_state == 'U') {
				$where [] = 's.published = 0';
			}
		}
		
		// TEXT FILTER
		if ($this->state->get ( 'searchword' )) {
			$where [] = "(s.original_text_regex LIKE " . $this->dbInstance->quote("%" . $this->state->get ( 'searchword' ) . "%") . " OR " .
						"s.target_text_regex LIKE " . $this->dbInstance->quote("%" . $this->state->get ( 'searchword' ) . "%") . ")";
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
		
		$query = "SELECT s.*," .
				 "\n u.name AS editor" . 
				 "\n FROM #__jmap_text_replacements AS s" .
				 "\n LEFT JOIN #__users AS u" .
				 "\n ON s.checked_out = u.id" . 
				 $whereString . 
				 $orderString;
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
		} catch (JMapException $e) {
			$this->app->enqueueMessage($e->getMessage(), $e->getExceptionLevel());
			$result = array();
		} catch (\Exception $e) {
			$jredirectsException = new JMapException($e->getMessage(), 'error');
			$this->app->enqueueMessage($jredirectsException->getMessage(), $jredirectsException->getExceptionLevel());
			$result = array();
		}
		return $result;
	}
	
	/**
	 * Return select lists used as filter for listEntities
	 *
	 * @access public
	 * @return array
	 */
	public function getFilters(): array {
		$filters = [];
		
		// Filter by redirect state
		$filterState = [];
		$filterState[] = HTMLHelper::_('select.option', null, Text::_('COM_JMAP_PATTERNS_ALL'));
		$filterState[] = HTMLHelper::_('select.option', 'P', Text::_('COM_JMAP_PATTERNS_PUBLISHED'));
		$filterState[] = HTMLHelper::_('select.option', 'U', Text::_('COM_JMAP_PATTERNS_UNPUBLISHED'));
		
		$filters ['state'] = HTMLHelper::_ ( 'select.genericlist', $filterState, 'filter_state', 'onchange="Joomla.submitform();"', 'value', 'text', $this->getState ( 'state' ));
		
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
		$lists = parent::getLists($record);

		return $lists;
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
}