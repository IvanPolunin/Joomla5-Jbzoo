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
class UserActivitySession extends \Google\Collection {
	protected $collection_key = 'activities';
	protected $activitiesType = Activity::class;
	protected $activitiesDataType = 'array';
	public $dataSource;
	public $deviceCategory;
	public $platform;
	public $sessionDate;
	public $sessionId;

	/**
	 *
	 * @param
	 *        	Activity[]
	 */
	public function setActivities($activities) {
		$this->activities = $activities;
	}
	/**
	 *
	 * @return Activity[]
	 */
	public function getActivities() {
		return $this->activities;
	}
	public function setDataSource($dataSource) {
		$this->dataSource = $dataSource;
	}
	public function getDataSource() {
		return $this->dataSource;
	}
	public function setDeviceCategory($deviceCategory) {
		$this->deviceCategory = $deviceCategory;
	}
	public function getDeviceCategory() {
		return $this->deviceCategory;
	}
	public function setPlatform($platform) {
		$this->platform = $platform;
	}
	public function getPlatform() {
		return $this->platform;
	}
	public function setSessionDate($sessionDate) {
		$this->sessionDate = $sessionDate;
	}
	public function getSessionDate() {
		return $this->sessionDate;
	}
	public function setSessionId($sessionId) {
		$this->sessionId = $sessionId;
	}
	public function getSessionId() {
		return $this->sessionId;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( UserActivitySession::class, 'Google_Service_AnalyticsReporting_UserActivitySession' );
