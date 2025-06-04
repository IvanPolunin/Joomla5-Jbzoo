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
class SegmentFilter extends \Google\Model {
	public $not;
	protected $sequenceSegmentType = SequenceSegment::class;
	protected $sequenceSegmentDataType = '';
	protected $simpleSegmentType = SimpleSegment::class;
	protected $simpleSegmentDataType = '';
	public function setNot($not) {
		$this->not = $not;
	}
	public function getNot() {
		return $this->not;
	}
	/**
	 *
	 * @param
	 *        	SequenceSegment
	 */
	public function setSequenceSegment(SequenceSegment $sequenceSegment) {
		$this->sequenceSegment = $sequenceSegment;
	}
	/**
	 *
	 * @return SequenceSegment
	 */
	public function getSequenceSegment() {
		return $this->sequenceSegment;
	}
	/**
	 *
	 * @param
	 *        	SimpleSegment
	 */
	public function setSimpleSegment(SimpleSegment $simpleSegment) {
		$this->simpleSegment = $simpleSegment;
	}
	/**
	 *
	 * @return SimpleSegment
	 */
	public function getSimpleSegment() {
		return $this->simpleSegment;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( SegmentFilter::class, 'Google_Service_AnalyticsReporting_SegmentFilter' );
