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

use Google\Service\Analytics\Columns;

/**
 * The "columns" collection of methods.
 * Typical usage is:
 * <code>
 * $analyticsService = new Google\Service\Analytics(...);
 * $columns = $analyticsService->columns;
 * </code>
 */
class MetadataColumns extends \Google\Service\Resource {
	/**
	 * Lists all columns for a report type (columns.listMetadataColumns)
	 *
	 * @param string $reportType
	 *        	Report type. Allowed Values: 'ga'. Where 'ga'
	 *        	corresponds to the Core Reporting API
	 * @param array $optParams
	 *        	Optional parameters.
	 * @return Columns
	 */
	public function listMetadataColumns($reportType, $optParams = [ ]) {
		$params = [ 
				'reportType' => $reportType
		];
		$params = array_merge ( $params, $optParams );
		return $this->call ( 'list', [ 
				$params
		], Columns::class );
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( MetadataColumns::class, 'Google_Service_Analytics_Resource_MetadataColumns' );
