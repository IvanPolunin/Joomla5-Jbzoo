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

// Adapter for Contact items and categories route helper
$helperRouteClass= 'OSMembershipHelperRoute';
switch ($targetViewName) {
	case 'plan':
		$classMethod = 'getPlanMenuId';
		$planItemid = $helperRouteClass::$classMethod($elm->id, $elm->catid);
		if(!$planItemid) {
			// Fallback on the plans view
			$classMethod = 'findView';
			$planItemid = $helperRouteClass::$classMethod('plans', 0);
		}
		if(!$planItemid) {
			// Fallback on the categories view
			$classMethod = 'findView';
			$planItemid = $helperRouteClass::$classMethod('categories', 0);
		}
		
		$seflink = \JMapRoute::_ ('index.php?option=com_osmembership&view=plan&id=' . $elm->id . '&catid=' . $elm->catid . '&Itemid=' . $planItemid);
		break;
		
	case 'plans':
		$classMethod = 'getCategoryRoute';
		$seflink = \JMapRoute::_ ($helperRouteClass::$classMethod($elm->id));
		
		if(stripos($seflink, 'component/osmembership') !== false) {
			// Fallback on the categories view
			$classMethod = 'findView';
			$categoryItemid = $helperRouteClass::$classMethod('categories', 0);
			$seflink = \JMapRoute::_ ('index.php?option=com_osmembership&view=plans&id=' . $elm->id . '&Itemid=' . $categoryItemid);
		}
		
		break;
}



