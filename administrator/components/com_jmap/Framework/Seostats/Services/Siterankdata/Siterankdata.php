<?php
namespace JExtstore\Component\JMap\Administrator\Framework\Seostats\Services;
/**
 *
 * @package JMAP::SEOSTATS::administrator::components::com_jmap
 * @subpackage seostats
 * @subpackage services
 * @subpackage siterankdata
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use JExtstore\Component\JMap\Administrator\Framework\Seostats\Services\Base as SeostatsServicesBase;
use JExtstore\Component\JMap\Administrator\Framework\Seostats\Helper\Url as SeostatsHelperUrl;
use JExtstore\Component\JMap\Administrator\Framework\Seostats\Services;
/**
 * Siterankdata stats service
 *
 * @package JMAP::SEOSTATS::administrator::components::com_jmap
 * @subpackage seostats
 * @subpackage services
 * @subpackage siterankdata
 * @since 4.6.2
 */
class Siterankdata extends SeostatsServicesBase {
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
		$html = static::_getSiterankPage ( $url );
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
	protected static function _getSiterankPage($url) {
		$domain = SeostatsHelperUrl::parseHost ( $url );
		$dataUrl = sprintf ( Services::$SITERANKDATA_SITEINFO_URL, $domain );
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
				"//h1[contains(@class,'text-success')]"
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
				"//div[@id='data-blocks']/div/div/div[@class='panel-body list']/div/div[1]/h3"
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
				"//div[@id='data-blocks']/div/div/div[@class='panel-body list']/div/div[2]/h3"
		);
	
		return static::parseDomByXpathsToIntegerWithoutTags ( $xpath, $xpathQueryList );
	}
	
	/**
	 * Get yearly visitors
	 *
	 * @access public
	 * @static
	 * @return int
	 */
	public static function getYearlyVisitors($url = false) {
		$xpath = self::_getXPath ( $url );
	
		$xpathQueryList = array (
				"//div[@id='data-blocks']/div/div/div[@class='panel-body list']/div/div[3]/h3"
		);
	
		return static::parseDomByXpathsToIntegerWithoutTags ( $xpath, $xpathQueryList );
	}
	
	/**
	 * Get website screen
	 *
	 * @access public
	 * @static
	 * @return sttring
	 */
	public static function getWebsiteScreen($url = false) {
		$imgNode = '';
		$xpath = self::_getXPath ( $url );
	
		$xpathQueryList = array (
				"//div[@class='panel-body']//img[contains(@class,'screen')]"
		);
	
		$nodes = static::parseDomByXpaths ( $xpath, $xpathQueryList );
		
		if($nodes) {
			$dom = self::_getDOMObject();
			
			$originalNode = $nodes->item(0);
			
			$src = $originalNode->getAttribute('src');
			$absoluteSrc = 'https://siterankdata.com' . $src;
			$originalNode->setAttribute('src', $absoluteSrc);
			
			$imgNode = $dom->saveHTML($originalNode);
		}
		
		return $imgNode;
	}
	
	/**
	 * Get website competitors
	 *
	 * @access public
	 * @static
	 * @return array
	 */
	public static function getCompetitors($url = false) {
		$xpath = self::_getXPath ( $url );
	
		$xpathQueryList = array (
				"//div[@class='row']//ul[@class='list-group']/li/a"
		);
	
		return static::parseDomByXpathsGetObjectArray ( $xpath, $xpathQueryList, 'competitor' );
	}
}
