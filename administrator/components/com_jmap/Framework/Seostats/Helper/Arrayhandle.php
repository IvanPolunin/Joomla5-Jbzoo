<?php
namespace JExtstore\Component\JMap\Administrator\Framework\Seostats\Helper;
/**
 *
 * @package JMAP::SEOSTATS::administrator::components::com_jmap
 * @subpackage seostats
 * @subpackage helper
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

/**
 * Array handler helper
 *
 * @package JMAP::SEOSTATS::administrator::components::com_jmap
 * @subpackage seostats
 * @subpackage helper
 * @since 3.3
 */
class Arrayhandle {
	
	protected $array = array();
	
	/**
	 * Push element into array structure
	 *
	 * @param mixed $element
	 * @return void
	 */
	public function push($element) {
		$this->array [] = $element;
	}
	
	/**
	 * Push element into array structure by key
	 *
	 * @param mixed $element
	 * @return void
	 */
	public function setElement($key, $element) {
		$this->array [$key] [] = $element;
	}
	
	/**
	 * Return the array structure count
	 *
	 * @param mixed $element
	 * @return int
	 */
	public function count() {
		return count ( $this->array );
	}
	
	/**
	 * Cast class to array
	 *
	 * @return array
	 */
	public function toArray() {
		return $this->array;
	}
}
