<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

/**
 * Helper to manage error. Wrapper for JError
 *
 * @package Framework.Helpers
 */
class ErrorHelper extends AppHelper {

	/**
	 * Map all the methods of JError to the helper
	 *
	 * @param string $method The method name
	 * @param array $args The list of arguments to pass on to the method
	 *
	 * @since 1.0.0
	 */
    public function __call($method, $args) {

		if (version_compare(JVERSION, '4.0', '<')) {
			return $this->_call(array('JError', $method), $args);
		} else {
			$code = is_int($args[0]) ? $args[0] : 500;
			$message = is_int($args[0]) ? $args[1] : $args[0];
			$type = strtolower(str_replace('raise', '', $method));

			if ($type === 'error') {
				throw new \Exception($message, $code);
			}

			$this->app->system->application->enqueueMessage((string) $message, $type);
		}

    }

}
