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
class InListFilter extends \Google\Collection {
	protected $collection_key = 'values';
	public $caseSensitive;
	public $values;
	public function setCaseSensitive($caseSensitive) {
		$this->caseSensitive = $caseSensitive;
	}
	public function getCaseSensitive() {
		return $this->caseSensitive;
	}
	public function setValues($values) {
		$this->values = $values;
	}
	public function getValues() {
		return $this->values;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( InListFilter::class, 'Google_Service_AnalyticsData_InListFilter' );
