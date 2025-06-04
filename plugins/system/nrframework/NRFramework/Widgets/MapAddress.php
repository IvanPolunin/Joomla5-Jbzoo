<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
* @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

namespace NRFramework\Widgets;

defined('_JEXEC') or die;

class MapAddress extends Widget
{
	/**
	 * Widget default options
	 *
	 * @var array
	 */
	protected $widget_options = [
		/**
		 * The map coordinates.
		 * Format: latitude,longitude
		 * 
		 * i.e. 36.891319,27.283480
		 */
		'value' => '',

		/**
		 * Set whether and where to show the map.
		 * 
		 * Available values:
		 * 
		 * false
		 * backend
		 * frontend
		 * both
		 */
		'show_map' => false,

		// The map HTML (If can be rendered)
		'map' => false,

		// Set what information the user can see.
		'showAddressDetails' => [
			'address' => true,
			'latitude' => false,
			'longitude' => false,
			'country' => true,
			'country_code' => false,
			'city' => false,
			'postal_code' => true,
			'county' => false,
			'state' => false,
			'municipality' => false,
			'town' => false,
			'road' => false,
		],

		/**
		 * The address details.
		 * 
		 * Supported data:
		 * 
		 * address
		 * latitude
		 * longitude
		 * country
		 * country_code
		 * city
		 * postal_code
		 * county
		 * state
		 * municipality
		 * town
		 * road
		 */
		'address' => [
			'address' => '',
			'latitude' => '',
			'longitude' => '',
			'country' => '',
			'country_code' => '',
			'city' => '',
			'postal_code' => '',
			'county' => '',
			'state' => '',
			'municipality' => '',
			'town' => '',
			'road' => '',
		],

		/**
		 * The layout type of the output.
		 * 
		 * Available values:
		 * 
		 * - default
		 * - custom
		 */
		'layout_type' => 'default',

		/**
		 * The custom layout code (HTML + Smart Tags).
		 * 
		 * Available Smart Tags:
		 * 
		 * Allowed Smart Tags:
		 * 
		 * {address.map}
		 * {address.address} - {address.address.label}
		 * {address.latitude} - {address.latitude.label}
		 * {address.longitude} - {address.longitude.label}
		 * {address.country} - {address.country.label}
		 * {address.country_code} - {address.country_code.label}
		 * {address.city} - {address.city.label}
		 * {address.county} - {address.county.label}
		 * {address.postal_code} - {address.postal_code.label}
		 * {address.state} - {address.state.label}
		 * {address.municipality} - {address.municipality.label}
		 * {address.town} - {address.town.label}
		 * {address.road} - {address.road.label}
		 */
		'custom_layout' => '{address.address.label}: {address.address}',
		
		/**
		 * Map location in correlation with the address details.
		 * 
		 * Note: This takes effect only if no custom layout is used.
		 * 
		 * Available values:
		 * 
		 * - above (Above the address details)
		 * - below (Below the address details)
		 */
		'map_location' => 'below',

		// The map HTML which will return the map HTML only if a map is set and current layout is not custom
		'map_html' => ''
	];
	
	/**
	 * Renders the widget
	 * 
	 * @return  string
	 */
	public function render()
	{
		$this->options['enable_info_window'] = false;

		if (in_array($this->options['show_map'], ['frontend', 'both']) || (in_array($this->options['show_map'], ['frontend', 'both']) && $this->options['layout_type'] === 'custom' && !empty($this->options['custom_layout']) && strpos($this->options['custom_layout'], '{address.map}') !== false))
		{
			$this->options['markerImage'] = $this->options['marker_image'];
			// Get the map
			$map_options = $this->options;
			// Remove unneeded props.
			// The custom layout can include Smart Tags and this breaks the Smart Tags replace method as it tries to replace inner Smart Tags too
			unset($map_options['custom_layout']);
			unset($map_options['showAddressDetails']);
			unset($map_options['address']);
			$map = new OpenStreetMap($map_options);
			$map->loadMedia();
			$this->options['map'] = $map->render();
		}

		$this->options['map_html'] = in_array($this->options['show_map'], ['frontend', 'both']) && $this->options['layout_type'] !== 'custom' ? $this->options['map'] : '';

		return parent::render();
	}
}