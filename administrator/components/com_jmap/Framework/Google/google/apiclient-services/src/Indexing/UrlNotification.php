<?php

namespace Google\Service\Indexing;

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
class UrlNotification extends \Google\Model {
	public $notifyTime;
	public $type;
	public $url;
	public function setNotifyTime($notifyTime) {
		$this->notifyTime = $notifyTime;
	}
	public function getNotifyTime() {
		return $this->notifyTime;
	}
	public function setType($type) {
		$this->type = $type;
	}
	public function getType() {
		return $this->type;
	}
	public function setUrl($url) {
		$this->url = $url;
	}
	public function getUrl() {
		return $this->url;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( UrlNotification::class, 'Google_Service_Indexing_UrlNotification' );
