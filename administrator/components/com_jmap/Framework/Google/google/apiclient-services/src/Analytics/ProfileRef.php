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
class ProfileRef extends \Google\Model {
	public $accountId;
	public $href;
	public $id;
	public $internalWebPropertyId;
	public $kind;
	public $name;
	public $webPropertyId;
	public function setAccountId($accountId) {
		$this->accountId = $accountId;
	}
	public function getAccountId() {
		return $this->accountId;
	}
	public function setHref($href) {
		$this->href = $href;
	}
	public function getHref() {
		return $this->href;
	}
	public function setId($id) {
		$this->id = $id;
	}
	public function getId() {
		return $this->id;
	}
	public function setInternalWebPropertyId($internalWebPropertyId) {
		$this->internalWebPropertyId = $internalWebPropertyId;
	}
	public function getInternalWebPropertyId() {
		return $this->internalWebPropertyId;
	}
	public function setKind($kind) {
		$this->kind = $kind;
	}
	public function getKind() {
		return $this->kind;
	}
	public function setName($name) {
		$this->name = $name;
	}
	public function getName() {
		return $this->name;
	}
	public function setWebPropertyId($webPropertyId) {
		$this->webPropertyId = $webPropertyId;
	}
	public function getWebPropertyId() {
		return $this->webPropertyId;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( ProfileRef::class, 'Google_Service_Analytics_ProfileRef' );
