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
class PivotHeader extends \Google\Collection {
	protected $collection_key = 'pivotDimensionHeaders';
	protected $pivotDimensionHeadersType = PivotDimensionHeader::class;
	protected $pivotDimensionHeadersDataType = 'array';
	public $rowCount;

	/**
	 *
	 * @param
	 *        	PivotDimensionHeader[]
	 */
	public function setPivotDimensionHeaders($pivotDimensionHeaders) {
		$this->pivotDimensionHeaders = $pivotDimensionHeaders;
	}
	/**
	 *
	 * @return PivotDimensionHeader[]
	 */
	public function getPivotDimensionHeaders() {
		return $this->pivotDimensionHeaders;
	}
	public function setRowCount($rowCount) {
		$this->rowCount = $rowCount;
	}
	public function getRowCount() {
		return $this->rowCount;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( PivotHeader::class, 'Google_Service_AnalyticsData_PivotHeader' );
