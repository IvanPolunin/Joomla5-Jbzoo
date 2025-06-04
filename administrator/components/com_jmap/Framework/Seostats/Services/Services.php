<?php
namespace JExtstore\Component\JMap\Administrator\Framework\Seostats;
/**
 *
 * @package JMAP::SEOSTATS::administrator::components::com_jmap
 * @subpackage seostats
 * @subpackage services
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

/**
 * Configuration constants for the SEOSTATS package
 *
 * @package JMAP::SEOSTATS::administrator::components::com_jmap
 * @subpackage seostats
 * @subpackage services
 * @since 3.3
 */
class Services {
	// Statscrop URL
	public static $STATSCROP_SITEINFO_URL = 'https://www.statscrop.com/www/%s';
	
	// XRanks URL
	public static $XRANKS_SITEINFO_URL = 'https://xranks.com/%s';

	// Siterankdata URL
	public static $SITERANKDATA_SITEINFO_URL = 'https://siterankdata.com/%s';
	
	// Hypestat URL
	public static $HYPESTAT_SITEINFO_URL = 'https://hypestat.com/info/%s';
	
	// Zigstat URL
	public static $ZIGSTAT_SITEINFO_URL = 'https://%s.websiteoutlook.com';

	// The default top level domain ending to use to query Google.
	const GOOGLE_TLD = 'com';
	
	// SEMrush API Endpoints.
	const SEMRUSH_BE_URL = 'http://%s.backend.semrush.com/?action=report&type=%s&domain=%s';
	const SEMRUSH_GRAPH_URL = 'https://www.semrush.com/archive/graphs.php?domain=%s&db=%s&type=%s&w=%s&h=%s&lc=%s&dc=%s&l=%s';
	const SEMRUSH_WIDGET_URL = 'http://widget.semrush.com/widget.php?action=report&type=%s&db=%s&domain=%s';
	const SEMRUSH_DB = 'us';
	
	// The HTTP header value for the 'Accept-Language' attribute.
	const HTTP_HEADER_ACCEPT_LANGUAGE = 'en-US;q=0.8,en;q=0.3';
	
	// For curl instances: Whether to allow Google to store cookies, or not.
	const ALLOW_GOOGLE_COOKIES = 0;
}