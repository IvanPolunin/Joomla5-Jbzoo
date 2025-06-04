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
class Cohort extends \Google\Model {
	protected $dateRangeType = DateRange::class;
	protected $dateRangeDataType = '';
	public $dimension;
	public $name;

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
	public function setDimension($dimension) {
		$this->dimension = $dimension;
	}
	public function getDimension() {
		return $this->dimension;
	}
	public function setName($name) {
		$this->name = $name;
	}
	public function getName() {
		return $this->name;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( Cohort::class, 'Google_Service_AnalyticsData_Cohort' );
