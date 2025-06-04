<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Joomla\CMS\Component\ComponentHelper;

/*
	Class: ElementFile
		The file element class
*/
abstract class ElementFile extends Element {

	protected $_extensions = '';

	/*
	   Function: Constructor
	*/
	public function __construct() {

		// call parent constructor
		parent::__construct();

		// set defaults
		$params = ComponentHelper::getParams('com_media');
		$this->config->set('directory', $params->get('file_path'));
	}

	/*
		Function: hasValue
			Checks if the element's value is set.

	   Parameters:
			$params - render parameter

		Returns:
			Boolean - true, on success
	*/
	public function hasValue($params = array()) {
		return !is_null($this->getValue($params));
	}

    /*
        Function: getValue
            Returns the element's value.

        Parameters:
            $params - render parameter

        Returns:
            Value
    */
    public function getValue($params = array()) {

        $file = $this->get('file');

        if (empty($file)) {
            return;
        }

        $file = $this->app->path->path("root:{$file}");
        $filename = basename($file);

        return !empty($file) && is_readable($file) && is_file($file)
            ? compact('file', 'filename')
            : null;
    }

    /*
        Function: getDirectory
            Returns the directory with trailing slash.

        Returns:
            String - directory
    */
	public function getDirectory() {
		return rtrim($this->config->get('directory'), '/').'/';
	}

	/*
	   Function: getExtension
	       Get the file extension string.

	   Returns:
	       String - file extension
	*/
	public function getExtension() {
		return $this->app->filesystem->getExtension($this->get('file'));
	}

	/*
		Function: loadAssets
			Load elements css/js assets.

		Returns:
			Void
	*/
	public function loadAssets() {
		parent::loadAssets();
		$this->app->document->addScript('assets:js/finder.js');
	}

	/*
		Function: files
			Get directory/file list JSON formatted

		Returns:
			Void
	*/
	public function files() {
		$files = array();
		$path = ltrim($this->app->request->get('path', 'string'), '/');
		$path = empty($path) ? '' : $path.'/';
		foreach ($this->app->path->dirs('root:'.$this->getDirectory().$path) as $dir) {
			$dir = mb_convert_encoding($dir, "UTF-8");
			$files[] = array('name' => basename($dir), 'path' => $path.$dir, 'type' => 'folder');
		}
		foreach ($this->app->path->files('root:'.$this->getDirectory().$path, false, '/^.*('.$this->_extensions.')$/i') as $file) {
			$file = mb_convert_encoding($file, "UTF-8");
			$files[] = array('name' => basename($file), 'path' => $this->getDirectory().$path.$file, 'type' => 'file');
		}

		return json_encode($files);
	}

}
