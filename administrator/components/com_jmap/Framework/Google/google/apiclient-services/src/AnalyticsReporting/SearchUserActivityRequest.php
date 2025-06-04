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
class SearchUserActivityRequest extends \Google\Collection {
	protected $collection_key = 'activityTypes';
	public $activityTypes;
	protected $dateRangeType = DateRange::class;
	protected $dateRangeDataType = '';
	public $pageSize;
	public $pageToken;
	protected $userType = User::class;
	protected $userDataType = '';
	public $viewId;
	public function setActivityTypes($activityTypes) {
		$this->activityTypes = $activityTypes;
	}
	public function getActivityTypes() {
		return $this->activityTypes;
	}
	/**
	 *
	 * @param
	 *        	DateRange
	 */
	public function setDateRange(DateRange $dateRange) {
		$this->dateRange = $dateRange;
	}
	/**
	 *
	 * @return DateRange
	 */
	public function getDateRange() {
		return $this->dateRange;
	}
	public function setPageSize($pageSize) {
		$this->pageSize = $pageSize;
	}
	public function getPageSize() {
		return $this->pageSize;
	}
	public function setPageToken($pageToken) {
		$this->pageToken = $pageToken;
	}
	public function getPageToken() {
		return $this->pageToken;
	}
	/**
	 *
	 * @param
	 *        	User
	 */
	public function setUser(User $user) {
		$this->user = $user;
	}
	/**
	 *
	 * @return User
	 */
	public function getUser() {
		return $this->user;
	}
	public function setViewId($viewId) {
		$this->viewId = $viewId;
	}
	public function getViewId() {
		return $this->viewId;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( SearchUserActivityRequest::class, 'Google_Service_AnalyticsReporting_SearchUserActivityRequest' );
