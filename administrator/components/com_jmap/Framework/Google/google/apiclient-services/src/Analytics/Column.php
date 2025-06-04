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
class Column extends \Google\Model {
	public $attributes;
	public $id;
	public $kind;
	public function setAttributes($attributes) {
		$this->attributes = $attributes;
	}
	public function getAttributes() {
		return $this->attributes;
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
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( Column::class, 'Google_Service_Analytics_Column' );
