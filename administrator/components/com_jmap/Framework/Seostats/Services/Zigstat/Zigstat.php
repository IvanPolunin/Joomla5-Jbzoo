<?php
namespace JExtstore\Component\JMap\Administrator\Framework\Seostats\Services;
/**
 *
 * @package JMAP::SEOSTATS::administrator::components::com_jmap
 * @subpackage seostats
 * @subpackage services
 * @subpackage zigstat
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use JExtstore\Component\JMap\Administrator\Framework\Seostats\Services\Base as SeostatsServicesBase;
use JExtstore\Component\JMap\Administrator\Framework\Seostats\Helper\Url as SeostatsHelperUrl;
use JExtstore\Component\JMap\Administrator\Framework\Seostats\Services;

/**
 * Zigstat stats service
 *
 * @package JMAP::SEOSTATS::administrator::components::com_jmap
 * @subpackage seostats
 * @subpackage services
 * @subpackage zigstat
 * @since 4.6.2
 */
class Zigstat extends SeostatsServicesBase {
	/**
	 * HTML source code scraped
	 * 
	 * @access public
	 * @var string
	 */
	protected static $htmlSource;
	
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
		$html = static::_getZigstatPage ( $url );
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
	protected static function _getZigstatPage($url) {
		$domain = SeostatsHelperUrl::parseHost ( $url );
		$dataUrl = sprintf ( Services::$ZIGSTAT_SITEINFO_URL, $domain );
		$html = static::_getPage ( $dataUrl );
		self::$htmlSource = $html;
		return $html;
	}
	
	/**
	 * Get the website rank
	 *
	 * @access public
	 * @static
	 * @return int
	 */
	public static function getMozRank($url = false) {
		$xpath = self::_getXPath ( $url );
		
		$xpathQueryList = array (
				"//div[@id='semrush']//table//tr[1]//td[2]"
		);
		
		return static::parseDomByXpathsToIntegerWithoutTags ( $xpath, $xpathQueryList );
	}
	
	/**
	 * Get the website Alexa rank
	 *
	 * @access public
	 * @static
	 * @return int
	 */
	public static function getDomainAuth($url = false) {
		$xpath = self::_getXPath ( $url );
		
		$xpathQueryList = array (
				"//*[@id='basic']/div[2]/div[2]/table/tbody/tr[9]/td[2]"
		);
		
		$staticValue = static::parseDomByXpathsGetValue ( $xpath, $xpathQueryList );
		
		if(!$staticValue) {
			return Text::_ ( 'COM_JMAP_NA' );
		}
		
		return $staticValue;
	}
	
	/**
	 * Get the website Alexa rank
	 *
	 * @access public
	 * @static
	 * @return int
	 */
	public static function getPageViews($url = false) {
		$xpath = self::_getXPath ( $url );
		
		$xpathQueryList = array (
				"//*[@id='basic']/div[2]/div[2]/table/tbody/tr[10]/td[2]"
		);
		
		$staticValue = static::parseDomByXpathsGetValue ( $xpath, $xpathQueryList );
		
		if(!$staticValue) {
			return Text::_ ( 'COM_JMAP_NA' );
		}
		
		return $staticValue;
	}
	
	/**
	 * Get the website Alexa rank
	 *
	 * @access public
	 * @static
	 * @return int
	 */
	public static function getSerpKeywords($url = false) {
		$xpath = self::_getXPath ( $url );
		
		$xpathQueryList = array (
				"//*[@id='semrush']/div[2]/table/tbody/tr[2]/td[2]"
		);
		
		return static::parseDomByXpathsToIntegerWithoutTags ( $xpath, $xpathQueryList );
	}
	
	/**
	 * Get total backlinks
	 *
	 * @access public
	 * @static
	 * @return int
	 */
	public static function getBacklinks($url = false) {
		$xpath = self::_getXPath ( $url );
	
		$xpathQueryList = array (
				"//*[@id='backlink']/div[2]/table/tbody/tr[1]/td[2]"
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
				"//*[@id='semrush']/div[2]/table/tbody/tr[3]/td[2]"
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
	public static function getFollowBacklinks($url = false) {
		$xpath = self::_getXPath ( $url );

		$xpathQueryList = array (
				"//*[@id='backlink']/div[2]/table/tbody/tr[6]/td[2]"
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
	public static function getNoFollowBacklinks($url = false) {
		$xpath = self::_getXPath ( $url );
		
		$xpathQueryList = array (
				"//*[@id='backlink']/div[2]/table/tbody/tr[7]/td[2]"
		);
		
		return static::parseDomByXpathsToIntegerWithoutTags ( $xpath, $xpathQueryList );
	}
	
	/**
	 * Get the list of available backlink websites if available
	 *
	 * @access public
	 * @static
	 * @return array
	 */
	public static function getBacklinksList($url = false) {
		$xpath = self::_getXPath ( $url );

		$xpathQueryList = array (
				"(//div[@class='col-md-12'])[11]//table//tr[position()>1]//td[1]"
		);

		return static::parseDomByXpathsGetObjectArray ( $xpath, $xpathQueryList, 'backlink' );
	}
	
	/**
	 * Get website report text
	 *
	 * @access public
	 * @static
	 * @return string
	 */
	public static function getReportText($url = false) {
		$xpath = self::_getXPath ( $url );
	
		$xpathQueryList = array (
				"//*[@id='website']/div[2]/dl/dd[1]"
		);
	
		return static::parseDomByXpathsGetValue ( $xpath, $xpathQueryList );
	}
}
