<?php
namespace JExtstore\Component\JMap\Administrator\Model;
/**
 * @package JMAP::METAINFO::administrator::components::com_jmap
 * @subpackage models
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use JExtstore\Component\JMap\Administrator\Framework\Model as JMapModel;
use JExtstore\Component\JMap\Administrator\Framework\File;
use JExtstore\Component\JMap\Administrator\Framework\Exception as JMapException;

/**
 * Metainfo concrete model
 * Operates not on DB but directly on a cached copy of the XML sitemap file
 *
 * @package JMAP::METAINFO::administrator::components::com_jmap
 * @subpackage models
 * @since 3.2
 */
class MetainfoModel extends JMapModel {
	/**
	 * Number of XML records
	 * 
	 * @access private
	 * @var Int
	 */
	private $recordsNumber = 0;
	
	/**
	 * Main get data method
	 *
	 * @access public
	 * @return Object[]
	 */
	public function getData(): array {
		// Load data from XML file, parse it to load records
		$cachedSitemapFilePath = JPATH_COMPONENT_ADMINISTRATOR . '/cache/metainfo/';
		
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
				throw new JMapException ( Text::sprintf ( 'COM_JMAP_METAINFO_NOCACHED_FILE_EXISTS', '' ), 'error' );
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
				
				// Get a search filter
				$searchFilter = $this->state->get('searchpageword', null);
				$stateFilter = $this->state->get('state', null);
				$excludeStateFilter = $this->state->get('excludestate', null);
				// Cycle on every links, filter by search word if any and augment with link metainfo
				foreach ($loadedSitemapXML as $index=>&$url) {
					// Evaluate filtering by search word
					if($searchFilter) {
						// Evaluate position or exact match
						if($this->getState('exactsearchpage', null)) {
							$isMatching = $url->loc == $searchFilter;
						} else {
							$isMatching = (stripos($url->loc, $searchFilter) !== false);
						}
						if(!$isMatching) {
							array_splice($loadedSitemapXML, $index, 1);
							
							// Re-assign array
							$tmp = array_values($loadedSitemapXML);
							$loadedSitemapXML = $tmp;
							continue;
						}
					}
					
					$url = (object)(array)$url;
					
					// Remove trailing slash for URL matching, this ensure home page match if the option to remove ending slash is enabled
					if($this->getComponentParams()->get('metainfo_remove_trailing_slash', 0)) {
						$url->loc = rtrim( $url->loc, '/' );
					}
					
					// Add trailing slash for URL matching, this ensure home page match if the option to not remove ending slash is enabled
					if($this->getComponentParams()->get('metainfo_add_trailing_slash', 0)) {
						$url->loc = rtrim( $url->loc, '/' ) . '/';
					}
					
					// Load meta info for this link from the table and augment the array and object
					$query = "SELECT *" .
							 "\n FROM " . $this->dbInstance->quoteName('#__jmap_metainfo') .
							 "\n WHERE " . $this->dbInstance->quoteName('linkurl') . " = " . $this->dbInstance->quote($url->loc);
					$metaInfos = $this->dbInstance->setQuery($query)->loadObject();
					// This link has valid metainfo
					if(isset($metaInfos->id)) {
						$url->metainfos = $metaInfos;
					}
					
					// Evaluate the first link scheme to detect a possible unmatching and required https migration
					if($index == 0) {
						$query = "SELECT " . $this->dbInstance->quoteName('linkurl') .
								 "\n FROM " . $this->dbInstance->quoteName('#__jmap_metainfo');
						$sampleUrl = $this->dbInstance->setQuery($query)->loadResult();
						// Get current URI scheme
						$currentUriScheme = Uri::getInstance()->getScheme();
						if($sampleUrl && stripos($sampleUrl, $currentUriScheme) === false) {
							$this->setState('needhttpsmigration', true);
						}
					}
					
					// Evaluate filtering by state
					if($stateFilter) {
						if(isset($url->metainfos->published)) {
							$isMatching = ((int)$url->metainfos->published === ($stateFilter == 'P' ? 1 : 0));
							if(!$isMatching) {
								array_splice($loadedSitemapXML, $index, 1);
								// Re-assign array
								$tmp = array_values($loadedSitemapXML);
								$loadedSitemapXML = $tmp;
								continue;
							}
						} else {
							array_splice($loadedSitemapXML, $index, 1);
							// Re-assign array
							$tmp = array_values($loadedSitemapXML);
							$loadedSitemapXML = $tmp;
							continue;
						}
					}
					
					// Evaluate filtering by exclude state
					if(is_numeric($excludeStateFilter)) {
						// Special case for empty links
						if((int)$excludeStateFilter == 2) {
							if(isset($url->metainfos) && ($url->metainfos->meta_title || $url->metainfos->meta_desc || $url->metainfos->meta_image)) {
								array_splice($loadedSitemapXML, $index, 1);
								// Re-assign array
								$tmp = array_values($loadedSitemapXML);
								$loadedSitemapXML = $tmp;
								continue;
							}
						} elseif((int)$excludeStateFilter == 3) {
							if(isset($url->metainfos) && ($url->metainfos->meta_title || $url->metainfos->meta_desc || $url->metainfos->meta_image)) {
								// Leave the link included
							} else {
								array_splice($loadedSitemapXML, $index, 1);
								// Re-assign array
								$tmp = array_values($loadedSitemapXML);
								$loadedSitemapXML = $tmp;
								continue;
							}
						} else {
							if(isset($url->metainfos->excluded)) {
								$isMatching = ((int)$url->metainfos->excluded === (int)$excludeStateFilter);
								if(!$isMatching) {
									array_splice($loadedSitemapXML, $index, 1);
									// Re-assign array
									$tmp = array_values($loadedSitemapXML);
									$loadedSitemapXML = $tmp;
									continue;
								}
							} else {
								if((int)$excludeStateFilter == 1) {
									array_splice($loadedSitemapXML, $index, 1);
									// Re-assign array
									$tmp = array_values($loadedSitemapXML);
									$loadedSitemapXML = $tmp;
									continue;
								}
							}
						}
					}
				}
				
				// Perform array sorting if any
				$order = $this->getState('order', null);
				$jmapMetainfoOrderDir = $this->getState('order_dir', 'asc');
				
				if($order == 'link') {
					function cmpAsc($a, $b){
						return strcmp($a->loc, $b->loc);
					}
					function cmpDesc($a, $b){
						return strcmp($b->loc, $a->loc);
					}
					$callbackName = ($jmapMetainfoOrderDir == 'asc') ? '\JExtstore\Component\JMap\Administrator\Model\cmpAsc' : '\JExtstore\Component\JMap\Administrator\Model\cmpDesc';
					usort($loadedSitemapXML, $callbackName);
				}
			} else {
				throw new JMapException ( Text::sprintf ( 'COM_JMAP_METAINFO_EMPTY_SITEMAP', '' ), 'notice' );
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
	 * Delete DB meta info completely
	 *
	 * @access public
	 * @return bool
	 */
	public function deleteEntity($ids): bool {
		try {
			$query = "DELETE FROM #__jmap_metainfo";
			$this->dbInstance->setQuery($query);
			$this->dbInstance->execute();
		} catch (JMapException $e) {
			$this->setException($e);
			return false;
		} catch (\Exception $e) {
			$jmapException = new JMapException($e->getMessage(), 'error');
			$this->setException($jmapException);
			return false;
		}
		return true;
	}
	
	/**
	 * Update metainfo records to https domain
	 *
	 * @access public
	 * @return boolean
	 */
	public function httpsMigrate() {
		try {
			$query = "UPDATE " . $this->dbInstance->quoteName('#__jmap_metainfo') .
					 "\n SET " . $this->dbInstance->quoteName('linkurl') . " = REPLACE(" . $this->dbInstance->quoteName('linkurl') . ", 'http://', 'https://')";
			$this->dbInstance->setQuery($query);
			$this->dbInstance->execute();
		} catch (JMapException $e) {
			$this->setException($e);
			return false;
		} catch (\Exception $e) {
			$jMapException = new JMapException($e->getMessage(), 'error');
			$this->setException($jMapException);
			return false;
		}
		return true;
	}
	
	/**
	 * Update metainfo records to https domain
	 *
	 * @access public
	 * @return boolean
	 */
	public function domainsMigrate($currentDomain, $newDomain) {
		try {
			$query = "UPDATE " . $this->dbInstance->quoteName('#__jmap_metainfo') .
					 "\n SET " . $this->dbInstance->quoteName('linkurl') . " = " .
					 "REPLACE(" . $this->dbInstance->quoteName('linkurl') . "," . $this->dbInstance->quote($currentDomain) . "," . $this->dbInstance->quote($newDomain) . ")";
			$this->dbInstance->setQuery($query);
			$this->dbInstance->execute();
		} catch (JMapException $e) {
			$this->setException($e);
			return false;
		} catch (\Exception $e) {
			$jMapException = new JMapException($e->getMessage(), 'error');
			$this->setException($jMapException);
			return false;
		}
		return true;
	}
	
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
	 * Return select lists used as filter for listEntities
	 *
	 * @access public
	 * @return array
	 */
	public function getFilters(): array {
		$filters = array ();
		$filters ['state'] = HTMLHelper::_ ( 'grid.state', $this->getState ( 'state' ) );
		
		$excludeStates = array();
		$excludeStates[] = HTMLHelper::_('select.option', null, Text::_('COM_JMAP_ALL_METAINFO_LINKS'));
		$excludeStates[] = HTMLHelper::_('select.option', 1, Text::_('COM_JMAP_EXCLUDED_LINKS'));
		$excludeStates[] = HTMLHelper::_('select.option', 0, Text::_('COM_JMAP_NOT_EXCLUDED_LINKS'));
		$excludeStates[] = HTMLHelper::_('select.option', 2, Text::_('COM_JMAP_EMPTY_LINKS'));
		$excludeStates[] = HTMLHelper::_('select.option', 3, Text::_('COM_JMAP_NOT_EMPTY_LINKS'));
		$filters ['excludestate'] = HTMLHelper::_ ( 'select.genericlist', $excludeStates, 'filter_excludestate', 'onchange="Joomla.submitform();" class="form-select"', 'value', 'text', $this->getState ( 'excludestate' ));
		
		return $filters;
	}
}