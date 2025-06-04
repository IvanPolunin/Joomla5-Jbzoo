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
class IncludeConditions extends \Google\Model {
	public $daysToLookBack;
	public $isSmartList;
	public $kind;
	public $membershipDurationDays;
	public $segment;
	public function setDaysToLookBack($daysToLookBack) {
		$this->daysToLookBack = $daysToLookBack;
	}
	public function getDaysToLookBack() {
		return $this->daysToLookBack;
	}
	public function setIsSmartList($isSmartList) {
		$this->isSmartList = $isSmartList;
	}
	public function getIsSmartList() {
		return $this->isSmartList;
	}
	public function setKind($kind) {
		$this->kind = $kind;
	}
	public function getKind() {
		return $this->kind;
	}
	public function setMembershipDurationDays($membershipDurationDays) {
		$this->membershipDurationDays = $membershipDurationDays;
	}
	public function getMembershipDurationDays() {
		return $this->membershipDurationDays;
	}
	public function setSegment($segment) {
		$this->segment = $segment;
	}
	public function getSegment() {
		return $this->segment;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( IncludeConditions::class, 'Google_Service_Analytics_IncludeConditions' );
