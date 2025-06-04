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
class ProfileFilterLinks extends \Google\Collection {
	protected $collection_key = 'items';
	protected $itemsType = ProfileFilterLink::class;
	protected $itemsDataType = 'array';
	public $itemsPerPage;
	public $kind;
	public $nextLink;
	public $previousLink;
	public $startIndex;
	public $totalResults;
	public $username;

	/**
	 *
	 * @param
	 *        	ProfileFilterLink[]
	 */
	public function setItems($items) {
		$this->items = $items;
	}
	/**
	 *
	 * @return ProfileFilterLink[]
	 */
	public function getItems() {
		return $this->items;
	}
	public function setItemsPerPage($itemsPerPage) {
		$this->itemsPerPage = $itemsPerPage;
	}
	public function getItemsPerPage() {
		return $this->itemsPerPage;
	}
	public function setKind($kind) {
		$this->kind = $kind;
	}
	public function getKind() {
		return $this->kind;
	}
	public function setNextLink($nextLink) {
		$this->nextLink = $nextLink;
	}
	public function getNextLink() {
		return $this->nextLink;
	}
	public function setPreviousLink($previousLink) {
		$this->previousLink = $previousLink;
	}
	public function getPreviousLink() {
		return $this->previousLink;
	}
	public function setStartIndex($startIndex) {
		$this->startIndex = $startIndex;
	}
	public function getStartIndex() {
		return $this->startIndex;
	}
	public function setTotalResults($totalResults) {
		$this->totalResults = $totalResults;
	}
	public function getTotalResults() {
		return $this->totalResults;
	}
	public function setUsername($username) {
		$this->username = $username;
	}
	public function getUsername() {
		return $this->username;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( ProfileFilterLinks::class, 'Google_Service_Analytics_ProfileFilterLinks' );
