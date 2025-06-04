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
class User extends \Google\Model {
	public $type;
	public $userId;
	public function setType($type) {
		$this->type = $type;
	}
	public function getType() {
		return $this->type;
	}
	public function setUserId($userId) {
		$this->userId = $userId;
	}
	public function getUserId() {
		return $this->userId;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( User::class, 'Google_Service_AnalyticsReporting_User' );
