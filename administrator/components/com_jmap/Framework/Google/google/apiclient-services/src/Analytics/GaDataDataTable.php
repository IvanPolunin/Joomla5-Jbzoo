<?php

namespace Google\Service\Analytics;

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
class GaDataDataTable extends \Google\Collection {
	protected $collection_key = 'rows';
	protected $colsType = GaDataDataTableCols::class;
	protected $colsDataType = 'array';
	protected $rowsType = GaDataDataTableRows::class;
	protected $rowsDataType = 'array';

	/**
	 *
	 * @param
	 *        	GaDataDataTableCols[]
	 */
	public function setCols($cols) {
		$this->cols = $cols;
	}
	/**
	 *
	 * @return GaDataDataTableCols[]
	 */
	public function getCols() {
		return $this->cols;
	}
	/**
	 *
	 * @param
	 *        	GaDataDataTableRows[]
	 */
	public function setRows($rows) {
		$this->rows = $rows;
	}
	/**
	 *
	 * @return GaDataDataTableRows[]
	 */
	public function getRows() {
		return $this->rows;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( GaDataDataTable::class, 'Google_Service_Analytics_GaDataDataTable' );
