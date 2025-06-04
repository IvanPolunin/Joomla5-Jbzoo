<?php

namespace Google\Service\Analytics;

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
class ProfileFilterLink extends \Google\Model {
	protected $filterRefType = FilterRef::class;
	protected $filterRefDataType = '';
	public $id;
	public $kind;
	protected $profileRefType = ProfileRef::class;
	protected $profileRefDataType = '';
	public $rank;
	public $selfLink;

	/**
	 *
	 * @param
	 *        	FilterRef
	 */
	public function setFilterRef(FilterRef $filterRef) {
		$this->filterRef = $filterRef;
	}
	/**
	 *
	 * @return FilterRef
	 */
	public function getFilterRef() {
		return $this->filterRef;
	}
	public function setId($id) {
		$this->id = $id;
	}
	public function getId() {
		return $this->id;
	}
	public function setKind($kind) {
		$this->kind = $kind;
	}
	public function getKind() {
		return $this->kind;
	}
	/**
	 *
	 * @param
	 *        	ProfileRef
	 */
	public function setProfileRef(ProfileRef $profileRef) {
		$this->profileRef = $profileRef;
	}
	/**
	 *
	 * @return ProfileRef
	 */
	public function getProfileRef() {
		return $this->profileRef;
	}
	public function setRank($rank) {
		$this->rank = $rank;
	}
	public function getRank() {
		return $this->rank;
	}
	public function setSelfLink($selfLink) {
		$this->selfLink = $selfLink;
	}
	public function getSelfLink() {
		return $this->selfLink;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( ProfileFilterLink::class, 'Google_Service_Analytics_ProfileFilterLink' );
