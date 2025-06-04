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

use Google\Service\Analytics\CustomMetric;
use Google\Service\Analytics\CustomMetrics;

/**
 * The "customMetrics" collection of methods.
 * Typical usage is:
 * <code>
 * $analyticsService = new Google\Service\Analytics(...);
 * $customMetrics = $analyticsService->customMetrics;
 * </code>
 */
class ManagementCustomMetrics extends \Google\Service\Resource {
	/**
	 * Get a custom metric to which the user has access.
	 * (customMetrics.get)
	 *
	 * @param string $accountId
	 *        	Account ID for the custom metric to retrieve.
	 * @param string $webPropertyId
	 *        	Web property ID for the custom metric to
	 *        	retrieve.
	 * @param string $customMetricId
	 *        	The ID of the custom metric to retrieve.
	 * @param array $optParams
	 *        	Optional parameters.
	 * @return CustomMetric
	 */
	public function get($accountId, $webPropertyId, $customMetricId, $optParams = [ ]) {
		$params = [ 
				'accountId' => $accountId,
				'webPropertyId' => $webPropertyId,
				'customMetricId' => $customMetricId
		];
		$params = array_merge ( $params, $optParams );
		return $this->call ( 'get', [ 
				$params
		], CustomMetric::class );
	}
	/**
	 * Create a new custom metric.
	 * (customMetrics.insert)
	 *
	 * @param string $accountId
	 *        	Account ID for the custom metric to create.
	 * @param string $webPropertyId
	 *        	Web property ID for the custom dimension to
	 *        	create.
	 * @param CustomMetric $postBody
	 * @param array $optParams
	 *        	Optional parameters.
	 * @return CustomMetric
	 */
	public function insert($accountId, $webPropertyId, CustomMetric $postBody, $optParams = [ ]) {
		$params = [ 
				'accountId' => $accountId,
				'webPropertyId' => $webPropertyId,
				'postBody' => $postBody
		];
		$params = array_merge ( $params, $optParams );
		return $this->call ( 'insert', [ 
				$params
		], CustomMetric::class );
	}
	/**
	 * Lists custom metrics to which the user has access.
	 * (customMetrics.listManagementCustomMetrics)
	 *
	 * @param string $accountId
	 *        	Account ID for the custom metrics to retrieve.
	 * @param string $webPropertyId
	 *        	Web property ID for the custom metrics to
	 *        	retrieve.
	 * @param array $optParams
	 *        	Optional parameters.
	 *        	
	 * @opt_param int max-results The maximum number of custom metrics to include in
	 * this response.
	 * @opt_param int start-index An index of the first entity to retrieve. Use this
	 * parameter as a pagination mechanism along with the max-results parameter.
	 * @return CustomMetrics
	 */
	public function listManagementCustomMetrics($accountId, $webPropertyId, $optParams = [ ]) {
		$params = [ 
				'accountId' => $accountId,
				'webPropertyId' => $webPropertyId
		];
		$params = array_merge ( $params, $optParams );
		return $this->call ( 'list', [ 
				$params
		], CustomMetrics::class );
	}
	/**
	 * Updates an existing custom metric.
	 * This method supports patch semantics.
	 * (customMetrics.patch)
	 *
	 * @param string $accountId
	 *        	Account ID for the custom metric to update.
	 * @param string $webPropertyId
	 *        	Web property ID for the custom metric to update.
	 * @param string $customMetricId
	 *        	Custom metric ID for the custom metric to
	 *        	update.
	 * @param CustomMetric $postBody
	 * @param array $optParams
	 *        	Optional parameters.
	 *        	
	 * @opt_param bool ignoreCustomDataSourceLinks Force the update and ignore any
	 * warnings related to the custom metric being linked to a custom data source /
	 * data set.
	 * @return CustomMetric
	 */
	public function patch($accountId, $webPropertyId, $customMetricId, CustomMetric $postBody, $optParams = [ ]) {
		$params = [ 
				'accountId' => $accountId,
				'webPropertyId' => $webPropertyId,
				'customMetricId' => $customMetricId,
				'postBody' => $postBody
		];
		$params = array_merge ( $params, $optParams );
		return $this->call ( 'patch', [ 
				$params
		], CustomMetric::class );
	}
	/**
	 * Updates an existing custom metric.
	 * (customMetrics.update)
	 *
	 * @param string $accountId
	 *        	Account ID for the custom metric to update.
	 * @param string $webPropertyId
	 *        	Web property ID for the custom metric to update.
	 * @param string $customMetricId
	 *        	Custom metric ID for the custom metric to
	 *        	update.
	 * @param CustomMetric $postBody
	 * @param array $optParams
	 *        	Optional parameters.
	 *        	
	 * @opt_param bool ignoreCustomDataSourceLinks Force the update and ignore any
	 * warnings related to the custom metric being linked to a custom data source /
	 * data set.
	 * @return CustomMetric
	 */
	public function update($accountId, $webPropertyId, $customMetricId, CustomMetric $postBody, $optParams = [ ]) {
		$params = [ 
				'accountId' => $accountId,
				'webPropertyId' => $webPropertyId,
				'customMetricId' => $customMetricId,
				'postBody' => $postBody
		];
		$params = array_merge ( $params, $optParams );
		return $this->call ( 'update', [ 
				$params
		], CustomMetric::class );
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( ManagementCustomMetrics::class, 'Google_Service_Analytics_Resource_ManagementCustomMetrics' );
