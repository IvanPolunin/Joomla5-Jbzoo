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
class PublishUrlNotificationResponse extends \Google\Model {
	protected $urlNotificationMetadataType = UrlNotificationMetadata::class;
	protected $urlNotificationMetadataDataType = '';
	protected $urlNotificationMetadata;

	/**
	 *
	 * @param
	 *        	UrlNotificationMetadata
	 */
	public function setUrlNotificationMetadata(UrlNotificationMetadata $urlNotificationMetadata) {
		$this->urlNotificationMetadata = $urlNotificationMetadata;
	}
	/**
	 *
	 * @return UrlNotificationMetadata
	 */
	public function getUrlNotificationMetadata() {
		return $this->urlNotificationMetadata;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( PublishUrlNotificationResponse::class, 'Google_Service_Indexing_PublishUrlNotificationResponse' );
