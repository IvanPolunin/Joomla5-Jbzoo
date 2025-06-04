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
class DynamicSegment extends \Google\Model {
	public $name;
	protected $sessionSegmentType = SegmentDefinition::class;
	protected $sessionSegmentDataType = '';
	protected $userSegmentType = SegmentDefinition::class;
	protected $userSegmentDataType = '';
	public function setName($name) {
		$this->name = $name;
	}
	public function getName() {
		return $this->name;
	}
	/**
	 *
	 * @param
	 *        	SegmentDefinition
	 */
	public function setSessionSegment(SegmentDefinition $sessionSegment) {
		$this->sessionSegment = $sessionSegment;
	}
	/**
	 *
	 * @return SegmentDefinition
	 */
	public function getSessionSegment() {
		return $this->sessionSegment;
	}
	/**
	 *
	 * @param
	 *        	SegmentDefinition
	 */
	public function setUserSegment(SegmentDefinition $userSegment) {
		$this->userSegment = $userSegment;
	}
	/**
	 *
	 * @return SegmentDefinition
	 */
	public function getUserSegment() {
		return $this->userSegment;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( DynamicSegment::class, 'Google_Service_AnalyticsReporting_DynamicSegment' );
