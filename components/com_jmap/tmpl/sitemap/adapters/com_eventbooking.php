<?php
/** 
 * @package JMAP::SITEMAP::components::com_jmap
 * @subpackage views
 * @subpackage sitemap
 * @subpackage tmpl
 * @subpackage adapters
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') or die('Restricted access');

// Adapter for EventBooking items and categories route helper
include_once JPATH_SITE . '/components/com_eventbooking/helper/helper.php';
$helperRouteClass= 'EventbookingHelperRoute';
switch ($targetViewName) {
	case 'event':
		$classMethod = 'getEventRoute';
		$seflink = \JMapRoute::_ ($helperRouteClass::$classMethod($elm->id, $elm->catid));
		
		// Fallback for not routed category menu item
		if(stripos($seflink, '/component/') !== false) {
			$topCategoryEventMenuId = $helperRouteClass::getEventMenuId($elm->id, $elm->catid, 0);
			if($topCategoryEventMenuId) {
				$seflink = \JMapRoute::_ ($helperRouteClass::$classMethod($elm->id, $elm->catid, $topCategoryEventMenuId));
			} else {
				$needlesViews = [
						'upcomingevents',
						'calendar',
						'fullcalendar',
						'categories',
						'category'
				];
				foreach ($needlesViews as $tryView) {
					$genericViewSefLink = $helperRouteClass::getViewRoute($tryView, 0);
					if($genericViewSefLink) {
						// Parse the query string
						$queryParams = [];
						parse_str(parse_url($genericViewSefLink, PHP_URL_QUERY), $queryParams);
						
						// Extract the 'Itemid' parameter
						$extractedItemid = isset($queryParams['Itemid']) ? $queryParams['Itemid'] : null;
						if($extractedItemid) {
							$seflink = \JMapRoute::_ ($helperRouteClass::$classMethod($elm->id, $elm->catid, $extractedItemid));
							break;
						}
					}
				}
			}
		}
	break;

	case 'category':
		$classMethod = 'getCategoryRoute';
		$seflink = \JMapRoute::_ ($helperRouteClass::$classMethod($elm->id));
		
		// Fallback for not routed category menu item
		if(stripos($seflink, '/component/') !== false) {
			$needlesViews = [
					'upcomingevents',
					'calendar',
					'fullcalendar',
					'categories',
					'category'
			];
			foreach ($needlesViews as $tryView) {
				$genericViewSefLink = $helperRouteClass::getViewRoute($tryView, 0);
				if($genericViewSefLink) {
					// Parse the query string
					$queryParams = [];
					parse_str(parse_url($genericViewSefLink, PHP_URL_QUERY), $queryParams);
					
					// Extract the 'Itemid' parameter
					$extractedItemid = isset($queryParams['Itemid']) ? $queryParams['Itemid'] : null;
					if($extractedItemid) {
						$seflink = \JMapRoute::_ ($helperRouteClass::$classMethod($elm->id, $extractedItemid));
						break;
					}
				}
			}
		}
	break;
}

