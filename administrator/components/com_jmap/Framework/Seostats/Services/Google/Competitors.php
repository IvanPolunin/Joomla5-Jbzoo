<?php
namespace JExtstore\Component\JMap\Administrator\Framework\Seostats\Services\Google;
/**
 *
 * @package JMAP::SEOSTATS::administrator::components::com_jmap
 * @subpackage seostats
 * @subpackage services
 * @subpackage google
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

use Joomla\CMS\Language\Text;
use JExtstore\Component\JMap\Administrator\Framework\Seostats\Services\Google\Search as ServicesGoogleSearch;

/**
 * Restrieve stats service for competitors
 *
 * @package JMAP::SEOSTATS::administrator::components::com_jmap
 * @subpackage seostats
 * @subpackage services
 * @subpackage google
 * @since 4.6.7
 */
class Competitors extends ServicesGoogleSearch {
	/**
	 * Default for all values to retrieve for a certain competitor domain
	 * @var array
	 * @access protected
	 */
	protected static $_values;
	
	/**
	 * Calculate Google pages
	 *
	 * @access protected
	 * @static
	 *
	 * @return void
	 */
	protected static function googlepages() {
		$g_pages = false;
		$search = urlencode ( 'site:' . self::$_url );
		$url = 'search?q=' . $search . '&gws_rd=cr';
		
		$response = static::gCurl ( $url, '', array (
				'cookie' => 'CGIC=InZ0ZXh0L2h0bWwsYXBwbGljYXRpb24veGh0bWwreG1sLGFwcGxpY2F0aW9uL3htbDtxPTAuOSxpbWFnZS93ZWJwLGltYWdlL2FwbmcsKi8qO3E9MC44LGFwcGxpY2F0aW9uL3NpZ25lZC1leGNoYW5nZTt2PWIz; CONSENT=YES+GB.en+20151207-13-0; SEARCH_SAMESITE=CgQI3o0B; SID=oQeF2oOrI8ct0mg68RmuhS9lq-rL5YDqzv_oWZFHyFm6SZYu_9MqGGFtgMqEco0A-5927g.; HSID=A6bVsYvjyB4aFQt1R; SSID=AMbs1MzN52UyDqZwm; APISID=iHy5G9KqldrHN2Nf/AQP6jZh1BhpuJLCqt; SAPISID=CkVydZgJs3KyBEJk/AEU5W6P4uiXHScqyS; ANID=AHWqTUmybJX0DetXtktJFaw06fqjq77B9CbX7QR6GkOKlYm1lB1MCUWoNbOE0Lu7; NID=188=FY6KW-ygeYgrkLl8FnyQBrChL9FP1UoS9WWs3IbJLqnuAmK6qATL-gwSNV6tSl4d7sgUU5By9tFM9zp1phivP9oykPRtPsqf4zH37OpRmjYxq6R0SSJr0Ez41qbw3HYW-uY0R_5sG-CE5jCEYWzEMU8Hb-emr51PGPeTKbeQQVOElFVjrVkJiPLQbNB8Sma6BEzzYwiFC6U-rXs1_Y42mcJEjTh31y40M00ffpGEXpTkmY1DJb4LKX6se0KnQk4YGBgzLoNhB5GyKv5YeL74yURM3linL_UeIqE3FskyQG1y; 1P_JAR=2019-9-20-15; DV=k4rz414DoMFIgAbC04tAEYubN-jz1JZw4P5MyRCtQwAAACD7P2SWOiEwcAAAAHC4M1I0Yx1kMQAAAA; SIDCC=AN0-TYvfXZ1gZPUm4szeckxeJXAciDzQvACQp6tDglk9LWV4SqY3nvNYozuIr2DLAn8IwgI8fvs' 
		) );
		if ($response) {
			$pattern1 = '#<div id=["|\']result-stats["|\']>(.*?)<nobr>#is';
			$pattern2 = '#<div class="sd" id=["|\']result-stats["|\']>(.*?)<\/div>#is';
			if (preg_match ( $pattern1, $response, $match )) {
				if (isset ( $match [1] )) {
					$result = trim ( $match [1] );
					$result = preg_replace ( '#[^0-9]#', '', $result );
					$g_pages = $result;
				}
			}
			
			if ($g_pages === false) {
				if (preg_match ( $pattern2, $response, $match )) {
					if (isset ( $match [1] )) {
						$result = trim ( $match [1] );
						$result = preg_replace ( '#[^0-9]#', '', $result );
						$g_pages = $result;
					}
				}
			}
		}
		
		self::$_values ['googlepages'] = $g_pages === false ? Text::_ ( 'COM_JMAP_NA' ) : ( int ) $g_pages;
	}
	
	/**
	 * Calculate Google backlinks
	 *
	 * @access protected
	 * @static
	 *
	 * @return void
	 */
	protected static function googlebacklinks() {
		$g_back = false;
		$url = self::$_url;
		$search = urlencode ( '"' . $url . '" -site:' . $url );
		$url = 'search?q=' . $search . '&as_lq=&num=100&start=0&filter=0&gws_rd=cr';
		
		$response = static::gCurl ( $url, '', array (
				'cookie' => 'CGIC=InZ0ZXh0L2h0bWwsYXBwbGljYXRpb24veGh0bWwreG1sLGFwcGxpY2F0aW9uL3htbDtxPTAuOSxpbWFnZS93ZWJwLGltYWdlL2FwbmcsKi8qO3E9MC44LGFwcGxpY2F0aW9uL3NpZ25lZC1leGNoYW5nZTt2PWIz; CONSENT=YES+GB.en+20151207-13-0; SEARCH_SAMESITE=CgQI3o0B; SID=oQeF2oOrI8ct0mg68RmuhS9lq-rL5YDqzv_oWZFHyFm6SZYu_9MqGGFtgMqEco0A-5927g.; HSID=A6bVsYvjyB4aFQt1R; SSID=AMbs1MzN52UyDqZwm; APISID=iHy5G9KqldrHN2Nf/AQP6jZh1BhpuJLCqt; SAPISID=CkVydZgJs3KyBEJk/AEU5W6P4uiXHScqyS; ANID=AHWqTUmybJX0DetXtktJFaw06fqjq77B9CbX7QR6GkOKlYm1lB1MCUWoNbOE0Lu7; NID=188=FY6KW-ygeYgrkLl8FnyQBrChL9FP1UoS9WWs3IbJLqnuAmK6qATL-gwSNV6tSl4d7sgUU5By9tFM9zp1phivP9oykPRtPsqf4zH37OpRmjYxq6R0SSJr0Ez41qbw3HYW-uY0R_5sG-CE5jCEYWzEMU8Hb-emr51PGPeTKbeQQVOElFVjrVkJiPLQbNB8Sma6BEzzYwiFC6U-rXs1_Y42mcJEjTh31y40M00ffpGEXpTkmY1DJb4LKX6se0KnQk4YGBgzLoNhB5GyKv5YeL74yURM3linL_UeIqE3FskyQG1y; 1P_JAR=2019-9-20-15; DV=k4rz414DoMFIgAbC04tAEYubN-jz1JZw4P5MyRCtQwAAACD7P2SWOiEwcAAAAHC4M1I0Yx1kMQAAAA; SIDCC=AN0-TYvfXZ1gZPUm4szeckxeJXAciDzQvACQp6tDglk9LWV4SqY3nvNYozuIr2DLAn8IwgI8fvs' 
		) );
		if ($response) {
			$pattern1 = '#<div id=["|\']result-stats["|\']>(.*?)<nobr>#is';
			$pattern2 = '#<div class="sd" id=["|\']result-stats["|\']>(.*?)<\/div>#is';
			if (preg_match ( $pattern1, $response, $match )) {
				if (isset ( $match [1] )) {
					$result = trim ( $match [1] );
					$result = preg_replace ( '#[^0-9]#', '', $result );
					$g_back = $result;
				}
			}
			
			if ($g_back === false) {
				if (preg_match ( $pattern2, $response, $match )) {
					if (isset ( $match [1] )) {
						$result = trim ( $match [1] );
						$result = preg_replace ( '#[^0-9]#', '', $result );
						$g_back = $result;
					}
				}
			}
		}
		
		self::$_values ['googlebacklinks'] = $g_back === false ? Text::_ ( 'COM_JMAP_NA' ) : ( int ) $g_back;
	}
	
	/**
	 * Calculate Google similar pages
	 *
	 * @access protected
	 * @static
	 *
	 * @return void
	 */
	protected static function googleRelated() {
		$r_pages = false;
		$url = self::$_url;
		$search = urlencode ( 'related:' . $url );
		$url = 'search?q=' . $search . '&gws_rd=cr';
		
		$response = static::gCurl ( $url, '', array (
				'cookie' => 'CGIC=InZ0ZXh0L2h0bWwsYXBwbGljYXRpb24veGh0bWwreG1sLGFwcGxpY2F0aW9uL3htbDtxPTAuOSxpbWFnZS93ZWJwLGltYWdlL2FwbmcsKi8qO3E9MC44LGFwcGxpY2F0aW9uL3NpZ25lZC1leGNoYW5nZTt2PWIz; CONSENT=YES+GB.en+20151207-13-0; SEARCH_SAMESITE=CgQI3o0B; SID=oQeF2oOrI8ct0mg68RmuhS9lq-rL5YDqzv_oWZFHyFm6SZYu_9MqGGFtgMqEco0A-5927g.; HSID=A6bVsYvjyB4aFQt1R; SSID=AMbs1MzN52UyDqZwm; APISID=iHy5G9KqldrHN2Nf/AQP6jZh1BhpuJLCqt; SAPISID=CkVydZgJs3KyBEJk/AEU5W6P4uiXHScqyS; ANID=AHWqTUmybJX0DetXtktJFaw06fqjq77B9CbX7QR6GkOKlYm1lB1MCUWoNbOE0Lu7; NID=188=FY6KW-ygeYgrkLl8FnyQBrChL9FP1UoS9WWs3IbJLqnuAmK6qATL-gwSNV6tSl4d7sgUU5By9tFM9zp1phivP9oykPRtPsqf4zH37OpRmjYxq6R0SSJr0Ez41qbw3HYW-uY0R_5sG-CE5jCEYWzEMU8Hb-emr51PGPeTKbeQQVOElFVjrVkJiPLQbNB8Sma6BEzzYwiFC6U-rXs1_Y42mcJEjTh31y40M00ffpGEXpTkmY1DJb4LKX6se0KnQk4YGBgzLoNhB5GyKv5YeL74yURM3linL_UeIqE3FskyQG1y; 1P_JAR=2019-9-20-15; DV=k4rz414DoMFIgAbC04tAEYubN-jz1JZw4P5MyRCtQwAAACD7P2SWOiEwcAAAAHC4M1I0Yx1kMQAAAA; SIDCC=AN0-TYvfXZ1gZPUm4szeckxeJXAciDzQvACQp6tDglk9LWV4SqY3nvNYozuIr2DLAn8IwgI8fvs' 
		) );
		if ($response) {
			$pattern1 = '#<div id=["|\']result-stats["|\']>(.*?)<nobr>#is';
			$pattern2 = '#<div class="sd" id=["|\']result-stats["|\']>(.*?)<\/div>#is';
			if (preg_match ( $pattern1, $response, $match )) {
				if (isset ( $match [1] )) {
					$result = trim ( $match [1] );
					$result = preg_replace ( '#[^0-9]#', '', $result );
					$r_pages = $result;
				}
			}
			
			if ($r_pages === false) {
				if (preg_match ( $pattern2, $response, $match )) {
					if (isset ( $match [1] )) {
						$result = trim ( $match [1] );
						$result = preg_replace ( '#[^0-9]#', '', $result );
						$r_pages = $result;
					}
				}
			}
		}
		
		self::$_values ['googlerelated'] = $r_pages === false ? Text::_ ( 'COM_JMAP_NA' ) : ( int ) $r_pages;
	}
	
	/**
	 * Calculate Bing pages
	 *
	 * @access protected
	 * @static
	 *
	 * @return void
	 */
	protected static function bingpages() {
		$url = self::$_url;
		$url = 'https://www.bing.com/search?filt=all&q=' . urlencode ( 'link: ' . $url );
		$found = false;
		
		$response = static::_getPage ( $url );
		if ($response && $response != Text::_ ( 'COM_JMAP_NA' )) {
			$pattern1 = '#<span class="sb_count" id="count">(.*?)<\/span>#i';
			$pattern2 = '#<span class="sb_count" id="count">(.*?) of (.*?) results<\/span>#is';
			$pattern3 = '#<span class="sb_count">(.*?)<\/span>#i';
			
			if (preg_match ( $pattern1, $response, $matches1 )) {
				if (! empty ( $matches1 [1] )) {
					$number = explode ( ' ', $matches1 [1] );
					self::$_values ['bingpages'] = ( int ) str_replace ( array (
							',',
							'.',
							'&#160;'
					), '', @$number [0] );
					$found = true;
				}
			}
			
			if (! $found) {
				if (preg_match ( $pattern2, $response, $matches2 )) {
					if (! empty ( $matches2 [2] )) {
						self::$_values ['bingpages'] = ( int ) str_replace ( array (
								',',
								'.',
								'&#160;'
						), '', $matches2 [2] );
						$found = true;
					}
				}
			}
			
			if (! $found) {
				if (preg_match ( $pattern3, $response, $matches3 )) {
					if (! empty ( $matches3 [1] )) {
						preg_match('/\d+(\.\d+)?/', $matches3 [1], $matches4);
						
						$number = 0;
						
						if (isset($matches4[0])) {
							$number = $matches4[0];
							
							$number = str_replace ( array (
									',',
									'.',
									'&#160;'
							), '', $number);
						}
						
						self::$_values ['bingpages'] = $number;
						$found = true;
					}
				}
			}
		}
		
		if (! $found)
			self::$_values ['bingpages'] = Text::_ ( 'COM_JMAP_NA' );
	}
	
	/**
	 * Calculate Bing backlinks
	 *
	 * @access protected
	 * @static
	 *
	 * @return void
	 */
	protected static function bingbacklinks() {
		$url = self::$_url;
		$url = 'https://www.bing.com/search?filt=all&q=' . urlencode ( 'link: ' . $url );
		$found = false;
		
		$response = static::_getPage ( $url );
		if ($response && $response != Text::_ ( 'COM_JMAP_NA' )) {
			$pattern1 = '#<span class="sb_count" id="count">(.*?)<\/span>#i';
			$pattern2 = '#<span class="sb_count" id="count">(.*?) of (.*?) results<\/span>#is';
			$pattern3 = '#<span class="sb_count">(.*?)<\/span>#i';
			
			if (preg_match ( $pattern1, $response, $matches1 )) {
				if (! empty ( $matches1 [1] )) {
					$number = explode ( ' ', $matches1 [1] );
					self::$_values ['bingbacklinks'] = ( int ) str_replace ( array (
							',',
							'.',
							'&#160;'
					), '', @$number [0] );
					$found = true;
				}
			}
			
			if (! $found) {
				if (preg_match ( $pattern2, $response, $matches2 )) {
					if (! empty ( $matches2 [2] )) {
						self::$_values ['bingbacklinks'] = ( int ) str_replace ( array (
								',',
								'.',
								'&#160;'
						), '', $matches2 [2] );
						$found = true;
					}
				}
			}
			
			if (! $found) {
				if (preg_match ( $pattern3, $response, $matches3 )) {
					if (! empty ( $matches3 [1] )) {
						preg_match('/\d+(\.\d+)?/', $matches3 [1], $matches4);
						
						$number = 0;
						
						if (isset($matches4[0])) {
							$number = $matches4[0];
							
							$number = str_replace ( array (
									',',
									'.',
									'&#160;'
							), '', $number);
							
							$number = ($number * 15) / 100;
						}
						
						self::$_values ['bingbacklinks'] = $number;
						$found = true;
					}
				}
			}
		}
		
		if (! $found)
			self::$_values ['bingbacklinks'] = Text::_ ( 'COM_JMAP_NA' );
	}
	
	/**
	 * Retrieve various stats for a given URL
	 * 
	 * @access public
	 * @static
	 * @param string $url
	 * @return array
	 */
	public static function getStats($url) {
		$url = str_replace ( array (
				'http://',
				'https://',
				'www.'
		), '', $url );
		
		self::$_url = $url;
		
		self::$_values = array(
				'googlepages' => Text::_ ( 'COM_JMAP_NA' ),
				'googlebacklinks' => Text::_ ( 'COM_JMAP_NA' ),
				'googlerelated' => Text::_ ( 'COM_JMAP_NA' ),
				'bingpages' => Text::_ ( 'COM_JMAP_NA' ),
				'bingbacklinks' => Text::_ ( 'COM_JMAP_NA' )
		);
		
		static::googlepages();
		static::googlebacklinks();
		static::googleRelated();
		static::bingpages();
		static::bingbacklinks();
	
		return self::$_values;
	}
}