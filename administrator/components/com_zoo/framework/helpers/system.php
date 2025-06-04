<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;

/**
 * Joomla System Helper. Provides integration with the underlying Joomla! system
 *
 * @package Framework.Helpers
 * @property CMSApplication $application
 */
class SystemHelper extends AppHelper {

	/**
	 * Variables that can be fetched through Factory
	 *
	 * @var array
	 * @since 1.0.0
	 */
	protected static $_factory = array('application', 'config', 'language', 'user', 'session', 'document', 'acl', 'template', 'dbo', 'mailer', 'editor');

	/**
	 * Class constructor
	 *
	 * @param App $app A reference to the global App object
	 */
	public function __construct($app) {
		parent::__construct($app);
	}

	/**
	 * Wraps Joomla hash method
	 *
	 * @param string $seed
	 *
	 * @return string Md5 hash
	 *
	 * @since 3.6
	 */
	public function getHash($seed) {
		return ApplicationHelper::getHash($seed);
	}

	/**
	 * Get a Joomla environment variable
	 *
	 * @param string $name The name of the variable to retrieve
	 *
	 * @return mixed The variable
	 *
	 * @see SystemHelper::$_factory
	 *
	 * @since 1.0.0
	 */
	public function __get($name) {

		$name = strtolower($name);

        if ($name === 'itemid' && $this->application->isClient('site')) {
            $this->itemid = $this->app->request->get('Itemid', 'int', 0);
        }

		if (in_array($name, self::$_factory)) {
			return call_user_func(array(Factory::class, 'get'.$name));
		}

		return null;
	}

	/**
	 * Map all the methods to the Factory class
	 *
	 * @param string $method The name of the method
	 * @param array $args The list of arguments to pass on to the method
	 *
	 * @return mixed The result of the call
	 *
	 * @see Factory
	 *
	 * @since 1.0.0
	 */
    public function __call($method, $args) {
		return $this->_call(array(Factory::class, $method), $args);
    }

}
