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
class FilterExpression extends \Google\Model {
	protected $andGroupType = FilterExpressionList::class;
	protected $andGroupDataType = '';
	protected $filterType = Filter::class;
	protected $filterDataType = '';
	protected $notExpressionType = FilterExpression::class;
	protected $notExpressionDataType = '';
	protected $orGroupType = FilterExpressionList::class;
	protected $orGroupDataType = '';

	/**
	 *
	 * @param
	 *        	FilterExpressionList
	 */
	public function setAndGroup(FilterExpressionList $andGroup) {
		$this->andGroup = $andGroup;
	}
	/**
	 *
	 * @return FilterExpressionList
	 */
	public function getAndGroup() {
		return $this->andGroup;
	}
	/**
	 *
	 * @param
	 *        	Filter
	 */
	public function setFilter(Filter $filter) {
		$this->filter = $filter;
	}
	/**
	 *
	 * @return Filter
	 */
	public function getFilter() {
		return $this->filter;
	}
	/**
	 *
	 * @param
	 *        	FilterExpression
	 */
	public function setNotExpression(FilterExpression $notExpression) {
		$this->notExpression = $notExpression;
	}
	/**
	 *
	 * @return FilterExpression
	 */
	public function getNotExpression() {
		return $this->notExpression;
	}
	/**
	 *
	 * @param
	 *        	FilterExpressionList
	 */
	public function setOrGroup(FilterExpressionList $orGroup) {
		$this->orGroup = $orGroup;
	}
	/**
	 *
	 * @return FilterExpressionList
	 */
	public function getOrGroup() {
		return $this->orGroup;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( FilterExpression::class, 'Google_Service_AnalyticsData_FilterExpression' );
