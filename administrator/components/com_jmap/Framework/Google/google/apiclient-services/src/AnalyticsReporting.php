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
 * Service definition for AnalyticsReporting (v4).
 *
 * <p>
 * Accesses Analytics report data.</p>
 *
 * <p>
 * For more information about this service, see the API
 * <a href="https://developers.google.com/analytics/devguides/reporting/core/v4/" target="_blank">Documentation</a>
 * </p>
 *
 * @author Google, Inc.
 */
class AnalyticsReporting extends \Google\Service {
	/**
	 * View and manage your Google Analytics data.
	 */
	const ANALYTICS = "https://www.googleapis.com/auth/analytics";
	/**
	 * See and download your Google Analytics data.
	 */
	const ANALYTICS_READONLY = "https://www.googleapis.com/auth/analytics.readonly";
	public $reports;
	public $userActivity;

	/**
	 * Constructs the internal representation of the AnalyticsReporting service.
	 *
	 * @param Client|array $clientOrConfig
	 *        	The client used to deliver requests, or a
	 *        	config array to pass to a new Client instance.
	 * @param string $rootUrl
	 *        	The root URL used for requests to the service.
	 */
	public function __construct($clientOrConfig = [ ], $rootUrl = null) {
		parent::__construct ( $clientOrConfig );
		$this->rootUrl = $rootUrl ?: 'https://analyticsreporting.googleapis.com/';
		$this->servicePath = '';
		$this->batchPath = 'batch';
		$this->version = 'v4';
		$this->serviceName = 'analyticsreporting';

		$this->reports = new AnalyticsReporting\Resource\Reports ( $this, $this->serviceName, 'reports', [ 
				'methods' => [ 
						'batchGet' => [ 
								'path' => 'v4/reports:batchGet',
								'httpMethod' => 'POST',
								'parameters' => [ ]
						]
				]
		] );
		$this->userActivity = new AnalyticsReporting\Resource\UserActivity ( $this, $this->serviceName, 'userActivity', [ 
				'methods' => [ 
						'search' => [ 
								'path' => 'v4/userActivity:search',
								'httpMethod' => 'POST',
								'parameters' => [ ]
						]
				]
		] );
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( AnalyticsReporting::class, 'Google_Service_AnalyticsReporting' );
