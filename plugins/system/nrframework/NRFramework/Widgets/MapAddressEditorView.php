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
use Joomla\CMS\Language\Text;

class MapAddressEditorView extends OpenStreetMap
{
	/**
	 * Widget default options
	 *
	 * @var array
	 */
	protected $_widget_options = [
		// Whether autocomplete is enabled for the address field
		'autocomplete' => false,

		// Whether to show the map
		'show_map' => true,

		/**
		 * Set whether & where to display the address input.
		 * 
		 * Available values:
		 * - before_map: Show it before the map
		 * - true/after_map: Show it after the map
		 * - false: Hide the address field
		 */
		'show_address' => 'after_map',

		// The address value
		'address' => '',

		/**
		 * Markers
		 */
		// Show the markers list
		'show_markers_list' => false,

		// Max markers allowed
		'max_markers' => 1
	];

	public function __construct($options = [])
	{
		$this->widget_options = array_merge($this->widget_options, $this->_widget_options);

		parent::__construct($options);

		$this->prepare();
	}

	private function prepare()
	{
		// We do not show the map
		if (!$this->options['show_map'])
		{
			$this->options['css_class'] .= ' no-map';
		}

		// Hide "Clear" button if no markers exists
		if (empty($this->options['markers']))
		{
			$this->options['css_class'] .= ' clear-is-hidden';
		}

		if ((!$this->options['pro'] && count($this->options['markers']) >= 1) || ($this->options['max_markers'] !== 0 && count($this->options['markers']) >= $this->options['max_markers']))
		{
			$this->options['css_class'] .= ' markers-limit-reached';
		}

		Text::script('NR_ARE_YOU_SURE_YOU_WANT_TO_DELETE_ALL_MARKERS');
		Text::script('NR_ARE_YOU_SURE_YOU_WANT_TO_DELETE_THIS_MARKER');
		Text::script('NR_ADD_MARKER');
		Text::script('NR_EDIT_MARKER');
		Text::script('NR_DELETE_MARKER');
		Text::script('NR_UNKNOWN_LOCATION');
	}

	/**
	 * Loads media files
	 * 
	 * @return  void
	 */
	public function loadMedia()
	{
		if ($this->options['show_map'])
		{
			parent::loadMedia();

			HTMLHelper::stylesheet('plg_system_nrframework/vendor/leaflet.contextmenu.min.css', ['relative' => true, 'version' => 'auto']);
			HTMLHelper::script('plg_system_nrframework/vendor/leaflet.contextmenu.min.js', ['relative' => true, 'version' => 'auto']);
		}

		HTMLHelper::stylesheet('plg_system_nrframework/widgets/mapaddresseditorview.css', ['relative' => true, 'version' => 'auto']);
		HTMLHelper::script('plg_system_nrframework/widgets/mapaddresseditorview.js', ['relative' => true, 'version' => 'auto']);
	}
}