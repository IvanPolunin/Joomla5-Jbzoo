<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

/**
 * Googlemaps helper class.
 *
 * @package Component.Helpers
 * @since 2.0
 */
class GooglemapsHelper extends AppHelper {

	/**
	 * Strips text
	 *
	 * @param string $text The text to strip
	 *
	 * @return string The stripped text
	 * @since 2.0
	 */
	public function stripText($text) {
		$text = str_replace(array("\r\n", "\n", "\r", "\t"), "", $text);
		$text = addcslashes($text, "'");
		return $text;
	}

	/**
	 * Locate
	 *
	 * @param string $location The location
	 * @param string $cache The cache to look in
	 *
	 * @return string|void The geocoded location
	 * @since 2.0
	 */
	public function locate($location, $cache = null, $key = '') {
		// check if location are lng / lat values
		$location = trim($location);

		if (!$location) {
			return;
		}

		if (preg_match('/^([-]?(?:[0-9]+(?:\.[0-9]+)?|\.[0-9]+)),\s?([-]?(?:[0-9]+(?:\.[0-9]+)?|\.[0-9]+))$/i', $location, $regs)) {
			if ($location == $regs[0]) {
				return array('lat' => $regs[1], 'lng' => $regs[2]);
			}
		}

		// use geocode to translate location
		return $this->geoCode($location, $cache, $key);
	}

	/**
	 * Geocode an address
	 *
	 * @param string $address The address to locate
	 * @param string $cache The cache to lock in
	 *
	 * @return array coordinates
	 * @since 2.0
	 */
	public function geoCode($address, $cache = null, $key = '') {
		// use cache result
		if ($cache !== null && $value = $cache->get($address)) {
			if (preg_match('/^([-]?(?:[0-9]+(?:\.[0-9]+)?|\.[0-9]+)),\s?([-]?(?:[0-9]+(?:\.[0-9]+)?|\.[0-9]+))$/i', $value, $regs)) {
				return array('lat' => $regs[1], 'lng' => $regs[2]);
			}
		}

		// query google maps geocoder and parse result
		$result = $this->queryGeoCoder($address, $key);
		$coordinates = null;

		if (!empty($result->results)) {

			$hit = $result->results[0];

			if (!empty($hit->geometry->location)) {
				$coordinates['lat'] = $hit->geometry->location->lat;
				$coordinates['lng'] = $hit->geometry->location->lng;
			}

			// cache geocoder result
			if ($cache !== null && $coordinates !== null) {
				$cache->set($address, "{$coordinates['lat']},{$coordinates['lng']}");
			}

		}

		return $coordinates;
	}

	/**
	 * Query the Geocoder for an address
	 *
	 * @param string $address The address to locate
	 *
	 * @return array result array
	 * @since 2.0
	 */
	public function queryGeoCoder($address, $key = '') {

		// query use http helper
		$response = $this->app->http->get('https://maps.googleapis.com/maps/api/geocode/json?' . http_build_query([
            'address'=> $address,
            'key' => $key,
        ]));

		if (isset($response['body'])) {
			return json_decode($response['body']);
		}

		return null;
	}

}

/**
 * GooglemapsHelperException identifies an Exception in the GooglemapsHelper class
 * @see GooglemapsHelper
 */
class GooglemapsHelperException extends AppException {}
