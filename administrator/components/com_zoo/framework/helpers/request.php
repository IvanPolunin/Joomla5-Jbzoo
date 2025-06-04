<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Joomla\Input\Input;

/**
 * Helper do deal with request variables. Wrapper for JRequest
 *
 * @package Framework.Helpers
 */
class RequestHelper extends AppHelper {

	/**
	 * Get a variable from the request
	 *
	 * @param string $var The name of the variable
	 * @param string $type The type of the variale (string, int, float, bool, array, word, cmd)
	 * @param mixed $default The default value
	 *
	 * @return mixed The value of the variable
	 *
	 * @see Input::get()
	 *
	 * @since 1.0.0
	 */
    public function get($var, $type, $default = null) {

        /**
         * @var string $name
         * @var string $hash
         */
		extract($this->_parse($var));

		// get hash array, if name is empty
		if ($name == '') {
            $hash = strtolower($hash);
            $type = $type === 'array' ? 'unknown' : $type;
			return $this->app->system->application->input->$hash->getArray([], null, $type);
		}

		// access a array value ?
		if (strpos($name, '.') !== false) {

			$parts = explode('.', $name);
            $array = $this->getVar(array_shift($parts), $default, $hash, null, $type);

			foreach ($parts as $part) {

				if (!is_array($array) || !isset($array[$part])) {
					return $default;
				}

				$array =& $array[$part];
			}

			return $array;
		}

        return $this->getVar($name, $default, $hash, $type);
    }

	/**
	 * Set a request variable value
	 *
	 * @param string $var The variable name (hash:name)
	 * @param mixed $value The value to set
	 *
	 * @return RequestHelper $this for chaining support
	 *
	 * @since 1.0.0
	 */
    public function set($var, $value = null) {

		// parse variable name
		extract($this->_parse($var));

		if (!empty($name)) {

			// set a array value ?
			if (strpos($name, '.') !== false) {

				$parts = explode('.', $name);
				$name  = array_shift($parts);
				$array = $this->app->system->application->input->get($name, [], 'array');
				$val   =& $array;

				foreach ($parts as $i => $part) {

					if (!isset($array[$part])) {
						$array[$part] = array();
					}

					if (isset($parts[$i + 1])) {
						$array =& $array[$part];
					} else {
						$array[$part] = $value;
					}
				}

				$value = $val;
			}

			$this->app->system->application->input->set($name, $value);
		}

		return $this;
    }

	/**
	 * Map all the methods to the mapped class
	 *
	 * @param string $method The name of the method
	 * @param array $args The list of arguments to pass on to the method
	 *
	 * @return mixed The result of the call
	 *
	 * @see Input
	 *
	 * @since 1.0.0
	 */
    public function __call($method, $args) {
        return $this->_call(array(
            $this->getInput(!empty($args[2]) ? $args[2] : 'default'),
            $method
        ), $args);
    }

    /**
     * Compatibility Joomla 3
     */
    public function getVar($name, $default = null, $hash = 'default', $type = 'none', $mask = 0) {

        if ($mask === 2 || $mask === 'raw') {
            $type = 'raw';
        }

        return $this->getInput($hash)->get($name, $default, isset($type) ? $type : '');
    }

    /**
     * Compatibility Joomla 3
     */
    public function setVar($name, $value = null, $hash = 'method', $overwrite = true) {

        // If overwrite is true, makes sure the variable hasn't been set yet
        if (!$overwrite && array_key_exists($name, $_REQUEST))
        {
            return $_REQUEST[$name];
        }

        if ($hash === 'method') {
            $hash = strtolower($_SERVER['REQUEST_METHOD']);
        }

        $this->getInput($hash)->set($name, $value);

        if (in_array($hash, ['get', 'post'])) {
            $this->app->system->application->input->set($name, $value);
        }
    }

	/**
	 * Parse a variable string
	 *
	 * @param string $var The variable string to parse
	 *
	 * @return string[] An array containing the hash and the name of the variable
	 *
	 * @since 1.0.0
	 */
	protected function _parse($var) {

	    // init vars
		$parts = explode(':', $var, 2);
		$count = count($parts);
		$name  = '';
		$hash  = 'default';

		// parse variable name
		if ($count == 1) {
			list($name) = $parts;
		} elseif ($count == 2) {
			list($hash, $name) = $parts;
		}

		return compact('hash', 'name');
    }

    protected function getInput($hash = 'method') {
        $hash = strtolower($hash);

        if ($hash === 'method') {
            $hash = strtolower($_SERVER['REQUEST_METHOD']);
        }

        if ($hash === 'default' || !$hash) {
            $hash = 'request';
        }

        if ($hash === 'request') {
            return $this->app->system->application->input;
        }

        return $this->app->system->application->input->$hash;
    }

}
