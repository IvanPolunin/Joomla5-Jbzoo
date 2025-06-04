<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Filter\OutputFilter;

/**
 * The general String Helper.
 *
 * @package Component.Helpers
 * @since 2.0
 */
class StringHelper extends AppHelper {

	/**
	 * Map all functions to StringHelper class
	 *
	 * @param string $method Method name
	 * @param array $args Method arguments
	 *
	 * @return mixed
	 */
	public function __call($method, $args) {
		return $this->_call(array(Joomla\String\StringHelper::class, $method), $args);
	}

	/**
	 * Truncates the input string.
	 *
	 * @param string $text input string
	 * @param int $length the length of the output string
	 * @param string $truncate_string the truncate string
	 *
	 * @return string The truncated string
	 * @since 2.0
	 */
	public function truncate($text, $length = 30, $truncate_string = '...') {

		if ($text == '') {
			return '';
		}

		if ($this->strlen($text) > $length) {
			$length -= min($length, strlen($truncate_string));
			$text = preg_replace('/\s+?(\S+)?$/', '', substr($text, 0, $length + 1));

			return $this->substr($text, 0, $length).$truncate_string;
		} else {
			return $text;
		}
	}

	/**
	 * Sluggifies the input string.
	 *
	 * @param string $string 		input string
	 * @param bool   $force_safe 	Do we have to enforce ASCII instead of UTF8 (default: false)
	 *
	 * @return string sluggified string
	 * @since 2.0
	 */
	public function sluggify($string, $force_safe = false) {

		$string = $this->strtolower((string) $string);
        $string = $this->str_ireplace(array('$',','), '', $string);

		if ($force_safe) {
			$string = OutputFilter::stringURLSafe($string);
		} else {
			$string = ApplicationHelper::stringURLSafe($string);
		}

		return trim($string);
	}

    /**
     * Apply Joomla text filters based on the user's groups
     *
     * @param  string|array $string The string to clean
     *
     * @return string|array The cleaned string
     */
    public function applyTextFilters($string) {
        if (is_array($string)) {
            foreach ($string as $k => $v) {
                $string[$k] = $this->applyTextFilters($v);
            }
            return $string;
        }

        return ComponentHelper::filterText((string) $string);
    }

	/**
	 * Converts string to camel case (https://en.wikipedia.org/wiki/Camel_case).
	 *
	 * @param string|string[] $string
	 * @param bool            $upper
	 *
	 * @return string
	 */
	public function camelCase($string, $upper = false)
	{
		$string = join(' ', (array) $string);
		$string = str_replace(array('-', '_'), ' ', $string);
		$string = str_replace(' ', '', ucwords($string));

		return $upper ? $string : lcfirst($string);
	}
}
