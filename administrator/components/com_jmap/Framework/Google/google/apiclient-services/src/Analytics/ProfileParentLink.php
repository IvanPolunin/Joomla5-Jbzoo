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
class ProfileParentLink extends \Google\Model {
	public $href;
	public $type;
	public function setHref($href) {
		$this->href = $href;
	}
	public function getHref() {
		return $this->href;
	}
	public function setType($type) {
		$this->type = $type;
	}
	public function getType() {
		return $this->type;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( ProfileParentLink::class, 'Google_Service_Analytics_ProfileParentLink' );
