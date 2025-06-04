<?php

namespace Google\Service\Analytics;

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
class GaDataProfileInfo extends \Google\Model {
	public $accountId;
	public $internalWebPropertyId;
	public $profileId;
	public $profileName;
	public $tableId;
	public $webPropertyId;
	public function setAccountId($accountId) {
		$this->accountId = $accountId;
	}
	public function getAccountId() {
		return $this->accountId;
	}
	public function setInternalWebPropertyId($internalWebPropertyId) {
		$this->internalWebPropertyId = $internalWebPropertyId;
	}
	public function getInternalWebPropertyId() {
		return $this->internalWebPropertyId;
	}
	public function setProfileId($profileId) {
		$this->profileId = $profileId;
	}
	public function getProfileId() {
		return $this->profileId;
	}
	public function setProfileName($profileName) {
		$this->profileName = $profileName;
	}
	public function getProfileName() {
		return $this->profileName;
	}
	public function setTableId($tableId) {
		$this->tableId = $tableId;
	}
	public function getTableId() {
		return $this->tableId;
	}
	public function setWebPropertyId($webPropertyId) {
		$this->webPropertyId = $webPropertyId;
	}
	public function getWebPropertyId() {
		return $this->webPropertyId;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( GaDataProfileInfo::class, 'Google_Service_Analytics_GaDataProfileInfo' );
