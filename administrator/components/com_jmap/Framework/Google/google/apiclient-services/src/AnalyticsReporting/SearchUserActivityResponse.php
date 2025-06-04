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
class SearchUserActivityResponse extends \Google\Collection {
	protected $collection_key = 'sessions';
	public $nextPageToken;
	public $sampleRate;
	protected $sessionsType = UserActivitySession::class;
	protected $sessionsDataType = 'array';
	public $totalRows;
	public function setNextPageToken($nextPageToken) {
		$this->nextPageToken = $nextPageToken;
	}
	public function getNextPageToken() {
		return $this->nextPageToken;
	}
	public function setSampleRate($sampleRate) {
		$this->sampleRate = $sampleRate;
	}
	public function getSampleRate() {
		return $this->sampleRate;
	}
	/**
	 *
	 * @param
	 *        	UserActivitySession[]
	 */
	public function setSessions($sessions) {
		$this->sessions = $sessions;
	}
	/**
	 *
	 * @return UserActivitySession[]
	 */
	public function getSessions() {
		return $this->sessions;
	}
	public function setTotalRows($totalRows) {
		$this->totalRows = $totalRows;
	}
	public function getTotalRows() {
		return $this->totalRows;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( SearchUserActivityResponse::class, 'Google_Service_AnalyticsReporting_SearchUserActivityResponse' );
