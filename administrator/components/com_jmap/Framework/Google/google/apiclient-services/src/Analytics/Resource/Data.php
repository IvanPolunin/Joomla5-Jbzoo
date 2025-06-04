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

/**
 * The "data" collection of methods.
 * Typical usage is:
 * <code>
 * $analyticsService = new Google\Service\Analytics(...);
 * $data = $analyticsService->data;
 * </code>
 */
class Data extends \Google\Service\Resource {
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( Data::class, 'Google_Service_Analytics_Resource_Data' );
