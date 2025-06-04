<?php

namespace Google\Service\Webmasters\Resource;

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

use Google\Service\Webmasters\SitesListResponse;
use Google\Service\Webmasters\WmxSite;

/**
 * The "sites" collection of methods.
 * Typical usage is:
 * <code>
 * $webmastersService = new Google\Service\Webmasters(...);
 * $sites = $webmastersService->sites;
 * </code>
 */
class Sites extends \Google\Service\Resource {
	/**
	 * Adds a site to the set of the user's sites in Search Console.
	 * (sites.add)
	 *
	 * @param string $siteUrl
	 *        	The URL of the site to add.
	 * @param array $optParams
	 *        	Optional parameters.
	 */
	public function add($siteUrl, $optParams = [ ]) {
		$params = [ 
				'siteUrl' => $siteUrl
		];
		$params = array_merge ( $params, $optParams );
		return $this->call ( 'add', [ 
				$params
		] );
	}
	/**
	 * Removes a site from the set of the user's Search Console sites.
	 * (sites.delete)
	 *
	 * @param string $siteUrl
	 *        	The URI of the property as defined in Search Console.
	 *        	Examples: http://www.example.com/ or android-app://com.example/ Note: for
	 *        	property-sets, use the URI that starts with sc-set: which is used in Search
	 *        	Console URLs.
	 * @param array $optParams
	 *        	Optional parameters.
	 */
	public function delete($siteUrl, $optParams = [ ]) {
		$params = [ 
				'siteUrl' => $siteUrl
		];
		$params = array_merge ( $params, $optParams );
		return $this->call ( 'delete', [ 
				$params
		] );
	}
	/**
	 * Retrieves information about specific site.
	 * (sites.get)
	 *
	 * @param string $siteUrl
	 *        	The URI of the property as defined in Search Console.
	 *        	Examples: http://www.example.com/ or android-app://com.example/ Note: for
	 *        	property-sets, use the URI that starts with sc-set: which is used in Search
	 *        	Console URLs.
	 * @param array $optParams
	 *        	Optional parameters.
	 * @return WmxSite
	 */
	public function get($siteUrl, $optParams = [ ]) {
		$params = [ 
				'siteUrl' => $siteUrl
		];
		$params = array_merge ( $params, $optParams );
		return $this->call ( 'get', [ 
				$params
		], WmxSite::class );
	}
	/**
	 * Lists the user's Search Console sites.
	 * (sites.listSites)
	 *
	 * @param array $optParams
	 *        	Optional parameters.
	 * @return SitesListResponse
	 */
	public function listSites($optParams = [ ]) {
		$params = [ ];
		$params = array_merge ( $params, $optParams );
		return $this->call ( 'list', [ 
				$params
		], SitesListResponse::class );
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( Sites::class, 'Google_Service_Webmasters_Resource_Sites' );
