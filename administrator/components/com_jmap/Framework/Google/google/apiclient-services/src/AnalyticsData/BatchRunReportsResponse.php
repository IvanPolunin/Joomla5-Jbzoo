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
class BatchRunReportsResponse extends \Google\Collection {
	protected $collection_key = 'reports';
	public $kind;
	protected $reportsType = RunReportResponse::class;
	protected $reportsDataType = 'array';
	public function setKind($kind) {
		$this->kind = $kind;
	}
	public function getKind() {
		return $this->kind;
	}
	/**
	 *
	 * @param
	 *        	RunReportResponse[]
	 */
	public function setReports($reports) {
		$this->reports = $reports;
	}
	/**
	 *
	 * @return RunReportResponse[]
	 */
	public function getReports() {
		return $this->reports;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( BatchRunReportsResponse::class, 'Google_Service_AnalyticsData_BatchRunReportsResponse' );
