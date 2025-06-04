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
class PageviewData extends \Google\Model {
	public $pagePath;
	public $pageTitle;
	public function setPagePath($pagePath) {
		$this->pagePath = $pagePath;
	}
	public function getPagePath() {
		return $this->pagePath;
	}
	public function setPageTitle($pageTitle) {
		$this->pageTitle = $pageTitle;
	}
	public function getPageTitle() {
		return $this->pageTitle;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( PageviewData::class, 'Google_Service_AnalyticsReporting_PageviewData' );
