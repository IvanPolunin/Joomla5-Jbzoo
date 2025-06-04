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
class ApiDimensionFilterGroup extends \Google\Collection {
	protected $collection_key = 'filters';
	protected $filtersType = ApiDimensionFilter::class;
	protected $filtersDataType = 'array';
	public $groupType;

	/**
	 *
	 * @param
	 *        	ApiDimensionFilter[]
	 */
	public function setFilters($filters) {
		$this->filters = $filters;
	}
	/**
	 *
	 * @return ApiDimensionFilter[]
	 */
	public function getFilters() {
		return $this->filters;
	}
	public function setGroupType($groupType) {
		$this->groupType = $groupType;
	}
	public function getGroupType() {
		return $this->groupType;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( ApiDimensionFilterGroup::class, 'Google_Service_Webmasters_ApiDimensionFilterGroup' );
