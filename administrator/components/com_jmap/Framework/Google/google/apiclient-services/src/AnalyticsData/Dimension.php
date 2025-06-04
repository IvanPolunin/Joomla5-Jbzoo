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
class Dimension extends \Google\Model {
	protected $dimensionExpressionType = DimensionExpression::class;
	protected $dimensionExpressionDataType = '';
	public $name;

	/**
	 *
	 * @param
	 *        	DimensionExpression
	 */
	public function setDimensionExpression(DimensionExpression $dimensionExpression) {
		$this->dimensionExpression = $dimensionExpression;
	}
	/**
	 *
	 * @return DimensionExpression
	 */
	public function getDimensionExpression() {
		return $this->dimensionExpression;
	}
	public function setName($name) {
		$this->name = $name;
	}
	public function getName() {
		return $this->name;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( Dimension::class, 'Google_Service_AnalyticsData_Dimension' );
