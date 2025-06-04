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

use Google\Service\Analytics\RemarketingAudience;
use Google\Service\Analytics\RemarketingAudiences;

/**
 * The "remarketingAudience" collection of methods.
 * Typical usage is:
 * <code>
 * $analyticsService = new Google\Service\Analytics(...);
 * $remarketingAudience = $analyticsService->remarketingAudience;
 * </code>
 */
class ManagementRemarketingAudience extends \Google\Service\Resource {
	/**
	 * Delete a remarketing audience.
	 * (remarketingAudience.delete)
	 *
	 * @param string $accountId
	 *        	Account ID to which the remarketing audience
	 *        	belongs.
	 * @param string $webPropertyId
	 *        	Web property ID to which the remarketing
	 *        	audience belongs.
	 * @param string $remarketingAudienceId
	 *        	The ID of the remarketing audience to
	 *        	delete.
	 * @param array $optParams
	 *        	Optional parameters.
	 */
	public function delete($accountId, $webPropertyId, $remarketingAudienceId, $optParams = [ ]) {
		$params = [ 
				'accountId' => $accountId,
				'webPropertyId' => $webPropertyId,
				'remarketingAudienceId' => $remarketingAudienceId
		];
		$params = array_merge ( $params, $optParams );
		return $this->call ( 'delete', [ 
				$params
		] );
	}
	/**
	 * Gets a remarketing audience to which the user has access.
	 * (remarketingAudience.get)
	 *
	 * @param string $accountId
	 *        	The account ID of the remarketing audience to
	 *        	retrieve.
	 * @param string $webPropertyId
	 *        	The web property ID of the remarketing audience
	 *        	to retrieve.
	 * @param string $remarketingAudienceId
	 *        	The ID of the remarketing audience to
	 *        	retrieve.
	 * @param array $optParams
	 *        	Optional parameters.
	 * @return RemarketingAudience
	 */
	public function get($accountId, $webPropertyId, $remarketingAudienceId, $optParams = [ ]) {
		$params = [ 
				'accountId' => $accountId,
				'webPropertyId' => $webPropertyId,
				'remarketingAudienceId' => $remarketingAudienceId
		];
		$params = array_merge ( $params, $optParams );
		return $this->call ( 'get', [ 
				$params
		], RemarketingAudience::class );
	}
	/**
	 * Creates a new remarketing audience.
	 * (remarketingAudience.insert)
	 *
	 * @param string $accountId
	 *        	The account ID for which to create the remarketing
	 *        	audience.
	 * @param string $webPropertyId
	 *        	Web property ID for which to create the
	 *        	remarketing audience.
	 * @param RemarketingAudience $postBody
	 * @param array $optParams
	 *        	Optional parameters.
	 * @return RemarketingAudience
	 */
	public function insert($accountId, $webPropertyId, RemarketingAudience $postBody, $optParams = [ ]) {
		$params = [ 
				'accountId' => $accountId,
				'webPropertyId' => $webPropertyId,
				'postBody' => $postBody
		];
		$params = array_merge ( $params, $optParams );
		return $this->call ( 'insert', [ 
				$params
		], RemarketingAudience::class );
	}
	/**
	 * Lists remarketing audiences to which the user has access.
	 * (remarketingAudience.listManagementRemarketingAudience)
	 *
	 * @param string $accountId
	 *        	The account ID of the remarketing audiences to
	 *        	retrieve.
	 * @param string $webPropertyId
	 *        	The web property ID of the remarketing audiences
	 *        	to retrieve.
	 * @param array $optParams
	 *        	Optional parameters.
	 *        	
	 * @opt_param int max-results The maximum number of remarketing audiences to
	 * include in this response.
	 * @opt_param int start-index An index of the first entity to retrieve. Use this
	 * parameter as a pagination mechanism along with the max-results parameter.
	 * @opt_param string type
	 * @return RemarketingAudiences
	 */
	public function listManagementRemarketingAudience($accountId, $webPropertyId, $optParams = [ ]) {
		$params = [ 
				'accountId' => $accountId,
				'webPropertyId' => $webPropertyId
		];
		$params = array_merge ( $params, $optParams );
		return $this->call ( 'list', [ 
				$params
		], RemarketingAudiences::class );
	}
	/**
	 * Updates an existing remarketing audience.
	 * This method supports patch
	 * semantics. (remarketingAudience.patch)
	 *
	 * @param string $accountId
	 *        	The account ID of the remarketing audience to
	 *        	update.
	 * @param string $webPropertyId
	 *        	The web property ID of the remarketing audience
	 *        	to update.
	 * @param string $remarketingAudienceId
	 *        	The ID of the remarketing audience to
	 *        	update.
	 * @param RemarketingAudience $postBody
	 * @param array $optParams
	 *        	Optional parameters.
	 * @return RemarketingAudience
	 */
	public function patch($accountId, $webPropertyId, $remarketingAudienceId, RemarketingAudience $postBody, $optParams = [ ]) {
		$params = [ 
				'accountId' => $accountId,
				'webPropertyId' => $webPropertyId,
				'remarketingAudienceId' => $remarketingAudienceId,
				'postBody' => $postBody
		];
		$params = array_merge ( $params, $optParams );
		return $this->call ( 'patch', [ 
				$params
		], RemarketingAudience::class );
	}
	/**
	 * Updates an existing remarketing audience.
	 * (remarketingAudience.update)
	 *
	 * @param string $accountId
	 *        	The account ID of the remarketing audience to
	 *        	update.
	 * @param string $webPropertyId
	 *        	The web property ID of the remarketing audience
	 *        	to update.
	 * @param string $remarketingAudienceId
	 *        	The ID of the remarketing audience to
	 *        	update.
	 * @param RemarketingAudience $postBody
	 * @param array $optParams
	 *        	Optional parameters.
	 * @return RemarketingAudience
	 */
	public function update($accountId, $webPropertyId, $remarketingAudienceId, RemarketingAudience $postBody, $optParams = [ ]) {
		$params = [ 
				'accountId' => $accountId,
				'webPropertyId' => $webPropertyId,
				'remarketingAudienceId' => $remarketingAudienceId,
				'postBody' => $postBody
		];
		$params = array_merge ( $params, $optParams );
		return $this->call ( 'update', [ 
				$params
		], RemarketingAudience::class );
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( ManagementRemarketingAudience::class, 'Google_Service_Analytics_Resource_ManagementRemarketingAudience' );
