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
class GetReportsResponse extends \Google\Collection {
	protected $collection_key = 'reports';
	public $queryCost;
	protected $reportsType = Report::class;
	protected $reportsDataType = 'array';
	protected $resourceQuotasRemainingType = ResourceQuotasRemaining::class;
	protected $resourceQuotasRemainingDataType = '';
	public function setQueryCost($queryCost) {
		$this->queryCost = $queryCost;
	}
	public function getQueryCost() {
		return $this->queryCost;
	}
	/**
	 *
	 * @param
	 *        	Report[]
	 */
	public function setReports($reports) {
		$this->reports = $reports;
	}
	/**
	 *
	 * @return Report[]
	 */
	public function getReports() {
		return $this->reports;
	}
	/**
	 *
	 * @param
	 *        	ResourceQuotasRemaining
	 */
	public function setResourceQuotasRemaining(ResourceQuotasRemaining $resourceQuotasRemaining) {
		$this->resourceQuotasRemaining = $resourceQuotasRemaining;
	}
	/**
	 *
	 * @return ResourceQuotasRemaining
	 */
	public function getResourceQuotasRemaining() {
		return $this->resourceQuotasRemaining;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( GetReportsResponse::class, 'Google_Service_AnalyticsReporting_GetReportsResponse' );
