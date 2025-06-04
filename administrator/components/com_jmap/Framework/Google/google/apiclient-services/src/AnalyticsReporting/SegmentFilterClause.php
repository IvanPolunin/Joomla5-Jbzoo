<?php

namespace Google\Service\AnalyticsReporting;

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
class SegmentFilterClause extends \Google\Model {
	protected $dimensionFilterType = SegmentDimensionFilter::class;
	protected $dimensionFilterDataType = '';
	protected $metricFilterType = SegmentMetricFilter::class;
	protected $metricFilterDataType = '';
	public $not;

	/**
	 *
	 * @param
	 *        	SegmentDimensionFilter
	 */
	public function setDimensionFilter(SegmentDimensionFilter $dimensionFilter) {
		$this->dimensionFilter = $dimensionFilter;
	}
	/**
	 *
	 * @return SegmentDimensionFilter
	 */
	public function getDimensionFilter() {
		return $this->dimensionFilter;
	}
	/**
	 *
	 * @param
	 *        	SegmentMetricFilter
	 */
	public function setMetricFilter(SegmentMetricFilter $metricFilter) {
		$this->metricFilter = $metricFilter;
	}
	/**
	 *
	 * @return SegmentMetricFilter
	 */
	public function getMetricFilter() {
		return $this->metricFilter;
	}
	public function setNot($not) {
		$this->not = $not;
	}
	public function getNot() {
		return $this->not;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( SegmentFilterClause::class, 'Google_Service_AnalyticsReporting_SegmentFilterClause' );
