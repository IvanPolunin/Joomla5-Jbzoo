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

use Google\Service\Analytics\ProfileFilterLink;
use Google\Service\Analytics\ProfileFilterLinks;

/**
 * The "profileFilterLinks" collection of methods.
 * Typical usage is:
 * <code>
 * $analyticsService = new Google\Service\Analytics(...);
 * $profileFilterLinks = $analyticsService->profileFilterLinks;
 * </code>
 */
class ManagementProfileFilterLinks extends \Google\Service\Resource {
	/**
	 * Delete a profile filter link.
	 * (profileFilterLinks.delete)
	 *
	 * @param string $accountId
	 *        	Account ID to which the profile filter link belongs.
	 * @param string $webPropertyId
	 *        	Web property Id to which the profile filter link
	 *        	belongs.
	 * @param string $profileId
	 *        	Profile ID to which the filter link belongs.
	 * @param string $linkId
	 *        	ID of the profile filter link to delete.
	 * @param array $optParams
	 *        	Optional parameters.
	 */
	public function delete($accountId, $webPropertyId, $profileId, $linkId, $optParams = [ ]) {
		$params = [ 
				'accountId' => $accountId,
				'webPropertyId' => $webPropertyId,
				'profileId' => $profileId,
				'linkId' => $linkId
		];
		$params = array_merge ( $params, $optParams );
		return $this->call ( 'delete', [ 
				$params
		] );
	}
	/**
	 * Returns a single profile filter link.
	 * (profileFilterLinks.get)
	 *
	 * @param string $accountId
	 *        	Account ID to retrieve profile filter link for.
	 * @param string $webPropertyId
	 *        	Web property Id to retrieve profile filter link
	 *        	for.
	 * @param string $profileId
	 *        	Profile ID to retrieve filter link for.
	 * @param string $linkId
	 *        	ID of the profile filter link.
	 * @param array $optParams
	 *        	Optional parameters.
	 * @return ProfileFilterLink
	 */
	public function get($accountId, $webPropertyId, $profileId, $linkId, $optParams = [ ]) {
		$params = [ 
				'accountId' => $accountId,
				'webPropertyId' => $webPropertyId,
				'profileId' => $profileId,
				'linkId' => $linkId
		];
		$params = array_merge ( $params, $optParams );
		return $this->call ( 'get', [ 
				$params
		], ProfileFilterLink::class );
	}
	/**
	 * Create a new profile filter link.
	 * (profileFilterLinks.insert)
	 *
	 * @param string $accountId
	 *        	Account ID to create profile filter link for.
	 * @param string $webPropertyId
	 *        	Web property Id to create profile filter link
	 *        	for.
	 * @param string $profileId
	 *        	Profile ID to create filter link for.
	 * @param ProfileFilterLink $postBody
	 * @param array $optParams
	 *        	Optional parameters.
	 * @return ProfileFilterLink
	 */
	public function insert($accountId, $webPropertyId, $profileId, ProfileFilterLink $postBody, $optParams = [ ]) {
		$params = [ 
				'accountId' => $accountId,
				'webPropertyId' => $webPropertyId,
				'profileId' => $profileId,
				'postBody' => $postBody
		];
		$params = array_merge ( $params, $optParams );
		return $this->call ( 'insert', [ 
				$params
		], ProfileFilterLink::class );
	}
	/**
	 * Lists all profile filter links for a profile.
	 * (profileFilterLinks.listManagementProfileFilterLinks)
	 *
	 * @param string $accountId
	 *        	Account ID to retrieve profile filter links for.
	 * @param string $webPropertyId
	 *        	Web property Id for profile filter links for.
	 *        	Can either be a specific web property ID or '~all', which refers to all the
	 *        	web properties that user has access to.
	 * @param string $profileId
	 *        	Profile ID to retrieve filter links for. Can either
	 *        	be a specific profile ID or '~all', which refers to all the profiles that
	 *        	user has access to.
	 * @param array $optParams
	 *        	Optional parameters.
	 *        	
	 * @opt_param int max-results The maximum number of profile filter links to
	 * include in this response.
	 * @opt_param int start-index An index of the first entity to retrieve. Use this
	 * parameter as a pagination mechanism along with the max-results parameter.
	 * @return ProfileFilterLinks
	 */
	public function listManagementProfileFilterLinks($accountId, $webPropertyId, $profileId, $optParams = [ ]) {
		$params = [ 
				'accountId' => $accountId,
				'webPropertyId' => $webPropertyId,
				'profileId' => $profileId
		];
		$params = array_merge ( $params, $optParams );
		return $this->call ( 'list', [ 
				$params
		], ProfileFilterLinks::class );
	}
	/**
	 * Update an existing profile filter link.
	 * This method supports patch semantics.
	 * (profileFilterLinks.patch)
	 *
	 * @param string $accountId
	 *        	Account ID to which profile filter link belongs.
	 * @param string $webPropertyId
	 *        	Web property Id to which profile filter link
	 *        	belongs
	 * @param string $profileId
	 *        	Profile ID to which filter link belongs
	 * @param string $linkId
	 *        	ID of the profile filter link to be updated.
	 * @param ProfileFilterLink $postBody
	 * @param array $optParams
	 *        	Optional parameters.
	 * @return ProfileFilterLink
	 */
	public function patch($accountId, $webPropertyId, $profileId, $linkId, ProfileFilterLink $postBody, $optParams = [ ]) {
		$params = [ 
				'accountId' => $accountId,
				'webPropertyId' => $webPropertyId,
				'profileId' => $profileId,
				'linkId' => $linkId,
				'postBody' => $postBody
		];
		$params = array_merge ( $params, $optParams );
		return $this->call ( 'patch', [ 
				$params
		], ProfileFilterLink::class );
	}
	/**
	 * Update an existing profile filter link.
	 * (profileFilterLinks.update)
	 *
	 * @param string $accountId
	 *        	Account ID to which profile filter link belongs.
	 * @param string $webPropertyId
	 *        	Web property Id to which profile filter link
	 *        	belongs
	 * @param string $profileId
	 *        	Profile ID to which filter link belongs
	 * @param string $linkId
	 *        	ID of the profile filter link to be updated.
	 * @param ProfileFilterLink $postBody
	 * @param array $optParams
	 *        	Optional parameters.
	 * @return ProfileFilterLink
	 */
	public function update($accountId, $webPropertyId, $profileId, $linkId, ProfileFilterLink $postBody, $optParams = [ ]) {
		$params = [ 
				'accountId' => $accountId,
				'webPropertyId' => $webPropertyId,
				'profileId' => $profileId,
				'linkId' => $linkId,
				'postBody' => $postBody
		];
		$params = array_merge ( $params, $optParams );
		return $this->call ( 'update', [ 
				$params
		], ProfileFilterLink::class );
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( ManagementProfileFilterLinks::class, 'Google_Service_Analytics_Resource_ManagementProfileFilterLinks' );
