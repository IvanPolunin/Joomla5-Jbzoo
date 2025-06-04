<?php
namespace JExtstore\Component\JMap\Administrator\Framework\Seostats\Services;
/**
 *
 * @package JMAP::SEOSTATS::administrator::components::com_jmap
 * @subpackage seostats
 * @subpackage services
 * @subpackage hypestat
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use JExtstore\Component\JMap\Administrator\Framework\Seostats\Services\Base as SeostatsServicesBase;
use JExtstore\Component\JMap\Administrator\Framework\Seostats\Helper\Url as SeostatsHelperUrl;
use JExtstore\Component\JMap\Administrator\Framework\Seostats\Services;

/**
 * Hypestat stats service
 *
 * @package JMAP::SEOSTATS::administrator::components::com_jmap
 * @subpackage seostats
 * @subpackage services
 * @subpackage hypestat
 * @since 4.6.2
 */
class Hypestat extends SeostatsServicesBase {
	/**
	 * @access protected
	 * @static
	 * @return DOMXPath
	 */
	protected static function _getXPath($url) {
		$url = parent::getUrl ( $url );
		if (stripos(parent::getLastLoadedUrl (), $url) !== false && self::$_xpath) {
			return self::$_xpath;
		}
		$html = static::_getHypestatPage ( $url );
		$doc = parent::_getDOMDocument ( $html );
		$xpath = parent::_getDOMXPath ( $doc );
	
		self::$_xpath = $xpath;
	
		return $xpath;
	}
	
	/**
	 * @access protected
	 * @static
	 * @return string
	 */
	protected static function _getHypestatPage($url) {
		$domain = SeostatsHelperUrl::parseHost ( $url );
		$dataUrl = sprintf ( Services::$HYPESTAT_SITEINFO_URL, $domain );
		$html = static::_getPage ( $dataUrl );
		return $html;
	}
	
	/**
	 * Get the website rank
	 *
	 * @access public
	 * @static
	 * @return int
	 */
	public static function getGlobalRank($url = false) {
		$xpath = self::_getXPath ( $url );
	
		$xpathQueryList = array (
				"//*[@id='traffic']/dl[2]/dd[7]"
		);
	
		return static::parseDomByXpathsToIntegerWithoutTags ( $xpath, $xpathQueryList );
	}
	
	/**
	 * Get daily visitors
	 *
	 * @access public
	 * @static
	 * @return int
	 */
	public static function getDailyVisitors($url = false) {
		$xpath = self::_getXPath ( $url );
	
		$xpathQueryList = array (
				"//dl[contains(@class,'traffic_report')]/dd[1]"
		);
	
		return static::parseDomByXpathsToIntegerWithoutTags ( $xpath, $xpathQueryList );
	}
	
	/**
	 * Get monthly visitors
	 *
	 * @access public
	 * @static
	 * @return int
	 */
	public static function getMonthlyVisitors($url = false) {
		$xpath = self::_getXPath ( $url );
	
		$xpathQueryList = array (
				"//dl[contains(@class,'traffic_report')]/dd[2]"
		);
	
		return static::parseDomByXpathsToIntegerWithoutTags ( $xpath, $xpathQueryList );
	}
	
	/**
	 * Get pages per visit
	 *
	 * @access public
	 * @static
	 * @return int
	 */
	public static function getPagesPerVisit($url = false) {
		$xpath = self::_getXPath ( $url );
	
		$xpathQueryList = array (
				"//dl[contains(@class,'traffic_report')]/dd[3]"
		);
	
		return static::parseDomByXpathsToIntegerWithoutTags ( $xpath, $xpathQueryList );
	}
	
	/**
	 * Get pages per visit
	 *
	 * @access public
	 * @static
	 * @return int
	 */
	public static function getDailyPageViews($url = false) {
		$xpath = self::_getXPath ( $url );
	
		$xpathQueryList = array (
				"//dl[contains(@class,'traffic_report')]/dd[4]"
		);
	
		return static::parseDomByXpathsToIntegerWithoutTags ( $xpath, $xpathQueryList );
	}
	
	/**
	 * Get backlinks
	 *
	 * @access public
	 * @static
	 * @return int
	 */
	public static function getBacklinks($url = false) {
		$xpath = self::_getXPath ( $url );
	
		$xpathQueryList = array (
				"//*[@id='backlinks_m']/dl/dd[1]"
		);
	
		return static::parseDomByXpathsGetValue ( $xpath, $xpathQueryList );
	}
	
	/**
	 * Get website report text
	 *
	 * @access public
	 * @static
	 * @return int
	 */
	public static function getReportText($url = false) {
		$xpath = self::_getXPath ( $url );
	
		$xpathQueryList = array (
				"//div[@class='website_report_text']"
		);
	
		return static::parseDomByXpathsGetValue ( $xpath, $xpathQueryList );
	}
	
	/**
	 * Get website screen
	 *
	 * @access public
	 * @static
	 * @return int
	 */
	public static function getWebsiteScreen($url = false) {
		$imgNode = '';
		$xpath = self::_getXPath ( $url );
	
		$xpathQueryList = array (
				"//div[@class='website_about']//img[contains(@class,'article_logo')]"
		);
	
		$nodes = static::parseDomByXpaths ( $xpath, $xpathQueryList );
		
		if($nodes) {
			$dom = self::_getDOMObject();
			
			$originalNode = $nodes->item(0);
			$imgNode = $dom->saveHTML($originalNode);
		}
		
		return $imgNode;
	}
}
