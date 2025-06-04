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
class SegmentDefinition extends \Google\Collection {
	protected $collection_key = 'segmentFilters';
	protected $segmentFiltersType = SegmentFilter::class;
	protected $segmentFiltersDataType = 'array';

	/**
	 *
	 * @param
	 *        	SegmentFilter[]
	 */
	public function setSegmentFilters($segmentFilters) {
		$this->segmentFilters = $segmentFilters;
	}
	/**
	 *
	 * @return SegmentFilter[]
	 */
	public function getSegmentFilters() {
		return $this->segmentFilters;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( SegmentDefinition::class, 'Google_Service_AnalyticsReporting_SegmentDefinition' );
