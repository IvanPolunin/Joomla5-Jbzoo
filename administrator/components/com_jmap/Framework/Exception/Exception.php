<?php
namespace JExtstore\Component\JMap\Administrator\Framework;
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
 * JMap Exception object
 *
 * @package JMAP::FRAMEWORK::administrator::components::com_jmap
 * @subpackage framework
 * @subpackage exception
 * @since 1.0.5
 */
class Exception extends \Exception {
	/**
	 * Error level
	 * @access private
	 * @var string
	 */
	private $exceptionLevel;
	
	/**
	 * Error level accessor method
	 * @access public
	 * @return string
	 */
	public function getExceptionLevel() {
		return $this->exceptionLevel;
	}
	
	/**
	 * Class constructor
	 * @access public
	 * @return Object&
	 */
	public function __construct($message, $level, $code = 0) {
		parent::__construct($message, $code);
	
		// Set error level
		$this->exceptionLevel = $level;
	}
}