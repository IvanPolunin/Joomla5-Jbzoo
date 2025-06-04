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
class ConcatenateExpression extends \Google\Collection {
	protected $collection_key = 'dimensionNames';
	public $delimiter;
	public $dimensionNames;
	public function setDelimiter($delimiter) {
		$this->delimiter = $delimiter;
	}
	public function getDelimiter() {
		return $this->delimiter;
	}
	public function setDimensionNames($dimensionNames) {
		$this->dimensionNames = $dimensionNames;
	}
	public function getDimensionNames() {
		return $this->dimensionNames;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( ConcatenateExpression::class, 'Google_Service_AnalyticsData_ConcatenateExpression' );
