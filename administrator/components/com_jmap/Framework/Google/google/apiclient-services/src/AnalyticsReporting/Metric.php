<?php

namespace Google\Service\AnalyticsReporting;

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
	public $alias;
	public $expression;
	public $formattingType;
	public function setAlias($alias) {
		$this->alias = $alias;
	}
	public function getAlias() {
		return $this->alias;
	}
	public function setExpression($expression) {
		$this->expression = $expression;
	}
	public function getExpression() {
		return $this->expression;
	}
	public function setFormattingType($formattingType) {
		$this->formattingType = $formattingType;
	}
	public function getFormattingType() {
		return $this->formattingType;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( Metric::class, 'Google_Service_AnalyticsReporting_Metric' );
