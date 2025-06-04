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
class GaDataColumnHeaders extends \Google\Model {
	public $columnType;
	public $dataType;
	public $name;
	public function setColumnType($columnType) {
		$this->columnType = $columnType;
	}
	public function getColumnType() {
		return $this->columnType;
	}
	public function setDataType($dataType) {
		$this->dataType = $dataType;
	}
	public function getDataType() {
		return $this->dataType;
	}
	public function setName($name) {
		$this->name = $name;
	}
	public function getName() {
		return $this->name;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( GaDataColumnHeaders::class, 'Google_Service_Analytics_GaDataColumnHeaders' );
