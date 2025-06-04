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
class PivotDimensionHeader extends \Google\Collection {
	protected $collection_key = 'dimensionValues';
	protected $dimensionValuesType = DimensionValue::class;
	protected $dimensionValuesDataType = 'array';

	/**
	 *
	 * @param
	 *        	DimensionValue[]
	 */
	public function setDimensionValues($dimensionValues) {
		$this->dimensionValues = $dimensionValues;
	}
	/**
	 *
	 * @return DimensionValue[]
	 */
	public function getDimensionValues() {
		return $this->dimensionValues;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( PivotDimensionHeader::class, 'Google_Service_AnalyticsData_PivotDimensionHeader' );
