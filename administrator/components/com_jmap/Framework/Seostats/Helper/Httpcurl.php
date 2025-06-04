<?php
namespace JExtstore\Component\JMap\Administrator\Framework\Seostats\Helper;
/**
 *
 * @package JMAP::SEOSTATS::administrator::components::com_jmap
 * @subpackage seostats
 * @subpackage helper
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use JExtstore\Component\JMap\Administrator\Framework\Exception as JMapException;

/**
 * HTTP Request Helper Class based on CURL lib
 *
 * @package JMAP::SEOSTATS::administrator::components::com_jmap
 * @subpackage seostats
 * @subpackage helper
 * @since 3.0
 */
class Httpcurl {
	/**
	 * HTTP GET/POST request with curl
	 * 
	 * @access public
	 * @param String $url
	 *        	The Request URL
	 * @param Array $postData
	 *        	Optional: POST data array to be send.
	 * @return Mixed On success, returns the response string.
	 *         Else, the the HTTP status code received
	 *         in reponse to the request.
	 */
	public static function sendRequest($url, $postData = false, $postJson = false, $headers = 1) {
		// Dummy User Agent
		$ua = sprintf ( 'JSitemap Professional %s http://storejextensions.org', strval(simplexml_load_file(JPATH_COMPONENT_ADMINISTRATOR . '/jmap.xml')->version) );
		
		$ch = curl_init ( $url );
		curl_setopt_array ( $ch, array (
				CURLOPT_USERAGENT => $ua,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_CONNECTTIMEOUT => 15,
				CURLOPT_HEADER => $headers,
				CURLOPT_MAXREDIRS => 2,
				CURLOPT_SSL_VERIFYPEER => 0 
		) );
		
		if(!ini_get('open_basedir')) {
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		}
		
		// Check for proxy settings
		$cParams = ComponentHelper::getParams('com_jmap');
		if ($cParams->get('enable_proxy', 0)) {
			$proxyServer = $cParams->get('proxy_server_ipaddress', '');
			$proxyPort = $cParams->get('proxy_server_port', '');
			$proxyUsername = $cParams->get('proxy_server_username', '');
			$proxyPassword = $cParams->get('proxy_server_password', '');
			if (!empty($proxyServer)) curl_setopt($ch, CURLOPT_PROXY, $proxyServer);
			if (!empty($proxyPort)) curl_setopt($ch, CURLOPT_PROXYPORT, $proxyPort);
			if (!empty($proxyUsername) && !empty($proxyPassword)) curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyUsername . ':' . $proxyPassword);
		}
		
		if (false !== $postData) {
			if (false !== $postJson) {
				curl_setopt ( $ch, CURLOPT_HTTPHEADER, array (
						'Content-type: application/json' 
				) );
				$data = json_encode ( $postData );
			} else {
				$data = http_build_query ( $postData );
			}
			curl_setopt ( $ch, CURLOPT_POST, 1 );
			curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
		}
		
		$response = curl_exec ( $ch );
		$httpCode = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );
		curl_close ( $ch );
		
		// If it's a redirection (3XX) follow the redirect
		if ((int) $httpCode >= 300 && (int) $httpCode < 400) {
			$redirectionLink = '';
			$headers = explode("\n", $response);
			// loop through the headers and check for a Location: str
			$j = count($headers);
			for($i = 0; $i < $j; $i++){
				// if we find the Location header strip it and fill the redir var
				if(strpos($headers[$i],"Location:") !== false){
					$redirectionLink = trim(str_replace("Location:","",$headers[$i]));
					break;
				}
			}
			if($redirectionLink) {
				return static::sendRequest($redirectionLink, $postData, $postJson );
			}
		}
		
		// Connection success?
		if((int) $httpCode != 200 & !$response) {
			throw new JMapException(Text::_('COM_JMAP_NO_SERVICE_ANSWER'), 'notice');
		}
		
		return (200 == ( int ) $httpCode) ? $response : false;
	}
	
	/**
	 * HTTP HEAD request with curl
	 *
	 * @access public
	 * @static
	 * @param String $a
	 *        	The request URL
	 * @return Integer Returns the HTTP status code received in
	 *         response to a GET request of the input URL.
	 */
	public static function getHttpCode($url) {
		// Dummy User Agent
		$ua = sprintf ( 'JSitemap Professional %s http://storejextensions.org', strval(simplexml_load_file(JPATH_COMPONENT_ADMINISTRATOR . '/jmap.xml')->version) );
		
		$ch = curl_init ( $url );
		curl_setopt_array ( $ch, array (
				CURLOPT_USERAGENT => $ua,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_CONNECTTIMEOUT => 10,
				CURLOPT_FOLLOWLOCATION => 1,
				CURLOPT_MAXREDIRS => 2,
				CURLOPT_SSL_VERIFYPEER => 0,
				CURLOPT_NOBODY => 1 
		) );
		
		curl_exec ( $ch );
		$httpCode = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );
		curl_close ( $ch );
		
		return ( int ) $httpCode;
	}
	
	/**
	 * * HTTP GET/POST request with curl written to a response file
	 *
	 * @access public
	 * @static
	 * @param String $a
	 *        	The request URL
	 * @return Integer Returns the HTTP status code received in
	 *         response to a GET request of the input URL.
	 */
	public function getFile($url, $file) {
		// Dummy User Agent
		$ua = sprintf ( 'JSitemap Professional %s http://storejextensions.org', strval(simplexml_load_file(JPATH_COMPONENT_ADMINISTRATOR . '/jmap.xml')->version) );
		
		$fp = fopen ( "$file", 'w' );
		
		$ch = curl_init ( $url );
		curl_setopt_array ( $ch, array (
				CURLOPT_USERAGENT => $ua,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_CONNECTTIMEOUT => 30,
				CURLOPT_FOLLOWLOCATION => 1,
				CURLOPT_MAXREDIRS => 2,
				CURLOPT_SSL_VERIFYPEER => 0,
				CURLOPT_FILE => $fp 
		) );
		
		curl_exec ( $ch );
		curl_close ( $ch );
		fclose ( $fp );
		
		clearstatcache ();
		return ( bool ) (false !== stat ( $file ));
	}
}