<?php

namespace Google\Service\Indexing\Resource;

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

use Google\Service\Indexing\PublishUrlNotificationResponse;
use Google\Service\Indexing\UrlNotification;
use Google\Service\Indexing\UrlNotificationMetadata;

/**
 * The "urlNotifications" collection of methods.
 * Typical usage is:
 *  <code>
 *   $indexingService = new Google\Service\Indexing(...);
 *   $urlNotifications = $indexingService->urlNotifications;
 *  </code>
 */
class UrlNotifications extends \Google\Service\Resource
{
  /**
   * Gets metadata about a Web Document. This method can _only_ be used to query
   * URLs that were previously seen in successful Indexing API notifications.
   * Includes the latest `UrlNotification` received via this API.
   * (urlNotifications.getMetadata)
   *
   * @param array $optParams Optional parameters.
   *
   * @opt_param string url URL that is being queried.
   * @return UrlNotificationMetadata
   */
  public function getMetadata($optParams = [])
  {
    $params = [];
    $params = array_merge($params, $optParams);
    return $this->call('getMetadata', [$params], UrlNotificationMetadata::class);
  }
  /**
   * Notifies that a URL has been updated or deleted. (urlNotifications.publish)
   *
   * @param UrlNotification $postBody
   * @param array $optParams Optional parameters.
   * @return PublishUrlNotificationResponse
   */
  public function publish(UrlNotification $postBody, $optParams = [])
  {
    $params = ['postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('publish', [$params], PublishUrlNotificationResponse::class);
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(UrlNotifications::class, 'Google_Service_Indexing_Resource_UrlNotifications');
