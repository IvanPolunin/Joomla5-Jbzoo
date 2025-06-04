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
class ApiDataRow extends \Google\Collection {
	protected $collection_key = 'keys';
	public $clicks;
	public $ctr;
	public $impressions;
	public $keys;
	public $position;
	public function setClicks($clicks) {
		$this->clicks = $clicks;
	}
	public function getClicks() {
		return $this->clicks;
	}
	public function setCtr($ctr) {
		$this->ctr = $ctr;
	}
	public function getCtr() {
		return $this->ctr;
	}
	public function setImpressions($impressions) {
		$this->impressions = $impressions;
	}
	public function getImpressions() {
		return $this->impressions;
	}
	public function setKeys($keys) {
		$this->keys = $keys;
	}
	public function getKeys() {
		return $this->keys;
	}
	public function setPosition($position) {
		$this->position = $position;
	}
	public function getPosition() {
		return $this->position;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( ApiDataRow::class, 'Google_Service_Webmasters_ApiDataRow' );
