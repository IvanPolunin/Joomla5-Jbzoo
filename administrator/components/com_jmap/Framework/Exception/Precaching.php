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
 * JMap Exception object
 *
 * @package JMAP::FRAMEWORK::administrator::components::com_jmap
 * @subpackage framework
 * @subpackage exception
 * @since 2.3
 */
class Precaching extends \Exception {
	/**
	 * Error level
	 * @access private
	 * @var string
	 */
	private $exceptionLevel;
	
	/**
	 * Exception context
	 * @access private
	 * @var string
	 */
	private $context;
	
	/**
	 * Error level accessor method
	 * @access public
	 * @return string
	 */
	public function getContext() {
		return $this->context;
	}
	
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
	public function __construct($message, $level = 'error', $context = null, $code = null) {
		parent::__construct($message, $code);
	
		// Set error level
		$this->exceptionLevel = $level;
		
		// Set file info for SMVC core 
		$this->context = $context;
	}
}