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
class Row extends \Google\Collection {
	protected $collection_key = 'metricValues';
	protected $dimensionValuesType = DimensionValue::class;
	protected $dimensionValuesDataType = 'array';
	protected $metricValuesType = MetricValue::class;
	protected $metricValuesDataType = 'array';

	/**
	 *
	 * @param
	 *        	DimensionValue[]
	 */
	public function setDimensionValues($dimensionValues) {
		$this->dimensionValues = $dimensionValues;
	}
	/**
	 *
	 * @return DimensionValue[]
	 */
	public function getDimensionValues() {
		return $this->dimensionValues;
	}
	/**
	 *
	 * @param
	 *        	MetricValue[]
	 */
	public function setMetricValues($metricValues) {
		$this->metricValues = $metricValues;
	}
	/**
	 *
	 * @return MetricValue[]
	 */
	public function getMetricValues() {
		return $this->metricValues;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( Row::class, 'Google_Service_AnalyticsData_Row' );
