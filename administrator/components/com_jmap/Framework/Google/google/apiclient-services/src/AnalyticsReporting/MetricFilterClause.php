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
class MetricFilterClause extends \Google\Collection {
	protected $collection_key = 'filters';
	protected $filtersType = MetricFilter::class;
	protected $filtersDataType = 'array';
	public $operator;

	/**
	 *
	 * @param
	 *        	MetricFilter[]
	 */
	public function setFilters($filters) {
		$this->filters = $filters;
	}
	/**
	 *
	 * @return MetricFilter[]
	 */
	public function getFilters() {
		return $this->filters;
	}
	public function setOperator($operator) {
		$this->operator = $operator;
	}
	public function getOperator() {
		return $this->operator;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( MetricFilterClause::class, 'Google_Service_AnalyticsReporting_MetricFilterClause' );
