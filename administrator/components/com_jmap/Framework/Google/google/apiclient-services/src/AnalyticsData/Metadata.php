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
class Metadata extends \Google\Collection {
	protected $collection_key = 'metrics';
	protected $dimensionsType = DimensionMetadata::class;
	protected $dimensionsDataType = 'array';
	protected $metricsType = MetricMetadata::class;
	protected $metricsDataType = 'array';
	public $name;

	/**
	 *
	 * @param
	 *        	DimensionMetadata[]
	 */
	public function setDimensions($dimensions) {
		$this->dimensions = $dimensions;
	}
	/**
	 *
	 * @return DimensionMetadata[]
	 */
	public function getDimensions() {
		return $this->dimensions;
	}
	/**
	 *
	 * @param
	 *        	MetricMetadata[]
	 */
	public function setMetrics($metrics) {
		$this->metrics = $metrics;
	}
	/**
	 *
	 * @return MetricMetadata[]
	 */
	public function getMetrics() {
		return $this->metrics;
	}
	public function setName($name) {
		$this->name = $name;
	}
	public function getName() {
		return $this->name;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( Metadata::class, 'Google_Service_AnalyticsData_Metadata' );
