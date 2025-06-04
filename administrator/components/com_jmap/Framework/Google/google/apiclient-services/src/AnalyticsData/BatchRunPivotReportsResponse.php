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
class BatchRunPivotReportsResponse extends \Google\Collection {
	protected $collection_key = 'pivotReports';
	public $kind;
	protected $pivotReportsType = RunPivotReportResponse::class;
	protected $pivotReportsDataType = 'array';
	public function setKind($kind) {
		$this->kind = $kind;
	}
	public function getKind() {
		return $this->kind;
	}
	/**
	 *
	 * @param
	 *        	RunPivotReportResponse[]
	 */
	public function setPivotReports($pivotReports) {
		$this->pivotReports = $pivotReports;
	}
	/**
	 *
	 * @return RunPivotReportResponse[]
	 */
	public function getPivotReports() {
		return $this->pivotReports;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( BatchRunPivotReportsResponse::class, 'Google_Service_AnalyticsData_BatchRunPivotReportsResponse' );
