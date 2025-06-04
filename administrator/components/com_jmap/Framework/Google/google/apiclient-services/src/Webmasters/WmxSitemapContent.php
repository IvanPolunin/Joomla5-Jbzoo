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
class WmxSitemapContent extends \Google\Model {
	public $indexed;
	public $submitted;
	public $type;
	public function setIndexed($indexed) {
		$this->indexed = $indexed;
	}
	public function getIndexed() {
		return $this->indexed;
	}
	public function setSubmitted($submitted) {
		$this->submitted = $submitted;
	}
	public function getSubmitted() {
		return $this->submitted;
	}
	public function setType($type) {
		$this->type = $type;
	}
	public function getType() {
		return $this->type;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( WmxSitemapContent::class, 'Google_Service_Webmasters_WmxSitemapContent' );
