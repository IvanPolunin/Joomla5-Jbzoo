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
class OrFiltersForSegment extends \Google\Collection {
	protected $collection_key = 'segmentFilterClauses';
	protected $segmentFilterClausesType = SegmentFilterClause::class;
	protected $segmentFilterClausesDataType = 'array';

	/**
	 *
	 * @param
	 *        	SegmentFilterClause[]
	 */
	public function setSegmentFilterClauses($segmentFilterClauses) {
		$this->segmentFilterClauses = $segmentFilterClauses;
	}
	/**
	 *
	 * @return SegmentFilterClause[]
	 */
	public function getSegmentFilterClauses() {
		return $this->segmentFilterClauses;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( OrFiltersForSegment::class, 'Google_Service_AnalyticsReporting_OrFiltersForSegment' );
