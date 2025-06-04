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
class MetricHeader extends \Google\Collection {
	protected $collection_key = 'pivotHeaders';
	protected $metricHeaderEntriesType = MetricHeaderEntry::class;
	protected $metricHeaderEntriesDataType = 'array';
	protected $pivotHeadersType = PivotHeader::class;
	protected $pivotHeadersDataType = 'array';

	/**
	 *
	 * @param
	 *        	MetricHeaderEntry[]
	 */
	public function setMetricHeaderEntries($metricHeaderEntries) {
		$this->metricHeaderEntries = $metricHeaderEntries;
	}
	/**
	 *
	 * @return MetricHeaderEntry[]
	 */
	public function getMetricHeaderEntries() {
		return $this->metricHeaderEntries;
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
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( MetricHeader::class, 'Google_Service_AnalyticsReporting_MetricHeader' );
