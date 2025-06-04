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
class GaDataDataTableCols extends \Google\Model {
	public $id;
	public $label;
	public $type;
	public function setId($id) {
		$this->id = $id;
	}
	public function getId() {
		return $this->id;
	}
	public function setLabel($label) {
		$this->label = $label;
	}
	public function getLabel() {
		return $this->label;
	}
	public function setType($type) {
		$this->type = $type;
	}
	public function getType() {
		return $this->type;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( GaDataDataTableCols::class, 'Google_Service_Analytics_GaDataDataTableCols' );
