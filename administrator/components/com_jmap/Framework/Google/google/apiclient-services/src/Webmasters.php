<?php

namespace Google\Service;

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

use Google\Client;

/**
 * Service definition for Webmasters (v3).
 *
 * <p>
 * View Google Search Console data for your verified sites.</p>
 *
 * <p>
 * For more information about this service, see the API
 * <a href="https://developers.google.com/webmaster-tools/" target="_blank">Documentation</a>
 * </p>
 *
 * @author Google, Inc.
 */
class Webmasters extends \Google\Service {
	/**
	 * View and manage Search Console data for your verified sites.
	 */
	const WEBMASTERS = "https://www.googleapis.com/auth/webmasters";
	/**
	 * View Search Console data for your verified sites.
	 */
	const WEBMASTERS_READONLY = "https://www.googleapis.com/auth/webmasters.readonly";
	public $searchanalytics;
	public $sitemaps;
	public $sites;
	public $serviceName;

	/**
	 * Constructs the internal representation of the Webmasters service.
	 *
	 * @param Client|array $clientOrConfig
	 *        	The client used to deliver requests, or a
	 *        	config array to pass to a new Client instance.
	 * @param string $rootUrl
	 *        	The root URL used for requests to the service.
	 */
	public function __construct($clientOrConfig = [ ], $rootUrl = null) {
		parent::__construct ( $clientOrConfig );
		$this->rootUrl = $rootUrl ?: 'https://www.googleapis.com/';
		$this->servicePath = 'webmasters/v3/';
		$this->batchPath = 'batch/webmasters/v3';
		$this->version = 'v3';
		$this->serviceName = 'webmasters';

		$this->searchanalytics = new Webmasters\Resource\Searchanalytics ( $this, $this->serviceName, 'searchanalytics', [ 
				'methods' => [ 
						'query' => [ 
								'path' => 'sites/{siteUrl}/searchAnalytics/query',
								'httpMethod' => 'POST',
								'parameters' => [ 
										'siteUrl' => [ 
												'location' => 'path',
												'type' => 'string',
												'required' => true
										]
								]
						]
				]
		] );
		$this->sitemaps = new Webmasters\Resource\Sitemaps ( $this, $this->serviceName, 'sitemaps', [ 
				'methods' => [ 
						'delete' => [ 
								'path' => 'sites/{siteUrl}/sitemaps/{feedpath}',
								'httpMethod' => 'DELETE',
								'parameters' => [ 
										'siteUrl' => [ 
												'location' => 'path',
												'type' => 'string',
												'required' => true
										],
										'feedpath' => [ 
												'location' => 'path',
												'type' => 'string',
												'required' => true
										]
								]
						],
						'get' => [ 
								'path' => 'sites/{siteUrl}/sitemaps/{feedpath}',
								'httpMethod' => 'GET',
								'parameters' => [ 
										'siteUrl' => [ 
												'location' => 'path',
												'type' => 'string',
												'required' => true
										],
										'feedpath' => [ 
												'location' => 'path',
												'type' => 'string',
												'required' => true
										]
								]
						],
						'list' => [ 
								'path' => 'sites/{siteUrl}/sitemaps',
								'httpMethod' => 'GET',
								'parameters' => [ 
										'siteUrl' => [ 
												'location' => 'path',
												'type' => 'string',
												'required' => true
										],
										'sitemapIndex' => [ 
												'location' => 'query',
												'type' => 'string'
										]
								]
						],
						'submit' => [ 
								'path' => 'sites/{siteUrl}/sitemaps/{feedpath}',
								'httpMethod' => 'PUT',
								'parameters' => [ 
										'siteUrl' => [ 
												'location' => 'path',
												'type' => 'string',
												'required' => true
										],
										'feedpath' => [ 
												'location' => 'path',
												'type' => 'string',
												'required' => true
										]
								]
						],
						'index.inspect' => [
								'path' => 'https://searchconsole.googleapis.com/v1/urlInspection/index:inspect',
								'httpMethod' => 'POST'
						]
				]
		] );
		$this->sites = new Webmasters\Resource\Sites ( $this, $this->serviceName, 'sites', [ 
				'methods' => [ 
						'add' => [ 
								'path' => 'sites/{siteUrl}',
								'httpMethod' => 'PUT',
								'parameters' => [ 
										'siteUrl' => [ 
												'location' => 'path',
												'type' => 'string',
												'required' => true
										]
								]
						],
						'delete' => [ 
								'path' => 'sites/{siteUrl}',
								'httpMethod' => 'DELETE',
								'parameters' => [ 
										'siteUrl' => [ 
												'location' => 'path',
												'type' => 'string',
												'required' => true
										]
								]
						],
						'get' => [ 
								'path' => 'sites/{siteUrl}',
								'httpMethod' => 'GET',
								'parameters' => [ 
										'siteUrl' => [ 
												'location' => 'path',
												'type' => 'string',
												'required' => true
										]
								]
						],
						'list' => [ 
								'path' => 'sites',
								'httpMethod' => 'GET',
								'parameters' => [ ]
						]
				]
		] );
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( Webmasters::class, 'Google_Service_Webmasters' );
