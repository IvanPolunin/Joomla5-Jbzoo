<?php
namespace JExtstore\Component\JMap\Administrator\Framework\Seostats\Services;
/**
 *
 * @package JMAP::SEOSTATS::administrator::components::com_jmap
 * @subpackage seostats
 * @subpackage services
 * @subpackage xranks
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\String\StringHelper as JString;
use Joomla\CMS\Language\Text;
use JExtstore\Component\JMap\Administrator\Framework\Seostats\Services\Base as SeostatsServicesBase;
use JExtstore\Component\JMap\Administrator\Framework\Seostats\Helper\Url as SeostatsHelperUrl;
use JExtstore\Component\JMap\Administrator\Framework\Seostats\Services;

/**
 * XRanks stats service
 *
 * @package JMAP::SEOSTATS::administrator::components::com_jmap
 * @subpackage seostats
 * @subpackage services
 * @subpackage xranks
 * @since 3.0
 */
class Xranks extends SeostatsServicesBase {
	/**
	 * Used for cache
	 * 
	 * @access protected 
	 * @static
	 * @var DOMXPath
	 */
	protected static $_xpath = false;
	
	/**
	 * @access protected
	 * @static
	 * @var string
	 */
	protected static $_rankKeys = array (
			'1d' => 0,
			'7d' => 0,
			'1m' => 0,
			'3m' => 0 
	);
	
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
	
		$html = static::_getStatsPage ( $url );
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
	protected static function _getStatsPage($url) {
		$domain = SeostatsHelperUrl::parseHost ( $url );
		$dataUrl = sprintf ( Services::$XRANKS_SITEINFO_URL, $domain );
		$html = static::_getPage ( $dataUrl );
		return $html;
	}
	
	/**
	 * Get metric
	 * 
	 * @access public
	 * @static  
	 * @return int
	 */
	public static function getGlobalRank($url = false) {
		$xpath = self::_getXPath ( $url );
		
		$xpathQueryList = array (
				"//p[contains(@class,'big-data')]"
		);
		
		$stringRankValue = trim(static::parseDomByXpathsGetValue( $xpath, $xpathQueryList ));
		$stringRankValue = trim(JString::str_ireplace('#', '', $stringRankValue));
		
		if (!$stringRankValue) {
			return parent::noDataDefaultValue ();
		}
		
		return $stringRankValue;
	}
	
	/**
	 * Get metric
	 * 
	 * @access public
	 * @static  
	 * @return int
	 */
	public static function getMetricProviders($url = false, $index = 4) {
		$xpath = self::_getXPath ( $url );
		
		$xpathQueryList = array (
				"//div[@id='semrush']//div[contains(@class,'col-12')][2]//span[contains(@class,'font-36')]"
		);
		
		$stringRankValue = trim(static::parseDomByXpathsGetValue( $xpath, $xpathQueryList ));
		
		if (!$stringRankValue) {
			return parent::noDataDefaultValue ();
		}
		
		return $stringRankValue;
	}
	
	/**
	 * Get metric
	 *
	 * @access public
	 * @static
	 * @return int
	 */
	public static function getMozMetrics($url = false, $index = 1) {
		$xpath = self::_getXPath ( $url );
		
		$xpathQueryList = array (
				"//div[@id='moz']//div[contains(@class,'stretch-card')][1]//div[contains(@class,'col-4')][$index]//*[local-name() = 'text']"
		);
		
		$intTraffic = trim(static::parseDomByXpathsGetValue( $xpath, $xpathQueryList ));
		
		if (!$intTraffic) {
			return parent::noDataDefaultValue ();
		}
		
		return $intTraffic;
	}
	
	/**
	 * Get metric
	 *
	 * @access public
	 * @static
	 * @return int
	 */
	public static function getOrganicSearchTraffic($url = false) {
		$xpath = self::_getXPath ( $url );
		
		$xpathQueryList = array (
				"//div[@id='semrush']//div[contains(@class,'col-12')][1]//span[contains(@class,'font-36')]"
		);
		
		$intTraffic = trim(static::parseDomByXpathsGetValue( $xpath, $xpathQueryList ));
		
		if (!$intTraffic) {
			return parent::noDataDefaultValue ();
		}
		
		return $intTraffic;
	}
	
	/**
	 * Get metric
	 *
	 * @access public
	 * @static
	 * @return int
	 */
	public static function getOrganicTraffic($url = false) {
		$xpath = self::_getXPath ( $url );
		
		$xpathQueryList = array (
				"//*[@id='alexa']/div/div/div[1]/div/div[3]/div/div/div[2]/span"
		);
		
		$intTraffic = trim(static::parseDomByXpathsGetValue( $xpath, $xpathQueryList ));
		
		if (!$intTraffic) {
			return parent::noDataDefaultValue ();
		}
		
		return $intTraffic;
	}
	
	/**
	 * Get metric
	 *
	 * @access public
	 * @static
	 * @return int
	 */
	public static function getUsersPerDay($url = false) {
		$xpath = self::_getXPath ( $url );
		
		$xpathQueryList = array (
				"//*[@id='alexa']/div/div/div[2]/div/div[1]/div/p[1]/span"
		);
		
		$intTraffic = trim(static::parseDomByXpathsGetValue( $xpath, $xpathQueryList ));
		
		if (!$intTraffic) {
			return parent::noDataDefaultValue ();
		}
		
		return $intTraffic;
	}
	
	/**
	 * Get metric
	 *
	 * @access public
	 * @static
	 * @return int
	 */
	public static function getpageviewsPerDay($url = false) {
		$xpath = self::_getXPath ( $url );
		
		$xpathQueryList = array (
				"//*[@id='alexa']/div/div/div[2]/div/div[2]/div/p[1]/span"
		);
		
		$intTraffic = trim(static::parseDomByXpathsGetValue( $xpath, $xpathQueryList ));
		
		if (!$intTraffic) {
			return parent::noDataDefaultValue ();
		}
		
		return $intTraffic;
	}
	
	/**
	 * Get metric
	 *
	 * @access public
	 * @static
	 * @return int
	 */
	public static function getSemrushRank($url = false) {
		$xpath = self::_getXPath ( $url );
		
		$xpathQueryList = array (
				"//div[@id='semrush']//table[contains(@class,'table-semrush')]//tr[1]//span[contains(@class,'label-info')]"
		);
		
		$intTraffic = trim(static::parseDomByXpathsGetValue( $xpath, $xpathQueryList ));
		
		if (!$intTraffic) {
			return parent::noDataDefaultValue ();
		}
		
		return $intTraffic;
	}
	
	/**
	 * Get metric
	 *
	 * @access public
	 * @static
	 * @return int
	 */
	public static function getNumKeywords($url = false) {
		$xpath = self::_getXPath ( $url );
		
		$xpathQueryList = array (
				"//div[@id='semrush']//table[contains(@class,'table-semrush')]//tr[2]//span[contains(@class,'label-info')]"
		);
		
		$numKeywords = trim(static::parseDomByXpathsGetValue( $xpath, $xpathQueryList ));
		
		if (!$numKeywords) {
			return parent::noDataDefaultValue ();
		}
		
		return $numKeywords;
	}
	
	/**
	 * Get competitors list
	 *
	 * @access public
	 * @static
	 * @return Object
	 */
	public static function getCompetitors($url = false) {
		$xpath = self::_getXPath ( $url );
		
		$queryList = array (
				"//div[contains(@class,'listing-card')]//a[contains(@class,'font-weight-500')]"
		);
		
		return static::parseDomByXpathsGetObjectArray ( $xpath, $queryList, 'Dn' );
	}
	
	/**
	 * Get website screen
	 *
	 * @access public
	 * @static
	 * @return string
	 */
	public static function getWebsiteScreen($url = false) {
		$imgNode = '';
		$xpath = self::_getXPath ( $url );
		
		$xpathQueryList = array (
				"//div[@class='card']//div[@class='image_container']//img"
		);
		
		$nodes = static::parseDomByXpaths ( $xpath, $xpathQueryList );
		
		if($nodes) {
			$dom = self::_getDOMObject();
			
			$originalNode = $nodes->item(0);
			$originalNode->removeAttribute('class');
			$originalNode->removeAttribute('rel');
			$originalNode->removeAttribute('src');
			$currentDataSrc = $originalNode->getAttribute('data-src');
			$originalNode->setAttribute('src', $currentDataSrc);
			$originalNode->removeAttribute('data-src');
			$originalNode->setAttribute('class', 'xranks-screenshot');
			$imgNode = $dom->saveHTML($originalNode);
		}
		
		return $imgNode;
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
				"//div[@class='content-wrapper']/div/div[@class='card'][1]//p[contains(@class,'font-weight-300')]"
		);
		
		return trim(static::parseDomByXpathsGetValue ( $xpath, $xpathQueryList ));
	}
}
