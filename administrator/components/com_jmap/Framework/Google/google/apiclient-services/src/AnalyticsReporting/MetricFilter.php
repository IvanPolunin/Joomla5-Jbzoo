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
class MetricFilter extends \Google\Model {
	public $comparisonValue;
	public $metricName;
	public $not;
	public $operator;
	public function setComparisonValue($comparisonValue) {
		$this->comparisonValue = $comparisonValue;
	}
	public function getComparisonValue() {
		return $this->comparisonValue;
	}
	public function setMetricName($metricName) {
		$this->metricName = $metricName;
	}
	public function getMetricName() {
		return $this->metricName;
	}
	public function setNot($not) {
		$this->not = $not;
	}
	public function getNot() {
		return $this->not;
	}
	public function setOperator($operator) {
		$this->operator = $operator;
	}
	public function getOperator() {
		return $this->operator;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( MetricFilter::class, 'Google_Service_AnalyticsReporting_MetricFilter' );
