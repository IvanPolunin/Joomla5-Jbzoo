<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

namespace NRFramework\Widgets;

defined('_JEXEC') or die;

use Joomla\Filesystem\Folder;

class Helper
{
    /**
     * This is a map with all widgets used for caching the widget's class name
     *
     * @var array
     */
    public static $widgets_map = [];

	/**
	 * Renders a Widget and returns 
	 * 
	 * @param   array   $options	A list of attributes passed to the layout
	 * 
	 * @return  string  The widget's final HTML layout
	 */
	public static function render($widget_name, $options = [])
	{
        if (!$widgetClass = self::find($widget_name))
        {
            return;
        }

		$class = __NAMESPACE__ . '\\' . $widgetClass;

		// ensure class exists
		if (!class_exists($class))
		{
			return;
		}

		return (new $class($options))->render();
    }

    /**
     * Return the real class name of a widget by a case-insensitive name.
     *
     * @param   string   $name   The widget's name
     * 
     * @return  mixed    Null when the class name is not found, string when the class name is found.
     */
    public static function find($name)
    {
        if (!$name)
        {
            return;
        }

        $name = strtolower($name);

        if (empty(self::$widgets_map) || !isset(self::$widgets_map[$name]))
        {
            $widgetClasses = Folder::files(__DIR__);
            foreach ($widgetClasses as $widgetClass)
            {
                $widgetClass = str_replace('.php', '', $widgetClass);
    
                self::$widgets_map[strtolower($widgetClass)] = $widgetClass;
            }
        }

        return isset(self::$widgets_map[$name]) ? self::$widgets_map[$name] : null;
    }

	/**
	 * Returns all layout overrides of a widget by its name.
	 * 
	 * @param   string  $name
	 * 
	 * @return  array
	 */
	public static function getLayoutOverrides($name = '')
	{
		if (!$name)
		{
			return;
		}

		$path = self::getLayoutOverridePath($name);

		if (!is_dir($path))
		{
			return;
		}

		$labels = array_diff(scandir($path), ['.', '..', '.DS_Store']);
		$values = array_map(function($value) {
			return rtrim($value, '.php');
		}, $labels);

		return array_combine($values, $labels);
	}

	/**
	 * Returns the layout override path of a widget by its name.
	 * 
	 * @param   string  $name
	 * 
	 * @return  string
	 */
	public static function getLayoutOverridePath($name = '')
	{
		if (!$name)
		{
			return;
		}

		return implode(DIRECTORY_SEPARATOR, [JPATH_SITE, 'templates', \NRFramework\Helpers\Template::getTemplateName(), 'html', 'tassos', 'widgets', $name]);
	}
}