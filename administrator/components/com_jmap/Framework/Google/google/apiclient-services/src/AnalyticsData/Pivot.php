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
class Pivot extends \Google\Collection {
	protected $collection_key = 'orderBys';
	public $fieldNames;
	public $limit;
	public $metricAggregations;
	public $offset;
	protected $orderBysType = OrderBy::class;
	protected $orderBysDataType = 'array';
	public function setFieldNames($fieldNames) {
		$this->fieldNames = $fieldNames;
	}
	public function getFieldNames() {
		return $this->fieldNames;
	}
	public function setLimit($limit) {
		$this->limit = $limit;
	}
	public function getLimit() {
		return $this->limit;
	}
	public function setMetricAggregations($metricAggregations) {
		$this->metricAggregations = $metricAggregations;
	}
	public function getMetricAggregations() {
		return $this->metricAggregations;
	}
	public function setOffset($offset) {
		$this->offset = $offset;
	}
	public function getOffset() {
		return $this->offset;
	}
	/**
	 *
	 * @param
	 *        	OrderBy[]
	 */
	public function setOrderBys($orderBys) {
		$this->orderBys = $orderBys;
	}
	/**
	 *
	 * @return OrderBy[]
	 */
	public function getOrderBys() {
		return $this->orderBys;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( Pivot::class, 'Google_Service_AnalyticsData_Pivot' );
