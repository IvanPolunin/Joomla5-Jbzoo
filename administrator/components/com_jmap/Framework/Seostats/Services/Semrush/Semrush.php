<?php
namespace JExtstore\Component\JMap\Administrator\Framework\Seostats\Services;
/**
 *
 * @package JMAP::SEOSTATS::administrator::components::com_jmap
 * @subpackage seostats
 * @subpackage services
 * @subpackage semrush
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use JExtstore\Component\JMap\Administrator\Framework\Seostats;
use JExtstore\Component\JMap\Administrator\Framework\Seostats\Helper\Url as SeostatsHelperUrl;
use JExtstore\Component\JMap\Administrator\Framework\Seostats\Services;

/**
 * SEMRush stats service
 *
 * @package JMAP::SEOSTATS::administrator::components::com_jmap
 * @subpackage seostats
 * @subpackage services
 * @subpackage semrush
 * @since 4.0
 */
class Semrush extends Seostats {
	public static function getDBs() {
		return array (
				"au", // Google.com.au (Australia)
				"br", // Google.com.br (Brazil)
				"ca", // Google.ca (Canada)
				"de", // Google.de (Germany)
				"es", // Google.es (Spain)
				"fr", // Google.fr (France)
				"it", // Google.it (Italy)
				"ru", // Google.ru (Russia)
				"uk", // Google.co.uk (United Kingdom)
				'us', // Google.com (United States)
				"us.bing"  # Bing.com
        );
	}
	
	/**
	 * Returns the SEMRush main report data.
	 * (Only main report is public available.)
	 *
	 * @access public
	 * @param
	 *        	url string Domain name only, eg. "ebay.com" (/wo quotes).
	 * @param
	 *        	db string Optional: The database to use. Valid values are:
	 *        	au, br, ca, de, es, fr, it, ru, uk, us, us.bing (us is default)
	 * @return array Returns an array containing the main report data.
	 * @link http://www.semrush.com/api.html
	 */
	public static function getDomainRank($domain = false, $db = false) {
		$url = 'https://openpagerank.com/api/v1.0/getPageRank';
		$query = http_build_query ( array (
				'domains' => array (
						$domain 
				) 
		) );
		$url = $url . '?' . $query;
		$ch = curl_init ();
		$headers = [ 
				'API-OPR: wwoc8cgw88go0cswscw44g88ggwg0s0o4g8o4ok0' 
		];
		curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		$output = curl_exec ( $ch );
		curl_close ( $ch );
		$output = json_decode ( $output, true );
		
		if (isset ( $output ['response'] ) && isset ($output ['response'][0]['rank'] ) && $output ['response'][0]['rank'] != null) {
			return $output ['response'] [0] ['page_rank_decimal'] . '/<span class="seostats_unit_measure_small">10</span>';
		} else {
			return parent::noDataDefaultValue ();
		}
	}
}
