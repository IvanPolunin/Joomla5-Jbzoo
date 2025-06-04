<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            http://www.tassos.gr
 * @copyright       Copyright © 2020 Tassos Marinos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

namespace NRFramework\Widgets;

defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;

class YouTube extends Video
{
	/**
	 * Widget default options
	 *
	 * @var array
	 */
	protected $video_widget_options = [
		/**
		 * Set the cover image type.
		 * 
		 * Allowed Values:
		 * - none
		 * - auto
		 * - custom
		 */
		'coverImageType' => 'none',

		// The Cover Image URL when coverImage="custom"
		'coverImage' => '',

		// Whether we allow fullscreen
		'fs' => false,

		// Whether controls will appear in the video
		'controls' => true,

		// Loop
		'loop' => false,

		// Mute
		'mute' => false,

		// Closed Captions
		'cc_load_policy' => false,

		/**
		 * The color that will be used in the player's video progress bar to highlight
		 * the amount of the video that the viewer has already seen.
		 * 
		 * Allowed Values:
		 * - red
		 * - white
		 */
		'color' => 'red',

		// Whether to allow or not keyboard shortcuts
		'disablekb' => false,

		// Start the video from X seconds
		'start' => null,

		// End the video at X seconds
		'end' => null,

		/**
		 * Set whether to show related videos.
		 * 
		 * Allowed Values:
		 * 0: Don't show related videos
		 * 1: Show related videos from anywhere
		 */
		'rel' => '1',

		/**
		 * Set whether to load the video in privacy-enhanced mode.
		 * 
		 * When this is enabled, YouTube won't store information about
		 * visitors unless they play the video.
		 */
		'privacy' => false
	];

	/**
	 * Prepares the widget.
	 * 
	 * @return  void
	 */
	protected function prepare()
	{
		$videoDetails = \NRFramework\Helpers\Video::getDetails($this->options['value']);
		$videoProvider = isset($videoDetails['provider']) ? $videoDetails['provider'] : '';
		
		// Abort
		if ($videoProvider !== 'youtube')
		{
			$this->options['value'] = null;
			return;
		}

		$this->options['css_class'] .= ' youtube';

		$videoID = isset($videoDetails['id']) ? $videoDetails['id'] : '';

		if ($this->options['coverImageType'] === 'auto')
		{
			$this->options['coverImage'] = 'url("https://img.youtube.com/vi/' . $videoID . '/maxresdefault.jpg")';
		}
		else if ($this->options['coverImageType'] === 'custom' && !empty($this->options['coverImage']))
		{
			$coverImage = explode('#', $this->options['coverImage']);
			$this->options['coverImage'] = 'url("' . Uri::base() . reset($coverImage) . '")';
		}
		
		$atts = [
			'data-video-id="' . $videoID . '"',
			'data-video-controls="' . var_export($this->options['controls'], true) . '"',
			'data-video-type="' . $videoProvider . '"',
			'data-video-mute="' . var_export($this->options['mute'], true) . '"',
			'data-video-loop="' . var_export($this->options['loop'], true) . '"',
			'data-video-start="' . $this->options['start'] . '"',
			'data-video-end="' . $this->options['end'] . '"',
			'data-video-autoplay="' . var_export($this->options['autoplay'], true) . '"',
			'data-video-fs="' . var_export($this->options['fs'], true) . '"',
			'data-video-autopause="' . var_export($this->options['autopause'], true) . '"',
			'data-video-cc="' . var_export($this->options['cc_load_policy'], true) . '"',
			'data-video-disablekb="' . var_export($this->options['disablekb'], true) . '"',
			'data-video-privacy="' . var_export($this->options['privacy'], true) . '"',
			'data-video-rel="' . $this->options['rel'] . '"',
			'data-video-color="' . $this->options['color'] . '"'
		];

		$this->options['atts'] = implode(' ', $atts);
	}

	/**
	 * We use the video widget layout file.
	 * 
	 * @return  string
	 */
	public function getName()
	{
		return 'video';
	}
	
	/**
	 * Loads media files
	 * 
	 * @return  void
	 */
	public function videoAssets()
	{
		HTMLHelper::script('plg_system_nrframework/widgets/video/youtube.js', ['relative' => true, 'version' => 'auto']);
	}
}