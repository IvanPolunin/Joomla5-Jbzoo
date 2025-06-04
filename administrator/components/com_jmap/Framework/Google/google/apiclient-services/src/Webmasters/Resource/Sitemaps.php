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

use Google\Service\Webmasters\SitemapsListResponse;
use Google\Service\Webmasters\WmxSitemap;

/**
 * The "sitemaps" collection of methods.
 * Typical usage is:
 * <code>
 * $webmastersService = new Google\Service\Webmasters(...);
 * $sitemaps = $webmastersService->sitemaps;
 * </code>
 */
class Sitemaps extends \Google\Service\Resource {
	/**
	 * Deletes a sitemap from this site.
	 * (sitemaps.delete)
	 *
	 * @param string $siteUrl
	 *        	The site's URL, including protocol. For example:
	 *        	http://www.example.com/
	 * @param string $feedpath
	 *        	The URL of the actual sitemap. For example:
	 *        	http://www.example.com/sitemap.xml
	 * @param array $optParams
	 *        	Optional parameters.
	 */
	public function delete($siteUrl, $feedpath, $optParams = [ ]) {
		$params = [ 
				'siteUrl' => $siteUrl,
				'feedpath' => $feedpath
		];
		$params = array_merge ( $params, $optParams );
		return $this->call ( 'delete', [ 
				$params
		] );
	}
	/**
	 * Retrieves information about a specific sitemap.
	 * (sitemaps.get)
	 *
	 * @param string $siteUrl
	 *        	The site's URL, including protocol. For example:
	 *        	http://www.example.com/
	 * @param string $feedpath
	 *        	The URL of the actual sitemap. For example:
	 *        	http://www.example.com/sitemap.xml
	 * @param array $optParams
	 *        	Optional parameters.
	 * @return WmxSitemap
	 */
	public function get($siteUrl, $feedpath, $optParams = [ ]) {
		$params = [ 
				'siteUrl' => $siteUrl,
				'feedpath' => $feedpath
		];
		$params = array_merge ( $params, $optParams );
		return $this->call ( 'get', [ 
				$params
		], WmxSitemap::class );
	}
	/**
	 * Lists the sitemaps-entries submitted for this site, or included in the
	 * sitemap index file (if sitemapIndex is specified in the request).
	 * (sitemaps.listSitemaps)
	 *
	 * @param string $siteUrl
	 *        	The site's URL, including protocol. For example:
	 *        	http://www.example.com/
	 * @param array $optParams
	 *        	Optional parameters.
	 *        	
	 * @opt_param string sitemapIndex A URL of a site's sitemap index. For example:
	 * http://www.example.com/sitemapindex.xml
	 * @return SitemapsListResponse
	 */
	public function listSitemaps($siteUrl, $optParams = [ ]) {
		$params = [ 
				'siteUrl' => $siteUrl
		];
		$params = array_merge ( $params, $optParams );
		return $this->call ( 'list', [ 
				$params
		], SitemapsListResponse::class );
	}
	/**
	 * Submits a sitemap for a site.
	 * (sitemaps.submit)
	 *
	 * @param string $siteUrl
	 *        	The site's URL, including protocol. For example:
	 *        	http://www.example.com/
	 * @param string $feedpath
	 *        	The URL of the sitemap to add. For example:
	 *        	http://www.example.com/sitemap.xml
	 * @param array $optParams
	 *        	Optional parameters.
	 */
	public function submit($siteUrl, $feedpath, $optParams = [ ]) {
		$params = [ 
				'siteUrl' => $siteUrl,
				'feedpath' => $feedpath
		];
		$params = array_merge ( $params, $optParams );
		return $this->call ( 'submit', [ 
				$params
		] );
	}
	
	/**
	 * Resources to get or put information about URLs in your property in the Google index.
	 * View the indexed, or indexable, status of the provided URL.
	 * Presently only the status of the version in the Google index is available; you cannot test the indexability of a live URL.
	 * @param string $inspectionUrl
	 * @param string $siteUrl
	 *
	 * @return string JSON response
	 */
	public function inspect($inspectionUrl, $siteUrl) {
		$params = [
				'inspectionUrl' => $inspectionUrl,
				'siteUrl' => $siteUrl
		];
		$postBody = ['postBody'=>$params];
		return $this->call ( 'index.inspect', [
				$postBody
		] );
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( Sitemaps::class, 'Google_Service_Webmasters_Resource_Sitemaps' );
