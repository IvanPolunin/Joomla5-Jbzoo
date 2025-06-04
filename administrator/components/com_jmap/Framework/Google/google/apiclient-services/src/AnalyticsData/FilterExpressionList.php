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
class FilterExpressionList extends \Google\Collection {
	protected $collection_key = 'expressions';
	protected $expressionsType = FilterExpression::class;
	protected $expressionsDataType = 'array';

	/**
	 *
	 * @param
	 *        	FilterExpression[]
	 */
	public function setExpressions($expressions) {
		$this->expressions = $expressions;
	}
	/**
	 *
	 * @return FilterExpression[]
	 */
	public function getExpressions() {
		return $this->expressions;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( FilterExpressionList::class, 'Google_Service_AnalyticsData_FilterExpressionList' );
