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

class GoogleMap extends Map
{
	/**
	 * Loads media files
	 * 
	 * @return  void
	 */
	public function loadMedia()
	{
		parent::loadMedia();

		HTMLHelper::script('plg_system_nrframework/widgets/googlemap.js', ['relative' => true, 'version' => 'auto']);
		HTMLHelper::script('https://maps.googleapis.com/maps/api/js?callback=Function.prototype&key=' . $this->options['provider_key'], ['relative' => false, 'version' => false]);
	}
}