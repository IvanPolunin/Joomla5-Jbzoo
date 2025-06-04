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
class CustomDimension extends \Google\Model {
	public $index;
	public $value;
	public function setIndex($index) {
		$this->index = $index;
	}
	public function getIndex() {
		return $this->index;
	}
	public function setValue($value) {
		$this->value = $value;
	}
	public function getValue() {
		return $this->value;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( CustomDimension::class, 'Google_Service_AnalyticsReporting_CustomDimension' );
