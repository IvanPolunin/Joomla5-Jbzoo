<?php
namespace Google;

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

use Google\Http\Batch;
use TypeError;

class Service {
	public $batchPath;
	public $rootUrl;
	public $version;
	public $servicePath;
	public $serviceName;
	public $availableScopes;
	public $resource;
	private $client;
	public function __construct($clientOrConfig = [ ]) {
		if ($clientOrConfig instanceof Client) {
			$this->client = $clientOrConfig;
		} elseif (is_array ( $clientOrConfig )) {
			$this->client = new Client ( $clientOrConfig ?: [ ] );
		} else {
			$errorMessage = 'constructor must be array or instance of Google\Client';
			if (class_exists ( 'TypeError' )) {
				throw new TypeError ( $errorMessage );
			}
			trigger_error ( $errorMessage, E_USER_ERROR );
		}
	}

	/**
	 * Return the associated Google\Client class.
	 *
	 * @return \Google\Client
	 */
	public function getClient() {
		return $this->client;
	}

	/**
	 * Create a new HTTP Batch handler for this service
	 *
	 * @return Batch
	 */
	public function createBatch() {
		return new Batch ( $this->client, false, $this->rootUrl, $this->batchPath );
	}
}
