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
class SegmentSequenceStep extends \Google\Collection {
	protected $collection_key = 'orFiltersForSegment';
	public $matchType;
	protected $orFiltersForSegmentType = OrFiltersForSegment::class;
	protected $orFiltersForSegmentDataType = 'array';
	public function setMatchType($matchType) {
		$this->matchType = $matchType;
	}
	public function getMatchType() {
		return $this->matchType;
	}
	/**
	 *
	 * @param
	 *        	OrFiltersForSegment[]
	 */
	public function setOrFiltersForSegment($orFiltersForSegment) {
		$this->orFiltersForSegment = $orFiltersForSegment;
	}
	/**
	 *
	 * @return OrFiltersForSegment[]
	 */
	public function getOrFiltersForSegment() {
		return $this->orFiltersForSegment;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( SegmentSequenceStep::class, 'Google_Service_AnalyticsReporting_SegmentSequenceStep' );
