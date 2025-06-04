<?php
namespace JExtstore\Component\JMap\Administrator\Framework\Seostats\Services;
/**
 *
 * @package JMAP::SEOSTATS::administrator::components::com_jmap
 * @subpackage seostats
 * @subpackage services
 * @subpackage base
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use JExtstore\Component\JMap\Administrator\Framework\Seostats;
use JExtstore\Component\JMap\Administrator\Framework\Seostats\Services;
use JExtstore\Component\JMap\Administrator\Framework\Seostats\Helper\Url as SeostatsHelperUrl;

/**
 * Base stats service
 *
 * @package JMAP::SEOSTATS::administrator::components::com_jmap
 * @subpackage seostats
 * @subpackage services
 * @subpackage base
 * @since 3.0
 */
class Base extends Seostats {
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
	 * @return int
	 */
	protected static function retInt($str) {
		$strim = trim ( str_replace ( array(',', ' '), '', $str ) );
		$intStr = 0 < strlen ( $strim ) ? $strim : '0';
		return intval ( $intStr );
	}
	
	/**
	 * @access protected
	 * @static
	 * @return mixed nodeValue
	 */
	protected static function parseDomByXpaths($xpathDom, $xpathQueryList) {
		foreach ( $xpathQueryList as $query ) {
			$nodes = @$xpathDom->query ( $query );
				
			if ($nodes->length != 0) {
				return $nodes;
			}
		}
	
		return null;
	}
	
	/**
	 * @access protected
	 * @static
	 * @return mixed nodeValue
	 */
	protected static function parseDomByXpathsGetValue($xpathDom, $xpathQueryList) {
		$nodes = static::parseDomByXpaths ( $xpathDom, $xpathQueryList );
	
		return ($nodes) ? $nodes->item ( 0 )->nodeValue : '';
	}
	
	/**
	 * @access protected
	 * @static
	 * @return Object if results are found, false otherwise
	 */
	protected static function parseDomByXpathsGetObjectArray($xpathDom, $xpathQueryList, $associativeArrayKey) {
		$nodes = static::parseDomByXpaths ( $xpathDom, $xpathQueryList );
		
		// Iterate over DOMNodeList
		if(is_object($nodes) && $nodes->length > 0) {
			// Init the main container object
			$dataObject = new \stdClass();
			$dataObject->data = array();
			
			foreach ($nodes as $node) {
				$dataObject->data[] = array($associativeArrayKey => trim($node->nodeValue));
			}
			
			return $dataObject;
		}
		return false;
	}
	
	/**
	 * @access protected
	 * @static
	 * @return mixed nodeValue
	 */
	protected static function parseDomByXpathsToInteger($xpathDom, $xpathQueryList) {
		$nodeValue = static::parseDomByXpathsGetValue ( $xpathDom, $xpathQueryList );
	
		if ($nodeValue === null) {
			return parent::noDataDefaultValue ();
		}
		return self::retInt ( $nodeValue );
	}
	
	/**
	 * @access protected
	 * @static
	 * @return mixed nodeValue
	 */
	protected static function parseDomByXpathsWithoutTags($xpathDom, $xpathQueryList) {
		$nodeValue = static::parseDomByXpathsGetValue ( $xpathDom, $xpathQueryList );
	
		if ($nodeValue === null) {
			return parent::noDataDefaultValue ();
		}
	
		return strip_tags ( $nodeValue );
	}
	
	/**
	 * @access protected
	 * @static
	 * @return mixed nodeValue
	 */
	protected static function parseDomByXpathsToIntegerWithoutTags($xpathDom, $xpathQueryList) {
		$nodeValue = static::parseDomByXpathsGetValue ( $xpathDom, $xpathQueryList );
	
		if ($nodeValue === null) {
			return parent::noDataDefaultValue ();
		}
	
		return self::retInt ( strip_tags ( $nodeValue ) );
	}
}
