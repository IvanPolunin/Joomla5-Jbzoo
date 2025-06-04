<?php
namespace JExtstore\Component\JMap\Administrator\Model;
/**
 * @package JMAP::SEOSPIDER::administrator::components::com_jmap
 * @subpackage models
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use JExtstore\Component\JMap\Administrator\Framework\Model as JMapModel;
use JExtstore\Component\JMap\Administrator\Framework\File;
use JExtstore\Component\JMap\Administrator\Framework\Exception as JMapException;

/**
 * Analyzer concrete model
 * Operates not on DB but directly on a cached copy of the XML sitemap file
 *
 * @package JMAP::SEOSPIDER::administrator::components::com_jmap
 * @subpackage models
 * @since 3.8
 */
class SeospiderModel extends JMapModel {
	/**
	 * Number of XML records
	 * 
	 * @access private
	 * @var Int
	 */
	private $recordsNumber = 0;
	
	/**
	 * Counter result set
	 *
	 * @access public
	 * @return int
	 */
	public function getTotal(): int {
		// Return simply the XML records number
		return $this->recordsNumber;
	}
	
	/**
	 * Main get data method
	 *
	 * @access public
	 * @return Object[]
	 */
	public function getData(): array {
		// Load data from XML file, parse it to load records
		$cachedSitemapFilePath = JPATH_COMPONENT_ADMINISTRATOR . '/cache/seospider/';
		
		// Has sitemap some vars such as lang or Itemid?
		$sitemapLang = $this->getState('sitemaplang', '');
		$sitemapLinksLang = $sitemapLang ? $sitemapLang . '/' : '';
		$sitemapLang = $sitemapLang ? '_' . $sitemapLang : '';
		
		$sitemapDataset = $this->getState('sitemapdataset', '');
		$sitemapDataset = $sitemapDataset ? '_dataset' . (int)$sitemapDataset : '';
		
		$sitemapItemid = $this->getState('sitemapitemid', '');
		$sitemapItemid = $sitemapItemid ? '_menuid' . (int)$sitemapItemid : '';
		
		// Final name
		$cachedSitemapFilename = 'sitemap_xml' . $sitemapLang . $sitemapDataset . $sitemapItemid . '.xml'; 
		
		// Start processing
		try {
			// Now check if the file correctly exists
			if(File::exists($cachedSitemapFilePath . $cachedSitemapFilename)) {
				$loadedSitemapXML = simplexml_load_file($cachedSitemapFilePath . $cachedSitemapFilename);
				if(!$loadedSitemapXML) {
					throw new JMapException ( 'Invalid XML', 'error' );
				}
			} else {
				throw new JMapException ( Text::sprintf ( 'COM_JMAP_SEOSPIDER_NOCACHED_FILE_EXISTS', '' ), 'error' );
			}
			
			// Execute HTTP request and associate HTTP response code with each record links
			if($loadedSitemapXML->url->count()) {
				// Manage splice pagination here for the XML records
				$convertedIteratorToArray = iterator_to_array($loadedSitemapXML->url, false);
				
				// Store number of records for pagination
				$this->recordsNumber = count($convertedIteratorToArray);
				
				// Execute pagination splicing if any limit is set
				$limit = $this->getState ( 'limit' );
				if($limit) {
					$loadedSitemapXML = array_splice($convertedIteratorToArray, $this->getState ( 'limitstart' ), $this->getState ( 'limit' ));
				} else {
					$loadedSitemapXML = $convertedIteratorToArray;
				}
				
				// Cycle on every links, filter by search word if any and augment with link metainfo
				foreach ($loadedSitemapXML as $index=>&$url) {
					$url = (object)(array)$url;
					
					// Remove trailing slash for URL matching, this ensure home page match if the option to remove ending slash is enabled
					if($this->getComponentParams()->get('metainfo_remove_trailing_slash', 0)) {
						$url->loc = rtrim( $url->loc, '/' );
					}
					
					// Add trailing slash for URL matching, this ensure home page match if the option to not remove ending slash is enabled
					if($this->getComponentParams()->get('metainfo_add_trailing_slash', 0)) {
						$url->loc = rtrim( $url->loc, '/' ) . '/';
					}
				}
				
				// Perform array sorting if any
				$order = $this->getState('order', null);
				$jmapAnalyzerOrderDir = $this->getState('order_dir', 'asc');
				
				if($order == 'link') {
					function cmpAsc($a, $b){
						return strcmp($a->loc, $b->loc);
					}
					function cmpDesc($a, $b){
						return strcmp($b->loc, $a->loc);
					}
					$callbackName = ($jmapAnalyzerOrderDir == 'asc') ? '\JExtstore\Component\JMap\Administrator\Model\cmpAsc' : '\JExtstore\Component\JMap\Administrator\Model\cmpDesc';
					usort($loadedSitemapXML, $callbackName);
				}
			} else {
				throw new JMapException ( Text::sprintf ( 'COM_JMAP_SEOSPIDER_EMPTY_SITEMAP', '' ), 'notice' );
			}
		} catch ( JMapException $e ) {
			$this->app->enqueueMessage ( $e->getMessage (), $e->getExceptionLevel () );
			$loadedSitemapXML = array ();
		} catch ( \Exception $e ) {
			$jmapException = new JMapException ( $e->getMessage (), 'error' );
			$this->app->enqueueMessage ( $jmapException->getMessage (), $jmapException->getExceptionLevel () );
			$loadedSitemapXML = array ();
		}
		
		return $loadedSitemapXML;
	}
	
	/**
	 * Return select lists used as filter for listEntities
	 *
	 * @access public
	 * @return array
	 */
	public function getFilters(): array {
		return array();
	}
}