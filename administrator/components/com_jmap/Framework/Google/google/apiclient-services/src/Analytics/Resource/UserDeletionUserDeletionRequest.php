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

use Google\Service\Analytics\UserDeletionRequest;

/**
 * The "userDeletionRequest" collection of methods.
 * Typical usage is:
 * <code>
 * $analyticsService = new Google\Service\Analytics(...);
 * $userDeletionRequest = $analyticsService->userDeletionRequest;
 * </code>
 */
class UserDeletionUserDeletionRequest extends \Google\Service\Resource {
	/**
	 * Insert or update a user deletion requests.
	 * (userDeletionRequest.upsert)
	 *
	 * @param UserDeletionRequest $postBody
	 * @param array $optParams
	 *        	Optional parameters.
	 * @return UserDeletionRequest
	 */
	public function upsert(UserDeletionRequest $postBody, $optParams = [ ]) {
		$params = [ 
				'postBody' => $postBody
		];
		$params = array_merge ( $params, $optParams );
		return $this->call ( 'upsert', [ 
				$params
		], UserDeletionRequest::class );
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( UserDeletionUserDeletionRequest::class, 'Google_Service_Analytics_Resource_UserDeletionUserDeletionRequest' );
