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
class PropertyQuota extends \Google\Model {
	protected $concurrentRequestsType = QuotaStatus::class;
	protected $concurrentRequestsDataType = '';
	protected $potentiallyThresholdedRequestsPerHourType = QuotaStatus::class;
	protected $potentiallyThresholdedRequestsPerHourDataType = '';
	protected $serverErrorsPerProjectPerHourType = QuotaStatus::class;
	protected $serverErrorsPerProjectPerHourDataType = '';
	protected $tokensPerDayType = QuotaStatus::class;
	protected $tokensPerDayDataType = '';
	protected $tokensPerHourType = QuotaStatus::class;
	protected $tokensPerHourDataType = '';

	/**
	 *
	 * @param
	 *        	QuotaStatus
	 */
	public function setConcurrentRequests(QuotaStatus $concurrentRequests) {
		$this->concurrentRequests = $concurrentRequests;
	}
	/**
	 *
	 * @return QuotaStatus
	 */
	public function getConcurrentRequests() {
		return $this->concurrentRequests;
	}
	/**
	 *
	 * @param
	 *        	QuotaStatus
	 */
	public function setPotentiallyThresholdedRequestsPerHour(QuotaStatus $potentiallyThresholdedRequestsPerHour) {
		$this->potentiallyThresholdedRequestsPerHour = $potentiallyThresholdedRequestsPerHour;
	}
	/**
	 *
	 * @return QuotaStatus
	 */
	public function getPotentiallyThresholdedRequestsPerHour() {
		return $this->potentiallyThresholdedRequestsPerHour;
	}
	/**
	 *
	 * @param
	 *        	QuotaStatus
	 */
	public function setServerErrorsPerProjectPerHour(QuotaStatus $serverErrorsPerProjectPerHour) {
		$this->serverErrorsPerProjectPerHour = $serverErrorsPerProjectPerHour;
	}
	/**
	 *
	 * @return QuotaStatus
	 */
	public function getServerErrorsPerProjectPerHour() {
		return $this->serverErrorsPerProjectPerHour;
	}
	/**
	 *
	 * @param
	 *        	QuotaStatus
	 */
	public function setTokensPerDay(QuotaStatus $tokensPerDay) {
		$this->tokensPerDay = $tokensPerDay;
	}
	/**
	 *
	 * @return QuotaStatus
	 */
	public function getTokensPerDay() {
		return $this->tokensPerDay;
	}
	/**
	 *
	 * @param
	 *        	QuotaStatus
	 */
	public function setTokensPerHour(QuotaStatus $tokensPerHour) {
		$this->tokensPerHour = $tokensPerHour;
	}
	/**
	 *
	 * @return QuotaStatus
	 */
	public function getTokensPerHour() {
		return $this->tokensPerHour;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( PropertyQuota::class, 'Google_Service_AnalyticsData_PropertyQuota' );
