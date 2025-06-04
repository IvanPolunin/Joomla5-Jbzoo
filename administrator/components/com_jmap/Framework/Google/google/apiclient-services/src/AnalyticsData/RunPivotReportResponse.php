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
class RunPivotReportResponse extends \Google\Collection {
	protected $collection_key = 'rows';
	protected $aggregatesType = Row::class;
	protected $aggregatesDataType = 'array';
	protected $dimensionHeadersType = DimensionHeader::class;
	protected $dimensionHeadersDataType = 'array';
	public $kind;
	protected $metadataType = ResponseMetaData::class;
	protected $metadataDataType = '';
	protected $metricHeadersType = MetricHeader::class;
	protected $metricHeadersDataType = 'array';
	protected $pivotHeadersType = PivotHeader::class;
	protected $pivotHeadersDataType = 'array';
	protected $propertyQuotaType = PropertyQuota::class;
	protected $propertyQuotaDataType = '';
	protected $rowsType = Row::class;
	protected $rowsDataType = 'array';

	/**
	 *
	 * @param
	 *        	Row[]
	 */
	public function setAggregates($aggregates) {
		$this->aggregates = $aggregates;
	}
	/**
	 *
	 * @return Row[]
	 */
	public function getAggregates() {
		return $this->aggregates;
	}
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
	 *        	PivotHeader[]
	 */
	public function setPivotHeaders($pivotHeaders) {
		$this->pivotHeaders = $pivotHeaders;
	}
	/**
	 *
	 * @return PivotHeader[]
	 */
	public function getPivotHeaders() {
		return $this->pivotHeaders;
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
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( RunPivotReportResponse::class, 'Google_Service_AnalyticsData_RunPivotReportResponse' );
