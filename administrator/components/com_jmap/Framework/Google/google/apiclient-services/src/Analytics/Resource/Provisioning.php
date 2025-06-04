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

use Google\Service\Analytics\AccountTicket;
use Google\Service\Analytics\AccountTreeRequest;
use Google\Service\Analytics\AccountTreeResponse;

/**
 * The "provisioning" collection of methods.
 * Typical usage is:
 * <code>
 * $analyticsService = new Google\Service\Analytics(...);
 * $provisioning = $analyticsService->provisioning;
 * </code>
 */
class Provisioning extends \Google\Service\Resource {
	/**
	 * Creates an account ticket.
	 * (provisioning.createAccountTicket)
	 *
	 * @param AccountTicket $postBody
	 * @param array $optParams
	 *        	Optional parameters.
	 * @return AccountTicket
	 */
	public function createAccountTicket(AccountTicket $postBody, $optParams = [ ]) {
		$params = [ 
				'postBody' => $postBody
		];
		$params = array_merge ( $params, $optParams );
		return $this->call ( 'createAccountTicket', [ 
				$params
		], AccountTicket::class );
	}
	/**
	 * Provision account.
	 * (provisioning.createAccountTree)
	 *
	 * @param AccountTreeRequest $postBody
	 * @param array $optParams
	 *        	Optional parameters.
	 * @return AccountTreeResponse
	 */
	public function createAccountTree(AccountTreeRequest $postBody, $optParams = [ ]) {
		$params = [ 
				'postBody' => $postBody
		];
		$params = array_merge ( $params, $optParams );
		return $this->call ( 'createAccountTree', [ 
				$params
		], AccountTreeResponse::class );
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( Provisioning::class, 'Google_Service_Analytics_Resource_Provisioning' );
