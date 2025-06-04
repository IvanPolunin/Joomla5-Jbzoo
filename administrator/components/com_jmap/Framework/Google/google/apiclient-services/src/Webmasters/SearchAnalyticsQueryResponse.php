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
class SearchAnalyticsQueryResponse extends \Google\Collection {
	protected $collection_key = 'rows';
	public $responseAggregationType;
	protected $rowsType = ApiDataRow::class;
	protected $rowsDataType = 'array';
	public function setResponseAggregationType($responseAggregationType) {
		$this->responseAggregationType = $responseAggregationType;
	}
	public function getResponseAggregationType() {
		return $this->responseAggregationType;
	}
	/**
	 *
	 * @param
	 *        	ApiDataRow[]
	 */
	public function setRows($rows) {
		$this->rows = $rows;
	}
	/**
	 *
	 * @return ApiDataRow[]
	 */
	public function getRows() {
		return $this->rows;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( SearchAnalyticsQueryResponse::class, 'Google_Service_Webmasters_SearchAnalyticsQueryResponse' );
