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
class GetReportsRequest extends \Google\Collection {
	protected $collection_key = 'reportRequests';
	protected $reportRequestsType = ReportRequest::class;
	protected $reportRequestsDataType = 'array';
	public $useResourceQuotas;

	/**
	 *
	 * @param
	 *        	ReportRequest[]
	 */
	public function setReportRequests($reportRequests) {
		$this->reportRequests = $reportRequests;
	}
	/**
	 *
	 * @return ReportRequest[]
	 */
	public function getReportRequests() {
		return $this->reportRequests;
	}
	public function setUseResourceQuotas($useResourceQuotas) {
		$this->useResourceQuotas = $useResourceQuotas;
	}
	public function getUseResourceQuotas() {
		return $this->useResourceQuotas;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( GetReportsRequest::class, 'Google_Service_AnalyticsReporting_GetReportsRequest' );
