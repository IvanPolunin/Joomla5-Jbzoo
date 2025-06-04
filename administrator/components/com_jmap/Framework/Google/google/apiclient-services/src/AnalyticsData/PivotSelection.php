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
class PivotSelection extends \Google\Model {
	public $dimensionName;
	public $dimensionValue;
	public function setDimensionName($dimensionName) {
		$this->dimensionName = $dimensionName;
	}
	public function getDimensionName() {
		return $this->dimensionName;
	}
	public function setDimensionValue($dimensionValue) {
		$this->dimensionValue = $dimensionValue;
	}
	public function getDimensionValue() {
		return $this->dimensionValue;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( PivotSelection::class, 'Google_Service_AnalyticsData_PivotSelection' );
