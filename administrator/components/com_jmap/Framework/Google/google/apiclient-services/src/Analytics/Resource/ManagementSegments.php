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

use Google\Service\Analytics\Segments;

/**
 * The "segments" collection of methods.
 * Typical usage is:
 * <code>
 * $analyticsService = new Google\Service\Analytics(...);
 * $segments = $analyticsService->segments;
 * </code>
 */
class ManagementSegments extends \Google\Service\Resource {
	/**
	 * Lists segments to which the user has access.
	 * (segments.listManagementSegments)
	 *
	 * @param array $optParams
	 *        	Optional parameters.
	 *        	
	 * @opt_param int max-results The maximum number of segments to include in this
	 * response.
	 * @opt_param int start-index An index of the first segment to retrieve. Use
	 * this parameter as a pagination mechanism along with the max-results
	 * parameter.
	 * @return Segments
	 */
	public function listManagementSegments($optParams = [ ]) {
		$params = [ ];
		$params = array_merge ( $params, $optParams );
		return $this->call ( 'list', [ 
				$params
		], Segments::class );
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( ManagementSegments::class, 'Google_Service_Analytics_Resource_ManagementSegments' );
