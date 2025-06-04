<?php
namespace JExtstore\Component\JMap\Administrator\Framework\Seostats\Services;
/**
 *
 * @package JMAP::SEOSTATS::administrator::components::com_jmap
 * @subpackage seostats
 * @subpackage services
 * @subpackage statscrop
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\String\StringHelper as JString;
use Joomla\CMS\Language\Text;
use JExtstore\Component\JMap\Administrator\Framework\Seostats\Helper\Httpcurl;
use JExtstore\Component\JMap\Administrator\Framework\Seostats\Services\Base as SeostatsServicesBase;
use JExtstore\Component\JMap\Administrator\Framework\Seostats\Helper\Url as SeostatsHelperUrl;
use JExtstore\Component\JMap\Administrator\Framework\Seostats\Services;

/**
 * Statscrop stats service
 *
 * @package JMAP::SEOSTATS::administrator::components::com_jmap
 * @subpackage seostats
 * @subpackage services
 * @subpackage statscrop
 * @since 3.0
 */
class Statscrop extends SeostatsServicesBase {
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
	 *
	 * @access protected
	 * @return HTML string
	 */
	protected static function _getAPICall($url) {
		$jsonResponse = Httpcurl::sendRequest ( $url, false, false, 0 );
		if ($jsonResponse) {
			$decodedJsonResponse = json_decode($jsonResponse);
			return $decodedJsonResponse;
		} else {
			self::noDataDefaultValue ();
		}
	}
	
	/**
	 * @access protected
	 * @static
	 * @return string
	 */
	protected static function _getStatsPage($url) {
		$domain = SeostatsHelperUrl::parseHost ( $url );
		$dataUrl = sprintf ( Services::$STATSCROP_SITEINFO_URL, $domain );
		$html = static::_getPage ( $dataUrl );
		return $html;
	}

	/**
	 * @access protected
	 * @static
	 * @return mixed nodeValue
	 */
	protected static function parseDomByXpathsGetAttribute($xpathDom, $xpathQueryList, $attributeName) {
		$nodes = static::parseDomByXpaths ( $xpathDom, $xpathQueryList );
		
		return ($nodes) ? $nodes->item ( 0 )->getAttribute($attributeName) : null;
	}
	
	/**
	 * Get the global rank
	 * 
	 * @access public
	 * @static  
	 * @return int
	 */
	public static function getGlobalRank($url = false) {
		$xpath = self::_getXPath ( $url );
		
		$xpathQueryList = array (
				"//div[@id='site-simple-charts']/div[1]//span[@class='number']"
		);
		
		$stringRankValue = static::parseDomByXpathsGetAttribute ( $xpath, $xpathQueryList, 'data-endval');
		
		if (!$stringRankValue) {
			return parent::noDataDefaultValue ();
		}
		
		return $stringRankValue;
	}
	
	/**
	 * Get the daily visitors
	 *
	 * @access public
	 * @static
	 * @return int
	 */
	public static function getDailyVisitors($url = false) {
		$xpath = self::_getXPath ( $url );
		
		$xpathQueryList = array (
				"//div[@id='site-simple-charts']/div[2]//span[@class='number']"
		);
		
		$stringRankValue = static::parseDomByXpathsGetAttribute ( $xpath, $xpathQueryList, 'data-endval');
		
		if (!$stringRankValue) {
			return parent::noDataDefaultValue ();
		}
		
		return $stringRankValue;
	}
	
	/**
	 * Get the daily page views
	 *
	 * @access public
	 * @static
	 * @return int
	 */
	public static function getBounceRate($url = false) {
		$xpath = self::_getXPath ( $url );
		
		$xpathQueryList = array (
				"//div[@id='site-simple-charts']/div[3]//span[@class='number']"
		);
		
		$stringRankValue = static::parseDomByXpathsGetAttribute ( $xpath, $xpathQueryList, 'data-endval');
		
		if (!$stringRankValue) {
			return parent::noDataDefaultValue ();
		}
		
		return $stringRankValue;
	}
	
	/**
	 * Get the load time
	 *
	 * @access public
	 * @static
	 * @return int
	 */
	public static function getPageLoadtime($url = false) {
		$xpath = self::_getXPath ( $url );
		
		$xpathQueryList = array (
				"//div[@id='site-simple-charts']/div[4]//span[@class='number']"
		);
		
		$stringRankValue = static::parseDomByXpathsGetAttribute ( $xpath, $xpathQueryList, 'data-endval');
		
		if (!$stringRankValue) {
			return parent::noDataDefaultValue ();
		}
		
		return $stringRankValue . '<span class="seostats_unit_measure">s</span>';
	}
	
	/**
	 * Get the average rank over the week
	 * 
	 * @access public
	 * @static  
	 * @return int
	 */
	public static function setRankingKeys($url = false) {
		$xpath = self::_getXPath ( $url );
		$nodes = @$xpath->query ( "//*[@id='rank']/table/tr" );
		
		if (5 == $nodes->length) {
			self::$_rankKeys = array (
					'1d' => 2,
					'7d' => 3,
					'1m' => 4,
					'3m' => 5 
			);
		} else if (4 == $nodes->length) {
			self::$_rankKeys = array (
					'1d' => 0,
					'7d' => 2,
					'1m' => 3,
					'3m' => 4 
			);
		} else if (3 == $nodes->length) {
			self::$_rankKeys = array (
					'1d' => 0,
					'7d' => 0,
					'1m' => 2,
					'3m' => 3 
			);
		} else if (2 == $nodes->length) {
			self::$_rankKeys = array (
					'1d' => 0,
					'7d' => 0,
					'1m' => 0,
					'3m' => 2 
			);
		}
	}
	
	/**
	 * Get the rank by country
	 *
	 * @access public
	 * @static
	 * @return int
	 */
	public static function getCountryRank($url = false) {
		$xpath = self::_getXPath ( $url );
		$node1 = self::parseDomByXpaths ( $xpath, array (
				"//*[@id='traffic-rank-content']/div/span[2]/div[2]/span/span/h4/a",
				"//*[@id='traffic-rank-content']/div/span[2]/div[2]/span/span/h4/strong/a" 
		) );
		
		$node2 = self::parseDomByXpaths ( $xpath, array (
				"//*[@id='traffic-rank-content']/div/span[2]/div[2]/span/span/div/strong/a",
				"//*[@id='traffic-rank-content']/div/span[2]/div[2]/span/span/div/strong" 
		) );
		
		if (! is_null ( $node2 ) && $node2->item ( 0 )) {
			$rank = self::retInt ( strip_tags ( $node2->item ( 0 )->nodeValue ) );
			if ($node1->item ( 0 ) && 0 != $rank) {
				return array (
						'rank' => $rank,
						'country' => $node1->item ( 0 )->nodeValue 
				);
			}
		}
		
		return parent::noDataDefaultValue ();
	}
	
	/**
	 * Get backlinks count
	 *
	 * @access public
	 * @static
	 * @return int
	 */
	public static function getBacklinkCount($url = false) {
		$xpath = self::_getXPath ( $url );
		
		$queryList = array (
				"//section[@class='linksin']/div/span",
		);
		
		return static::parseDomByXpathsToInteger ( $xpath, $queryList );
	}
	
	/**
	 * Get internal links
	 *
	 * @access public
	 * @static
	 * @return int
	 */
	public static function getInternalLinksCount($url = false) {
		$apiEndpoint = 'https://www.statscrop.com/data/www-traffic/?ac=linking-in-out&domain=' . $url . '&hash=727fe87abe4d483bff2b9d0959e7d64cb11a8ecd&ut=1722087303&__source_origin=https%3A%2F%2Fwww.statscrop.com';
		$results = self::_getAPICall ( $apiEndpoint );
		
		return $results;
	}
	
	/**
	 * Get external links
	 *
	 * @access public
	 * @static
	 * @return int
	 */
	public static function getExternalLinksCount($url = false) {
		$apiEndpoint = 'https://www.statscrop.com/data/www-traffic/?ac=linking-in-out&domain=' . $url . '&hash=727fe87abe4d483bff2b9d0959e7d64cb11a8ecd&ut=1722087303&__source_origin=https%3A%2F%2Fwww.statscrop.com';
		$results = self::_getAPICall ( $apiEndpoint );
		
		return $results;
	}
	
	/**
	 * Get keywords list
	 *
	 * @access public
	 * @static
	 * @return Object
	 */
	public static function getKeywords($url = false) {
		$apiEndpoint = 'https://www.statscrop.com/data/www-traffic/?ac=keywords&domain=' . $url . '&hash=727fe87abe4d483bff2b9d0959e7d64cb11a8ecd&ut=1722087303&__source_origin=https%3A%2F%2Fwww.statscrop.com';
		$results = self::_getAPICall ( $apiEndpoint );
		
		return $results;
	}
	
	/**
	 * Get tags list
	 *
	 * @access public
	 * @static
	 * @return Object
	 */
	public static function getCountries($url = false) {
		$apiEndpoint = 'https://www.statscrop.com/data/www-traffic/?ac=countries&domain=' . $url . '&hash=727fe87abe4d483bff2b9d0959e7d64cb11a8ecd&ut=1722087303&__source_origin=https%3A%2F%2Fwww.statscrop.com';
		$results = self::_getAPICall ( $apiEndpoint );
		
		return $results;
	}
	
	/**
	 * Get backlinkers list
	 *
	 * @access public
	 * @static
	 * @return Object
	 */
	public static function getBacklinkers($url = false) {
		$apiEndpoint = 'https://www.statscrop.com/data/www-referrals/?ac=backward-links&domain=' . $url . '&hash=727fe87abe4d483bff2b9d0959e7d64cb11a8ecd&ut=1722087303&__source_origin=https%3A%2F%2Fwww.statscrop.com';
		$results = self::_getAPICall ( $apiEndpoint );
		
		return $results;
	}
	
	/**
	 * Get subdomains list
	 *
	 * @access public
	 * @static
	 * @return Object
	 */
	public static function getSubdomains($url = false) {
		$apiEndpoint = 'https://www.statscrop.com/data/www-traffic/?ac=subdomains&domain=' . $url . '&hash=727fe87abe4d483bff2b9d0959e7d64cb11a8ecd&ut=1722087303&__source_origin=https%3A%2F%2Fwww.statscrop.com';
		$results = self::_getAPICall ( $apiEndpoint );
		
		return $results;
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
				"//div[@class='row site-thumbnail']//img[@class='img-thumbnail']"
		);
		
		$nodes = static::parseDomByXpaths ( $xpath, $xpathQueryList );
		
		if($nodes) {
			$dom = self::_getDOMObject();
			
			$originalNode = $nodes->item(0);
			$originalNode->removeAttribute('class');
			$originalNode->removeAttribute('width');
			$originalNode->removeAttribute('height');
			$originalNode->setAttribute('class', 'statscrop-screenshot');
			$imgNode = $dom->saveHTML($originalNode);
		}
		
		return $imgNode;
	}
	
	/**
	 *
	 * @access public
	 * @static
	 * @return string Returns a JSON string or null if stats are not available
	 */
	public static function getTrafficGraph($url = false) {
		$apiEndpoint = 'https://www.statscrop.com/data/www-traffic/?ac=visitors&domain=' . $url . '&hash=727fe87abe4d483bff2b9d0959e7d64cb11a8ecd&ut=1722087303&__source_origin=https%3A%2F%2Fwww.statscrop.com';
		$results = self::_getAPICall ( $apiEndpoint );
		
		return $results;
	}
}
