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
class ProfilePermissions extends \Google\Collection {
	protected $collection_key = 'effective';
	public $effective;
	public function setEffective($effective) {
		$this->effective = $effective;
	}
	public function getEffective() {
		return $this->effective;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( ProfilePermissions::class, 'Google_Service_Analytics_ProfilePermissions' );
