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

// Adapter for Weblinks items and categories route helper
$helperRouteClass= '\\Joomla\\Component\\Weblinks\\Site\\Helper\\RouteHelper';
switch ($targetViewName) {
	case 'weblink':
		$classMethod = 'getWeblinkRoute';
		$seflink = \JMapRoute::_ ($helperRouteClass::$classMethod($elm->id, $elm->catid, $elm->language));
		break;
			
	case 'category':
		$classMethod = 'getCategoryRoute';
		$seflink = \JMapRoute::_ ($helperRouteClass::$classMethod($elm->id, $elm->language));
		break;
}	

