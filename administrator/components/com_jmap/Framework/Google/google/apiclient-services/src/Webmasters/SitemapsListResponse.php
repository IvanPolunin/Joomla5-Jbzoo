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
class SitemapsListResponse extends \Google\Collection {
	protected $collection_key = 'sitemap';
	protected $sitemapType = WmxSitemap::class;
	protected $sitemapDataType = 'array';

	/**
	 *
	 * @param
	 *        	WmxSitemap[]
	 */
	public function setSitemap($sitemap) {
		$this->sitemap = $sitemap;
	}
	/**
	 *
	 * @return WmxSitemap[]
	 */
	public function getSitemap() {
		return $this->sitemap;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( SitemapsListResponse::class, 'Google_Service_Webmasters_SitemapsListResponse' );
