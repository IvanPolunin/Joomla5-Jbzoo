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
class SitesListResponse extends \Google\Collection {
	protected $collection_key = 'siteEntry';
	protected $siteEntryType = WmxSite::class;
	protected $siteEntryDataType = 'array';

	/**
	 *
	 * @param
	 *        	WmxSite[]
	 */
	public function setSiteEntry($siteEntry) {
		$this->siteEntry = $siteEntry;
	}
	/**
	 *
	 * @return WmxSite[]
	 */
	public function getSiteEntry() {
		return $this->siteEntry;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( SitesListResponse::class, 'Google_Service_Webmasters_SitesListResponse' );
