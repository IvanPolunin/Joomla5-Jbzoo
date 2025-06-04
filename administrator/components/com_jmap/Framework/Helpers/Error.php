<?php
namespace JExtstore\Component\JMap\Administrator\Framework\Helpers;
/**
 * @package JMAP::FRAMEWORK::administrator::components::com_jmap
 * @subpackage framework
 * @subpackage helpers
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') or die('Restricted access');

/**
 * Base class for error objects aware
 */
class Error {
	/**
	 * An array of error messages or Exception objects.
	 *
	 * @var array
	 */
	protected $_errors = array ();

	/**
	 * Get the most recent error message.
	 *
	 * @param integer $i
	 *        	Option error index.
	 * @param boolean $toString
	 *        	Indicates if Exception objects should return their error message.
	 *        	
	 * @return string Error message
	 */
	public function getError($i = null, $toString = true) {
		// Find the error
		if ($i === null) {
			// Default, return the last message
			$error = end ( $this->_errors );
		} elseif (! \array_key_exists ( $i, $this->_errors )) {
			// If $i has been specified but does not exist, return false
			return false;
		} else {
			$error = $this->_errors [$i];
		}

		// Check if only the string is requested
		if ($error instanceof \Exception && $toString) {
			return $error->getMessage ();
		}

		return $error;
	}

	/**
	 * Return all errors, if any.
	 *
	 * @return array Array of error messages.
	 */
	public function getErrors() {
		return $this->_errors;
	}

	/**
	 * Add an error message.
	 *
	 * @param string $error
	 *        	Error message.
	 *        	
	 * @return void
	 */
	public function setError($error) {
		$this->_errors [] = $error;
	}
}
