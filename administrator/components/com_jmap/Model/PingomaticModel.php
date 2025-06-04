<?php
namespace JExtstore\Component\JMap\Administrator\Model;
/**
 * @package JMAP::PINGOMATIC::administrator::components::com_jmap
 * @subpackage models
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use JExtstore\Component\JMap\Administrator\Framework\Model as JMapModel;
use JExtstore\Component\JMap\Administrator\Framework\Helpers\Html as JMapHelpersHtml;
use JExtstore\Component\JMap\Administrator\Framework\Exception as JMapException;
use JExtstore\Component\JMap\Administrator\Framework\Http;

/**
 * Pingomatic links model concrete implementation <<testable_behavior>>
 *
 * @package JMAP::PINGOMATIC::administrator::components::com_jmap
 * @subpackage models
 * @since 2.0
 */
class PingomaticModel extends JMapModel {
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
			$where [] = "(s.title LIKE " . $this->dbInstance->quote("%" . $this->state->get ( 'searchword' ) . "%") . ") OR" .
						"(s.blogurl LIKE " . $this->dbInstance->quote("%" . $this->state->get ( 'searchword' ) . "%") . ") OR" .
						"(s.rssurl LIKE " . $this->dbInstance->quote("%" . $this->state->get ( 'searchword' ) . "%") . ")";
		}
		
		if($this->state->get('fromPeriod')) {
			$where[] = "\n s.lastping > " . $this->dbInstance->quote(($this->state->get('fromPeriod')));
		}
		
		if($this->state->get('toPeriod')) {
			$where[] = "\n s.lastping < " . $this->dbInstance->quote(date('Y-m-d', strtotime("+1 day", strtotime($this->state->get('toPeriod')))));
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
				 "\n FROM #__jmap_pingomatic AS s" .
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
	 * @param Object& $record
	 * @return array
	 */
	public function getLists($record = null): array {
		$lists =[];

		// Common services
		$lists ['ajs_google'] = JMapHelpersHtml::booleanlist( 'ajs_google', 'data-host="https://www.google.com/search?q="', $record->services->get('ajs_google', 1));
		$lists ['ajs_bing'] = JMapHelpersHtml::booleanlist( 'ajs_bing', 'data-host="https://www.bing.com/search?q="', $record->services->get('ajs_bing', 1));
		$lists ['ajs_yandex'] = JMapHelpersHtml::booleanlist( 'ajs_yandex', 'data-host="https://yandex.com/search/?text="', $record->services->get('ajs_yandex', 1));
		$lists ['ajs_entireweb'] = JMapHelpersHtml::booleanlist( 'ajs_entireweb', 'data-host="https://search.entireweb.com/search?q="', $record->services->get('ajs_entireweb', 1));
		$lists ['ajs_viesearch'] = JMapHelpersHtml::booleanlist( 'ajs_viesearch', 'data-host="https://viesearch.com/?q="', $record->services->get('ajs_viesearch', 1));
		$lists ['ajs_webcrawler'] = JMapHelpersHtml::booleanlist( 'ajs_webcrawler', 'data-host="https://www.webcrawler.com/serp?q="', $record->services->get('ajs_webcrawler', 1));
		$lists ['ajs_yahoo'] = JMapHelpersHtml::booleanlist( 'ajs_yahoo', 'data-host="https://search.yahoo.com/search?p="', $record->services->get('ajs_yahoo', 1));
		$lists ['ajs_duckduckgo'] = JMapHelpersHtml::booleanlist( 'ajs_duckduckgo', 'data-host="https://duckduckgo.com/?q="', $record->services->get('ajs_duckduckgo', 1));
		$lists ['ajs_ask'] = JMapHelpersHtml::booleanlist( 'ajs_ask', 'data-host="https://www.ask.com/web?q="', $record->services->get('ajs_ask', 1));
		$lists ['ajs_indexnowbing'] = JMapHelpersHtml::booleanlist( 'ajs_indexnowbing', 'data-host="https://bing.com/indexnow?key=' . $this->getComponentParams()->get('indexnow_apikey', '28bcb027f9b443719ceac7cd30556c3c') . '&url="', $record->services->get('ajs_indexnowbing', 1));
		$lists ['ajs_indexnowyandex'] = JMapHelpersHtml::booleanlist( 'ajs_indexnowyandex', 'data-host="https://yandex.com/indexnow?key=' . $this->getComponentParams()->get('indexnow_apikey', '28bcb027f9b443719ceac7cd30556c3c') . '&url="', $record->services->get('ajs_indexnowyandex', 1));
		
		// Specialized services
		$lists ['chk_blogs'] = JMapHelpersHtml::booleanlist( 'chk_blogs', null, $record->services->get('chk_blogs', 1));
		$lists ['chk_feedburner'] = JMapHelpersHtml::booleanlist( 'chk_feedburner', null, $record->services->get('chk_feedburner', 1));
		$lists ['chk_tailrank'] = JMapHelpersHtml::booleanlist( 'chk_tailrank', null, $record->services->get('chk_tailrank', 1));
		$lists ['chk_superfeedr'] = JMapHelpersHtml::booleanlist( 'chk_superfeedr', null, $record->services->get('chk_superfeedr', 1));
		
		return $lists;
	}

	/**
	 * Get Pingomatic server stats
	 *
	 * @access public
	 * @return mixed HTML code
	 */
	public function getPingomaticStats(Http $httpClient) {
		return null;
	}
}