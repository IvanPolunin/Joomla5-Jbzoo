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
class RunPivotReportRequest extends \Google\Collection {
	protected $collection_key = 'pivots';
	protected $cohortSpecType = CohortSpec::class;
	protected $cohortSpecDataType = '';
	public $currencyCode;
	protected $dateRangesType = DateRange::class;
	protected $dateRangesDataType = 'array';
	protected $dimensionFilterType = FilterExpression::class;
	protected $dimensionFilterDataType = '';
	protected $dimensionsType = Dimension::class;
	protected $dimensionsDataType = 'array';
	public $keepEmptyRows;
	protected $metricFilterType = FilterExpression::class;
	protected $metricFilterDataType = '';
	protected $metricsType = Metric::class;
	protected $metricsDataType = 'array';
	protected $pivotsType = Pivot::class;
	protected $pivotsDataType = 'array';
	public $property;
	public $returnPropertyQuota;

	/**
	 *
	 * @param
	 *        	CohortSpec
	 */
	public function setCohortSpec(CohortSpec $cohortSpec) {
		$this->cohortSpec = $cohortSpec;
	}
	/**
	 *
	 * @return CohortSpec
	 */
	public function getCohortSpec() {
		return $this->cohortSpec;
	}
	public function setCurrencyCode($currencyCode) {
		$this->currencyCode = $currencyCode;
	}
	public function getCurrencyCode() {
		return $this->currencyCode;
	}
	/**
	 *
	 * @param
	 *        	DateRange[]
	 */
	public function setDateRanges($dateRanges) {
		$this->dateRanges = $dateRanges;
	}
	/**
	 *
	 * @return DateRange[]
	 */
	public function getDateRanges() {
		return $this->dateRanges;
	}
	/**
	 *
	 * @param
	 *        	FilterExpression
	 */
	public function setDimensionFilter(FilterExpression $dimensionFilter) {
		$this->dimensionFilter = $dimensionFilter;
	}
	/**
	 *
	 * @return FilterExpression
	 */
	public function getDimensionFilter() {
		return $this->dimensionFilter;
	}
	/**
	 *
	 * @param
	 *        	Dimension[]
	 */
	public function setDimensions($dimensions) {
		$this->dimensions = $dimensions;
	}
	/**
	 *
	 * @return Dimension[]
	 */
	public function getDimensions() {
		return $this->dimensions;
	}
	public function setKeepEmptyRows($keepEmptyRows) {
		$this->keepEmptyRows = $keepEmptyRows;
	}
	public function getKeepEmptyRows() {
		return $this->keepEmptyRows;
	}
	/**
	 *
	 * @param
	 *        	FilterExpression
	 */
	public function setMetricFilter(FilterExpression $metricFilter) {
		$this->metricFilter = $metricFilter;
	}
	/**
	 *
	 * @return FilterExpression
	 */
	public function getMetricFilter() {
		return $this->metricFilter;
	}
	/**
	 *
	 * @param
	 *        	Metric[]
	 */
	public function setMetrics($metrics) {
		$this->metrics = $metrics;
	}
	/**
	 *
	 * @return Metric[]
	 */
	public function getMetrics() {
		return $this->metrics;
	}
	/**
	 *
	 * @param
	 *        	Pivot[]
	 */
	public function setPivots($pivots) {
		$this->pivots = $pivots;
	}
	/**
	 *
	 * @return Pivot[]
	 */
	public function getPivots() {
		return $this->pivots;
	}
	public function setProperty($property) {
		$this->property = $property;
	}
	public function getProperty() {
		return $this->property;
	}
	public function setReturnPropertyQuota($returnPropertyQuota) {
		$this->returnPropertyQuota = $returnPropertyQuota;
	}
	public function getReturnPropertyQuota() {
		return $this->returnPropertyQuota;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( RunPivotReportRequest::class, 'Google_Service_AnalyticsData_RunPivotReportRequest' );
