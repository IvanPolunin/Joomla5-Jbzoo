<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Joomla\CMS\Session\Session;

/**
 * Helper for the session. Wrapper for Session
 *
 * @package Framework.Helpers
 *
 * @see Session
 */
class SessionHelper extends AppHelper {

	/**
	 * Map all the methods to the Session class
	 *
	 * @param string $method The name of the method
	 * @param array $args The list of arguments to pass on to the method
	 *
	 * @return mixed The result of the call
	 *
	 * @see Session
	 *
	 * @since 1.0.0
	 */
    public function __call($method, $args) {
		return $this->_call(array($this->app->system->session, $method), $args);
    }

}
