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
class DateRangeValues extends \Google\Collection {
	protected $collection_key = 'values';
	protected $pivotValueRegionsType = PivotValueRegion::class;
	protected $pivotValueRegionsDataType = 'array';
	public $values;

	/**
	 *
	 * @param
	 *        	PivotValueRegion[]
	 */
	public function setPivotValueRegions($pivotValueRegions) {
		$this->pivotValueRegions = $pivotValueRegions;
	}
	/**
	 *
	 * @return PivotValueRegion[]
	 */
	public function getPivotValueRegions() {
		return $this->pivotValueRegions;
	}
	public function setValues($values) {
		$this->values = $values;
	}
	public function getValues() {
		return $this->values;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( DateRangeValues::class, 'Google_Service_AnalyticsReporting_DateRangeValues' );
