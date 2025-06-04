<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            http://www.tassos.gr
 * @copyright       Copyright © 2022 Tassos Marinos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

namespace NRFramework\Helpers\Controls;

defined('_JEXEC') or die;

class CSS
{
    public static function generateCSS($styles = [])
	{
		if (!$styles || !is_array($styles))
		{
			return;
		}

		$css = '';
		foreach ($styles as $breakpoint => $array)
		{
			if (!$selectors = self::groupCSSBySelectors($array))
			{
				continue;
			}

			$css_tmp = '';

			// Get all the CSS for this breakpoint for all selectors
			foreach ($selectors as $selector => $_styles)
			{
				$css_tmp .= $selector . '{' . implode('', $_styles) . '}';
			}

			// Then enapsulate all the breakpoint CSS in the breakpoint media query
			$css_tmp = \NRFramework\Helpers\Responsive::renderResponsiveCSS([
				$breakpoint => [$css_tmp]
			]);

			if (!$css_tmp)
			{
				continue;
			}

			$css .= $css_tmp;
		}

        return $css;
	}
	
	public static function groupCSSBySelectors($styles = [])
	{
		if (!$styles)
		{
			return;
		}

		$selectors = [];

		foreach ($styles as $style)
		{
			$selectors[$style['selector']][] = $style['css'];
		}

		if (!$selectors)
		{
			return;
		}

		return $selectors;
	}
}