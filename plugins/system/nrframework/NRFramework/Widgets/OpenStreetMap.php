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

class OpenStreetMap extends Map
{
	/**
	 * Loads media files
	 * 
	 * @return  void
	 */
	public function loadMedia()
	{
		parent::loadMedia();
		
		HTMLHelper::stylesheet('plg_system_nrframework/vendor/leaflet.css', ['relative' => true, 'version' => 'auto']);
		HTMLHelper::script('plg_system_nrframework/vendor/leaflet.js', ['relative' => true, 'version' => 'auto']);
		
		if ($this->options['load_stylesheet'])
		{
			HTMLHelper::stylesheet('plg_system_nrframework/widgets/openstreetmap.css', ['relative' => true, 'version' => 'auto']);
		}

		if ($this->options['view'] === 'satellite')
		{
			HTMLHelper::script('https://unpkg.com/esri-leaflet@3.0.10/dist/esri-leaflet.js');
			HTMLHelper::script('https://unpkg.com/esri-leaflet-vector@4.0.1/dist/esri-leaflet-vector.js');
		}

		HTMLHelper::script('plg_system_nrframework/widgets/openstreetmap.js', ['relative' => true, 'version' => 'auto']);
	}
}