<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

namespace NRFramework\Helpers;

defined('_JEXEC') or die;

use NRFramework\Cache;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;

class Responsive
{
	/**
	 * Renders the given CSS.
	 * 
	 * @param   array   $css
	 * @param   string  $selector
	 * 
	 * @return  string
	 */
	public static function renderResponsiveCSS($css, $selector = '')
	{
		if (!$css || !is_array($css))
		{
			return;
		}
		
		$output = '';

		foreach (self::getBreakpoints() as $breakpoint => $breakpoint_data)
		{
			if (!isset($css[$breakpoint]) || empty($css[$breakpoint]))
			{
				continue;
			}

			/**
			 * If we were given an array of strings of CSS, transform them to a string so we can output it.
			 * 
			 * i.e. transform
			 * [
			 * 	 'color: #fff;',
			 * 	 'background: #000;'
			 * ]
			 * 
			 * to:
			 * 
			 * 'color: #fff;background: #000;'
			 */
			if (!is_string($css[$breakpoint]))
			{
				$css[$breakpoint] = implode('', $css[$breakpoint]);
			}

			$max_width = isset($breakpoint_data['max_width']) ? $breakpoint_data['max_width'] : '';
			
			$output .= self::getGenericTemplate($css[$breakpoint], $max_width, $selector);
		}

		return $output;
	}

	/**
	 * Returns the responsive output of a specific media query size.
	 * 
	 * @param   string  $css		 The Custom CSS
	 * @param   int		$size	     This is the max-width in pixels
	 * @param   string  $selector    The CSS Selector to apply the CSS
	 * 
	 * @return  string
	 */
	public static function getGenericTemplate($css, $size = '', $selector = '')
	{
		if (!is_string($css) || !is_scalar($size) || !is_string($selector))
		{
			return '';
		}
		
		$selector_prefix = $selector_suffix = $size_prefix = $size_suffix = '';
		
		if (!empty($size))
		{
			$size_prefix = '@media screen and (max-width: ' . $size . 'px){';
			$size_suffix = '}';
		}

		if (!empty($selector))
		{
			$selector_prefix = $selector . '{';
			$selector_suffix = '}';
		}
		
		return $size_prefix . $selector_prefix . $css . $selector_suffix . $size_suffix;
	}

    /**
     * Returns all breakpoints.
     * 
     * @return  array
     */
    public static function getBreakpoints()
    {
		$breakpointsSettings = self::getBreakpointsSettings();

		$tablet_max_width = isset($breakpointsSettings['tablet']) && !empty($breakpointsSettings['tablet']) ? $breakpointsSettings['tablet'] : 1024;
		$mobile_max_width = isset($breakpointsSettings['mobile']) && !empty($breakpointsSettings['mobile']) ? $breakpointsSettings['mobile'] : 575;
		
        return [
            'desktop' => [
                'icon' => '<svg width="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><mask id="mask0_112_458" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="24" height="24"><rect width="24" height="24" fill="#D9D9D9"/></mask><g mask="url(#mask0_112_458)"><path d="M8.5 20.5V19H10.5V17H4.3077C3.80257 17 3.375 16.825 3.025 16.475C2.675 16.125 2.5 15.6974 2.5 15.1923V5.3077C2.5 4.80257 2.675 4.375 3.025 4.025C3.375 3.675 3.80257 3.5 4.3077 3.5H19.6923C20.1974 3.5 20.625 3.675 20.975 4.025C21.325 4.375 21.5 4.80257 21.5 5.3077V15.1923C21.5 15.6974 21.325 16.125 20.975 16.475C20.625 16.825 20.1974 17 19.6923 17H13.5V19H15.5V20.5H8.5ZM4.3077 15.5H19.6923C19.7692 15.5 19.8397 15.468 19.9038 15.4039C19.9679 15.3398 20 15.2692 20 15.1923V5.3077C20 5.23077 19.9679 5.16024 19.9038 5.09613C19.8397 5.03203 19.7692 4.99998 19.6923 4.99998H4.3077C4.23077 4.99998 4.16024 5.03203 4.09613 5.09613C4.03202 5.16024 3.99998 5.23077 3.99998 5.3077V15.1923C3.99998 15.2692 4.03202 15.3398 4.09613 15.4039C4.16024 15.468 4.23077 15.5 4.3077 15.5Z" fill="currentColor"/></g></svg>',
                'label' => Text::_('NR_DESKTOP'),
                'desc' => Text::_('NR_DESKTOPS_WITH_BREAKPOINT_INFO')
            ],
            'tablet' => [
                'icon' => '<svg width="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><mask id="mask0_112_446" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="24" height="24"><rect width="24" height="24" fill="#D9D9D9"/></mask><g mask="url(#mask0_112_446)"><path d="M12 20.2692C12.2448 20.2692 12.4535 20.183 12.6259 20.0105C12.7984 19.8381 12.8846 19.6294 12.8846 19.3846C12.8846 19.1397 12.7984 18.9311 12.6259 18.7586C12.4535 18.5862 12.2448 18.5 12 18.5C11.7551 18.5 11.5465 18.5862 11.374 18.7586C11.2016 18.9311 11.1154 19.1397 11.1154 19.3846C11.1154 19.6294 11.2016 19.8381 11.374 20.0105C11.5465 20.183 11.7551 20.2692 12 20.2692ZM5.3077 22.5C4.80898 22.5 4.38302 22.3233 4.02982 21.9701C3.67661 21.6169 3.5 21.191 3.5 20.6923V3.3077C3.5 2.80898 3.67661 2.38302 4.02982 2.02982C4.38302 1.67661 4.80898 1.5 5.3077 1.5H18.6923C19.191 1.5 19.6169 1.67661 19.9701 2.02982C20.3233 2.38302 20.5 2.80898 20.5 3.3077V20.6923C20.5 21.191 20.3233 21.6169 19.9701 21.9701C19.6169 22.3234 19.191 22.5 18.6923 22.5L5.3077 22.5ZM4.99997 17.7692V20.6923C4.99997 20.782 5.02883 20.8557 5.08653 20.9134C5.14423 20.9711 5.21795 21 5.3077 21H18.6923C18.782 21 18.8557 20.9711 18.9134 20.9134C18.9711 20.8557 19 20.782 19 20.6923V17.7692H4.99997ZM4.99997 16.2692H19V5.74995H4.99997V16.2692ZM4.99997 4.25H19V3.3077C19 3.21795 18.9711 3.14423 18.9134 3.08652C18.8557 3.02882 18.782 2.99998 18.6923 2.99998H5.3077C5.21795 2.99998 5.14423 3.02882 5.08653 3.08652C5.02883 3.14423 4.99997 3.21795 4.99997 3.3077V4.25Z" fill="currentColor"/></g></svg>',
                'label' => Text::_('NR_TABLET'),
                'desc' => Text::sprintf('NR_TABLETS_WITH_BREAKPOINT_INFO', $tablet_max_width),
				'max_width' => $tablet_max_width
            ],
            'mobile' => [
                'icon' => '<svg width="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><mask id="mask0_112_452" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="24" height="24"><rect width="24" height="24" fill="#D9D9D9"/></mask><g mask="url(#mask0_112_452)"><path d="M7.3077 22.4999C6.80257 22.4999 6.375 22.3249 6.025 21.9749C5.675 21.6249 5.5 21.1974 5.5 20.6923V3.3077C5.5 2.80257 5.675 2.375 6.025 2.025C6.375 1.675 6.80257 1.5 7.3077 1.5H16.6922C17.1974 1.5 17.625 1.675 17.975 2.025C18.325 2.375 18.5 2.80257 18.5 3.3077V20.6923C18.5 21.1974 18.325 21.625 17.975 21.975C17.625 22.325 17.1974 22.5 16.6922 22.5L7.3077 22.4999ZM6.99997 19.75V20.6923C6.99997 20.7692 7.03202 20.8397 7.09613 20.9039C7.16024 20.968 7.23077 21 7.3077 21H16.6922C16.7692 21 16.8397 20.968 16.9038 20.9039C16.9679 20.8397 17 20.7692 17 20.6923V19.75H6.99997ZM6.99997 18.25H17V5.74998H6.99997V18.25ZM6.99997 4.25003H17V3.30773C17 3.23079 16.9679 3.16027 16.9038 3.09615C16.8397 3.03205 16.7692 3 16.6922 3H7.3077C7.23077 3 7.16024 3.03205 7.09613 3.09615C7.03202 3.16027 6.99997 3.23079 6.99997 3.30773V4.25003Z" fill="currentColor"/></g></svg>',
                'label' => Text::_('NR_MOBILE'),
                'desc' => Text::sprintf('NR_MOBILES_WITH_BREAKPOINT_INFO', $mobile_max_width),
				'max_width' => $mobile_max_width
            ]
        ];
    }

	public static function getBreakpointsSettings()
	{
        $hash = 'tassosResponsiveBreakpoints';

        if (Cache::has($hash))
        {
            return Cache::get($hash);
        }

		$settings = PluginHelper::getPlugin('system', 'nrframework');

		$default = [
			'desktop' => 'any',
			'tablet' => 1024,
			'mobile' => 575
		];
		
		if (!isset($settings->params))
		{
			return $default;
		}

		if (!$params = json_decode($settings->params, true))
		{
			return $default;
		}

		$data = isset($params['breakpoints']) ? $params['breakpoints'] : [];

        return Cache::set($hash, $data);
	}
}