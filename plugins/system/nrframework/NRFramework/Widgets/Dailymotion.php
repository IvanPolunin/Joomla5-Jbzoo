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

class Dailymotion extends Video
{
	/**
	 * Widget default options
	 *
	 * @var array
	 */
	protected $video_widget_options = [
		// Start the video from X seconds
		'start' => null,

		// End the video at X seconds
		'end' => null,

		// Loop
		'loop' => false,

		// Mute
		'mute' => false,

		// Whether controls will appear in the video
		'controls' => false,

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
		if ($videoProvider !== 'dailymotion')
		{
			$this->options['value'] = null;
			return;
		}

		$this->options['css_class'] .= ' dailymotion';

		$videoID = isset($videoDetails['id']) ? $videoDetails['id'] : '';

		if ($this->options['coverImageType'] === 'auto')
		{
			$this->options['coverImage'] = 'url("https://www.dailymotion.com/thumbnail/video/' . $videoID . '")';
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
			'data-video-loop="' . var_export($this->options['loop'], true) . '"',
			'data-video-start="' . $this->options['start'] . '"',
			'data-video-end="' . $this->options['end'] . '"',
			'data-video-autoplay="' . var_export($this->options['autoplay'], true) . '"',
			'data-video-autopause="' . var_export($this->options['autopause'], true) . '"',
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
		HTMLHelper::script('plg_system_nrframework/widgets/video/dailymotion.js', ['relative' => true, 'version' => 'auto']);
	}
}