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

class SelfHostedVideo extends Video
{
	/**
	 * Widget default options
	 *
	 * @var array
	 */
	protected $video_widget_options = [
		/**
		 * Specify how the video should be loaded when the page loads.
		 * 
		 * Allowed values:
		 * - metadata
		 * - auto
		 * - none
		 */
		'preload' => 'auto',

		// Whether to mute the video
		'mute' => false,

		// Whether to display controls on the video
		'controls' => true,

		// Whether to loop the video
		'loop' => false,

		// Stores the given video details
		'video' => ''
	];

	protected function prepare()
	{
		if (isset($this->options['value']) && !empty($this->options['value']))
		{
			$videos = \NRFramework\Helpers\File::getFileSources($this->options['value'], ['mp4', 'webm','ogg']);
			$this->options['video'] = is_array($videos) && isset($videos[0]) ? $videos[0] : false;
		}

		$atts = [
			'data-video-id="' . $this->options['value'] . '"',
			'data-video-type="selfhostedvideo"',
			'data-video-mute="' . var_export($this->options['mute'], true) . '"',
			'data-video-controls="' . var_export($this->options['controls'], true) . '"',
			'data-video-loop="' . var_export($this->options['loop'], true) . '"',
			'data-video-autoplay="' . var_export($this->options['autoplay'], true) . '"',
			'data-video-autopause="' . var_export($this->options['autopause'], true) . '"',
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
		HTMLHelper::script('plg_system_nrframework/widgets/video/selfhostedvideo.js', ['relative' => true, 'version' => 'auto']);
	}
}