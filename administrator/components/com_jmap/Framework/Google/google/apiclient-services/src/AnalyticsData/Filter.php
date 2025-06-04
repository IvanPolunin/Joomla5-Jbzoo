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
class Filter extends \Google\Model {
	protected $betweenFilterType = BetweenFilter::class;
	protected $betweenFilterDataType = '';
	public $fieldName;
	protected $inListFilterType = InListFilter::class;
	protected $inListFilterDataType = '';
	protected $numericFilterType = NumericFilter::class;
	protected $numericFilterDataType = '';
	protected $stringFilterType = StringFilter::class;
	protected $stringFilterDataType = '';

	/**
	 *
	 * @param
	 *        	BetweenFilter
	 */
	public function setBetweenFilter(BetweenFilter $betweenFilter) {
		$this->betweenFilter = $betweenFilter;
	}
	/**
	 *
	 * @return BetweenFilter
	 */
	public function getBetweenFilter() {
		return $this->betweenFilter;
	}
	public function setFieldName($fieldName) {
		$this->fieldName = $fieldName;
	}
	public function getFieldName() {
		return $this->fieldName;
	}
	/**
	 *
	 * @param
	 *        	InListFilter
	 */
	public function setInListFilter(InListFilter $inListFilter) {
		$this->inListFilter = $inListFilter;
	}
	/**
	 *
	 * @return InListFilter
	 */
	public function getInListFilter() {
		return $this->inListFilter;
	}
	/**
	 *
	 * @param
	 *        	NumericFilter
	 */
	public function setNumericFilter(NumericFilter $numericFilter) {
		$this->numericFilter = $numericFilter;
	}
	/**
	 *
	 * @return NumericFilter
	 */
	public function getNumericFilter() {
		return $this->numericFilter;
	}
	/**
	 *
	 * @param
	 *        	StringFilter
	 */
	public function setStringFilter(StringFilter $stringFilter) {
		$this->stringFilter = $stringFilter;
	}
	/**
	 *
	 * @return StringFilter
	 */
	public function getStringFilter() {
		return $this->stringFilter;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( Filter::class, 'Google_Service_AnalyticsData_Filter' );
