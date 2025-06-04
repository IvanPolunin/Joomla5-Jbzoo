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
class DimensionExpression extends \Google\Model {
	protected $concatenateType = ConcatenateExpression::class;
	protected $concatenateDataType = '';
	protected $lowerCaseType = CaseExpression::class;
	protected $lowerCaseDataType = '';
	protected $upperCaseType = CaseExpression::class;
	protected $upperCaseDataType = '';

	/**
	 *
	 * @param
	 *        	ConcatenateExpression
	 */
	public function setConcatenate(ConcatenateExpression $concatenate) {
		$this->concatenate = $concatenate;
	}
	/**
	 *
	 * @return ConcatenateExpression
	 */
	public function getConcatenate() {
		return $this->concatenate;
	}
	/**
	 *
	 * @param
	 *        	CaseExpression
	 */
	public function setLowerCase(CaseExpression $lowerCase) {
		$this->lowerCase = $lowerCase;
	}
	/**
	 *
	 * @return CaseExpression
	 */
	public function getLowerCase() {
		return $this->lowerCase;
	}
	/**
	 *
	 * @param
	 *        	CaseExpression
	 */
	public function setUpperCase(CaseExpression $upperCase) {
		$this->upperCase = $upperCase;
	}
	/**
	 *
	 * @return CaseExpression
	 */
	public function getUpperCase() {
		return $this->upperCase;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( DimensionExpression::class, 'Google_Service_AnalyticsData_DimensionExpression' );
