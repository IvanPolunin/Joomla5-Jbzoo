<?php

namespace Google\Service\AnalyticsData;

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
class StringFilter extends \Google\Model {
	public $caseSensitive;
	public $matchType;
	public $value;
	public function setCaseSensitive($caseSensitive) {
		$this->caseSensitive = $caseSensitive;
	}
	public function getCaseSensitive() {
		return $this->caseSensitive;
	}
	public function setMatchType($matchType) {
		$this->matchType = $matchType;
	}
	public function getMatchType() {
		return $this->matchType;
	}
	public function setValue($value) {
		$this->value = $value;
	}
	public function getValue() {
		return $this->value;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( StringFilter::class, 'Google_Service_AnalyticsData_StringFilter' );
