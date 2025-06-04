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
class GaDataDataTableRows extends \Google\Collection {
	protected $collection_key = 'c';
	protected $cType = GaDataDataTableRowsC::class;
	protected $cDataType = 'array';

	/**
	 *
	 * @param
	 *        	GaDataDataTableRowsC[]
	 */
	public function setC($c) {
		$this->c = $c;
	}
	/**
	 *
	 * @return GaDataDataTableRowsC[]
	 */
	public function getC() {
		return $this->c;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( GaDataDataTableRows::class, 'Google_Service_Analytics_GaDataDataTableRows' );
