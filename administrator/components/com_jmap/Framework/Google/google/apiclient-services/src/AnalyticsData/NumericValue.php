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
class NumericValue extends \Google\Model {
	public $doubleValue;
	public $int64Value;
	public function setDoubleValue($doubleValue) {
		$this->doubleValue = $doubleValue;
	}
	public function getDoubleValue() {
		return $this->doubleValue;
	}
	public function setInt64Value($int64Value) {
		$this->int64Value = $int64Value;
	}
	public function getInt64Value() {
		return $this->int64Value;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( NumericValue::class, 'Google_Service_AnalyticsData_NumericValue' );
