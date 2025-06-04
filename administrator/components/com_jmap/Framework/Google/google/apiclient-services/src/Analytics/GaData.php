<?php

namespace Google\Service\Analytics;

/**
 *
 * @package JMAP::FRAMEWORK::administrator::components::com_jmap
 * @subpackage framework
 * @subpackage google
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ();
class GaData extends \Google\Collection {
	protected $collection_key = 'rows';
	protected $columnHeadersType = GaDataColumnHeaders::class;
	protected $columnHeadersDataType = 'array';
	public $containsSampledData;
	public $dataLastRefreshed;
	protected $dataTableType = GaDataDataTable::class;
	protected $dataTableDataType = '';
	public $id;
	public $itemsPerPage;
	public $kind;
	public $nextLink;
	public $previousLink;
	protected $profileInfoType = GaDataProfileInfo::class;
	protected $profileInfoDataType = '';
	protected $queryType = GaDataQuery::class;
	protected $queryDataType = '';
	public $rows;
	public $sampleSize;
	public $sampleSpace;
	public $selfLink;
	public $totalResults;
	public $totalsForAllResults;

	/**
	 *
	 * @param
	 *        	GaDataColumnHeaders[]
	 */
	public function setColumnHeaders($columnHeaders) {
		$this->columnHeaders = $columnHeaders;
	}
	/**
	 *
	 * @return GaDataColumnHeaders[]
	 */
	public function getColumnHeaders() {
		return $this->columnHeaders;
	}
	public function setContainsSampledData($containsSampledData) {
		$this->containsSampledData = $containsSampledData;
	}
	public function getContainsSampledData() {
		return $this->containsSampledData;
	}
	public function setDataLastRefreshed($dataLastRefreshed) {
		$this->dataLastRefreshed = $dataLastRefreshed;
	}
	public function getDataLastRefreshed() {
		return $this->dataLastRefreshed;
	}
	/**
	 *
	 * @param
	 *        	GaDataDataTable
	 */
	public function setDataTable(GaDataDataTable $dataTable) {
		$this->dataTable = $dataTable;
	}
	/**
	 *
	 * @return GaDataDataTable
	 */
	public function getDataTable() {
		return $this->dataTable;
	}
	public function setId($id) {
		$this->id = $id;
	}
	public function getId() {
		return $this->id;
	}
	public function setItemsPerPage($itemsPerPage) {
		$this->itemsPerPage = $itemsPerPage;
	}
	public function getItemsPerPage() {
		return $this->itemsPerPage;
	}
	public function setKind($kind) {
		$this->kind = $kind;
	}
	public function getKind() {
		return $this->kind;
	}
	public function setNextLink($nextLink) {
		$this->nextLink = $nextLink;
	}
	public function getNextLink() {
		return $this->nextLink;
	}
	public function setPreviousLink($previousLink) {
		$this->previousLink = $previousLink;
	}
	public function getPreviousLink() {
		return $this->previousLink;
	}
	/**
	 *
	 * @param
	 *        	GaDataProfileInfo
	 */
	public function setProfileInfo(GaDataProfileInfo $profileInfo) {
		$this->profileInfo = $profileInfo;
	}
	/**
	 *
	 * @return GaDataProfileInfo
	 */
	public function getProfileInfo() {
		return $this->profileInfo;
	}
	/**
	 *
	 * @param
	 *        	GaDataQuery
	 */
	public function setQuery(GaDataQuery $query) {
		$this->query = $query;
	}
	/**
	 *
	 * @return GaDataQuery
	 */
	public function getQuery() {
		return $this->query;
	}
	public function setRows($rows) {
		$this->rows = $rows;
	}
	public function getRows() {
		return $this->rows;
	}
	public function setSampleSize($sampleSize) {
		$this->sampleSize = $sampleSize;
	}
	public function getSampleSize() {
		return $this->sampleSize;
	}
	public function setSampleSpace($sampleSpace) {
		$this->sampleSpace = $sampleSpace;
	}
	public function getSampleSpace() {
		return $this->sampleSpace;
	}
	public function setSelfLink($selfLink) {
		$this->selfLink = $selfLink;
	}
	public function getSelfLink() {
		return $this->selfLink;
	}
	public function setTotalResults($totalResults) {
		$this->totalResults = $totalResults;
	}
	public function getTotalResults() {
		return $this->totalResults;
	}
	public function setTotalsForAllResults($totalsForAllResults) {
		$this->totalsForAllResults = $totalsForAllResults;
	}
	public function getTotalsForAllResults() {
		return $this->totalsForAllResults;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( GaData::class, 'Google_Service_Analytics_GaData' );
