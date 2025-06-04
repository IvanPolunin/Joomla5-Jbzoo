<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

namespace NRFramework\Widgets;

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

class MapAddressEditor extends Widget
{
	/**
	 * Default latitude.
	 * 
	 * @var  string
	 */
	private $default_lat = '38.24921060739844';

	/**
	 * Default longitude.
	 * 
	 * @var  string
	 */
	private $default_long = '25.314512745029823';
	
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
		'value' => '0,0',

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

		// The actual map HTML
		'map' => false,

		// Whether autocomplete is enabled for the address field
		'autocomplete' => false,

		// Set what information the user can see/edit when selecting an address.
		'showAddressDetails' => [
			'address' => false,
			'latitude' => false,
			'longitude' => false,
			'country' => false,
			'country_code' => false,
			'city' => false,
			'postal_code' => false,
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
		 * Map location in correlation with the address details.
		 * 
		 * Note: This takes effect only if no custom layout is used.
		 * 
		 * Available values:
		 * 
		 * - above (Above the address details)
		 * - below (Below the address details)
		 */
		'map_location' => 'below'
	];

	public function __construct($options = [])
	{
		parent::__construct($options);
		
		if (isset($options['_showAddressDetails']))
		{
			$this->options['showAddressDetails'] = array_merge($this->options['showAddressDetails'], $this->options['_showAddressDetails']);
		}

		if ($options['required'])
		{
			$this->options['css_class'] = ' is-required';
		}

		$this->options['enable_info_window'] = false;
	}
	
	/**
	 * Renders the widget
	 * 
	 * @return  string
	 */
	public function render()
	{
		$this->loadMedia();

		$show_map = in_array($this->options['show_map'], ['backend', 'both']);
		
		// Get the map editor
		$map_options = array_merge($this->options, [
			'show_map' => $show_map,
			'autocomplete' => $this->options['autocomplete'],
			'address' => isset($this->options['address']['address']) ? $this->options['address']['address'] : ''
		]);
		$map = new MapAddressEditorView($map_options);
		$map->loadMedia();
		$this->options['map'] = $map->render();

		return parent::render();
	}

	/**
	 * Loads media files
	 * 
	 * @return  void
	 */
	private function loadMedia()
	{
		HTMLHelper::stylesheet('plg_system_nrframework/widgets/mapaddresseditor.css', ['relative' => true, 'version' => 'auto']);
		HTMLHelper::script('plg_system_nrframework/widgets/mapaddresseditor.js', ['relative' => true, 'version' => 'auto']);
	}
}