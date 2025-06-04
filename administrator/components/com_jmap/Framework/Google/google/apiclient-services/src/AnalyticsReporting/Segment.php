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
class Segment extends \Google\Model {
	protected $dynamicSegmentType = DynamicSegment::class;
	protected $dynamicSegmentDataType = '';
	public $segmentId;

	/**
	 *
	 * @param
	 *        	DynamicSegment
	 */
	public function setDynamicSegment(DynamicSegment $dynamicSegment) {
		$this->dynamicSegment = $dynamicSegment;
	}
	/**
	 *
	 * @return DynamicSegment
	 */
	public function getDynamicSegment() {
		return $this->dynamicSegment;
	}
	public function setSegmentId($segmentId) {
		$this->segmentId = $segmentId;
	}
	public function getSegmentId() {
		return $this->segmentId;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( Segment::class, 'Google_Service_AnalyticsReporting_Segment' );
