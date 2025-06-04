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
class SequenceSegment extends \Google\Collection {
	protected $collection_key = 'segmentSequenceSteps';
	public $firstStepShouldMatchFirstHit;
	protected $segmentSequenceStepsType = SegmentSequenceStep::class;
	protected $segmentSequenceStepsDataType = 'array';
	public function setFirstStepShouldMatchFirstHit($firstStepShouldMatchFirstHit) {
		$this->firstStepShouldMatchFirstHit = $firstStepShouldMatchFirstHit;
	}
	public function getFirstStepShouldMatchFirstHit() {
		return $this->firstStepShouldMatchFirstHit;
	}
	/**
	 *
	 * @param
	 *        	SegmentSequenceStep[]
	 */
	public function setSegmentSequenceSteps($segmentSequenceSteps) {
		$this->segmentSequenceSteps = $segmentSequenceSteps;
	}
	/**
	 *
	 * @return SegmentSequenceStep[]
	 */
	public function getSegmentSequenceSteps() {
		return $this->segmentSequenceSteps;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( SequenceSegment::class, 'Google_Service_AnalyticsReporting_SequenceSegment' );
