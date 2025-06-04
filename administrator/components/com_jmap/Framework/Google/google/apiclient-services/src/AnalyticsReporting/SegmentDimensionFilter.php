<?php

namespace Google\Service\AnalyticsReporting;

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
class SegmentDimensionFilter extends \Google\Collection {
	protected $collection_key = 'expressions';
	public $caseSensitive;
	public $dimensionName;
	public $expressions;
	public $maxComparisonValue;
	public $minComparisonValue;
	public $operator;
	public function setCaseSensitive($caseSensitive) {
		$this->caseSensitive = $caseSensitive;
	}
	public function getCaseSensitive() {
		return $this->caseSensitive;
	}
	public function setDimensionName($dimensionName) {
		$this->dimensionName = $dimensionName;
	}
	public function getDimensionName() {
		return $this->dimensionName;
	}
	public function setExpressions($expressions) {
		$this->expressions = $expressions;
	}
	public function getExpressions() {
		return $this->expressions;
	}
	public function setMaxComparisonValue($maxComparisonValue) {
		$this->maxComparisonValue = $maxComparisonValue;
	}
	public function getMaxComparisonValue() {
		return $this->maxComparisonValue;
	}
	public function setMinComparisonValue($minComparisonValue) {
		$this->minComparisonValue = $minComparisonValue;
	}
	public function getMinComparisonValue() {
		return $this->minComparisonValue;
	}
	public function setOperator($operator) {
		$this->operator = $operator;
	}
	public function getOperator() {
		return $this->operator;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( SegmentDimensionFilter::class, 'Google_Service_AnalyticsReporting_SegmentDimensionFilter' );
