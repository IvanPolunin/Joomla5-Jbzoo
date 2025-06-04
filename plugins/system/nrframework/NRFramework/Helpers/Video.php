<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

namespace NRFramework\Helpers;

defined('_JEXEC') or die;

class Video
{
	/**
	 * Returns the Video URL details.
	 * 
	 * Supported platforms:
	 * - YouTube
	 * - Vimeo
	 * 
	 * @param   string  $url
	 * 
	 * @return  array
	 */
	public static function getDetails($url)
	{
		$id = '';
		$provider = '';

		if (preg_match(self::getYouTubePattern(), $url, $matches))
		{
			$id = !empty($matches[1]) ? $matches[1] : $matches[2];
			$provider = 'youtube';
		}
		else if (preg_match(self::getVimeoPattern(), $url, $matches))
		{
			$id = !empty($matches[1]) ? $matches[1] : null;
			$provider = 'vimeo';
		}
		else if (preg_match(self::getFacebookVideoPattern(), $url))
		{
			$id = $url;
			$provider = 'facebookvideo';
		}
		else if (preg_match(self::getDailymotionPattern(), $url, $matches))
		{
			$id = end($matches);
			$provider = 'dailymotion';
		}
		
		return [
			'id' => $id,
			'provider' => $provider
		];
	}

	/**
	 * Get YouTube Pattern.
	 * 
	 * @return  string
	 */
	public static function getYouTubePattern()
	{
		return '/^https?:\/\/(?:m\.|www\.)?youtube\.com\/(?:watch\?v=|embed\/)?([a-zA-Z0-9_-]{11})|^https?:\/\/youtu\.be\/([a-zA-Z0-9_-]{11})/';
	}

	/**
	 * Get Vimeo Pattern.
	 * 
	 * @return  string
	 */
	public static function getVimeoPattern()
	{
		return '/^https?:\/\/(?:www\.)?(?:player\.)?vimeo\.com\/(\d+)/';
	}

	/**
	 * Get Facebook Video Pattern.
	 * 
	 * @return  string
	 */
	public static function getFacebookVideoPattern()
	{
		return '/^(?:(?:https?:)?\/\/)?(?:www\.)?facebook\.com\/(?:watch\/\?v=|[\w\.]+\/videos\/(?:[\w\.]+\/)?)?(\d+)/';
	}

	/**
	 * Get Dailymotion Pattern.
	 * 
	 * @return  string
	 */
	public static function getDailymotionPattern()
	{
		return '/(?:dailymotion\.com\/(?:video|hub)\/|dai\.ly\/)([a-zA-Z0-9]+)/';
	}
}