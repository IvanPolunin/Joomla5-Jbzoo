<?php

namespace Google\Service\Indexing;

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
class UrlNotificationMetadata extends \Google\Model {
	protected $latestRemoveType = UrlNotification::class;
	protected $latestRemoveDataType = '';
	protected $latestUpdateType = UrlNotification::class;
	protected $latestUpdateDataType = '';
	protected $latestUpdate;
	public $url;

	/**
	 *
	 * @param
	 *        	UrlNotification
	 */
	public function setLatestRemove(UrlNotification $latestRemove) {
		$this->latestRemove = $latestRemove;
	}
	/**
	 *
	 * @return UrlNotification
	 */
	public function getLatestRemove() {
		return $this->latestRemove;
	}
	/**
	 *
	 * @param
	 *        	UrlNotification
	 */
	public function setLatestUpdate(UrlNotification $latestUpdate) {
		$this->latestUpdate = $latestUpdate;
	}
	/**
	 *
	 * @return UrlNotification
	 */
	public function getLatestUpdate() {
		return $this->latestUpdate;
	}
	public function setUrl($url) {
		$this->url = $url;
	}
	public function getUrl() {
		return $this->url;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( UrlNotificationMetadata::class, 'Google_Service_Indexing_UrlNotificationMetadata' );
