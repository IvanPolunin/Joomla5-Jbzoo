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
class BatchRunReportsRequest extends \Google\Collection {
	protected $collection_key = 'requests';
	protected $requestsType = RunReportRequest::class;
	protected $requestsDataType = 'array';

	/**
	 *
	 * @param
	 *        	RunReportRequest[]
	 */
	public function setRequests($requests) {
		$this->requests = $requests;
	}
	/**
	 *
	 * @return RunReportRequest[]
	 */
	public function getRequests() {
		return $this->requests;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( BatchRunReportsRequest::class, 'Google_Service_AnalyticsData_BatchRunReportsRequest' );
