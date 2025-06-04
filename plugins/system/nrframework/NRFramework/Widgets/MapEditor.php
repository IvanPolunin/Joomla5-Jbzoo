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
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;

class MapEditor extends Widget
{
	/**
	 * Widget default options
	 *
	 * @var array
	 */
	protected $widget_options = [
		/**
		 * The list of markers added to the map.
		 * 
		 * Example:
		 * 
		 * [
		 * 	  'lat' => 37.9838,
		 * 	  'lng' => 23.7275,
		 * 	  'title' => 'Athens',
		 * 	  'description' => 'The capital of Greece',
		 * ]
		 */
		'value' => [],

		// The default map latitude. Where it points when no markers are added.
		'lat' => null,
		
		// The default map longitude. Where it points when no markers are added.
		'lng' => null,
		
		// Max markers allowed
		'maxMarkers' => 1,

		// Set whether to show the map editor sidebar
		'showSidebar' => true,

		// Set the marker image, relative path to an image file
		'markerImage' => ''
	];

	public function __construct($options = [])
	{
		parent::__construct($options);

		$this->prepare();

		$this->loadMedia();
	}

	private function prepare()
	{
		if (!$this->options['pro'] && is_array($this->options['value']) && count($this->options['value']) >= 1)
		{
			$this->options['css_class'] .= ' markers-limit-reached';
		}

		if ($this->options['markerImage'])
		{
			$markerImage = explode('#', ltrim($this->options['markerImage'], DIRECTORY_SEPARATOR));
			$this->options['markerImage'] = Uri::root() . reset($markerImage);
		}

		Text::script('NR_ENTER_AN_ADDRESS_OR_COORDINATES');
		Text::script('NR_ARE_YOU_SURE_YOU_WANT_TO_DELETE_ALL_SELECTED_MARKERS');
		Text::script('NR_ARE_YOU_SURE_YOU_WANT_TO_DELETE_THIS_MARKER');
		Text::script('NR_ADD_MARKER');
		Text::script('NR_EDIT_MARKER');
		Text::script('NR_DELETE_MARKER');
		Text::script('NR_UNKNOWN_LOCATION');
		Text::script('NR_UNLIMITED_MARKERS');
		Text::script('NR_ADD_MORE_MARKERS_UPGRADE_TO_PRO');
		Text::script('NR_MARKERS');
		Text::script('NR_YOU_HAVENT_ADDED_ANY_MARKERS_YET');
		Text::script('NR_ADD_YOUR_FIRST_MARKER');
		Text::script('NR_NO_MARKERS_FOUND');
		Text::script('NR_LOCATION_ADDRESS');
		Text::script('NR_ADD_TO_MAP');
		Text::script('NR_COORDINATES');
		Text::script('NR_ADDRESS_ADDRESS_HINT');
		Text::script('NR_LATITUDE');
		Text::script('NR_LONGITUDE');
		Text::script('NR_MARKER_INFO');
		Text::script('NR_LABEL');
		Text::script('NR_DESCRIPTION');
		Text::script('NR_MARKER_LABEL');
		Text::script('NR_MARKER_DESCRIPTION');
		Text::script('NR_SAVE');
		Text::script('NR_PLEASE_SELECT_A_LOCATION');
		Text::script('NR_IMPORT');
		Text::script('NR_IMPORT_MARKERS');
		Text::script('NR_IMPORT_LOCATIONS_DESC');
		Text::script('NR_IMPORT_LOCATIONS_DESC2');
		Text::script('NR_PLEASE_ENTER_LOCATIONS_TO_IMPORT');
		Text::script('NR_COULDNT_IMPORT_LOCATIONS');
		Text::script('NR_ADDING_MARKERS');
		Text::script('NR_SAVE_YOUR_FIRST_MARKER');
		Text::script('NR_OUT_OF');
		Text::script('NR_MARKERS_ADDED');
		Text::script('NR_MARKERS_LIMIT_REACHED_DELETE_MARKER_TO_ADD');
		Text::script('NR_EXPORT_MARKERS');
		Text::script('NR_EXPORT_MARKERS_DESC');
		Text::script('NR_THERE_ARE_NO_LOCATIONS_TO_EXPORT');
		Text::script('NR_LOCATIONS_IMPORTED');
	}

	/**
	 * Loads media files
	 * 
	 * @return  void
	 */
	public function loadMedia()
	{
		HTMLHelper::script('https://unpkg.com/react@18.2.0/umd/react.production.min.js');
		HTMLHelper::script('https://unpkg.com/react-dom@18.2.0/umd/react-dom.production.min.js');

		HTMLHelper::stylesheet('plg_system_nrframework/vendor/leaflet.css', ['relative' => true, 'version' => 'auto']);
		HTMLHelper::script('plg_system_nrframework/vendor/leaflet.js', ['relative' => true, 'version' => 'auto']);
		
		HTMLHelper::stylesheet('plg_system_nrframework/widgets/mapeditor.css', ['relative' => true, 'version' => 'auto']);
		HTMLHelper::script('plg_system_nrframework/mapeditor.js', ['relative' => true, 'version' => 'auto']);
	}
}