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
class QuotaStatus extends \Google\Model {
	public $consumed;
	public $remaining;
	public function setConsumed($consumed) {
		$this->consumed = $consumed;
	}
	public function getConsumed() {
		return $this->consumed;
	}
	public function setRemaining($remaining) {
		$this->remaining = $remaining;
	}
	public function getRemaining() {
		return $this->remaining;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( QuotaStatus::class, 'Google_Service_AnalyticsData_QuotaStatus' );
