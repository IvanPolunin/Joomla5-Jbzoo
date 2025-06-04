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
 * Service definition for Indexing (v3).
 *
 * <p>
 * Notifies Google when your web pages change.</p>
 *
 * <p>
 * For more information about this service, see the API
 * <a href="https://developers.google.com/search/apis/indexing-api/" target="_blank">Documentation</a>
 * </p>
 *
 * @author Google, Inc.
 */
class Indexing extends \Google\Service {
	/**
	 * Submit data to Google for indexing.
	 */
	const INDEXING = "https://www.googleapis.com/auth/indexing";
	public $urlNotifications;
	public $serviceName;

	/**
	 * Constructs the internal representation of the Indexing service.
	 *
	 * @param Client|array $clientOrConfig
	 *        	The client used to deliver requests, or a
	 *        	config array to pass to a new Client instance.
	 * @param string $rootUrl
	 *        	The root URL used for requests to the service.
	 */
	public function __construct($clientOrConfig = [ ], $rootUrl = null) {
		parent::__construct ( $clientOrConfig );
		$this->rootUrl = $rootUrl ?: 'https://indexing.googleapis.com/';
		$this->servicePath = '';
		$this->batchPath = 'batch';
		$this->version = 'v3';
		$this->serviceName = 'indexing';

		$this->urlNotifications = new Indexing\Resource\UrlNotifications ( $this, $this->serviceName, 'urlNotifications', [ 
				'methods' => [ 
						'getMetadata' => [ 
								'path' => 'v3/urlNotifications/metadata',
								'httpMethod' => 'GET',
								'parameters' => [ 
										'url' => [ 
												'location' => 'query',
												'type' => 'string'
										]
								]
						],
						'publish' => [ 
								'path' => 'v3/urlNotifications:publish',
								'httpMethod' => 'POST',
								'parameters' => [ ]
						]
				]
		] );
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( Indexing::class, 'Google_Service_Indexing' );
