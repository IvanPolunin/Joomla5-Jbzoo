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
class CohortSpec extends \Google\Collection {
	protected $collection_key = 'cohorts';
	protected $cohortReportSettingsType = CohortReportSettings::class;
	protected $cohortReportSettingsDataType = '';
	protected $cohortsType = Cohort::class;
	protected $cohortsDataType = 'array';
	protected $cohortsRangeType = CohortsRange::class;
	protected $cohortsRangeDataType = '';

	/**
	 *
	 * @param
	 *        	CohortReportSettings
	 */
	public function setCohortReportSettings(CohortReportSettings $cohortReportSettings) {
		$this->cohortReportSettings = $cohortReportSettings;
	}
	/**
	 *
	 * @return CohortReportSettings
	 */
	public function getCohortReportSettings() {
		return $this->cohortReportSettings;
	}
	/**
	 *
	 * @param
	 *        	Cohort[]
	 */
	public function setCohorts($cohorts) {
		$this->cohorts = $cohorts;
	}
	/**
	 *
	 * @return Cohort[]
	 */
	public function getCohorts() {
		return $this->cohorts;
	}
	/**
	 *
	 * @param
	 *        	CohortsRange
	 */
	public function setCohortsRange(CohortsRange $cohortsRange) {
		$this->cohortsRange = $cohortsRange;
	}
	/**
	 *
	 * @return CohortsRange
	 */
	public function getCohortsRange() {
		return $this->cohortsRange;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( CohortSpec::class, 'Google_Service_AnalyticsData_CohortSpec' );
