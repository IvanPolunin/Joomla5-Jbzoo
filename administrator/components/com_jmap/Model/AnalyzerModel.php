<?php
namespace JExtstore\Component\JMap\Administrator\Model;
/**
 * @package JMAP::ANALYZER::administrator::components::com_jmap
 * @subpackage models
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\SiteRouter;
use JExtstore\Component\JMap\Administrator\Framework\File;
use JExtstore\Component\JMap\Administrator\Framework\Model as JMapModel;
use JExtstore\Component\JMap\Administrator\Framework\Http;
use JExtstore\Component\JMap\Administrator\Framework\Http\Transport\Socket;
use JExtstore\Component\JMap\Administrator\Framework\Http\Transport\Curl;
use JExtstore\Component\JMap\Administrator\Framework\Exception as JMapException;

/**
 * Analyzer concrete model
 * Operates not on DB but directly on a cached copy of the XML sitemap file
 *
 * @package JMAP::ANALYZER::administrator::components::com_jmap
 * @subpackage models
 * @since 2.3.3
 */
class AnalyzerModel extends JMapModel {
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
		$cachedSitemapFilePath = JPATH_COMPONENT_ADMINISTRATOR . '/cache/analyzer/';
		$originalBackendLanguageTag = $this->app->getLanguage()->getTag();
		
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
				throw new JMapException ( Text::sprintf ( 'COM_JMAP_ANALYZER_NOCACHED_FILE_EXISTS', '' ), 'error' );
			}
			
			// Execute HTTP request and associate HTTP response code with each record links
			// Instantiante a new HTTP client
			$cParams = $this->getComponentParams();
			$httpTransport = $cParams->get('analytics_service_http_transport', 'curl') == 'socket' ? new Socket () : new Curl ();
			$httpClient = new Http ( $httpTransport, $cParams );
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
				
				// Now start the Analyzer
				$linksAnalyzerWorkingMode = $this->getComponentParams()->get('linksanalyzer_workingmode', 1);
				if($linksAnalyzerWorkingMode) {
					$siteRouter = Factory::getContainer()->has('SiteRouter') ? Factory::getContainer()->get('SiteRouter'): SiteRouter::getInstance('site');
					
					// Exception integration with Kunena Route
					if(class_exists('\Kunena\Forum\Libraries\Route\KunenaRoute')) {
						\Kunena\Forum\Libraries\Route\KunenaRoute::$current = new Uri('index.php');
					}
				}
				// Check the Analyzer Analysis working mode for the validation of links
				$validationAnalysisStandard = (bool)($this->getComponentParams()->get('linksanalyzer_validation_analysis', 2) == 2);
				
				// Load language to the SiteApplication
				Factory::getContainer()->get(\Joomla\CMS\Application\SiteApplication::class)->loadLanguage();
				
				$forceScheme = false;
				if ($this->app->get('force_ssl') >= 1) {
					$forceScheme = true;
				}
				
				// Get a search filter
				$searchFilter = $this->state->get('searchpageword', null);
				$headers = array('Accept'=>'text/html', 'User-Agent'=>'JSitemapbot/1.0');
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
					
					if($validationAnalysisStandard) {
						$httpResponse = $httpClient->get($url->loc, $headers);
						$url->httpstatus = $httpResponse->code;
					}
					
					// Find informations about the link, component and menu itemid if any, need a workaround to parse correctly from backend
					if($linksAnalyzerWorkingMode) {
						$baseAdmin = Uri::base();
						$baseSite = str_replace('/administrator', '', $baseAdmin);
						$fakedUrl = str_replace($baseSite, $baseAdmin, $url->loc);
						$fakedUrl = str_replace('/index.php', '', $fakedUrl);
						// Now instantiate and parse the faked url from backend, replacing the uri base it will be = site
						$uriObject = Uri::getInstance((string)$fakedUrl);
						try {
							// Avoid parse uri redirects when saving an article
							if ($forceScheme) {
								$uriObject->setScheme('https');
							}
							$parseUri = $siteRouter->parse($uriObject);
						} catch(\Exception $e) {
							// Always ignore the new router exceptions in order to evaluate error codes
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
					
					// Now augment the object to show informations
					$url->component = isset($parseUri['option']) ? $parseUri['option'] : '-';
					$url->menuId = isset($parseUri['Itemid']) ? $parseUri['Itemid'] : '-';
					$url->menuTitle = '-';
					// Translate human menu
					if(isset($parseUri['Itemid'])) {
						$query = "SELECT" . $this->dbInstance->quoteName('title') .
								 "\n FROM #__menu" .
								 "\n WHERE " . 
								 $this->dbInstance->quoteName('id') . " = " . (int)$parseUri['Itemid'];
						$menuTitle = $this->dbInstance->setQuery($query)->loadResult();
						$url->menuTitle = $menuTitle;
					}
				}
				
				// Override always the $siteRouter->parse language from the frontend side link applying the original backend language
				if($linksAnalyzerWorkingMode && $sitemapLinksLang) {
					$jLang = Factory::getContainer()->get(\Joomla\CMS\Language\LanguageFactoryInterface::class)->createLanguage($originalBackendLanguageTag, false);
					Factory::$language = $jLang;
					$jLang->load('joomla', JPATH_BASE, $originalBackendLanguageTag, true, true);
					$jLang->load('mod_menu', JPATH_BASE, $originalBackendLanguageTag, true, true);
					$jLang->load('mod_status', JPATH_BASE, $originalBackendLanguageTag, true, true);
					$jLang->load('mod_multilangstatus', JPATH_BASE, $originalBackendLanguageTag, true, true);
					$jLang->load('mod_post_installation_messages', JPATH_BASE, $originalBackendLanguageTag, true, true);
					$jLang->load('mod_user', JPATH_BASE, $originalBackendLanguageTag, true, true);
					$jLang->load('mod_messages', JPATH_BASE, $originalBackendLanguageTag, true, true);
					$jLang->load('com_content.sys', JPATH_BASE, $originalBackendLanguageTag, true, true);
					$jLang->load('com_users.sys', JPATH_BASE, $originalBackendLanguageTag, true, true);
					$jLang->load('com_jmap', JPATH_COMPONENT_ADMINISTRATOR, 'en-GB', true, true);
					if($originalBackendLanguageTag != 'en-GB') {
						$jLang->load('com_jmap', JPATH_ADMINISTRATOR, $originalBackendLanguageTag, true, false);
						$jLang->load('com_jmap', JPATH_COMPONENT_ADMINISTRATOR, $originalBackendLanguageTag, true, false);
					}
				}
				
				// Perform array sorting if any
				$order = $this->getState('order', null);
				$jmapAnalyzerOrderDir = $this->getState('order_dir', 'asc');
				
				if($validationAnalysisStandard && $order == 'httpstatus') {
					function cmpAsc($a, $b){
						return ((int)$a->httpstatus < (int)$b->httpstatus) ? -1 : 1;
					}
					function cmpDesc($a, $b){
						return ((int)$a->httpstatus > (int)$b->httpstatus) ? -1 : 1;
					}
					$callbackName = ($jmapAnalyzerOrderDir == 'asc') ? '\JExtstore\Component\JMap\Administrator\Model\cmpAsc' : '\JExtstore\Component\JMap\Administrator\Model\cmpDesc';
					usort($loadedSitemapXML, $callbackName);
				}
				
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
				throw new JMapException ( Text::sprintf ( 'COM_JMAP_ANALYZER_EMPTY_SITEMAP', '' ), 'notice' );
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
		$filters = [];
		
		$validationType = (int)($this->getComponentParams()->get('linksanalyzer_validation_analysis', 2));
		
		$filteringFunction = ($validationType == 2) ? 'class="form-select" onchange="Joomla.submitform();"' : 'class="form-select noanalyzer" onchange="JMapAnalyzer.filterOnAsyncPage(this);"';
		$selectName = ($validationType == 2) ? 'filter_type' : 'async_type';
		
		$datasourceTypes = array ();
		$datasourceTypes [] = HTMLHelper::_ ( 'select.option', null, Text::_ ( 'COM_JMAP_ANALYZER_ALL' ) );
		$datasourceTypes [] = HTMLHelper::_ ( 'select.option', '200', Text::_ ( 'COM_JMAP_ANALYZER_200' ) );
		$datasourceTypes [] = HTMLHelper::_ ( 'select.option', '301', Text::_ ( 'COM_JMAP_ANALYZER_301' ) );
		$datasourceTypes [] = HTMLHelper::_ ( 'select.option', '303', Text::_ ( 'COM_JMAP_ANALYZER_303' ) );
		$datasourceTypes [] = HTMLHelper::_ ( 'select.option', '404', Text::_ ( 'COM_JMAP_ANALYZER_404' ) );
		$datasourceTypes [] = HTMLHelper::_ ( 'select.option', '500', Text::_ ( 'COM_JMAP_ANALYZER_500' ) );
		$filters ['type'] = HTMLHelper::_ ( 'select.genericlist', $datasourceTypes, $selectName, $filteringFunction, 'value', 'text', $this->getState ( 'link_type' ) );
		
		return $filters;
	}
}