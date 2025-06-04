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
class NumericFilter extends \Google\Model {
	public $operation;
	protected $valueType = NumericValue::class;
	protected $valueDataType = '';
	public function setOperation($operation) {
		$this->operation = $operation;
	}
	public function getOperation() {
		return $this->operation;
	}
	/**
	 *
	 * @param
	 *        	NumericValue
	 */
	public function setValue(NumericValue $value) {
		$this->value = $value;
	}
	/**
	 *
	 * @return NumericValue
	 */
	public function getValue() {
		return $this->value;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( NumericFilter::class, 'Google_Service_AnalyticsData_NumericFilter' );
