<?php

namespace Google\Service\Analytics\Resource;

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

use Google\Service\Analytics\AccountSummaries;

/**
 * The "accountSummaries" collection of methods.
 * Typical usage is:
 * <code>
 * $analyticsService = new Google\Service\Analytics(...);
 * $accountSummaries = $analyticsService->accountSummaries;
 * </code>
 */
class ManagementAccountSummaries extends \Google\Service\Resource {
	/**
	 * Lists account summaries (lightweight tree comprised of
	 * accounts/properties/profiles) to which the user has access.
	 * (accountSummaries.listManagementAccountSummaries)
	 *
	 * @param array $optParams
	 *        	Optional parameters.
	 *        	
	 * @opt_param int max-results The maximum number of account summaries to include
	 * in this response, where the largest acceptable value is 1000.
	 * @opt_param int start-index An index of the first entity to retrieve. Use this
	 * parameter as a pagination mechanism along with the max-results parameter.
	 * @return AccountSummaries
	 */
	public function listManagementAccountSummaries($optParams = [ ]) {
		$params = [ ];
		$params = array_merge ( $params, $optParams );
		return $this->call ( 'list', [ 
				$params
		], AccountSummaries::class );
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( ManagementAccountSummaries::class, 'Google_Service_Analytics_Resource_ManagementAccountSummaries' );
