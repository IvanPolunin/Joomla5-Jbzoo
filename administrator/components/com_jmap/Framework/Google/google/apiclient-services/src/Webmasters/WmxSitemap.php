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
class WmxSitemap extends \Google\Collection {
	protected $collection_key = 'contents';
	protected $contentsType = WmxSitemapContent::class;
	protected $contentsDataType = 'array';
	public $errors;
	public $isPending;
	public $isSitemapsIndex;
	public $lastDownloaded;
	public $lastSubmitted;
	public $path;
	public $type;
	public $warnings;

	/**
	 *
	 * @param
	 *        	WmxSitemapContent[]
	 */
	public function setContents($contents) {
		$this->contents = $contents;
	}
	/**
	 *
	 * @return WmxSitemapContent[]
	 */
	public function getContents() {
		return $this->contents;
	}
	public function setErrors($errors) {
		$this->errors = $errors;
	}
	public function getErrors() {
		return $this->errors;
	}
	public function setIsPending($isPending) {
		$this->isPending = $isPending;
	}
	public function getIsPending() {
		return $this->isPending;
	}
	public function setIsSitemapsIndex($isSitemapsIndex) {
		$this->isSitemapsIndex = $isSitemapsIndex;
	}
	public function getIsSitemapsIndex() {
		return $this->isSitemapsIndex;
	}
	public function setLastDownloaded($lastDownloaded) {
		$this->lastDownloaded = $lastDownloaded;
	}
	public function getLastDownloaded() {
		return $this->lastDownloaded;
	}
	public function setLastSubmitted($lastSubmitted) {
		$this->lastSubmitted = $lastSubmitted;
	}
	public function getLastSubmitted() {
		return $this->lastSubmitted;
	}
	public function setPath($path) {
		$this->path = $path;
	}
	public function getPath() {
		return $this->path;
	}
	public function setType($type) {
		$this->type = $type;
	}
	public function getType() {
		return $this->type;
	}
	public function setWarnings($warnings) {
		$this->warnings = $warnings;
	}
	public function getWarnings() {
		return $this->warnings;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( WmxSitemap::class, 'Google_Service_Webmasters_WmxSitemap' );
