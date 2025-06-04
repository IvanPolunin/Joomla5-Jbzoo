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
 * Service definition for AnalyticsData (v1beta).
 *
 * <p>
 * Accesses report data in Google Analytics.</p>
 *
 * <p>
 * For more information about this service, see the API
 * <a href="https://developers.google.com/analytics/devguides/reporting/data/v1/" target="_blank">Documentation</a>
 * </p>
 *
 * @author Google, Inc.
 */
class AnalyticsData extends \Google\Service {
	/**
	 * View and manage your Google Analytics data.
	 */
	const ANALYTICS = "https://www.googleapis.com/auth/analytics";
	/**
	 * See and download your Google Analytics data.
	 */
	const ANALYTICS_READONLY = "https://www.googleapis.com/auth/analytics.readonly";
	public $properties;

	/**
	 * Constructs the internal representation of the AnalyticsData service.
	 *
	 * @param Client|array $clientOrConfig
	 *        	The client used to deliver requests, or a
	 *        	config array to pass to a new Client instance.
	 * @param string $rootUrl
	 *        	The root URL used for requests to the service.
	 */
	public function __construct($clientOrConfig = [ ], $rootUrl = null) {
		parent::__construct ( $clientOrConfig );
		$this->rootUrl = $rootUrl ?: 'https://analyticsdata.googleapis.com/';
		$this->servicePath = '';
		$this->batchPath = 'batch';
		$this->version = 'v1beta';
		$this->serviceName = 'analyticsdata';

		$this->properties = new AnalyticsData\Resource\Properties ( $this, $this->serviceName, 'properties', [ 
				'methods' => [ 
						'batchRunPivotReports' => [ 
								'path' => 'v1beta/{+property}:batchRunPivotReports',
								'httpMethod' => 'POST',
								'parameters' => [ 
										'property' => [ 
												'location' => 'path',
												'type' => 'string',
												'required' => true
										]
								]
						],
						'batchRunReports' => [ 
								'path' => 'v1beta/{+property}:batchRunReports',
								'httpMethod' => 'POST',
								'parameters' => [ 
										'property' => [ 
												'location' => 'path',
												'type' => 'string',
												'required' => true
										]
								]
						],
						'getMetadata' => [ 
								'path' => 'v1beta/{+name}',
								'httpMethod' => 'GET',
								'parameters' => [ 
										'name' => [ 
												'location' => 'path',
												'type' => 'string',
												'required' => true
										]
								]
						],
						'runPivotReport' => [ 
								'path' => 'v1beta/{+property}:runPivotReport',
								'httpMethod' => 'POST',
								'parameters' => [ 
										'property' => [ 
												'location' => 'path',
												'type' => 'string',
												'required' => true
										]
								]
						],
						'runRealtimeReport' => [ 
								'path' => 'v1beta/{+property}:runRealtimeReport',
								'httpMethod' => 'POST',
								'parameters' => [ 
										'property' => [ 
												'location' => 'path',
												'type' => 'string',
												'required' => true
										]
								]
						],
						'runReport' => [ 
								'path' => 'v1beta/{+property}:runReport',
								'httpMethod' => 'POST',
								'parameters' => [ 
										'property' => [ 
												'location' => 'path',
												'type' => 'string',
												'required' => true
										]
								]
						]
				]
		] );
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( AnalyticsData::class, 'Google_Service_AnalyticsData' );
