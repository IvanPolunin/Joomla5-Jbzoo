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
class ReportRow extends \Google\Collection {
	protected $collection_key = 'metrics';
	public $dimensions;
	protected $metricsType = DateRangeValues::class;
	protected $metricsDataType = 'array';
	public function setDimensions($dimensions) {
		$this->dimensions = $dimensions;
	}
	public function getDimensions() {
		return $this->dimensions;
	}
	/**
	 *
	 * @param
	 *        	DateRangeValues[]
	 */
	public function setMetrics($metrics) {
		$this->metrics = $metrics;
	}
	/**
	 *
	 * @return DateRangeValues[]
	 */
	public function getMetrics() {
		return $this->metrics;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( ReportRow::class, 'Google_Service_AnalyticsReporting_ReportRow' );
