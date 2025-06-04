<?php
namespace JExtstore\Component\JMap\Administrator\Framework\Exception;
/**
 * @package JMAP::FRAMEWORK::administrator::components::com_jmap
 * @subpackage framework
 * @subpackage exception
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') or die('Restricted access');

/**
 * Base class for exceptions objects aware
 */
trait Exceptions {
	/**
	 * An array of exceptions messages or Exception objects.
	 *
	 * @var array
	 */
	protected $_exceptions = array ();

	/**
	 * Get the most recent exceptions message.
	 *
	 * @param integer $i
	 *        	Option exception index.
	 * @param boolean $toString
	 *        	Indicates if Exception objects should return their exceptions message.
	 *        	
	 * @return string exceptions message
	 */
	public function getException($i = null, $toString = true) {
		// Find the exceptions
		if ($i === null) {
			// Default, return the last message
			$exception = end ( $this->_exceptions );
		} elseif (! \array_key_exists ( $i, $this->_exceptions )) {
			// If $i has been specified but does not exist, return false
			return false;
		} else {
			$exception = $this->_exceptions [$i];
		}

		// Check if only the string is requested
		if ($exception instanceof \Exception && $toString) {
			return $exception->getMessage ();
		}

		return $exception;
	}

	/**
	 * Return all exceptionss, if any.
	 *
	 * @return array Array of exceptions messages.
	 */
	public function getExceptions() {
		return $this->_exceptions;
	}

	/**
	 * Add an exceptions message.
	 *
	 * @param string $exception
	 *        	exceptions message.
	 *        	
	 * @return void
	 */
	public function setException($exception) {
		$this->_exceptions [] = $exception;
	}
}