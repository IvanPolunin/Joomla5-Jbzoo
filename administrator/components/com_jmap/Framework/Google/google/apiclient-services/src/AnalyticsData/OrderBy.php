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
class OrderBy extends \Google\Model {
	public $desc;
	protected $dimensionType = DimensionOrderBy::class;
	protected $dimensionDataType = '';
	protected $metricType = MetricOrderBy::class;
	protected $metricDataType = '';
	protected $pivotType = PivotOrderBy::class;
	protected $pivotDataType = '';
	public function setDesc($desc) {
		$this->desc = $desc;
	}
	public function getDesc() {
		return $this->desc;
	}
	/**
	 *
	 * @param
	 *        	DimensionOrderBy
	 */
	public function setDimension(DimensionOrderBy $dimension) {
		$this->dimension = $dimension;
	}
	/**
	 *
	 * @return DimensionOrderBy
	 */
	public function getDimension() {
		return $this->dimension;
	}
	/**
	 *
	 * @param
	 *        	MetricOrderBy
	 */
	public function setMetric(MetricOrderBy $metric) {
		$this->metric = $metric;
	}
	/**
	 *
	 * @return MetricOrderBy
	 */
	public function getMetric() {
		return $this->metric;
	}
	/**
	 *
	 * @param
	 *        	PivotOrderBy
	 */
	public function setPivot(PivotOrderBy $pivot) {
		$this->pivot = $pivot;
	}
	/**
	 *
	 * @return PivotOrderBy
	 */
	public function getPivot() {
		return $this->pivot;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( OrderBy::class, 'Google_Service_AnalyticsData_OrderBy' );
