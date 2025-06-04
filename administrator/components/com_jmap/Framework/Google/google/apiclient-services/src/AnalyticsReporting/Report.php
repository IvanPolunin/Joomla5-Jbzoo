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
class Report extends \Google\Model {
	protected $columnHeaderType = ColumnHeader::class;
	protected $columnHeaderDataType = '';
	protected $dataType = ReportData::class;
	protected $dataDataType = '';
	public $nextPageToken;

	/**
	 *
	 * @param
	 *        	ColumnHeader
	 */
	public function setColumnHeader(ColumnHeader $columnHeader) {
		$this->columnHeader = $columnHeader;
	}
	/**
	 *
	 * @return ColumnHeader
	 */
	public function getColumnHeader() {
		return $this->columnHeader;
	}
	/**
	 *
	 * @param
	 *        	ReportData
	 */
	public function setData(ReportData $data) {
		$this->data = $data;
	}
	/**
	 *
	 * @return ReportData
	 */
	public function getData() {
		return $this->data;
	}
	public function setNextPageToken($nextPageToken) {
		$this->nextPageToken = $nextPageToken;
	}
	public function getNextPageToken() {
		return $this->nextPageToken;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( Report::class, 'Google_Service_AnalyticsReporting_Report' );
