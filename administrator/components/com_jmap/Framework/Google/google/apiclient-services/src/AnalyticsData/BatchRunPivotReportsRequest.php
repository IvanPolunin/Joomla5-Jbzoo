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
class BatchRunPivotReportsRequest extends \Google\Collection {
	protected $collection_key = 'requests';
	protected $requestsType = RunPivotReportRequest::class;
	protected $requestsDataType = 'array';

	/**
	 *
	 * @param
	 *        	RunPivotReportRequest[]
	 */
	public function setRequests($requests) {
		$this->requests = $requests;
	}
	/**
	 *
	 * @return RunPivotReportRequest[]
	 */
	public function getRequests() {
		return $this->requests;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( BatchRunPivotReportsRequest::class, 'Google_Service_AnalyticsData_BatchRunPivotReportsRequest' );
