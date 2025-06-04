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
class DateRange extends \Google\Model {
	public $endDate;
	public $name;
	public $startDate;
	public function setEndDate($endDate) {
		$this->endDate = $endDate;
	}
	public function getEndDate() {
		return $this->endDate;
	}
	public function setName($name) {
		$this->name = $name;
	}
	public function getName() {
		return $this->name;
	}
	public function setStartDate($startDate) {
		$this->startDate = $startDate;
	}
	public function getStartDate() {
		return $this->startDate;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( DateRange::class, 'Google_Service_AnalyticsData_DateRange' );
