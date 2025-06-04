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
class MetricOrderBy extends \Google\Model {
	public $metricName;
	public function setMetricName($metricName) {
		$this->metricName = $metricName;
	}
	public function getMetricName() {
		return $this->metricName;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( MetricOrderBy::class, 'Google_Service_AnalyticsData_MetricOrderBy' );
