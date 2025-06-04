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
use Joomla\CMS\Factory;

// Adapter for Easydiscuss posts route helper
if(class_exists('EBR')) {
	$helperRouteClass= 'EBR';
} else {
	return;
}

// Use the component routing handler if it exists
$path = JPATH_SITE . '/components/com_easyblog/router.php';
// Use the custom routing handler if it exists
if (!isset($GLOBALS['jmapEBRouter']) && file_exists($path)) {
	require_once $path;
	$GLOBALS['jmapEBRouter'] = true;
}

// Get DB Object
$jDb = Factory::getContainer()->get('DatabaseDriver');

switch ($targetViewName) {
	case 'entry':
		// Set empty in all cases
		$itemId = null;
		$directPostMenuItemid = null;
		$createdbyAuthorItemId = null;
		
		// Buffered itemid already resolved for this category
		if(isset($GLOBALS['jmapEBStaticCatsBuffer'][$elm->jsitemap_category_id])) {
			$itemId = $GLOBALS['jmapEBStaticCatsBuffer'][$elm->jsitemap_category_id];
		}
		
		// Get all Easyblog 'latest' menu items
		if(!isset($GLOBALS['jmapEBStaticMenusBuffer'])) {
			$query	= 'SELECT ' . $jDb->quoteName('id') . 
					  ' FROM ' . $jDb->quoteName( '#__menu' ) .
					  ' WHERE ' .  $jDb->quoteName( 'link' ) . ' = ' .  $jDb->quote( 'index.php?option=com_easyblog&view=latest' ) .
					  ' AND ' .  $jDb->quoteName( 'client_id' ) . ' = 0' .
					  ' AND ' .  $jDb->quoteName( 'published' ) . ' = 1';
			$jDb->setQuery( $query );
			$topLatestMenuItemids = $jDb->loadColumn();
			$GLOBALS['jmapEBStaticMenusBuffer'] = $topLatestMenuItemids;
		}
		
		// Check always if we have a direct post id to menu item and give highest priority
		$classMethod = 'getItemIdByEntry';
		$directPostMenuItemid = $helperRouteClass::$classMethod ( $elm->id );
		if( $directPostMenuItemid ){
			if(!in_array($directPostMenuItemid, $GLOBALS['jmapEBStaticMenusBuffer'])) {
				$itemId = $directPostMenuItemid;
			} else {
				$directPostMenuItemid = null;
			}
		}
		
		// Check always if we have a blogger author information and give priority to the author menu item
		if (! $itemId && isset($elm->created_by)) {
			$classMethod = 'getItemIdByBlogger';
			$createdbyAuthorItemId = $helperRouteClass::$classMethod ( $elm->created_by );
			if($createdbyAuthorItemId) {
				$itemId = $createdbyAuthorItemId;
			}
		}
		
		// Check if we have a direct category id to menu item
		if (! $itemId) {
			$classMethod = 'getItemIdByCategories';
			$itemId = $helperRouteClass::$classMethod ( $elm->jsitemap_category_id );
		}
		
		// Check if we have a parent category id to menu item
		if (! $itemId) {
			$parentCategories = array();
			$query	= 'SELECT parent.id' . 
					  ' FROM ' . $jDb->quoteName( '#__easyblog_category' ) . ' AS ' . $jDb->quoteName('node') . ', ' .
					  $jDb->quoteName( '#__easyblog_category' ) . ' AS ' . $jDb->quoteName('parent') . 
					  ' WHERE node.lft BETWEEN parent.lft AND parent.rgt' .
					  ' AND node.id = ' . $jDb->quote( $elm->jsitemap_category_id ) .
					  ' AND parent.id != ' . $jDb->quote( $elm->jsitemap_category_id ) .
					  ' ORDER BY parent.lft';
			$jDb->setQuery( $query );
			$parentCategories = $jDb->loadColumn();
			
			// Found parent categories?
			if(!empty($parentCategories)) {
				foreach ($parentCategories as $parentCat) {
					$itemId = $helperRouteClass::$classMethod ( $parentCat );
					if($itemId) {
						break;
					}
				}
			}
		}
		
		// Get all Easyblog 'categories' menu items if there is not the view=latest menu item
		if(!$itemId && !$GLOBALS['jmapEBStaticMenusBuffer']) {
			if(isset($GLOBALS['jmapEBStaticAllCategoriesItemMenusBuffer']) && $GLOBALS['jmapEBStaticAllCategoriesItemMenusBuffer']) {
				$itemId = $GLOBALS['jmapEBStaticAllCategoriesItemMenusBuffer'];
			}
			
			if(!isset($GLOBALS['jmapEBStaticAllCategoriesItemMenusBuffer'])) {
				$query	= 'SELECT ' . $jDb->quoteName('id') . 
						  ' FROM ' . $jDb->quoteName( '#__menu' ) .
						  ' WHERE ' .  $jDb->quoteName( 'link' ) . ' = ' .  $jDb->quote( 'index.php?option=com_easyblog&view=categories' ) .
						  ' AND ' .  $jDb->quoteName( 'client_id' ) . ' = 0' .
						  ' AND ' .  $jDb->quoteName( 'published' ) . ' = 1';
				$jDb->setQuery( $query );
				$itemId = $jDb->loadResult();
				$GLOBALS['jmapEBStaticAllCategoriesItemMenusBuffer'] = $itemId ? $itemId : false;
			}
		}
		
		// As a last resort fallback to the first top latest menu item
		if(!$itemId && $GLOBALS['jmapEBStaticMenusBuffer']) {
			$itemId = $GLOBALS['jmapEBStaticMenusBuffer'][0];
		}
		
		if ($itemId) {
			// Assign only if a real category resolved Itemid
			if(!$directPostMenuItemid && !$createdbyAuthorItemId) {
				$GLOBALS['jmapEBStaticCatsBuffer'][$elm->jsitemap_category_id] = $itemId;
			}
			$itemId = '&Itemid=' . $itemId;
		}

		// Final SEF link routing
		if(!$directPostMenuItemid) {
			$seflink = \JMapRoute::_ ('index.php?option=com_easyblog&view=entry&id=' . $elm->id . $itemId);
		} else {
			$seflink = \JMapRoute::_ ('index.php?Itemid=' . $directPostMenuItemid);
		}

	break;

	case 'categories':
		if(strpos($additionalQueryStringParams, 'layout=listings')) {
			// Set empty in all cases
			$itemId = null;
			
			// Get all Easyblog 'latest' menu items
			if(!isset($GLOBALS['jmapEBStaticMenusBuffer'])) {
				$query	= 'SELECT ' . $jDb->quoteName('id') .
						  ' FROM ' . $jDb->quoteName( '#__menu' ) .
						  ' WHERE ' .  $jDb->quoteName( 'link' ) . ' = ' .  $jDb->quote( 'index.php?option=com_easyblog&view=latest' ) .
						  ' AND ' .  $jDb->quoteName( 'client_id' ) . ' = 0' .
						  ' AND ' .  $jDb->quoteName( 'published' ) . ' = 1';
				$jDb->setQuery( $query );
				$topLatestMenuItemids = $jDb->loadColumn();
				$GLOBALS['jmapEBStaticMenusBuffer'] = $topLatestMenuItemids;
			}
			
			$classMethod = 'getItemIdByCategories';
			$itemId = $helperRouteClass::$classMethod ( $elm->id );
			
			// Check if we have a parent category id to menu item
			if (! $itemId) {
				$parentCategories = array();
				$query	= 'SELECT parent.id' .
						  ' FROM ' . $jDb->quoteName( '#__easyblog_category' ) . ' AS ' . $jDb->quoteName('node') . ', ' .
						  $jDb->quoteName( '#__easyblog_category' ) . ' AS ' . $jDb->quoteName('parent') .
						  ' WHERE node.lft BETWEEN parent.lft AND parent.rgt' .
						  ' AND node.id = ' . $jDb->quote( $elm->id ) .
						  ' AND parent.id != ' . $jDb->quote( $elm->id ) .
						  ' ORDER BY parent.lft';
			    $jDb->setQuery( $query );
			    $parentCategories = $jDb->loadColumn();
							
				// Found parent categories?
				if(!empty($parentCategories)) {
					foreach ($parentCategories as $parentCat) {
						$itemId = $helperRouteClass::$classMethod ( $parentCat );
						if($itemId) {
							break;
						}
					}
				}
			}
			
			// Get all Easyblog 'categories' menu items
			if(!$itemId) {
				if(isset($GLOBALS['jmapEBStaticAllCategoriesMenusBuffer']) && $GLOBALS['jmapEBStaticAllCategoriesMenusBuffer']) {
					$itemId = $GLOBALS['jmapEBStaticAllCategoriesMenusBuffer'];
				}
				
				if(!isset($GLOBALS['jmapEBStaticAllCategoriesMenusBuffer'])) {
					$query	= 'SELECT ' . $jDb->quoteName('id') . 
							  ' FROM ' . $jDb->quoteName( '#__menu' ) .
							  ' WHERE ' .  $jDb->quoteName( 'link' ) . ' = ' .  $jDb->quote( 'index.php?option=com_easyblog&view=categories' ) .
							  ' AND ' .  $jDb->quoteName( 'client_id' ) . ' = 0' .
							  ' AND ' .  $jDb->quoteName( 'published' ) . ' = 1';
					$jDb->setQuery( $query );
					$itemId = $jDb->loadResult();
					$GLOBALS['jmapEBStaticAllCategoriesMenusBuffer'] = $itemId ? $itemId : false;
				}
			}
			
			// As a last resort fallback to the first top latest menu item
			if(!$itemId && $GLOBALS['jmapEBStaticMenusBuffer']) {
				$itemId = $GLOBALS['jmapEBStaticMenusBuffer'][0];
			}
			
			if ($itemId) {
				// Assign only if a real category resolved Itemid
				$itemId = '&Itemid=' . $itemId;
			}
			
			// Final SEF link routing
			$seflink = \JMapRoute::_ ('index.php?option=com_easyblog&view=categories&layout=listings&id=' . $elm->id . $itemId);
		}
		
	break;
	
	case 'blogger':
		// Set empty in all cases
		$itemId = null;
		$classMethod = 'getItemIdByBlogger';
		$itemId = $helperRouteClass::$classMethod ( $elm->id );
		
		// Fallback to the menu item param
		if(!$itemId) {
			$itemId = $sefItemid;
		}
		
		if ($itemId) {
			// Assign only if a real category resolved Itemid
			$itemId = '&Itemid=' . $itemId;
		}
		
		// Final SEF link routing
		$seflink = \JMapRoute::_ ('index.php?option=com_easyblog&view=blogger&layout=listings&id=' . $elm->id . $itemId);
	break;
}