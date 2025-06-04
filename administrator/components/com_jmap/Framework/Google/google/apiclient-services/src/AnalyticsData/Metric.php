<?php

namespace Google\Service\AnalyticsData;

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
class Metric extends \Google\Model {
	public $expression;
	public $invisible;
	public $name;
	public function setExpression($expression) {
		$this->expression = $expression;
	}
	public function getExpression() {
		return $this->expression;
	}
	public function setInvisible($invisible) {
		$this->invisible = $invisible;
	}
	public function getInvisible() {
		return $this->invisible;
	}
	public function setName($name) {
		$this->name = $name;
	}
	public function getName() {
		return $this->name;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( Metric::class, 'Google_Service_AnalyticsData_Metric' );
