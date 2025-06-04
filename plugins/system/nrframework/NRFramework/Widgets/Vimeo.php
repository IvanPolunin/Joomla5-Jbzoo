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
use Joomla\CMS\Uri\Uri;

class Vimeo extends Video
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

		// Whether controls will appear in the video
		'controls' => true,

		// Loop
		'loop' => false,

		// Mute
		'mute' => false,

		/**
		 * Set whether to load the video in privacy-enhanced mode.
		 * 
		 * When this is enabled, Vimeo will block the player from tracking
		 * any session data, including all cookies and analytics.
		 */
		'privacy' => false,

		// Whether to show the video title
		'title' => false,

		// Whether to show the author of the video
		'byline' => false,

		// Whether to show the author's profile image
		'portrait' => false,

		// The color of the video controls
		'color' => '#00adef',

		// Whether to allow keyboard inputs
		'keyboard' => false,

		// Enable to show the picture-in-picture button in the control bar
		'pip' => false,

		// Set the start time
		'start' => null,

		// Set the end time
		'end' => null,
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
		if ($videoProvider !== 'vimeo')
		{
			$this->options['value'] = null;
			return;
		}

		$this->options['css_class'] .= ' vimeo';

		$videoID = isset($videoDetails['id']) ? $videoDetails['id'] : '';

		if ($this->options['coverImageType'] === 'auto')
		{
			$this->options['coverImage'] = 'url("https://vumbnail.com/' . $videoID . '.jpg")';
		}
		else if ($this->options['coverImageType'] === 'custom' && !empty($this->options['coverImage']))
		{
			$coverImage = explode('#', $this->options['coverImage']);
			$this->options['coverImage'] = 'url("' . Uri::base() . reset($coverImage) . '")';
		}
		
		$atts = [
			'data-video-id="' . $videoID . '"',
			'data-video-type="' . $videoProvider . '"',
			'data-video-mute="' . var_export($this->options['mute'], true) . '"',
			'data-video-controls="' . var_export($this->options['controls'], true) . '"',
			'data-video-loop="' . var_export($this->options['loop'], true) . '"',
			'data-video-autoplay="' . var_export($this->options['autoplay'], true) . '"',
			'data-video-autopause="' . var_export($this->options['autopause'], true) . '"',
			'data-video-privacy="' . var_export($this->options['privacy'], true) . '"',
			'data-video-title="' . var_export($this->options['title'], true) . '"',
			'data-video-byline="' . var_export($this->options['byline'], true) . '"',
			'data-video-portrait="' . var_export($this->options['portrait'], true) . '"',
			'data-video-keyboard="' . var_export($this->options['keyboard'], true) . '"',
			'data-video-pip="' . var_export($this->options['pip'], true) . '"',
			'data-video-color="' . $this->options['color'] . '"',
			'data-video-start="' . $this->options['start'] . '"',
			'data-video-end="' . $this->options['end'] . '"',
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
		HTMLHelper::script('plg_system_nrframework/widgets/video/vimeo.js', ['relative' => true, 'version' => 'auto']);
	}
}