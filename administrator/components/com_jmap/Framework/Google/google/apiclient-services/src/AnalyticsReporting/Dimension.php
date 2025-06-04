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
class Dimension extends \Google\Collection {
	protected $collection_key = 'histogramBuckets';
	public $histogramBuckets;
	public $name;
	public function setHistogramBuckets($histogramBuckets) {
		$this->histogramBuckets = $histogramBuckets;
	}
	public function getHistogramBuckets() {
		return $this->histogramBuckets;
	}
	public function setName($name) {
		$this->name = $name;
	}
	public function getName() {
		return $this->name;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( Dimension::class, 'Google_Service_AnalyticsReporting_Dimension' );
