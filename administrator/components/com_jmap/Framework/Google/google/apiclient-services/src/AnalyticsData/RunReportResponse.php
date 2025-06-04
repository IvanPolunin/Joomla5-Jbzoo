<?php

namespace Google\Service\AnalyticsData;

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
class RunReportResponse extends \Google\Collection {
	protected $collection_key = 'totals';
	protected $dimensionHeadersType = DimensionHeader::class;
	protected $dimensionHeadersDataType = 'array';
	public $kind;
	protected $maximumsType = Row::class;
	protected $maximumsDataType = 'array';
	protected $metadataType = ResponseMetaData::class;
	protected $metadataDataType = '';
	protected $metricHeadersType = MetricHeader::class;
	protected $metricHeadersDataType = 'array';
	protected $minimumsType = Row::class;
	protected $minimumsDataType = 'array';
	protected $propertyQuotaType = PropertyQuota::class;
	protected $propertyQuotaDataType = '';
	public $rowCount;
	protected $rowsType = Row::class;
	protected $rowsDataType = 'array';
	protected $totalsType = Row::class;
	protected $totalsDataType = 'array';

	/**
	 *
	 * @param
	 *        	DimensionHeader[]
	 */
	public function setDimensionHeaders($dimensionHeaders) {
		$this->dimensionHeaders = $dimensionHeaders;
	}
	/**
	 *
	 * @return DimensionHeader[]
	 */
	public function getDimensionHeaders() {
		return $this->dimensionHeaders;
	}
	public function setKind($kind) {
		$this->kind = $kind;
	}
	public function getKind() {
		return $this->kind;
	}
	/**
	 *
	 * @param
	 *        	Row[]
	 */
	public function setMaximums($maximums) {
		$this->maximums = $maximums;
	}
	/**
	 *
	 * @return Row[]
	 */
	public function getMaximums() {
		return $this->maximums;
	}
	/**
	 *
	 * @param
	 *        	ResponseMetaData
	 */
	public function setMetadata(ResponseMetaData $metadata) {
		$this->metadata = $metadata;
	}
	/**
	 *
	 * @return ResponseMetaData
	 */
	public function getMetadata() {
		return $this->metadata;
	}
	/**
	 *
	 * @param
	 *        	MetricHeader[]
	 */
	public function setMetricHeaders($metricHeaders) {
		$this->metricHeaders = $metricHeaders;
	}
	/**
	 *
	 * @return MetricHeader[]
	 */
	public function getMetricHeaders() {
		return $this->metricHeaders;
	}
	/**
	 *
	 * @param
	 *        	Row[]
	 */
	public function setMinimums($minimums) {
		$this->minimums = $minimums;
	}
	/**
	 *
	 * @return Row[]
	 */
	public function getMinimums() {
		return $this->minimums;
	}
	/**
	 *
	 * @param
	 *        	PropertyQuota
	 */
	public function setPropertyQuota(PropertyQuota $propertyQuota) {
		$this->propertyQuota = $propertyQuota;
	}
	/**
	 *
	 * @return PropertyQuota
	 */
	public function getPropertyQuota() {
		return $this->propertyQuota;
	}
	public function setRowCount($rowCount) {
		$this->rowCount = $rowCount;
	}
	public function getRowCount() {
		return $this->rowCount;
	}
	/**
	 *
	 * @param
	 *        	Row[]
	 */
	public function setRows($rows) {
		$this->rows = $rows;
	}
	/**
	 *
	 * @return Row[]
	 */
	public function getRows() {
		return $this->rows;
	}
	/**
	 *
	 * @param
	 *        	Row[]
	 */
	public function setTotals($totals) {
		$this->totals = $totals;
	}
	/**
	 *
	 * @return Row[]
	 */
	public function getTotals() {
		return $this->totals;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( RunReportResponse::class, 'Google_Service_AnalyticsData_RunReportResponse' );
