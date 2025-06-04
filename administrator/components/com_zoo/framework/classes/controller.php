<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;

/**
 * The base Controller Class
 *
 * @package Framework.Classes
 */
#[\AllowDynamicProperties]
class AppController extends BaseController {

	/**
	 * Reference to the global App class
	 *
	 * @var App
	 * @since 1.0.0
	 */
	public $zoo;

	/**
	 * Reference to the request Helper
	 *
	 * @var RequestHelper
	 * @since 1.0.0
	 */
	public $request;

	/**
	 * The scope of the current request
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $option;

	/**
	 * The name of the controller
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $controller;

    /**
     * The Application
     *
     * @var    CMSApplication|null
     * @since  4.0.0
     */
    protected $app;

	/**
	 * Class Constructor
	 *
	 * @param App The reference to the global app object
	 * @param array An array of configuration values to pass to the controller
	 *
	 * @since 1.0.0
	 */
	public function __construct($app, $config = array()) {
		parent::__construct($config);

		// init vars
		$this->zoo        = $app;
		$this->request    = $app->request;
		$this->option     = $app->system->application->scope;
		$this->controller = $this->getName();

        // Joomla 3
        if (!isset($this->app)) {
            $this->app = $this->zoo->system->application;
        }
	}

	/**
	 * @inheritdoc
	 */
	public function getView($name = '', $type = '', $prefix = '', $config = array()) {
        return parent::getView($name, $type ?: Factory::getDocument()->getType(), $prefix, $config);
	}

    /**
     * @inheritdoc
     */
    protected function createView($name, $prefix = '', $type = '', $config = array()) {
        $view = new AppView(array_merge(array('name' => $name, 'template_path' => JPATH_COMPONENT. '/views/' . $name . '/tmpl'), $config));

        // automatically pass all public class variables on to view
        foreach (get_object_vars($this) as $var => $value) {

            if ($var == 'app') {
                continue;
            }

            if ($var == 'zoo') {
                $var = 'app';
            }

            if (substr($var, 0, 1) != '_') {
                $view->set($var, $value);
            }
        }

        return $view;
    }

 	/**
	 * Binds a named array/hash to an object
	 *
	 * @param object $object The object to which we'll bind the properties to
	 * @param array|object $data An array or object containing the data to be bound
	 * @param array|string $ignore An array or a space separated list of fields to ignore during the binding
	 *
	 * @since 1.0.0
	 */
	public static function bind($object, $data, $ignore = array()) {

		if (!is_array($data) && !is_object($data)) {
			throw new AppException(__CLASS__.'::bind() failed. Invalid from argument');
		}

		if (is_object($data)) {
			$data = get_object_vars($data);
		}

		if (!is_array($ignore)) {
			$ignore = explode(' ', $ignore);
		}

		foreach (get_object_vars($object) as $k => $v) {

			// ignore protected attributes
			if ('_' == substr($k, 0, 1)) {
				continue;
			}

			// internal attributes of an object are ignored
			if (isset($data[$k]) && !in_array($k, $ignore)) {
				$object->$k = $data[$k];
			}
		}
	}

	/**
	 * Translate a string into the current language
	 *
	 * @param string $string The string to translate
	 * @param boolean $js_safe If the string should be made javascript safe
	 *
	 * @return string The translated string
	 */
	public function l($string, $js_safe = false) {
		return $this->zoo->language->l($string, $js_safe);
	}

}

/**
 * The exception class dedicated for the controller classes
 *
 * @see AppController
 */
class AppControllerException extends AppException {}
