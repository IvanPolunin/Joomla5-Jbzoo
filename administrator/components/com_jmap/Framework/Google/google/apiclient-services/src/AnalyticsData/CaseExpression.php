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
class CaseExpression extends \Google\Model {
	public $dimensionName;
	public function setDimensionName($dimensionName) {
		$this->dimensionName = $dimensionName;
	}
	public function getDimensionName() {
		return $this->dimensionName;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( CaseExpression::class, 'Google_Service_AnalyticsData_CaseExpression' );
