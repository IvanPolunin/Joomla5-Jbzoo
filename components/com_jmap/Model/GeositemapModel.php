<?php
namespace JExtstore\Component\JMap\Site\Model;
/**
 * @package JMAP::SITEMAP::components::com_jmap
 * @subpackage models
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use JExtstore\Component\JMap\Administrator\Framework\Model as JMapModel;
use JExtstore\Component\JMap\Administrator\Framework\Exception as JMapException;
use JExtstore\Component\JMap\Administrator\Framework\Http;

/**
 * Main sitemap model public responsibilities interface
 *
 * @package JMAP::SITEMAP::components::com_jmap
 * @subpackage models
 * @since 3.5
 */
interface IJMapModelSitemap {
	/**
	 * Get the Data
	 * @access public
	 * @param Object $httpClient
	 * @return Object
	 */
	public function getSitemapData(Http $httpClient);
}

/**
 * Main sitemap model class <<testable_behavior>>
 *
 * @package JMAP::SITEMAP::components::com_jmap
 * @subpackage models
 * @since 3.5
 */
class GeositemapModel extends JMapModel implements IJMapModelSitemap {
	/**
	 * Get the Data
	 * @access public
	 * @param Object $httpClient
	 * @return Object
	 */
	public function getSitemapData(Http $httpClient) {
		// Try to get informations
		try {
			$address = $this->getComponentParams()->get('geositemap_address', null);
			if(!$address) {
				throw new \Exception(null);
			}
			$encodedAddress = urlencode($address);
			
			$url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . $encodedAddress . '&key=AIzaSyDNlp3wN1Al_ksW92rmb5Y96RQGn68tKb8';
			$response = $httpClient->get($url)->body;
			if($response) {
				$decodedUpdateInfos = json_decode($response);
			}
			
			if(!is_object($decodedUpdateInfos) || !is_array($decodedUpdateInfos->results) || $decodedUpdateInfos->status != 'OK') {
				throw new \Exception(null);
			}
			
			return $decodedUpdateInfos->results[0];
		} catch(JMapException $e) {
			return false;
		}  catch(\Exception $e) {
			return false;
		}

	}
}