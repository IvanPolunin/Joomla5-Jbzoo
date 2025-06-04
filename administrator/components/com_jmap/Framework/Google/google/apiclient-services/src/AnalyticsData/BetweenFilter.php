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
class BetweenFilter extends \Google\Model {
	protected $fromValueType = NumericValue::class;
	protected $fromValueDataType = '';
	protected $toValueType = NumericValue::class;
	protected $toValueDataType = '';

	/**
	 *
	 * @param
	 *        	NumericValue
	 */
	public function setFromValue(NumericValue $fromValue) {
		$this->fromValue = $fromValue;
	}
	/**
	 *
	 * @return NumericValue
	 */
	public function getFromValue() {
		return $this->fromValue;
	}
	/**
	 *
	 * @param
	 *        	NumericValue
	 */
	public function setToValue(NumericValue $toValue) {
		$this->toValue = $toValue;
	}
	/**
	 *
	 * @return NumericValue
	 */
	public function getToValue() {
		return $this->toValue;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( BetweenFilter::class, 'Google_Service_AnalyticsData_BetweenFilter' );
