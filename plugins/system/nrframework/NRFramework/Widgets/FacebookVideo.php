<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            http://www.tassos.gr
 * @copyright       Copyright © 2020 Tassos Marinos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

namespace NRFramework\Widgets;

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

class FacebookVideo extends Video
{
	/**
	 * Widget default options
	 *
	 * @var array
	 */
	protected $video_widget_options = [
		// Whether we allow fullscreen
		'fs' => false,

		// Set to include the text from the Facebook post associated with the video, if any. Only available for desktop sites.
		'show_text' => false,

		// Set to show captions (if available) by default. Captions are only available on desktop.
		'show_captions' => false,
	];

	/**
	 * We use the video widget layout file.
	 * 
	 * @return  string
	 */
	public function getName()
	{
		return 'video';
	}

	protected function prepare()
	{
		$videoDetails = \NRFramework\Helpers\Video::getDetails($this->options['value']);
		$videoProvider = isset($videoDetails['provider']) ? $videoDetails['provider'] : '';

		// Abort
		if ($videoProvider !== 'facebookvideo')
		{
			$this->options['value'] = null;
			return;
		}

		$this->options['css_class'] .= ' facebookvideo';

		$videoID = isset($videoDetails['id']) ? $videoDetails['id'] : '';

		$atts = [
			'data-video-id="' . $videoID . '"',
			'data-video-type="' . $videoProvider . '"',
			'data-video-width="auto"',
			'data-video-show-text="' . var_export($this->options['show_text'], true) . '"',
			'data-video-show-captions="' . var_export($this->options['show_captions'], true) . '"',
			'data-video-fs="' . var_export($this->options['fs'], true) . '"',
			'data-video-autopause="' . var_export($this->options['autopause'], true) . '"',
			'data-video-autoplay="' . var_export($this->options['autoplay'], true) . '"'
		];

		$this->options['atts'] = implode(' ', $atts);
	}

	/**
	 * Loads media files
	 * 
	 * @return  void
	 */
	public function videoAssets()
	{
		HTMLHelper::script('plg_system_nrframework/widgets/video/facebookvideo.js', ['relative' => true, 'version' => 'auto']);
	}
}