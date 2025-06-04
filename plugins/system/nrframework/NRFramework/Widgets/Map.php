<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

namespace NRFramework\Widgets;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;

abstract class Map extends Widget
{
	/**
	 * Widget default options
	 *
	 * @var array
	 */
	protected $widget_options = [
		/**
		 * The value of the widget.
		 * Format: latitude,longitude
		 * 
		 * i.e. 36.891319,27.283480
		 * 
		 * Otherwise, set the markers property
		 */
		'value' => '',

		// Map tile provider key (if needed) to use the provider tiles
		'provider_key' => null,

		// Default map width
		'width' => 500,

		// Default map height
		'height' => 400,

		/**
		 * The Zoom Level.
		 * 
		 * - preset: Set a fixed zoom.
		 * - fitbounds: Allow the map provider to auto-zoom and center the map around the markers.
		 */
		'zoom_level' => 'preset',

		// Default map zoon
		'zoom' => 4,

		// Define lat,long format which will be used to center the map when zoom_level=preset is used.
		'map_center' => null,

		// Map scale. Values: metric, imperial, false
		'scale' => false,

		// View mode of the map.
		'view' => '',

		/**
		 * Set whether to show or not the map marker info window.
		 * 
		 * Defaults to the map marker address (if not empty).
		 * If a map makrer label and/or description is set, these will be used.
		 */
		'enable_info_window' => true,
		
		/**
		 * Map Marker
		 */
		/**
		 * The markers.
		 * 
		 * An array of markers.
		 * 
		 * [
		 * 	  [
		 * 	  	  latitude: 36.891319,
		 * 	  	  longitude: 27.283480,
		 * 	  	  label: 'Marker label',
		 * 	  	  description: 'Marker description'
		 * 	  ]
		 * ]
		 */
		'markers' => [],

		// Marker image relative to Joomla installation
		'markerImage' => ''
	];

	public function __construct($options = [])
	{
		parent::__construct($options);

		$this->prepare();
	}

	private function prepare()
	{
		$this->options['markerImage'] = $this->options['markerImage'] ? Uri::root() . ltrim($this->options['markerImage'], DIRECTORY_SEPARATOR) : '';

		// Set the marker if a single value was given
		if ($this->options['value'] && empty($this->options['markers']))
		{
			$coords = array_filter(array_map('trim', explode(',', $this->options['value'])));
			if (count($coords) === 2)
			{
				$this->options['markers'] = [
					[
						'id' => 1,
						'latitude' => $coords[0],
						'longitude' => $coords[1]
					]
				];
			}
		}

		// Make markers an array if a JSON string was given
		if (is_string($this->options['markers']))
		{
			$this->options['markers'] = json_decode($this->options['markers'], true);
		}

		// Set as value the first marker so the JS library can have an initial center of the map
		if (is_array($this->options['markers']))
		{
			$latitude = isset($this->options['markers'][0]['latitude']) ? $this->options['markers'][0]['latitude'] : false;
			$longitude = isset($this->options['markers'][0]['longitude']) ? $this->options['markers'][0]['longitude'] : false;

			if ($latitude && $longitude)
			{
				$this->options['value'] = implode(',', [$latitude, $longitude]);
			}
		}

		if ($this->options['load_css_vars'])
		{
			$this->options['custom_css'] = $this->getWidgetCSS();
		}

		// Transform title/description translation strings
		$this->prepareMarkerText();
	}

	private function prepareMarkerText()
	{
		if (!is_array($this->options['markers']) || !count($this->options['markers']))
		{
			return;
		}

		foreach ($this->options['markers'] as &$marker)
		{
			if (isset($marker['label']) && $marker['label'])
			{
				$marker['label'] = Text::_($marker['label']);
			}

			if (isset($marker['description']) && $marker['description'])
			{
				$marker['description'] = Text::_($marker['description']);
			}
		}
	}

	/**
	 * Returns the CSS for the widget.
	 * 
	 * @param   array  $exclude_breakpoints   Define breakpoints to exclude their CSS
	 * 
	 * @return  string
	 */
	public function getWidgetCSS($exclude_breakpoints = [])
	{
		$controls = [
			// CSS Variables
            [
                'property' => '--width',
                'value' => $this->options['width'],
				'unit' => 'px'
			],
            [
                'property' => '--height',
                'value' => $this->options['height'],
				'unit' => 'px'
			],
		];

		$selector = '.nrf-widget.map-widget.' . $this->options['id'];
		
		$controlsInstance = new \NRFramework\Controls\Controls(null, $selector, $exclude_breakpoints);
        
		if (!$controlsCSS = $controlsInstance->generateCSS($controls))
		{
			return;
		}

		return $controlsCSS;
	}

	public function render()
	{
		$this->loadMedia();

		return parent::render();
	}

	/**
	 * Loads media files
	 * 
	 * @return  void
	 */
	public function loadMedia()
	{
		if ($this->options['load_stylesheet'])
		{
			HTMLHelper::stylesheet('plg_system_nrframework/widgets/map.css', ['relative' => true, 'version' => 'auto']);
		}
	}
}