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

use Google\Service\Analytics\HashClientIdRequest;
use Google\Service\Analytics\HashClientIdResponse;

/**
 * The "clientId" collection of methods.
 * Typical usage is:
 * <code>
 * $analyticsService = new Google\Service\Analytics(...);
 * $clientId = $analyticsService->clientId;
 * </code>
 */
class ManagementClientId extends \Google\Service\Resource {
	/**
	 * Hashes the given Client ID.
	 * (clientId.hashClientId)
	 *
	 * @param HashClientIdRequest $postBody
	 * @param array $optParams
	 *        	Optional parameters.
	 * @return HashClientIdResponse
	 */
	public function hashClientId(HashClientIdRequest $postBody, $optParams = [ ]) {
		$params = [ 
				'postBody' => $postBody
		];
		$params = array_merge ( $params, $optParams );
		return $this->call ( 'hashClientId', [ 
				$params
		], HashClientIdResponse::class );
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( ManagementClientId::class, 'Google_Service_Analytics_Resource_ManagementClientId' );
