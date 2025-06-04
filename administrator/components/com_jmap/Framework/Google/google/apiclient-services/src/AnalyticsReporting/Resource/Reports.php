<?php

namespace Google\Service\AnalyticsReporting\Resource;

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

use Google\Service\AnalyticsReporting\GetReportsRequest;
use Google\Service\AnalyticsReporting\GetReportsResponse;

/**
 * The "reports" collection of methods.
 * Typical usage is:
 * <code>
 * $analyticsreportingService = new Google\Service\AnalyticsReporting(...);
 * $reports = $analyticsreportingService->reports;
 * </code>
 */
class Reports extends \Google\Service\Resource {
	/**
	 * Returns the Analytics data.
	 * (reports.batchGet)
	 *
	 * @param GetReportsRequest $postBody
	 * @param array $optParams
	 *        	Optional parameters.
	 * @return GetReportsResponse
	 */
	public function batchGet(GetReportsRequest $postBody, $optParams = [ ]) {
		$params = [ 
				'postBody' => $postBody
		];
		$params = array_merge ( $params, $optParams );
		return $this->call ( 'batchGet', [ 
				$params
		], GetReportsResponse::class );
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( Reports::class, 'Google_Service_AnalyticsReporting_Resource_Reports' );
