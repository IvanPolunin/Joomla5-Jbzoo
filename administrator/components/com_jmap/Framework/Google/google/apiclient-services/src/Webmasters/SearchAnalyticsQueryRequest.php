<?php

namespace Google\Service\Webmasters;

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
class SearchAnalyticsQueryRequest extends \Google\Collection {
	protected $collection_key = 'dimensions';
	public $aggregationType;
	public $dataState;
	protected $dimensionFilterGroupsType = ApiDimensionFilterGroup::class;
	protected $dimensionFilterGroupsDataType = 'array';
	public $dimensions;
	public $endDate;
	public $rowLimit;
	public $searchType;
	public $startDate;
	public $startRow;
	public function setAggregationType($aggregationType) {
		$this->aggregationType = $aggregationType;
	}
	public function getAggregationType() {
		return $this->aggregationType;
	}
	public function setDataState($dataState) {
		$this->dataState = $dataState;
	}
	public function getDataState() {
		return $this->dataState;
	}
	/**
	 *
	 * @param
	 *        	ApiDimensionFilterGroup[]
	 */
	public function setDimensionFilterGroups($dimensionFilterGroups) {
		$this->dimensionFilterGroups = $dimensionFilterGroups;
	}
	/**
	 *
	 * @return ApiDimensionFilterGroup[]
	 */
	public function getDimensionFilterGroups() {
		return $this->dimensionFilterGroups;
	}
	public function setDimensions($dimensions) {
		$this->dimensions = $dimensions;
	}
	public function getDimensions() {
		return $this->dimensions;
	}
	public function setEndDate($endDate) {
		$this->endDate = $endDate;
	}
	public function getEndDate() {
		return $this->endDate;
	}
	public function setRowLimit($rowLimit) {
		$this->rowLimit = $rowLimit;
	}
	public function getRowLimit() {
		return $this->rowLimit;
	}
	public function setSearchType($searchType) {
		$this->searchType = $searchType;
	}
	public function getSearchType() {
		return $this->searchType;
	}
	public function setStartDate($startDate) {
		$this->startDate = $startDate;
	}
	public function getStartDate() {
		return $this->startDate;
	}
	public function setStartRow($startRow) {
		$this->startRow = $startRow;
	}
	public function getStartRow() {
		return $this->startRow;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( SearchAnalyticsQueryRequest::class, 'Google_Service_Webmasters_SearchAnalyticsQueryRequest' );
