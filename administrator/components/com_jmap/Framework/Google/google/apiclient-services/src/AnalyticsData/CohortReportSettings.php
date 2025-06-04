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
class CohortReportSettings extends \Google\Model {
	public $accumulate;
	public function setAccumulate($accumulate) {
		$this->accumulate = $accumulate;
	}
	public function getAccumulate() {
		return $this->accumulate;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( CohortReportSettings::class, 'Google_Service_AnalyticsData_CohortReportSettings' );
