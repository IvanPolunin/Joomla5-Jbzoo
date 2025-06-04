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

use Google\Service\AnalyticsReporting\SearchUserActivityRequest;
use Google\Service\AnalyticsReporting\SearchUserActivityResponse;

/**
 * The "userActivity" collection of methods.
 * Typical usage is:
 * <code>
 * $analyticsreportingService = new Google\Service\AnalyticsReporting(...);
 * $userActivity = $analyticsreportingService->userActivity;
 * </code>
 */
class UserActivity extends \Google\Service\Resource {
	/**
	 * Returns User Activity data.
	 * (userActivity.search)
	 *
	 * @param SearchUserActivityRequest $postBody
	 * @param array $optParams
	 *        	Optional parameters.
	 * @return SearchUserActivityResponse
	 */
	public function search(SearchUserActivityRequest $postBody, $optParams = [ ]) {
		$params = [ 
				'postBody' => $postBody
		];
		$params = array_merge ( $params, $optParams );
		return $this->call ( 'search', [ 
				$params
		], SearchUserActivityResponse::class );
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( UserActivity::class, 'Google_Service_AnalyticsReporting_Resource_UserActivity' );
