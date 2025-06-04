<?php

namespace Google\Service\Webmasters;

/**
 *
 * @package JMAP::FRAMEWORK::administrator::components::com_jmap
 * @subpackage framework
 * @subpackage google
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ();
class ApiDimensionFilter extends \Google\Model {
	public $dimension;
	public $expression;
	public $operator;
	public function setDimension($dimension) {
		$this->dimension = $dimension;
	}
	public function getDimension() {
		return $this->dimension;
	}
	public function setExpression($expression) {
		$this->expression = $expression;
	}
	public function getExpression() {
		return $this->expression;
	}
	public function setOperator($operator) {
		$this->operator = $operator;
	}
	public function getOperator() {
		return $this->operator;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( ApiDimensionFilter::class, 'Google_Service_Webmasters_ApiDimensionFilter' );
