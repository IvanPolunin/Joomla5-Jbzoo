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
class ColumnHeader extends \Google\Collection {
	protected $collection_key = 'dimensions';
	public $dimensions;
	protected $metricHeaderType = MetricHeader::class;
	protected $metricHeaderDataType = '';
	public function setDimensions($dimensions) {
		$this->dimensions = $dimensions;
	}
	public function getDimensions() {
		return $this->dimensions;
	}
	/**
	 *
	 * @param
	 *        	MetricHeader
	 */
	public function setMetricHeader(MetricHeader $metricHeader) {
		$this->metricHeader = $metricHeader;
	}
	/**
	 *
	 * @return MetricHeader
	 */
	public function getMetricHeader() {
		return $this->metricHeader;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( ColumnHeader::class, 'Google_Service_AnalyticsReporting_ColumnHeader' );
