<?php
namespace JExtstore\Component\JMap\Administrator\Model;
/** 
 * @package JMAP::AJAXSERVER::components::com_jmap 
 * @subpackage models
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerHelper;
use Joomla\String\StringHelper;
use Joomla\CMS\Table\Content as ContentTable;
use Joomla\Component\Content\Administrator\Model\ArticleModel;
use Joomla\Component\Modules\Administrator\Model\ModuleModel;
use JExtstore\Component\JMap\Administrator\Framework\File;
use JExtstore\Component\JMap\Administrator\Framework\Model as JMapModel;
use JExtstore\Component\JMap\Administrator\Framework\Http;
use JExtstore\Component\JMap\Administrator\Framework\Http\Transport\Socket;
use JExtstore\Component\JMap\Administrator\Framework\Http\Transport\Curl;
use JExtstore\Component\JMap\Administrator\Framework\Exception as JMapException;
use JExtstore\Component\JMap\Administrator\Framework\Seostats;
use JExtstore\Component\JMap\Administrator\Framework\Seostats\Services\Statscrop;
use JExtstore\Component\JMap\Administrator\Framework\Seostats\Services\Google;
use JExtstore\Component\JMap\Administrator\Framework\Seostats\Services\Hypestat;
use JExtstore\Component\JMap\Administrator\Framework\Seostats\Services\Zigstat;
use JExtstore\Component\JMap\Administrator\Framework\Seostats\Services\Siterankdata;
use JExtstore\Component\JMap\Administrator\Framework\Seostats\Services\Xranks;
use JExtstore\Component\JMap\Administrator\Framework\Seostats\Services\Semrush;
use JExtstore\Component\JMap\Administrator\Framework\Seostats\Services\Google\Competitors as GoogleCompetitors;
use JExtstore\Component\JMap\Administrator\Framework\Pinger\Xmlrpc\Client;
use JExtstore\Component\JMap\Administrator\Framework\Pinger\Xmlrpc\Message;
use JExtstore\Component\JMap\Administrator\Framework\Pinger\Xmlrpc\Value;
use JExtstore\Component\JMap\Administrator\Framework\Language\Multilang;

/**
 * Ajax Server model responsibilities
 *
 * @package JMAP::AJAXSERVER::components::com_jmap  
 * @subpackage models
 * @since 1.0
 */
interface IAjaxserverModel {
	public function loadAjaxEntity($id, $param, $DIModels) ;
}

/** 
 * Classe che gestisce il recupero dei dati per il POST HTTP
 * @package JMAP::AJAXSERVER::components::com_jmap  
 * @subpackage models
 * @since 1.0
 */
class AjaxserverModel extends JMapModel implements IAjaxserverModel {
	/**
	 * Check if an extension is currently installed on Joomla system and answer accordingly with an encoded object
	 *
	 * @access private
	 * @param string $tableName
	 * @param Object[] $additionalModels Array for additional injected models type hinted by interface
	 * @return Object
	 */
	private function checkExtension($extensionName, $additionalModels = null) {
		// Query to check
		$query = "SELECT " . $this->dbInstance->quoteName('extension_id') . 
				 "\n FROM " . $this->dbInstance->quoteName('#__extensions') . 
				 "\n WHERE " . $this->dbInstance->quoteName('element') . " = " . $this->dbInstance->Quote($extensionName);
		$this->dbInstance->setQuery($query);
		$extensionID = $this->dbInstance->loadResult();

		$response = (object) array('extensionFound' => (bool) $extensionID);

		return $response;
	}

	/**
	 * Check if an extension is currently installed on Joomla system and answer accordingly with an encoded object
	 *
	 * @access private
	 * @param string $tableName
	 * @param Object[] $additionalModels Array for additional injected models type hinted by interface
	 * @return Object
	 */
	private function loadDataSources($additionalModels = null) {
		// Response JSON object
		$response = new \stdClass();

		try {
			// Default for published data sources
			$where[] = "\n v.published = 1";

			$query = "SELECT v.id, v.type, v.name" .
					 "\n FROM #__jmap AS v" .
					 "\n WHERE " . implode(' AND ', $where) .
					 "\n ORDER BY v.ordering ASC";
			$this->dbInstance->setQuery ( $query );

			$response->datasources = $this->dbInstance->loadObjectList ();

			// All completed succesfully
			$response->result = true;
		} catch (JMapException $e) {
			$response->result = false;
			$response->exception_message = $e->getMessage();
			return $response;
		} catch (\Exception $e) {
			$jmapException = new JMapException(Text::sprintf('COM_JMAP_ERROR_RETRIEVING_DATA', $e->getMessage()), 'error');
			$response->result = false;
			$response->exception_message = $jmapException->getMessage();
			return $response;
		}

		return $response;
	}
	
	/**
	 * Get the top level host domain for each kind of URL needed to avoid redirects on CURL exec
	 *
	 * @access private
	 * @param string $url
	 * @return string
	 */
	private function getHost($url) {
		if (strpos ( $url, "http" ) !== false) {
			$httpurl = $url;
		} else {
			$httpurl = "http://" . $url;
		}
		$parse = parse_url ( $httpurl );
		$domain = $parse ['host'];
	
		$parts = explode ( ".", $domain );
		$count = sizeof ( $parts ) - 1;
	
		if ($count > 1) {
			$slicedParts = array_slice( $parts, -2, 1 );
			$slice = ( strlen( reset( $slicedParts ) ) == 2 || in_array(reset( $slicedParts ), array('com', 'org', 'gov', 'net'))) && ( count( $parts ) > 2 ) ? 3 : 2;
			$result = implode( '.', array_slice( $parts, ( 0 - $slice ), $slice ) );
		} else {
			$result = $domain;
		}
		return $result;
	}
	
	/**
	 * Fetch SEO stats from remote services
	 * based on Seo stats lib that is able to calculate Google Page rank
	 *
	 *
	 * @access private
	 * @param Object[] $additionalModels Array for additional injected models type hinted by interface
	 * @return Object
	 */
	private function fetchSeoStats($additionalModels = null) {
		// Response JSON object
		$response = new \stdClass ();
		$cParams = $this->getComponentParams();
		
		try {
			if (! function_exists ( 'curl_init' )) {
				throw new JMapException ( Text::_ ( 'COM_JMAP_CURL_NOT_SUPPORTED' ), 'error' );
			}

			// API REQUEST, define target URL, site default or custom url
			$siteUrl = Uri::root(false);
			$customUrl = ComponentHelper::getParams('com_jmap')->get('seostats_custom_link', null);
			$url = $customUrl ? $customUrl : $siteUrl;
			
			if($cParams->get('seostats_gethost', 1)) {
				$webserviceHostUrl = $this->getHost($url);
			} else {
				$webserviceHostUrl = $url;
			}

			// Create a new SEOstats instance.
			$seostats = new Seostats ();

			// Bind the URL to the current SEOstats instance.
			if ($seostats->setUrl ( $url )) {
				$seostatsService = $cParams->get('seostats_service', 'statscrop');
				switch($seostatsService) {
					case 'zigstat':
						// Set the resulting array
						$pageRanksArray = array (
							'mozrank' => Text::_ ( 'COM_JMAP_NA' ),
							'mozdomainauth' => Text::_ ( 'COM_JMAP_NA' ),
							'pageviews' => Text::_ ( 'COM_JMAP_NA' ),
							'serpkeywords' => Text::_ ( 'COM_JMAP_NA' ),
							'backlinks' => Text::_ ( 'COM_JMAP_NA' ),
							'openpagerank' => Text::_ ( 'COM_JMAP_NA' ),
							'dailyvisitors' => Text::_ ( 'COM_JMAP_NA' ),
							'followbacklinks' => Text::_ ( 'COM_JMAP_NA' ),
							'nofollowbacklinks' => Text::_ ( 'COM_JMAP_NA' ),
							'reporttext' => ''
						);

						$pageRanksArray ['mozrank'] = Zigstat::getMozRank ($webserviceHostUrl);
						$pageRanksArray ['mozdomainauth'] = Zigstat::getDomainAuth($webserviceHostUrl);
						$pageRanksArray ['pageviews'] = Zigstat::getPageViews($webserviceHostUrl);
						$pageRanksArray ['serpkeywords'] = Zigstat::getSerpKeywords($webserviceHostUrl);
						$pageRanksArray ['backlinks'] = Zigstat::getBacklinks ($webserviceHostUrl);
						
						// Open Page Rank SEMRush stats
						$topLevelDomain = 'us';
						$parsedUrl = explode('.', $url);
						$topLevelDomainDetected = array_pop($parsedUrl);
						if(in_array($topLevelDomainDetected, Semrush::getDBs())) {
							$topLevelDomain = $topLevelDomainDetected;
						}
						$pageRanksArray ['openpagerank'] = Semrush::getDomainRank($webserviceHostUrl, $topLevelDomain);
						
						$pageRanksArray ['dailyvisitors'] = Zigstat::getDailyVisitors ($webserviceHostUrl);
						$pageRanksArray ['followbacklinks'] = Zigstat::getFollowBacklinks ($webserviceHostUrl);
						$pageRanksArray ['nofollowbacklinks'] = Zigstat::getNoFollowBacklinks ($webserviceHostUrl);
						$pageRanksArray ['reporttext'] = Zigstat::getReportText ($webserviceHostUrl);
						
						// Store the service and target url
						$pageRanksArray ['service'] = 'zigstat';
						$pageRanksArray ['targeturl'] = $url;
					break;
						
					case 'siterankdata':
						// Set the resulting array
						$pageRanksArray = array (
								'rank' => Text::_ ( 'COM_JMAP_NA' ),
								'dailyvisitors' => Text::_ ( 'COM_JMAP_NA' ),
								'monthlyvisitors' => Text::_ ( 'COM_JMAP_NA' ),
								'yearlyvisitors' => Text::_ ( 'COM_JMAP_NA' ),
								'websitescreen' => '',
								'siterankdatacompetitors' => Text::_ ( 'COM_JMAP_NA' )
						);
						
						$pageRanksArray ['rank'] = Siterankdata::getGlobalRank ($webserviceHostUrl);
						$pageRanksArray ['dailyvisitors'] = Siterankdata::getDailyVisitors ($webserviceHostUrl);
						$pageRanksArray ['monthlyvisitors'] = Siterankdata::getMonthlyVisitors ($webserviceHostUrl);
						$pageRanksArray ['yearlyvisitors'] = Siterankdata::getYearlyVisitors ($webserviceHostUrl);
						$pageRanksArray ['websitescreen'] = Siterankdata::getWebsiteScreen ($webserviceHostUrl);
						$pageRanksArray ['siterankdatacompetitors'] = Siterankdata::getCompetitors ($webserviceHostUrl);
						
						// Store the service and target url
						$pageRanksArray ['service'] = 'siterankdata';
						$pageRanksArray ['targeturl'] = $url;
					break;
					
					case 'hypestat':
						// Set the resulting array
						$pageRanksArray = array (
								'rank' => Text::_ ( 'COM_JMAP_NA' ),
								'dailyvisitors' => Text::_ ( 'COM_JMAP_NA' ),
								'monthlyvisitors' => Text::_ ( 'COM_JMAP_NA' ),
								'pagespervisit' => Text::_ ( 'COM_JMAP_NA' ),
								'dailypageviews' => Text::_ ( 'COM_JMAP_NA' ),
								'backlinks' => Text::_ ( 'COM_JMAP_NA' ),
								'websitescreen' => '',
								'reporttext' => ''
						);
					
						$pageRanksArray ['rank'] = Hypestat::getGlobalRank ($webserviceHostUrl);
						$pageRanksArray ['dailyvisitors'] = Hypestat::getDailyVisitors ($webserviceHostUrl);
						$pageRanksArray ['monthlyvisitors'] = Hypestat::getMonthlyVisitors ($webserviceHostUrl);
						$pageRanksArray ['pagespervisit'] = Hypestat::getPagesPerVisit ($webserviceHostUrl);
						$pageRanksArray ['dailypageviews'] = Hypestat::getDailyPageViews ($webserviceHostUrl);
						$pageRanksArray ['backlinks'] = Hypestat::getBacklinks ($webserviceHostUrl);
						$pageRanksArray ['websitescreen'] = Hypestat::getWebsiteScreen ($webserviceHostUrl);
						$pageRanksArray ['reporttext'] = Hypestat::getReportText ($webserviceHostUrl);
						
						// Store the service and target url
						$pageRanksArray ['service'] = 'hypestat';
						$pageRanksArray ['targeturl'] = $url;
					break;
					
					case 'xranks':
						// Set the resulting array
						$pageRanksArray = array (
							'globalrank' => Text::_ ( 'COM_JMAP_NA' ),
							'organicvisit' => Text::_ ( 'COM_JMAP_NA' ),
							'domainauthority' => Text::_ ( 'COM_JMAP_NA' ),
							'traffic' => Text::_ ( 'COM_JMAP_NA' ),
							'backlinks' => Text::_ ( 'COM_JMAP_NA' ),
							'organicsearchtraffic' => Text::_ ( 'COM_JMAP_NA' ),
							'openpagerank' => Text::_ ( 'COM_JMAP_NA' ),
							'semrushrank' => Text::_ ( 'COM_JMAP_NA' ),
							'semrushkeywords' => Text::_ ( 'COM_JMAP_NA' ),
							'competitors' => Text::_ ( 'COM_JMAP_NA' ),
							'websitescreen' => '',
							'reporttext' => ''
						);
						
						$pageRanksArray ['globalrank'] = Xranks::getGlobalRank($webserviceHostUrl);
						$pageRanksArray ['organicvisit'] = Xranks::getMozMetrics ($webserviceHostUrl, 2);
						$pageRanksArray ['domainauthority'] = Xranks::getMozMetrics ($webserviceHostUrl, 1);
						$pageRanksArray ['traffic'] = Xranks::getMozMetrics ($webserviceHostUrl, 3);
						$pageRanksArray ['backlinks'] = Xranks::getMetricProviders ($webserviceHostUrl, 4);
						$pageRanksArray ['organicsearchtraffic'] = Xranks::getOrganicSearchTraffic($webserviceHostUrl);
						$pageRanksArray ['organictraffic'] = Xranks::getOrganicTraffic($webserviceHostUrl);
						$pageRanksArray ['usersperday'] = Xranks::getUsersPerDay($webserviceHostUrl);
						
						// Open Page Rank SEMRush stats
						$topLevelDomain = 'us';
						$parsedUrl = explode('.', $url);
						$topLevelDomainDetected = array_pop($parsedUrl);
						if(in_array($topLevelDomainDetected, Semrush::getDBs())) {
							$topLevelDomain = $topLevelDomainDetected;
						}
						$pageRanksArray ['openpagerank'] = Semrush::getDomainRank($webserviceHostUrl, $topLevelDomain);
						$pageRanksArray ['semrushrank'] = Xranks::getSemrushRank($webserviceHostUrl);
						$pageRanksArray ['semrushkeywords'] = Xranks::getNumKeywords($webserviceHostUrl);
						$pageRanksArray ['competitors'] = Xranks::getCompetitors($webserviceHostUrl);
						$pageRanksArray ['pageviewsperday'] = Xranks::getpageviewsPerDay($webserviceHostUrl);
						$pageRanksArray ['websitescreen'] = Xranks::getWebsiteScreen ($webserviceHostUrl);
						$pageRanksArray ['reporttext'] = str_replace(array("\n", "\t"), '', Xranks::getReportText ($webserviceHostUrl));
						
						// Store the service and target url
						$pageRanksArray ['service'] = 'xranks';
						$pageRanksArray ['targeturl'] = $url;
					break;
						
					case 'statscrop':
					default:
						// Set the resulting array
						$pageRanksArray = array (
								'globalrank' => Text::_ ( 'COM_JMAP_NA' ),
								'dailyvisitors' => Text::_ ( 'COM_JMAP_NA' ),
								'dailypageviews' => Text::_ ( 'COM_JMAP_NA' ),
								'pageloadtime' => Text::_ ( 'COM_JMAP_NA' ),
								'seoscore' => Text::_ ( 'COM_JMAP_NA' ),
								'rating' => Text::_ ( 'COM_JMAP_NA' ),
								'googleindexedlinks' => Text::_ ( 'COM_JMAP_NA' ),
								'openpagerank' => Text::_ ( 'COM_JMAP_NA' ),
								'keywords' => Text::_ ( 'COM_JMAP_NA' ),
								'backlinkers' => Text::_ ( 'COM_JMAP_NA' ),
								'tags' => Text::_ ( 'COM_JMAP_NA' ),
								'linksinternal' => Text::_ ( 'COM_JMAP_NA' ),
								'linksexternal' => Text::_ ( 'COM_JMAP_NA' ),
								'websitescreen' => '',
								'trafficgraph' => ''
						);
						
						$pageRanksArray ['globalrank'] = Statscrop::getGlobalRank ($webserviceHostUrl);
						$pageRanksArray ['dailyvisitors'] = Statscrop::getDailyVisitors($webserviceHostUrl);
						$pageRanksArray ['bouncerate'] = Statscrop::getBounceRate($webserviceHostUrl);
						$pageRanksArray ['pageloadtime'] = Statscrop::getPageLoadtime ($webserviceHostUrl);
						
						// Open Page Rank SEMRush stats
						$topLevelDomain = 'us';
						$parsedUrl = explode('.', $url);
						$topLevelDomainDetected = array_pop($parsedUrl);
						if(in_array($topLevelDomainDetected, Semrush::getDBs())) {
							$topLevelDomain = $topLevelDomainDetected;
						}
						$pageRanksArray ['openpagerank'] = Semrush::getDomainRank($webserviceHostUrl, $topLevelDomain);
						
						// New line
						$pageRanksArray ['googleindexedlinks'] = Google::getSiteindexTotal ();
						$pageRanksArray ['linksinternal'] = Statscrop::getInternalLinksCount ($webserviceHostUrl); // API call
						$pageRanksArray ['linksexternal'] = Statscrop::getExternalLinksCount ($webserviceHostUrl); // API call
						$pageRanksArray ['keywords'] = Statscrop::getKeywords ($webserviceHostUrl); // API call
						$pageRanksArray ['backlinkers'] = Statscrop::getBacklinkers ($webserviceHostUrl); // API call
						$pageRanksArray ['countries'] = Statscrop::getCountries ($webserviceHostUrl); // API call
						$pageRanksArray ['subdomains'] = Statscrop::getSubdomains ($webserviceHostUrl); // API call
						$pageRanksArray ['websitescreen'] = Statscrop::getWebsiteScreen ($webserviceHostUrl);
						
						// New graph line
						$pageRanksArray ['trafficgraph'] = Statscrop::getTrafficGraph ($webserviceHostUrl);

						// Store the service and target url
						$pageRanksArray ['service'] = 'statscrop';
						$pageRanksArray ['targeturl'] = $url;
					break;
				}

				// All completed successfully
				$response->result = true;
				$response->seostats = $pageRanksArray;
			}
		} catch ( JMapException $e ) {
			$response->result = false;
			$response->exception_message = $e->getMessage ();
			$response->errorlevel = $e->getExceptionLevel();
			$response->seostats = $pageRanksArray;
			return $response;
		} catch ( \Exception $e ) {
			$jmapException = new JMapException ( Text::sprintf ( 'COM_JMAP_ERROR_RETRIEVING_SEOSTATS', $e->getMessage () ), 'error' );
			$response->result = false;
			$response->exception_message = $jmapException->getMessage ();
			$response->errorlevel = $jmapException->getExceptionLevel();
			$response->seostats = $pageRanksArray;
			return $response;
		}

		return $response;
	}
	
	/**
	 * Fetch SEO stats from remote services
	 * based on Seo stats lib that is able to calculate Google Page rank
	 *
	 *
	 * @access private
	 * @param Object[] $additionalModels Array for additional injected models type hinted by interface
	 * @return Object
	 */
	private function fetchCompetitorStats($linkUrl, $additionalModels = null) {
		// Response JSON object
		$response = new \stdClass ();
	
		try {
			if (! function_exists ( 'curl_init' )) {
				throw new JMapException ( Text::_ ( 'COM_JMAP_CURL_NOT_SUPPORTED' ), 'error' );
			}
	
			// API REQUEST, define target URL
			$competitorStatsArray = GoogleCompetitors::getStats($linkUrl);
				
			// All completed successfully
			$response->result = true;
			$response->competitorstats = $competitorStatsArray;
		} catch ( JMapException $e ) {
			$response->result = false;
			$response->exception_message = $e->getMessage ();
			$response->errorlevel = $e->getExceptionLevel();
			return $response;
		} catch ( \Exception $e ) {
			$jmapException = new JMapException ( Text::sprintf ( 'COM_JMAP_ERROR_RETRIEVING_SEOSTATS', $e->getMessage () ), 'error' );
			$response->result = false;
			$response->exception_message = $jmapException->getMessage ();
			$response->errorlevel = $jmapException->getExceptionLevel();
			return $response;
		}
	
		return $response;
	}
	
	/**
	 * Fetch informations from the Google API for SERP to check the indexing status of a link
	 *
	 * @access private
	 * @param string $linkUrl
	 * @param Object[] $additionalModels Array for additional injected models type hinted by interface
	 * @return Object
	 */
	private function getIndexedStatus($linkUrl, $additionalModels = null) {
		// Response JSON object
		$response = new \stdClass ();
		$cParams = $this->getComponentParams();
		
		// Random user agents DB
		$userAgents=array(
				"Mozilla/5.0 (iPad; CPU OS 13_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) FxiOS/108.0 Mobile/15E148 Safari/605.1.15",
				"Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:108.0) Gecko/20100101 Firefox/108.0",
				"Mozilla/5.0 (Macintosh; Intel Mac OS X 13.1; rv:108.0) Gecko/20100101 Firefox/108.0",
				"Mozilla/5.0 (X11; Linux i686; rv:108.0) Gecko/20100101 Firefox/108.0",
				"Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36",
				"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36",
				"Mozilla/5.0 (Macintosh; Intel Mac OS X 13_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36",
				"Mozilla/5.0 (iPad; CPU OS 16_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/108.0.5359.112 Mobile/15E148 Safari/604.1",
				"Mozilla/5.0 (iPhone; CPU iPhone OS 16_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/108.0.5359.112 Mobile/15E148 Safari/604.1",
				"Mozilla/5.0 (Linux; Android 10; SM-A205U) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.5359.128 Mobile Safari/537.36",
		 		"Mozilla/5.0 (Linux; Android 10; LM-Q710(FGN)) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.5359.128 Mobile Safari/537.36");
	    $ua = $userAgents[rand (0, count($userAgents) - 1)];

		// Set number of max results to evaluate in the SERP
		$maxResults = $cParams->get('linksanalyzer_serp_numresults', 10);
		$engine = $cParams->get('linksanalyzer_indexing_engine', 'webcrawler');
		$referer = $engine == 'webcrawler' ? 'http://www.webcrawler.com/' : 'http://www.bing.com/';

	    // Format the request header array
		$headers = array (
				'Cache-Control' => 'max-age=0',
				'User-Agent' => $ua,
				'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
				'Referer' => $referer,
				'Accept-Language' => 'en-GB, en'
		);

		// Check if the Google Pagespeed score must be retrieved as well
		if($cParams->get('links_analyzer_pagespeed_insights_analysis', 0)) {
			// Ensure that at least a language is available for the backend and locale sent to Google otherwise the API fails
			$language = Multilang::getCurrentSefLanguage();
			$locale = $language ? $language : 'en';
			
			// Build the purified domain to scrape using the host only
			$hostDomain = rawurlencode ( $linkUrl );
			$customApiKey = trim($cParams->get ( 'ga_api_key', ''));
			$apiKey = $customApiKey ? $customApiKey : 'AIzaSyAO-uQIj5xS1k5-VAOQVkRDz5wzWPlSwnE';
			$strategy = $cParams->get('links_analyzer_pagespeed_insights_analysis_strategy', 'desktop');
			$url = "https://content.googleapis.com/pagespeedonline/v5/runPagespeed?url=$hostDomain&key=$apiKey&strategy=$strategy&category=performance&locale=" . $locale;
			
			try {
				// Fetch remote data to scrape
				$httpTransport = $cParams->get('analytics_service_http_transport', 'curl') == 'socket' ? new Socket () : new Curl ();
				$connectionAdapter = new Http ( $httpTransport, $cParams );
				$httpResponse = $connectionAdapter->get ( $url );
				
				// Check if HTTP status code is 200 OK
				if ($httpResponse->code == 200 && $httpResponse->body) {
					$decodedApiResponse = json_decode($httpResponse->body, true);
					if(is_array($decodedApiResponse)) {
						// Calculate the score category, range, colors for labels and sliders
						$response->pagespeed = isset($decodedApiResponse['lighthouseResult']['categories']['performance']) ? (int)($decodedApiResponse['lighthouseResult']['categories']['performance']['score'] * 100) : -1;
						
						// Largest Contentful Paint, loadingExperience if available, fallback to live metrics
						$response->pagespeed_lcp_score = isset($decodedApiResponse['loadingExperience']['metrics']['LARGEST_CONTENTFUL_PAINT_MS']) ? number_format($decodedApiResponse['loadingExperience']['metrics']['LARGEST_CONTENTFUL_PAINT_MS']['percentile'] / 1000, 1) : number_format($decodedApiResponse['lighthouseResult']['audits']['largest-contentful-paint']['numericValue'] / 1000, 1);
						if(isset($decodedApiResponse['loadingExperience']['metrics']['LARGEST_CONTENTFUL_PAINT_MS'])) {
							$response->pagespeed_lcp_score_vote = $decodedApiResponse['loadingExperience']['metrics']['LARGEST_CONTENTFUL_PAINT_MS']['category'];
						} else {
							if($response->pagespeed_lcp_score <= 2.5) {
								$response->pagespeed_lcp_score_vote = 'FAST';
							} elseif($response->pagespeed_lcp_score > 2.5 && $response->pagespeed_lcp_score <= 4.0) {
								$response->pagespeed_lcp_score_vote = 'AVERAGE';
							} elseif($response->pagespeed_lcp_score > 4.0) {
								$response->pagespeed_lcp_score_vote = 'LOW';
							}
						}
						
						// Load only if the loadingExperience is available as FID is only available as a real-use field metric
						if(isset($decodedApiResponse['loadingExperience']['metrics']['FIRST_INPUT_DELAY_MS'])) {
							$response->pagespeed_fid_score = $decodedApiResponse['loadingExperience']['metrics']['FIRST_INPUT_DELAY_MS']['percentile'];
							$response->pagespeed_fid_score_vote = $decodedApiResponse['loadingExperience']['metrics']['FIRST_INPUT_DELAY_MS']['category'];
						}

						// Cumulative Layout Shift, loadingExperience if available, fallback to live metrics
						$response->pagespeed_cls_score = isset($decodedApiResponse['loadingExperience']['metrics']['CUMULATIVE_LAYOUT_SHIFT_SCORE']) ? $decodedApiResponse['loadingExperience']['metrics']['CUMULATIVE_LAYOUT_SHIFT_SCORE']['percentile'] / 100 : number_format($decodedApiResponse['lighthouseResult']['audits']['cumulative-layout-shift']['numericValue'], 3);
						if(isset($decodedApiResponse['loadingExperience']['metrics']['CUMULATIVE_LAYOUT_SHIFT_SCORE']['category'])) {
							$response->pagespeed_cls_score_vote = $decodedApiResponse['loadingExperience']['metrics']['CUMULATIVE_LAYOUT_SHIFT_SCORE']['category'];
						} else {
							if($response->pagespeed_cls_score <= 0.1) {
								$response->pagespeed_cls_score_vote = 'FAST';
							} elseif($response->pagespeed_cls_score > 0.1 && $response->pagespeed_cls_score <= 0.25) {
								$response->pagespeed_cls_score_vote = 'AVERAGE';
							} elseif($response->pagespeed_cls_score > 0.25) {
								$response->pagespeed_cls_score_vote = 'LOW';
							}
						}
					}
				}
			} catch ( \Exception $e ) {
				// Go on with the next API without blocking exception
			}
		}

		// Start querying the SERP search engine
		try {
			if (! class_exists( '\DOMDocument' )) {
				throw new JMapException ( Text::_ ( 'COM_JMAP_DOMDOCUMENT_NOT_SUPPORTED' ), 'error' );
			}
			
			// Initialize indexing status to false AKA 'Not indexed'
			$response->indexing_status = 0;
			$fullDomainLinkUrl = null;
			
			// Check if the query URL has been requested
			if ($linkUrl) {
				// Instantiante a new HTTP client
				$httpTransport = $cParams->get('analytics_service_http_transport', 'curl') == 'socket' ? new Socket () : new Curl ();
				$httpClient = new Http ( $httpTransport, $cParams );
				
				// Remove the html prefix in the url and separators if any, this helps the algo to be more exact
				if($cParams->get('linksanalyzer_remove_separators', 1)) {
					$linkUrl = preg_replace('#http.?:\/\/#i', '', $linkUrl);
					$linkUrl = StringHelper::str_ireplace('-', ' ', $linkUrl);
					$linkUrl = StringHelper::str_ireplace('_', ' ', $linkUrl);
					$linkUrl = preg_replace('/\s[^0-9]\s/i', ' ', $linkUrl);
				}
				$fullDomainLinkUrl = $linkUrl;
				
				// Remove the html prefix in the url and separators if any, this helps the algo to be more exact
				$removeSlashes = $cParams->get('linksanalyzer_remove_slashes', 2);
				if($removeSlashes == 1 || ($removeSlashes >= 2 && substr_count($linkUrl, '/') <= ($removeSlashes - 1))) {
					$linkUrl = StringHelper::str_ireplace('/', ' ', $linkUrl);
					$linkUrl = preg_replace('#www\.#i', '', $linkUrl);
					$linkUrl = preg_replace('#\.com|\.org|\.edu|\.gov|\.uk|\.net|\.ca|\.de|\.jp|\.fr|\.au|\.us|\.ru|\.ch|\.it|\.nl|\.se|\.no|\.es|\.mil#i', '', $linkUrl);
				}
				
				// Perform the query to the http://www.webcrawler.com and limit the URL length to max 106 chars
				$encodedURL = urlencode(StringHelper::substr($linkUrl, 0 , 106));

				// Switch the web crawler
				if($engine == 'webcrawler') {
					$httpResponse = $httpClient->get('https://www.webcrawler.com/info.wbcrwl.sbox/search/web?q=' . $encodedURL . '&submit=Search', $headers);
				} else {
					$httpResponse = $httpClient->get('https://www.bing.com/search?q=' . $encodedURL, $headers);
				}
				
				// If the web service returns a HTTP 200 OK go on to parse results
				if($httpResponse->code == 200) {
					// Get the response body
					$responseBody = $httpResponse->body;

					// New instance of DOMDocument parser
					$doc = new \DOMDocument('1.0', 'UTF-8');
					libxml_use_internal_errors(true);
					
					//Load the DOMDocument document
					$doc->loadHTML($responseBody);
					libxml_clear_errors();

					// Set up the DOMXPath and the css className to find in the document for the target SERP elements
					$finder = new \DomXPath($doc);
					
					// Find SERP nodes, object of DOMNodeList
					if($engine == 'webcrawler') {
						$classname = $cParams->get('linksanalyzer_indexing_engine_selector_webcrawler', 'web-bing__url');
						$resultsNodes = $finder->query("//span[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')
														or contains(concat(' ', normalize-space(@class), ' '), ' web-bing__url ')
														or contains(concat(' ', normalize-space(@class), ' '), ' web-google__url ')]");
					} else {
						$classname = $cParams->get('linksanalyzer_indexing_engine_selector_bing', 'b_attribution');
						$resultsNodes = $finder->query("//div[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
					}
				
					// If SERP nodes has been found go on to check if the link is indexed for this domain
					if($resultsNodes->length) {
						// Evaluate n SERP results based on the config param
						for ($i=0,$k=$maxResults; $i<$maxResults; $i++) {
							// Get node by object instance, DOMNode
							$node = $resultsNodes->item($i);
							if(is_object($node)) {
								// Security safe, check again if the class name is correct
								if($node->getAttribute('class') == $classname || $node->getAttribute('class') == 'web-bing__url' || $node->getAttribute('class') == 'web-google__url') {
									$trimmedNode = trim($node->nodeValue);
									$trimmedNode = preg_replace('#http.?:\/\/#i', '', $trimmedNode);
									$urlArray = explode('/', $trimmedNode);
									// Extract the SERP domain and assume that it's indexed for the current query
									$serpDomain = $urlArray[0];
									if($cParams->get('linksanalyzer_remove_separators', 1)) {
										$serpDomain = StringHelper::str_ireplace('-', ' ', $serpDomain);
										$serpDomain = StringHelper::str_ireplace('_', ' ', $serpDomain);
									}
									if(preg_match('/' . $serpDomain . '/i', $fullDomainLinkUrl)) {
										$response->indexing_status = 1;
										break;
									}
								}
							}
						}
					} else {
						// No SERP found for this link, return as not available info
						$response->indexing_status = -1;
					}
				}
				
				// Final all went well, no exceptions triggered
				$response->result = true;
			}
		} catch ( JMapException $e ) {
			if(!isset($response->pagespeed)) {
				$response->result = false;
				$response->exception_message = $e->getMessage ();
			} else {
				$response->result = true;
			}
			return $response;
		} catch ( \Exception $e ) {
			if(!isset($response->pagespeed)) {
				$jmapException = new JMapException ( Text::sprintf ( 'COM_JMAP_ANALYZER_INDEXING_ERROR', $e->getMessage () ), 'error' );
				$response->result = false;
				$response->exception_message = $jmapException->getMessage ();
			} else {
				$response->result = true;
			}
			return $response;
		}
	
		return $response;
	}
	
	/**
	 * Check what sitemaps are cached on disk to show accordingly green labels
	 *
	 * @access private
	 * @param $idEntity
	 * @param $additionalModels
	 *
	 * @Return array
	 */
	private function getPrecachedSitemaps($queryStringLinksArray, $additionalModels = null) {
		// Response JSON object
		$response = new \stdClass ();

		try {
			// Init empty status
			$response->sitemapLinksStatus = array ();

			// Start to set an associative array based on url parsing and file existance
			if (! empty ( $queryStringLinksArray ) && is_array ( $queryStringLinksArray )) {
				$joomlaConfig = $this->app->getConfig();
				$localTimeZone = new \DateTimeZone($joomlaConfig->get('offset'));
				foreach ( $queryStringLinksArray as $singlePostedSitemapLink ) {
					$filename = 'sitemap_';
					$extractedQuery = parse_url ( $singlePostedSitemapLink, PHP_URL_QUERY );
					parse_str ( $extractedQuery, $parsedLink );
					// Evaluate format
					if (! empty ( $parsedLink ['format'] )) {
						$filename .= $parsedLink ['format'];
					}
					// Evaluate language
					if (! empty ( $parsedLink ['lang'] )) {
						$filename .= '_' . $parsedLink ['lang'];
					}
					// Evaluate dataset
					if (! empty ( $parsedLink ['dataset'] )) {
						$filename .= '_dataset' . $parsedLink ['dataset'];
					}
					// Evaluate Itemid
					if (! empty ( $parsedLink ['Itemid'] )) {
						$filename .= '_menuid' . $parsedLink ['Itemid'];
					}

					if (File::exists ( JPATH_COMPONENT_SITE . '/precache/' . $filename . '.xml' )) {
						// get last generation time
						$lastGenerationTimestamp = filemtime ( JPATH_COMPONENT_SITE . '/precache/' . $filename . '.xml' );
						$dateObject = new Date($lastGenerationTimestamp);
						$dateObject->setTimezone($localTimeZone);

						$response->sitemapLinksStatus [$singlePostedSitemapLink] = array (
								'cached' => true,
								'lastgeneration' => $dateObject->format('Y-m-d', true)
						);
					} else {
						$response->sitemapLinksStatus [$singlePostedSitemapLink] = false;
					}
				}
			}

			// All completed succesfully
			$response->result = true;
		} catch ( \Exception $e ) {
			$jmapException = new JMapException ( $e->getMessage (), 'error' );
			$response->result = false;
			$response->exception_message = $jmapException->getMessage ();
			return $response;
		}

		return $response;
	}

	/**
	 * Get file info to delete and check if file for precache exists
	 * In that case delete the file and clear cache
	 *
	 * @access private
	 * @param $fileInfo
	 * @param $additionalModels
	 *
	 * @Return array
	 */
	private function deletePrecachedSitemap($fileInfo, $additionalModels = null) {
		// Response JSON object
		$response = new \stdClass();
	
		try {
			// Resource Action detection dall'HTTP method name
			$HTTPMethod = $this->app->getInput()->server->get('REQUEST_METHOD', 'POST');
	
			if ($HTTPMethod !== 'POST') {
				throw new JMapException(Text::_('COM_JMAP_INVALID_RESTFUL_METHOD'), 'error');
			}
	
			// Start to set an associative array based on url parsing and file existance
			if(!empty($fileInfo)) {
				$filename = 'sitemap_';
				// Evaluate format
				if(!empty($fileInfo->format)) {
					$filename .= $fileInfo->format;
				}
				// Evaluate language
				if(!empty($fileInfo->lang)) {
					$filename .= '_' . $fileInfo->lang;
				}
				// Evaluate dataset
				if(!empty($fileInfo->dataset)) {
					$filename .= '_dataset' . $fileInfo->dataset;
				}
				// Evaluate Itemid
				if(!empty($fileInfo->Itemid)) {
					$filename .= '_menuid' . $fileInfo->Itemid;
				}
					
				if(File::exists(JPATH_COMPONENT_SITE . '/precache/' . $filename . '.xml')) {
					if(!@unlink(JPATH_COMPONENT_SITE . '/precache/' . $filename . '.xml')) {
						throw new JMapException(Text::_('COM_JMAP_PRECACHING_ERROR_DELETING_FILE'), 'error');
					}

					// Check also if a temp precached file is still present and clear it
					if(File::exists(JPATH_COMPONENT_SITE . '/precache/temp_' . $filename . '.xml')) {
						@unlink(JPATH_COMPONENT_SITE . '/precache/temp_' . $filename . '.xml');
					}
				}
			}
	
			// All completed succesfully
			$response->result = true;
		} catch (\Exception $e) {
			$jmapException = new JMapException($e->getMessage(), 'error');
			$response->result = false;
			$response->exception_message = $jmapException->getMessage();
			return $response;
		}
	
		return $response;
	}
	
	/**
	 * Load fields for selected database table
	 * 
	 * @access private
	 * @param string $tableName
	 * @param Object[] $additionalModels Array for additional injected models type hinted by interface
	 * @return array
	 */
	private function loadTableFields($tableName, $additionalModels = null) {
		// Fields select list
		$queryFields = "SHOW COLUMNS " . 
					   "\n FROM " . $this->dbInstance->quoteName($tableName);
		$this->dbInstance->setQuery($queryFields);
		$elements = $this->dbInstance->loadColumn();

		return $elements;
	}

	/**
	 * Manage store/update Pingomatic entity record
	 * 
	 * @access private
	 * @param $idEntity
	 * @param $additionalModels
	 *
	 * @Return array
	 */
	private function storeUpdatePingomatic($idEntity, $additionalModels = null) {
		// Response JSON object
		$response = new \stdClass();

		// Store on ORM Table
		$table = $this->getTable('Pingomatic', 'Administrator');
		$this->requestArray['lastping'] = Date::getInstance()->toSql();
		try {
			$table->bind($this->requestArray, array(), true);

			if (!$table->check()) {
				throw new JMapException($table->getException(), 'error');
			}

			// Update nulls
			if (!$table->store(true)) {
				throw new JMapException($table->getException(), 'error');
			}
			
			// Lock the checkout record again without a controller redirect being everything managed by AJAX request
			$table->checkOut($this->app->getIdentity()->id, $table->id);
		} catch (JMapException $e) {
			$response->result = false;
			$response->errorMsg = $e->getMessage();
			return $response;
		} catch (\Exception $e) {
			$jmapException = new JMapException($e->getMessage(), 'error');
			$response->result = false;
			$response->errorMsg = $jmapException->getMessage();
			return $response;
		}

		// Manage exceptions from DB Model and return to JS domain
		$response->result = true;
		$response->id = $table->id;
		$response->lastping = HTMLHelper::_('date', $table->lastping, Text::_('DATE_FORMAT_LC2'));
		$response->lastpingMySql = $table->lastping;

		return $response;
	}
	
	/**
	 * Manage store/update for menu priorities
	 *
	 * @access private
	 * @param $params
	 * @param $additionalModels
	 *
	 * @Return array
	 */
	private function storeUpdatePriority($params, $additionalModels = null) {
		// Response JSON object
		$response = new \stdClass();
	
		// Store on ORM Table
		$table = $this->getTable($params->type, 'Administrator');
	
		try {
			$table->load ((int)$params->itemId);
			
			$table->priority = $params->priorityValue;
			
			// Switch on subaction
			if(!isset($params->task)) {
				throw new JMapException(Text::_('COM_JMAP_VALIDATON_ERROR_NOPRIORITY'), 'warning');
			}
			if($params->task == 'store') {
				if (!$table->store(false, $params->itemId)) {
					throw new JMapException($table->getException(), 'warning');
				}
			} else {
				// Check if record still exists in database
				if(!$table->id) {
					throw new JMapException(Text::_('COM_JMAP_VALIDATON_ERROR_NOPRIORITY'), 'warning');
				}
				// Delete always
				if (!$table->delete()) {
					throw new JMapException($table->getException(), 'warning');
				}
			}
		} catch (JMapException $e) {
			$response->result = false;
			$response->errorMsg = $e->getMessage();
			return $response;
		} catch (\Exception $e) {
			$jmapException = new JMapException($e->getMessage(), 'warning');
			$response->result = false;
			$response->errorMsg = $jmapException->getMessage();
			return $response;
		}
	
		// Manage exceptions from DB Model and return to JS domain
		$response->result = true;
	
		return $response;
	}
	/**
	 * Get existing priority value for menu items
	 *
	 * @access private
	 * @param $params
	 * @param $additionalModels
	 *
	 * @Return array
	 */
	private function getPriority($params, $additionalModels = null) {
		// Response JSON object
		$response = new \stdClass();
		
		// Store on ORM Table
		$table = $this->getTable($params->type, 'Administrator');
		
		try {
			if (! $table->load ( (int)$params->iditem )) {
				throw new JMapException ( Text::_('COM_JMAP_ERROR_RECORD_NOT_FOUND'), 'warning' );
			}
			
			// Load a non existing record
			if(!$table->id) {
				throw new JMapException('nopriority', 'warning');
			}
		} catch (JMapException $e) {
			$response->result = false;
			$response->errorMsg = $e->getMessage();
			return $response;
		} catch (\Exception $e) {
			$jmapException = new JMapException($e->getMessage(), 'warning');
			$response->result = false;
			$response->errorMsg = $jmapException->getMessage();
			return $response;
		}
		
		// Manage exceptions from DB Model and return to JS domain
		$response->result = true;
		$response->priority = $table->priority;
		
		return $response;
	}

	/**
	 * Manage robots.txt entry
	 *
	 * @access private
	 * @param $idEntity
	 * @param $additionalModels
	 *
	 * @Return array
	 */
	private function robotsSitemapEntry($queryStringLink, $additionalModels = null) {
		// Response JSON object
		$response = new \stdClass();

		try {
			// Set the robots.txt path based on the subfolder parameter, the robots must always be at the top level
			if($this->getComponentParams()->get('robots_joomla_subfolder', 0)) {
				$topRootFolder = dirname(JPATH_ROOT);
			} else {
				$topRootFolder = JPATH_ROOT;
			}
			
			// Resource Action detection dall'HTTP method name
			$HTTPMethod = $this->app->getInput()->server->get('REQUEST_METHOD', 'POST');

			if ($HTTPMethod !== 'POST') {
				throw new JMapException(Text::_('COM_JMAP_INVALID_RESTFUL_METHOD'), 'error');
			}

			// Update robots.txt add entry Sitemap if not exists
			$targetRobot = null;
			// Try standard robots.txt
			if(File::exists($topRootFolder . '/robots.txt')) {
				$targetRobot = $topRootFolder . '/robots.txt';
			} elseif (File::exists($topRootFolder . '/robots.txt.dist')) { // Fallback on distribution version
				$targetRobot = $topRootFolder . '/robots.txt.dist';
			} else {
				throw new JMapException(Text::_('COM_JMAP_ROBOTS_NOTFOUND'), 'error');
			}
			
			// Robots.txt found!
			if($targetRobot !== false) {
				// If file permissions ko
				if(!$robotContents = File::read($targetRobot)) {
					throw new JMapException(Text::_('COM_JMAP_ERROR_READING_ROBOTS'), 'error');
				}
				
				$newEntry = null;
				// Entry for this sitemap 
				if(!stristr($robotContents, 'Sitemap: ' . $queryStringLink)) {
					$toAppend = null;
					// Check if JSitemap added already some entries
					if(!stristr($robotContents, '# JSitemap')) {
						// Empty line double EOL
						$toAppend = PHP_EOL . PHP_EOL . '# JSitemap entries';
					}
					$toAppend .= PHP_EOL . 'Sitemap: ' . $queryStringLink;
					$newEntry = $robotContents . $toAppend;
				}
				
				// If file permissions ko on rewrite updated contents
				if($newEntry) {
					$originalPermissions = null;
					if(!is_writable($targetRobot)) {
						$originalPermissions = intval(substr(sprintf('%o', fileperms($targetRobot)), -4), 8);
						@chmod($targetRobot, 0755);
					}
					if(@!File::write($targetRobot, $newEntry)) {
						throw new JMapException(Text::_('COM_JMAP_ERROR_WRITING_ROBOTS'), 'error');
					}
					// Check if permissions has been changed and recover the original in that case
					if($originalPermissions) {
						@chmod($targetRobot, $originalPermissions);
					}
				} else {
					throw new JMapException(Text::_('COM_JMAP_ENTRY_ALREADY_ADDED'), 'error');
				}
			}
			
			// All completed succesfully
			$response->result = true;
		} catch (JMapException $e) {
			$response->result = false;
			$response->errorMsg = $e->getMessage();
			return $response;
		} catch (\Exception $e) {
			$jmapException = new JMapException($e->getMessage(), 'error');
			$response->result = false;
			$response->errorMsg = $jmapException->getMessage();
			return $response;
		}
		
		return $response;
	}

	/**
	 * Store meta info for a given url
	 *
	 * @access private
	 * @param Object $dataObject
	 * @param Object[] $additionalModels Array for additional injected models type hinted by interface
	 * @return Object
	 */
	private function saveMeta($dataObject, $additionalModels = null) {
		// Response JSON object
		$response = new \stdClass();
	
		try {
			// Check if the link already exists in this table
			$selectQuery = "SELECT" . $this->dbInstance->quoteName('id') .
						   "\n FROM " . $this->dbInstance->quoteName('#__jmap_metainfo') .
						   "\n WHERE" .
						   $this->dbInstance->quoteName('linkurl') . " = " . $this->dbInstance->quote($dataObject->linkurl);
			$linkExists = $this->dbInstance->setQuery ( $selectQuery )->loadResult();

			// If the link exists just update it, otherwise insert a new one
			if($linkExists) {
				$query = "UPDATE" .
						 "\n " . $this->dbInstance->quoteName('#__jmap_metainfo') .
						 "\n SET " .
						 "\n " . $this->dbInstance->quoteName('meta_title') . " = " . $this->dbInstance->quote($dataObject->meta_title) . "," .
						 "\n " . $this->dbInstance->quoteName('meta_desc') . " = " . $this->dbInstance->quote($dataObject->meta_desc) . "," .
						 "\n " . $this->dbInstance->quoteName('meta_image') . " = " . $this->dbInstance->quote($dataObject->meta_image) . "," .
						 "\n " . $this->dbInstance->quoteName('robots') . " = " . $this->dbInstance->quote($dataObject->robots) .
						 "\n WHERE " .
						 "\n " . $this->dbInstance->quoteName('linkurl') . " = " . $this->dbInstance->quote($dataObject->linkurl);
				$this->dbInstance->setQuery ( $query );
			} else {
				$query = "INSERT INTO" .
						 "\n " . $this->dbInstance->quoteName('#__jmap_metainfo') . "(" .
						 $this->dbInstance->quoteName('linkurl') . "," .
						 $this->dbInstance->quoteName('meta_title') . "," .
						 $this->dbInstance->quoteName('meta_desc') . "," .
						 $this->dbInstance->quoteName('meta_image') . "," .
						 $this->dbInstance->quoteName('robots') . "," .
						 $this->dbInstance->quoteName('published') . "," .
						 $this->dbInstance->quoteName('excluded') . ") VALUES (" .
						 $this->dbInstance->quote($dataObject->linkurl) . "," .
						 $this->dbInstance->quote($dataObject->meta_title) . "," .
						 $this->dbInstance->quote($dataObject->meta_desc) . "," .
						 $this->dbInstance->quote($dataObject->meta_image) . "," .
						 $this->dbInstance->quote($dataObject->robots) . "," .
						 $this->dbInstance->quote($dataObject->published) . "," .
						 $this->dbInstance->quote($dataObject->excluded) . ")";
				$this->dbInstance->setQuery ( $query );
			}
			$this->dbInstance->execute ();
	
			// All completed succesfully
			$response->result = true;
		} catch (JMapException $e) {
			$response->result = false;
			$response->exception_message = $e->getMessage();
			return $response;
		} catch (\Exception $e) {
			$jmapException = new JMapException(Text::sprintf('COM_JMAP_ERROR_RETRIEVING_DATA', $e->getMessage()), 'error');
			$response->result = false;
			$response->exception_message = $jmapException->getMessage();
			return $response;
		}
	
		return $response;
	}
	
	/**
	 * Store meta info for a given url
	 *
	 * @access private
	 * @param Object $dataObject
	 * @param Object[] $additionalModels Array for additional injected models type hinted by interface
	 * @return Object
	 */
	private function deleteMeta($dataObject, $additionalModels = null) {
		// Response JSON object
		$response = new \stdClass();
	
		try {
			// Check if the link already exists in this table
			$selectQuery = "SELECT" . $this->dbInstance->quoteName('id') .
						   "\n FROM " . $this->dbInstance->quoteName('#__jmap_metainfo') .
						   "\n WHERE" .
						   $this->dbInstance->quoteName('linkurl') . " = " . $this->dbInstance->quote($dataObject->linkurl);
			$linkExists = $this->dbInstance->setQuery ( $selectQuery )->loadResult();

			// If the link exists just update it, otherwise insert a new one
			if($linkExists) {
				$query = "DELETE" .
						 "\n FROM " . $this->dbInstance->quoteName('#__jmap_metainfo') .
						 "\n WHERE " .
						 "\n " . $this->dbInstance->quoteName('linkurl') . " = " . $this->dbInstance->quote($dataObject->linkurl);
				$this->dbInstance->setQuery ( $query );
				$this->dbInstance->execute ();
			} else {
				$response->result = true;
				$response->exception_message = Text::_('COM_JMAP_NO_METAINFO_SAVED');
				return $response;
			}
	
			// All completed succesfully
			$response->result = true;
		} catch (JMapException $e) {
			$response->result = false;
			$response->exception_message = $e->getMessage();
			return $response;
		} catch (\Exception $e) {
			$jmapException = new JMapException(Text::sprintf('COM_JMAP_ERROR_RETRIEVING_DATA', $e->getMessage()), 'error');
			$response->result = false;
			$response->exception_message = $jmapException->getMessage();
			return $response;
		}
	
		return $response;
	}
	
	/**
	 * Store meta info for a given url
	 *
	 * @access private
	 * @param Object $dataObject
	 * @param Object[] $additionalModels Array for additional injected models type hinted by interface
	 * @return Object
	 */
	private function stateMeta($dataObject, $additionalModels = null) {
		// Response JSON object
		$response = new \stdClass();
	
		try {
			// Check if the link already exists in this table
			$selectQuery = "SELECT" . $this->dbInstance->quoteName('id') .
						   "\n FROM " . $this->dbInstance->quoteName('#__jmap_metainfo') .
						   "\n WHERE" .
			$this->dbInstance->quoteName('linkurl') . " = " . $this->dbInstance->quote($dataObject->linkurl);
			$linkExists = $this->dbInstance->setQuery ( $selectQuery )->loadResult();
	
			// If the link exists just update it, otherwise insert a new one
			if($linkExists) {
				$query = "UPDATE" .
						 "\n " . $this->dbInstance->quoteName('#__jmap_metainfo') .
						 "\n SET " .
						 "\n " . $this->dbInstance->quoteName($dataObject->field) . " = " . (int)($dataObject->fieldValue) .
						 "\n WHERE " .
						 "\n " . $this->dbInstance->quoteName('linkurl') . " = " . $this->dbInstance->quote($dataObject->linkurl);
				$this->dbInstance->setQuery ( $query );
				$this->dbInstance->execute ();
			} elseif(!$linkExists && $dataObject->field == 'excluded') {
				$query = "INSERT INTO" .
						 "\n " . $this->dbInstance->quoteName('#__jmap_metainfo') . "(" .
						 $this->dbInstance->quoteName('linkurl') . "," .
						 $this->dbInstance->quoteName('published') . "," .
						 $this->dbInstance->quoteName('excluded') . ") VALUES (" .
						 $this->dbInstance->quote($dataObject->linkurl) . "," .
						 $this->dbInstance->quote(0) . "," .
						 $this->dbInstance->quote($dataObject->fieldValue) . ")";
				$this->dbInstance->setQuery ( $query );
				$this->dbInstance->execute ();
			} else {
				$response->result = true;
				$response->exception_message = Text::_('COM_JMAP_NO_METAINFO_SAVED');
				return $response;
			}
	
			// All completed succesfully
			$response->result = true;
		} catch (JMapException $e) {
			$response->result = false;
			$response->exception_message = $e->getMessage();
			return $response;
		} catch (\Exception $e) {
			$jmapException = new JMapException(Text::sprintf('COM_JMAP_ERROR_RETRIEVING_DATA', $e->getMessage()), 'error');
			$response->result = false;
			$response->exception_message = $jmapException->getMessage();
			return $response;
		}
	
		return $response;
	}

	private function generateChatGPTMeta($dataObject, $additionalModels = null) {
		// Response JSON object
		$response = new \stdClass();
		
		try {
			// All completed succesfully
			$aiGeneratorModel = $additionalModels['Aigenerator'];
			$metainfoModel = $additionalModels['Metainfo'];
			$sitemapLanguageSef = $dataObject->sitemaplang;
			$sitemapLanguageTitle = Multilang::loadLanguageTitle($sitemapLanguageSef);
			if(!$sitemapLanguageTitle) {
				$sitemapLanguageSef = Multilang::getCurrentSefLanguage();
				$sitemapLanguageTitle = Multilang::loadLanguageTitle($sitemapLanguageSef);
			}
			
			// Generate a proper model state for the metadata generation
			$aiGeneratorModel->setState('maxresults', 1);
			$aiGeneratorModel->setState('temperature', '0.5');
			$aiGeneratorModel->setState('removeimgs', 1);
			
			$maxTitleLength = $this->getComponentParams()->get('metainfo_auto_generate_metatitle_max_length', 55);
			$maxDescriptionLength = $this->getComponentParams()->get('metainfo_auto_generate_metadescription_max_length', 155);
			
			$metaDataLength = $dataObject->type == 'title' ? $maxTitleLength : $maxDescriptionLength;
			
			// Call ChatGPT to generated the requested metadata type
			$metadata = $aiGeneratorModel->generateOpenAIContentData(Text::sprintf('COM_JMAP_AIGENERATOR_CHATGPT_CHAT_PROMPT_GENERATE_METADATA', $dataObject->type, $sitemapLanguageTitle, $metaDataLength, $dataObject->linkurl), true);
			
			// Check for valid generation
			if(!$metadata) {
				throw new JMapException($aiGeneratorModel->getException(), 'error');
			}
			
			$response->result = true;
			$metadata = trim($metadata);
			$metadata = StringHelper::str_ireplace(PHP_EOL, '', $metadata);
			$metadata = trim($metadata, '"');
			$response->generated = trim($metadata, '"');
		} catch (JMapException $e) {
			$response->result = false;
			$response->exception_message = $e->getMessage();
			return $response;
		} catch (\Exception $e) {
			$jmapException = new JMapException(Text::sprintf('COM_JMAP_AIGENERATOR_METAINFO_GENERATED_ERROR', $e->getMessage()), 'error');
			$response->result = false;
			$response->exception_message = $jmapException->getMessage();
			return $response;
		}
		
		return $response;
	}

	/**
	 * Get heading override for a given url if any
	 *
	 * @access private
	 * @param Object $dataObject
	 * @param Object[] $additionalModels Array for additional injected models type hinted by interface
	 * @return Object
	 */
	private function fetchHeadingOverride($dataObject, $additionalModels = null) {
		// Response JSON object
		$response = new \stdClass();
	
		try {
			// Check if the link already exists in this table
			$selectQuery = 	"SELECT" . $this->dbInstance->quoteName($dataObject->headingtag) .
							"\n FROM " . $this->dbInstance->quoteName('#__jmap_headings') .
							"\n WHERE" .
							$this->dbInstance->quoteName('linkurl') . " = " . $this->dbInstance->quote($dataObject->linkurl);
			$response->headingtext = $this->dbInstance->setQuery ( $selectQuery )->loadResult();
	
			// All completed succesfully
			$response->result = true;
		} catch (JMapException $e) {
			$response->result = false;
			$response->exception_message = $e->getMessage();
			return $response;
		} catch (\Exception $e) {
			$jmapException = new JMapException(Text::sprintf('COM_JMAP_ERROR_RETRIEVING_DATA', $e->getMessage()), 'error');
			$response->result = false;
			$response->exception_message = $jmapException->getMessage();
			return $response;
		}
	
		return $response;
	}
	
	/**
	 * Store a heading override for a given url
	 *
	 * @access private
	 * @param Object $dataObject
	 * @param Object[] $additionalModels Array for additional injected models type hinted by interface
	 * @return Object
	 */
	private function saveHeading($dataObject, $additionalModels = null) {
		// Response JSON object
		$response = new \stdClass();
	
		try {
			// Check if the HTML support for the field is enabled
			if($this->getComponentParams()->get('seospider_override_headings_html', 0)) {
				$recoverRawData = json_decode($this->requestArray['data']);
				$dataObject->fieldValue = strip_tags($recoverRawData->param->fieldValue, '<p><div><span><a><section><article><img><video><ul><li><br>');
			}
			
			// Check if the link already exists in this table
			$selectQuery = 	"SELECT" . $this->dbInstance->quoteName('id') .
							"\n FROM " . $this->dbInstance->quoteName('#__jmap_headings') .
							"\n WHERE" .
							$this->dbInstance->quoteName('linkurl') . " = " . $this->dbInstance->quote($dataObject->linkurl);
			$headingExists = $this->dbInstance->setQuery ( $selectQuery )->loadResult();
	
			// If the link exists just update it, otherwise insert a new one
			if($headingExists) {
				// Update as NULL if no values
				$toUpdateValue = trim($dataObject->fieldValue);
				$toUpdateValue = $toUpdateValue ? $toUpdateValue : null;
				$query = "UPDATE" .
						 "\n " . $this->dbInstance->quoteName('#__jmap_headings') .
						 "\n SET " .
						 "\n " . $this->dbInstance->quoteName($dataObject->headingTag) . " = " . $this->dbInstance->quote($toUpdateValue) .
						 "\n WHERE " .
						 "\n " . $this->dbInstance->quoteName('linkurl') . " = " . $this->dbInstance->quote($dataObject->linkurl);
				$this->dbInstance->setQuery ( $query );
			} else {
				$query = "INSERT INTO" .
						 "\n " . $this->dbInstance->quoteName('#__jmap_headings') . "(" .
						 $this->dbInstance->quoteName('linkurl') . "," .
						 $this->dbInstance->quoteName($dataObject->headingTag) . ") VALUES (" .
						 $this->dbInstance->quote($dataObject->linkurl) . "," .
						 $this->dbInstance->quote($dataObject->fieldValue) . ")";
				$this->dbInstance->setQuery ( $query );
			}
			$this->dbInstance->execute ();
	
			// All completed succesfully
			$response->result = true;
		} catch (JMapException $e) {
			$response->result = false;
			$response->exception_message = $e->getMessage();
			return $response;
		} catch (\Exception $e) {
			$jmapException = new JMapException(Text::sprintf('COM_JMAP_ERROR_RETRIEVING_DATA', $e->getMessage()), 'error');
			$response->result = false;
			$response->exception_message = $jmapException->getMessage();
			return $response;
		}
	
		return $response;
	}
	
	/**
	 * Delete a heading override for a given url
	 *
	 * @access private
	 * @param Object $dataObject
	 * @param Object[] $additionalModels Array for additional injected models type hinted by interface
	 * @return Object
	 */
	private function deleteHeading($dataObject, $additionalModels = null) {
		// Response JSON object
		$response = new \stdClass();
	
		try {
			// Check if the link already exists in this table
			$selectQuery = 	"SELECT *" .
							"\n FROM " . $this->dbInstance->quoteName('#__jmap_headings') .
							"\n WHERE" .
							$this->dbInstance->quoteName('linkurl') . " = " . $this->dbInstance->quote($dataObject->linkurl);
			$headingExists = $this->dbInstance->setQuery ( $selectQuery )->loadObject();
	
			// If the link exists just update it, otherwise insert a new one
			if(is_object($headingExists)) {
				// Bitwise mask, delete the record only if it's the last heading rest
				$heading1 = $headingExists->h1 ? 1 : 0;
				$heading2 = $headingExists->h2 ? 2 : 0;
				$heading3 = $headingExists->h3 ? 4 : 0;
				if (($heading1 | $heading2 | $heading3) == $heading1 ||
					($heading1 | $heading2 | $heading3) == $heading2 ||
					($heading1 | $heading2 | $heading3) == $heading3) {
					$query = "DELETE" .
							 "\n FROM " . $this->dbInstance->quoteName('#__jmap_headings') .
							 "\n WHERE " .
							 "\n " . $this->dbInstance->quoteName('linkurl') . " = " . $this->dbInstance->quote($dataObject->linkurl);
					$this->dbInstance->setQuery ( $query );
				} else {
					$query = "UPDATE" .
							 "\n " . $this->dbInstance->quoteName('#__jmap_headings') .
							 "\n SET " .
							 "\n " . $this->dbInstance->quoteName($dataObject->headingTag) . " = NULL" .
							 "\n WHERE " .
							 "\n " . $this->dbInstance->quoteName('linkurl') . " = " . $this->dbInstance->quote($dataObject->linkurl);
					$this->dbInstance->setQuery ( $query );
				}
				$this->dbInstance->execute ();
			} else {
				$response->result = true;
				$response->exception_message = Text::_('COM_JMAP_NO_SEOSPIDER_SAVED');
				return $response;
			}
	
			// All completed succesfully
			$response->result = true;
		} catch (JMapException $e) {
			$response->result = false;
			$response->exception_message = $e->getMessage();
			return $response;
		} catch (\Exception $e) {
			$jmapException = new JMapException(Text::sprintf('COM_JMAP_ERROR_RETRIEVING_DATA', $e->getMessage()), 'error');
			$response->result = false;
			$response->exception_message = $jmapException->getMessage();
			return $response;
		}
	
		return $response;
	}
	
	/**
	 * Get canonical override for a given url if any
	 *
	 * @access private
	 * @param Object $dataObject
	 * @param Object[] $additionalModels Array for additional injected models type hinted by interface
	 * @return Object
	 */
	private function fetchCanonicalOverride($dataObject, $additionalModels = null) {
		// Response JSON object
		$response = new \stdClass();
	
		try {
			// Check if the link already exists in this table
			$selectQuery = 	"SELECT" . $this->dbInstance->quoteName('canonical') .
							"\n FROM " . $this->dbInstance->quoteName('#__jmap_canonicals') .
							"\n WHERE" .
			$this->dbInstance->quoteName('linkurl') . " = " . $this->dbInstance->quote($dataObject->linkurl);
			$response->canonicaltext = $this->dbInstance->setQuery ( $selectQuery )->loadResult();
	
			// All completed succesfully
			$response->result = true;
		} catch (JMapException $e) {
			$response->result = false;
			$response->exception_message = $e->getMessage();
			return $response;
		} catch (\Exception $e) {
			$jmapException = new JMapException(Text::sprintf('COM_JMAP_ERROR_RETRIEVING_DATA', $e->getMessage()), 'error');
			$response->result = false;
			$response->exception_message = $jmapException->getMessage();
			return $response;
		}
	
		return $response;
	}
	
	/**
	 * Store a heading override for a given url
	 *
	 * @access private
	 * @param Object $dataObject
	 * @param Object[] $additionalModels Array for additional injected models type hinted by interface
	 * @return Object
	 */
	private function saveCanonical($dataObject, $additionalModels = null) {
		// Response JSON object
		$response = new \stdClass();
	
		try {
			// Check if the link already exists in this table
			$selectQuery = 	"SELECT" . $this->dbInstance->quoteName('id') .
							"\n FROM " . $this->dbInstance->quoteName('#__jmap_canonicals') .
							"\n WHERE" .
							$this->dbInstance->quoteName('linkurl') . " = " . $this->dbInstance->quote($dataObject->linkurl);
			$headingExists = $this->dbInstance->setQuery ( $selectQuery )->loadResult();
	
			// If the link exists just update it, otherwise insert a new one
			if($headingExists) {
				// Update as NULL if no values
				$toUpdateValue = trim($dataObject->fieldValue);
				$toUpdateValue = $toUpdateValue ? $toUpdateValue : null;
				$query = "UPDATE" .
						 "\n " . $this->dbInstance->quoteName('#__jmap_canonicals') .
						 "\n SET " .
						 "\n " . $this->dbInstance->quoteName('canonical') . " = " . $this->dbInstance->quote($toUpdateValue) .
						 "\n WHERE " .
						 "\n " . $this->dbInstance->quoteName('linkurl') . " = " . $this->dbInstance->quote($dataObject->linkurl);
				$this->dbInstance->setQuery ( $query );
			} else {
				$query = "INSERT INTO" .
						 "\n " . $this->dbInstance->quoteName('#__jmap_canonicals') . "(" .
						 $this->dbInstance->quoteName('linkurl') . "," .
						 $this->dbInstance->quoteName('canonical') . ") VALUES (" .
						 $this->dbInstance->quote($dataObject->linkurl) . "," .
						 $this->dbInstance->quote($dataObject->fieldValue) . ")";
				$this->dbInstance->setQuery ( $query );
			}
			$this->dbInstance->execute ();
			
			// All completed succesfully
			$response->result = true;
		} catch (JMapException $e) {
			$response->result = false;
			$response->exception_message = $e->getMessage();
			return $response;
		} catch (\Exception $e) {
			$jmapException = new JMapException(Text::sprintf('COM_JMAP_ERROR_RETRIEVING_DATA', $e->getMessage()), 'error');
			$response->result = false;
			$response->exception_message = $jmapException->getMessage();
			return $response;
		}
	
		return $response;
	}
	
	/**
	 * Delete a canonical override for a given url
	 *
	 * @access private
	 * @param Object $dataObject
	 * @param Object[] $additionalModels Array for additional injected models type hinted by interface
	 * @return Object
	 */
	private function deleteCanonical($dataObject, $additionalModels = null) {
		// Response JSON object
		$response = new \stdClass();
	
		try {
			// Check if the link already exists in this table
			$selectQuery = 	"SELECT *" .
							"\n FROM " . $this->dbInstance->quoteName('#__jmap_canonicals') .
							"\n WHERE" .
							$this->dbInstance->quoteName('linkurl') . " = " . $this->dbInstance->quote($dataObject->linkurl);
			$canonicalExists = $this->dbInstance->setQuery ( $selectQuery )->loadObject();
	
			// If the link exists just update it, otherwise insert a new one
			if(is_object($canonicalExists)) {
				$query = "DELETE" .
						 "\n FROM " . $this->dbInstance->quoteName('#__jmap_canonicals') .
						 "\n WHERE " .
						 "\n " . $this->dbInstance->quoteName('linkurl') . " = " . $this->dbInstance->quote($dataObject->linkurl);
				$this->dbInstance->setQuery ( $query );
				$this->dbInstance->execute ();
			} else {
				$response->result = true;
				$response->exception_message = Text::_('COM_JMAP_NO_SEOSPIDER_SAVED');
				return $response;
			}
	
			// All completed succesfully
			$response->result = true;
		} catch (JMapException $e) {
			$response->result = false;
			$response->exception_message = $e->getMessage();
			return $response;
		} catch (\Exception $e) {
			$jmapException = new JMapException(Text::sprintf('COM_JMAP_ERROR_RETRIEVING_DATA', $e->getMessage()), 'error');
			$response->result = false;
			$response->exception_message = $jmapException->getMessage();
			return $response;
		}
	
		return $response;
	}
	
	/**
	 * Submit the sitemap to Baidu using XML-RPC
	 *
	 * @static
	 * @access private
	 * @param string $sitemapLink
	 * @param Object[] $additionalModels Array for additional injected models type hinted by interface
	 * @return Object
	 */
	private function submitSitemapToBaidu($sitemapLink, $additionalModels = null) {
		// Response JSON object
		$response = new \stdClass ();
		$cParams = $this->getComponentParams();
		$joomlaConfig = $this->app->getConfig();

		try {
			// Ensure CURL support
			if (! function_exists ( 'curl_init' )) {
				throw new JMapException ( Text::_ ( 'COM_JMAP_CURL_NOT_SUPPORTED' ), 'error' );
			}

			$httpTransport = new Socket ();
			$connectionAdapter = new Http ( $httpTransport, $cParams );
			$headers = array (
					'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
					'Accept-Encoding' => 'text/plain',
					'Accept-Language' => 'en-GB,en;q=0.9,it;q=0.8',
					'Cache-Control' => 'no-cache',
					'Connection' => 'keep-alive',
					'Host' => 'www.baidu.com',
					'Pragma' => 'no-cache',
					'Upgrade-Insecure-Requests' => '1',
					'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36'
			);
			$httpResponse = $connectionAdapter->get ( 'https://www.baidu.com/s?wd=' . urlencode(StringHelper::str_ireplace('wd=', '', $sitemapLink)), $headers );

			if (!$httpResponse) {
				throw new JMapException ( Text::_ ( 'COM_JMAP_XMLRPC_NORESPONSE' ), 'error' );
			}
			
			if ($httpResponse->code != 200) {
				throw new JMapException ( Text::sprintf ( 'COM_JMAP_XMLRPC_FAULTCODE', $httpResponse->code, $httpResponse->body), 'error' );
			}

			// All went well
			$response->result = true;
		} catch ( JMapException $e ) {
			$response->result = false;
			$response->exception_message = $e->getMessage ();
			$response->errorlevel = $e->getExceptionLevel();
			return $response;
		} catch ( \Exception $e ) {
			$jmapException = new JMapException ( Text::sprintf ( 'COM_JMAP_ERROR_PINGING_SITEMAP_TOBAIDU', $e->getMessage () ), 'error' );
			$response->result = false;
			$response->exception_message = $jmapException->getMessage ();
			$response->errorlevel = $jmapException->getExceptionLevel();
			return $response;
		}
	
		return $response;
	}

	/**
	 * Submit the sitemap to Bing using Indexnow
	 *
	 * @static
	 * @access private
	 * @param string $sitemapLink
	 * @param Object[] $additionalModels Array for additional injected models type hinted by interface
	 * @return Object
	 */
	private function submitSitemapToBing($sitemapLink, $additionalModels = null) {
		// Response JSON object
		$response = new \stdClass ();
		$cParams = $this->getComponentParams();
		$joomlaConfig = $this->app->getConfig();
		
		try {
			// Ensure CURL support
			if (! function_exists ( 'curl_init' )) {
				throw new JMapException ( Text::_ ( 'COM_JMAP_CURL_NOT_SUPPORTED' ), 'error' );
			}
			
			$httpTransport = new Curl ();
			$connectionAdapter = new Http ( $httpTransport, $cParams );
			$httpResponse = $connectionAdapter->get ( 'https://www.bing.com/indexnow?' . $sitemapLink );
			
			if ($httpResponse->code != 200 && $httpResponse->code != 202) {
				throw new JMapException ( Text::sprintf ( 'COM_JMAP_ERROR_PINGING_SITEMAP_TOBING', ($httpResponse->code . ' - ' . $httpResponse->body)), 'error' );
			}
			
			// All went well
			$response->result = true;
		} catch ( JMapException $e ) {
			$response->result = false;
			$response->exception_message = $e->getMessage ();
			$response->errorlevel = $e->getExceptionLevel();
			return $response;
		} catch ( \Exception $e ) {
			$jmapException = new JMapException ( Text::sprintf ( 'COM_JMAP_ERROR_PINGING_SITEMAP_TOBING', $e->getMessage () ), 'error' );
			$response->result = false;
			$response->exception_message = $jmapException->getMessage ();
			$response->errorlevel = $jmapException->getExceptionLevel();
			return $response;
		}
		
		return $response;
	}

	/**
	 * Submit the sitemap to Google using unauthenticated endpoint
	 *
	 * @static
	 * @access private
	 * @param string $sitemapLink
	 * @param Object[] $additionalModels Array for additional injected models type hinted by interface
	 * @return Object
	 */
	private function submitSitemapToGoogle($sitemapLink, $additionalModels = null) {
		// Response JSON object
		$response = new \stdClass ();
		$cParams = $this->getComponentParams();
		$joomlaConfig = $this->app->getConfig();
		
		try {
			// Ensure CURL support
			if (! function_exists ( 'curl_init' )) {
				throw new JMapException ( Text::_ ( 'COM_JMAP_CURL_NOT_SUPPORTED' ), 'error' );
			}
			
			$httpTransport = new Curl ();
			$connectionAdapter = new Http ( $httpTransport, $cParams );
			$headers = array (
					'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
					'Accept-Encoding' => 'text/plain',
					'Accept-Language' => 'en,it;q=0.9,en-US;q=0.8,de;q=0.7,es;q=0.6,fr;q=0.5,ru;q=0.4,ja;q=0.3,el;q=0.2,sk;q=0.1,nl;q=0.1,ar;q=0.1,sv;q=0.1,da;q=0.1',
					'Cache-Control' => 'no-cache',
					'Connection' => 'keep-alive',
					'Host' => 'www.google.com',
					'Pragma' => 'no-cache',
					'Upgrade-Insecure-Requests' => '1',
					'User-Agent' => 'Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.4664.93 Safari/537.36'
			);
			$httpResponse = $connectionAdapter->get ( 'https://www.google.com/search?q=' . urlencode($sitemapLink), $headers );
			if ($httpResponse->code != 200) {
				$errorArray = explode ( '<p>', $httpResponse->body );
				if (isset ( $errorArray [1] ) && isset ( $errorArray [2] )) {
					throw new JMapException ( Text::sprintf ( 'COM_JMAP_ERROR_PINGING_SITEMAP_TOGOOGLE', ($httpResponse->code . ' - ' . $errorArray [1] . $errorArray [2]) ), 'error' );
				} else {
					throw new JMapException ( Text::sprintf ( 'COM_JMAP_ERROR_PINGING_SITEMAP_TOGOOGLE', ($httpResponse->code) ), 'error' );
				}
			}
			
			// All went well
			$response->result = true;
		} catch ( JMapException $e ) {
			$response->result = false;
			$response->exception_message = $e->getMessage ();
			$response->errorlevel = $e->getExceptionLevel();
			return $response;
		} catch ( \Exception $e ) {
			$jmapException = new JMapException ( Text::sprintf ( 'COM_JMAP_ERROR_PINGING_SITEMAP_TOGOOGLE', $e->getMessage () ), 'error' );
			$response->result = false;
			$response->exception_message = $jmapException->getMessage ();
			$response->errorlevel = $jmapException->getExceptionLevel();
			return $response;
		}
		
		return $response;
	}
	
	/**
	 * Store a heading override for a given url
	 *
	 * @access private
	 * @param Object $dataObject
	 * @param Object[] $additionalModels Array for additional injected models type hinted by interface
	 * @return Object
	 */
	private function aigeneratorContentCreate($dataObject, $additionalModels = null) {
		// Response JSON object
		$response = new \stdClass();
		$params = json_decode($this->app->getInput()->getRaw('data', null));
		
		try {
			// Create a new article
			if($dataObject->type == 'article') {
				$contentCategory = $this->getComponentParams()->get('aigenerator_default_category', '');
				if(!$contentCategory) {
					// Select the first category as default one
					$query = method_exists ( $this->dbInstance, 'createQuery' ) ? $this->dbInstance->createQuery () : $this->dbInstance->getQuery ( true );
					
					$query->select('id');
					$query->from('#__categories');
					$query->where($this->dbInstance->quoteName('extension') . ' = ' . $this->dbInstance->quote('com_content'));
					$query->where('published = 1');
					$query->order('id', 'ASC');
					$this->dbInstance->setQuery($query);
					$contentCategory = $this->dbInstance->loadResult();
				}
				
				$data = [];
				$data['title'] = $dataObject->title;
				$data['alias'] = '';
				$data['articletext'] = $params->param->body;
				$data['language'] = $dataObject->language;
				$data['catid'] = $contentCategory;
				$data['state'] = 0;
				
				$articleModel = new ArticleModel();
				$articleSaved = $articleModel->save($data);
				$lastInsertId = $articleModel->getState('article.id');
				
				if($articleSaved) {
					// All completed succesfully
					$response->content_id = $lastInsertId;
					$response->result = $articleSaved;
				} else {
					$response->result = false;
					$response->exception_message = Text::_('COM_JMAP_ERROR_CREATING_ARTICLE');
				}
			} elseif($dataObject->type == 'module') {
				$modulePosition = $this->getComponentParams()->get('aigenerator_default_module_position', 'sidebar-right');
				
				$data = [];
				$data['title'] = $dataObject->title;
				$data['module'] = 'mod_custom';
				$data['content'] = $params->param->body;
				$data['published'] = 0;
				$data['client_id'] = 0;
				$data['position'] = $modulePosition;
				$data['language'] = $dataObject->language;
				$data['params'] = '';
				
				// Check firstly if a module with this title already exists
				$truncatedTitle = StringHelper::substr($dataObject->title, 0, 100);
				$query = method_exists ( $this->dbInstance, 'createQuery' ) ? $this->dbInstance->createQuery () : $this->dbInstance->getQuery ( true );
				
				$query->select('id');
				$query->from('#__modules');
				$query->where($this->dbInstance->quoteName('title') . ' = ' . $this->dbInstance->quote($truncatedTitle));
				$query->where('published >= 0');
				$query->order('id', 'ASC');
				$this->dbInstance->setQuery($query);
				$moduleId = $this->dbInstance->loadResult();
				if($moduleId) {
					$response->result = false;
					$response->exception_message = Text::_('COM_JMAP_ERROR_CREATING_MODULE');
					return $response;
				}
				
				$moduleModel = new ModuleModel();
				$moduleSaved = $moduleModel->save($data);
				$lastInsertId = $moduleModel->getState('module.id');
				
				if($moduleSaved) {
					// All completed succesfully
					$response->content_id = $lastInsertId;
					$response->result = $moduleSaved;
				} else {
					$response->result = false;
					$response->exception_message = Text::_('COM_JMAP_ERROR_CREATING_MODULE');
				}
			}
		} catch (JMapException $e) {
			$response->result = false;
			$response->exception_message = $e->getMessage();
			return $response;
		} catch (\Exception $e) {
			$jmapException = new JMapException(Text::sprintf('COM_JMAP_ERROR_RETRIEVING_DATA', $e->getMessage()), 'error');
			$response->result = false;
			$response->exception_message = $jmapException->getMessage();
			return $response;
		}
		
		return $response;
	}

	/**
	 * Ping by proxy external services
	 *
	 * @access private
	 * @param Object $dataObject
	 * @param Object[] $additionalModels Array for additional injected models type hinted by interface
	 * @return Object
	 */
	private function pingomaticServicesProxy($dataObject, $additionalModels = null) {
		// Response JSON object
		$response = new \stdClass ();
		$cParams = $this->getComponentParams();
		$joomlaConfig = $this->app->getConfig();
		
		try {
			// Ensure CURL support
			if (! function_exists ( 'curl_init' )) {
				throw new JMapException ( Text::_ ( 'COM_JMAP_CURL_NOT_SUPPORTED' ), 'error' );
			}

			// Exception for Ask.com to remove sef suffix if any
			if(StringHelper::strpos($dataObject->serviceurl, 'ask.com')) {
				$dataObject->serviceurl = StringHelper::str_ireplace('.html', '',  $dataObject->serviceurl);
			}
			
			$httpTransport = new Curl ();
			$connectionAdapter = new Http ( $httpTransport, $cParams );
			$httpResponse = $connectionAdapter->get ( $dataObject->serviceurl );
			
			if (!$httpResponse) {
				throw new JMapException ( Text::_ ( 'COM_JMAP_XMLRPC_NORESPONSE' ), 'error' );
			}
			
			if ($httpResponse->code != 200) {
				throw new JMapException ( Text::sprintf ( 'COM_JMAP_XMLRPC_FAULTCODE', $httpResponse->code, $httpResponse->body), 'error' );
			}
			
			// All went well
			$response->result = true;
			$response->service = $dataObject->serviceurl;
		} catch ( JMapException $e ) {
			$response->result = false;
			$response->service = $dataObject->serviceurl;
			$response->exception_message = $e->getMessage ();
			$response->errorlevel = $e->getExceptionLevel();
			return $response;
		} catch ( \Exception $e ) {
			$jmapException = new JMapException ( Text::sprintf ( 'COM_JMAP_XMLRPC_NORESPONSE', $e->getMessage () ), 'error' );
			$response->result = false;
			$response->service = $dataObject->serviceurl;
			$response->exception_message = $jmapException->getMessage ();
			$response->errorlevel = $jmapException->getExceptionLevel();
			return $response;
		}
		
		return $response;
	}
	
	/**
	 * Get license informations about this user subscription license email code
	 * Use the RESTFul interface API on the remote License resource
	 *
	 * @static
	 * @access private
	 * @param Object[] $additionalModels Array for additional injected models type hinted by interface
	 * @return Object
	 */
	private function getLicenseStatus($additionalModels = null) {
		// Get email license code
		$code = ComponentHelper::getParams('com_jmap')->get('registration_email', null);
	
		// Instantiate HTTP client
		$HTTPClient = new Http();
	
		/*
		 * Status domain code
		* Remote API Call
		*/
		$headers = array('Accept'=>'application/json', 'User-agent' => 'JSitemap Pro updater');
		if($code) {
			try {
				$prodCode = 'jsitemappro';
				$cdFuncUsed = 'str_' . 'ro' . 't' . '13';
				$HTTPResponse = $HTTPClient->get($cdFuncUsed('uggc' . '://' . 'fgberwrkgrafvbaf' . '.bet') . "/option,com_easycommerce/action,licenseCode/email,$code/productcode,$prodCode", $headers);
			} catch (\Exception $e) {
				$HTTPResponse = new \stdClass();
				$HTTPResponse->body = '{"success":false,"reason":"connection_error","details":"' . $e->getMessage() . '"}';
			}
		} else {
			$HTTPResponse = new \stdClass();
			$HTTPResponse->body = '{"success":false,"reason":"nocode_inserted"}';
		}
			
		// Deserializing della response
		try {
			$objectHTTPResponse = json_decode($HTTPResponse->body);
			if(!is_object($objectHTTPResponse)) {
				throw new \Exception('decoding_error');
			}
		} catch (\Exception $e) {
			$HTTPResponse = new \stdClass();
			$HTTPResponse->body = '{"success":false,"reason":"' . $e->getMessage() . '"}';
			$objectHTTPResponse = json_decode($HTTPResponse->body);
		}
	
		return $objectHTTPResponse;
	}
	
	/**
	 * Perform the asyncronous update of the component
	 * 1- Dowload the remote update package file
	 * 2- Use the Joomla installer to install it
	 * 3- Return status to the js app
	 *
	 * @static
	 * @access private
	 * @param Object[] $additionalModels Array for additional injected models type hinted by interface
	 * @return Object
	 */
	private function downloadComponentUpdate($additionalModels = null) {
		// Response JSON object
		$response = new \stdClass ();
		$cdFuncUsed = 'str_' . 'ro' . 't' . '13';
		$ep = $cdFuncUsed('uggc' . '://' . 'fgberwrkgrafvbaf' . '.bet' . '/XZY1406TSPQnifs3243560923kfuxnj35td1rtt45664f.ugzy');
		$file_path = $this->app->getConfig()->get('tmp_path', '/tmp') . '/KML1406GFCDavsf3243560923xshkaw35gq1egg45664s.zip';

		try {
			// Ensure CURL support
			if (! function_exists ( 'curl_init' )) {
				throw new JMapException ( Text::_ ( 'COM_JMAP_CURL_NOT_SUPPORTED' ), 'error' );
			}

			// Firstly test if the server is up and HTTP 200 OK
			$ch = curl_init($ep);
			curl_setopt( $ch, CURLOPT_NOBODY, true );
			curl_setopt( $ch, CURLOPT_HEADER, false );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, false );
			curl_setopt( $ch, CURLOPT_MAXREDIRS, 3 );
			curl_exec( $ch );

			$headerInfo = curl_getinfo( $ch );
			curl_close( $ch );
			if($headerInfo['http_code'] != 200 || !$headerInfo['download_content_length']) {
				throw new JMapException ( Text::_ ( 'COM_JMAP_ERROR_DOWNLOADING_REMOTE_FILE' ), 'error' );
			}

			// 1- Download the remote update package file and put in local file
			$fp = fopen ($file_path, 'w+');
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, $ep );
			curl_setopt( $ch, CURLOPT_BINARYTRANSFER, true );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, false );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 60 );
			curl_setopt( $ch, CURLOPT_FILE, $fp );
			curl_exec( $ch );
			curl_close( $ch );
			fclose( $fp );

			if (!filesize($file_path)) {
				throw new JMapException ( Text::_ ( 'COM_JMAP_ERROR_WRITING_LOCAL_FILE' ), 'error' );
			}

			// All went well
			$response->result = true;
		} catch ( JMapException $e ) {
			$response->result = false;
			$response->exception_message = $e->getMessage ();
			$response->errorlevel = $e->getExceptionLevel();
			return $response;
		} catch ( \Exception $e ) {
			$jmapException = new JMapException ( Text::sprintf ( 'COM_JMAP_ERROR_UPDATING_COMPONENT', $e->getMessage () ), 'error' );
			$response->result = false;
			$response->exception_message = $jmapException->getMessage ();
			$response->errorlevel = $jmapException->getExceptionLevel();
			return $response;
		}

		return $response;
	}
	
	/**
	 * Perform the asyncronous update of the component
	 * 1- Dowload the remote update package file
	 * 2- Use the Joomla installer to install it
	 * 3- Return status to the js app
	 *
	 * @static
	 * @access private
	 * @param Object[] $additionalModels Array for additional injected models type hinted by interface
	 * @return Object
	 */
	private function installComponentUpdate($additionalModels = null) {
		// Response JSON object
		$response = new \stdClass ();
		$file_path = $this->app->getConfig()->get('tmp_path', '/tmp') . '/KML1406GFCDavsf3243560923xshkaw35gq1egg45664s.zip';
	
		try {
			// Unpack the downloaded package file.
			$package = InstallerHelper::unpack($file_path, true);
			if(!$package) {
				throw new JMapException ( Text::_ ( 'COM_JMAP_ERROR_EXTRACTING_UPDATES' ), 'error' );
			}

			// 2- Use the Joomla installer to install it
			// New plugin installer
			$updateInstaller = new Installer ();
			if (! $updateInstaller->install ( $package['extractdir'] )) {
				throw new JMapException ( Text::_ ( 'COM_JMAP_ERROR_INSTALLING_UPDATES' ), 'error' );
			}

			// Delete dirty files and folder
			unlink($file_path);
			$it = new \RecursiveDirectoryIterator($package['extractdir'], \RecursiveDirectoryIterator::SKIP_DOTS);
			$files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);
			foreach($files as $file) {
				if ($file->isDir()){
					@rmdir($file->getRealPath());
				} else {
					unlink($file->getRealPath());
				}
			}
			// Delete the now empty folder
			@rmdir($package['extractdir']);

			// All went well
			$response->result = true;
		} catch ( JMapException $e ) {
			$response->result = false;
			$response->exception_message = $e->getMessage ();
			$response->errorlevel = $e->getExceptionLevel();
			return $response;
		} catch ( \Exception $e ) {
			$jmapException = new JMapException ( Text::sprintf ( 'COM_JMAP_ERROR_UPDATING_COMPONENT', $e->getMessage () ), 'error' );
			$response->result = false;
			$response->exception_message = $jmapException->getMessage ();
			$response->errorlevel = $jmapException->getExceptionLevel();
			return $response;
		}
	
		return $response;
	}
	
	/**
	 * Mimic an entities list, as ajax calls arrive are redirected to loadEntity public responsibility to get handled
	 * by specific subtask. Responses are returned to controller and encoded from view over HTTP to JS client
	 * 
	 * @access public 
	 * @param string $id Rappresenta l'op da eseguire tra le private properties
	 * @param mixed $param Parametri da passare al private handler
	 * @param Object[]& $DIModels
	 * @return Object& $utenteSelezionato
	 */
	public function loadAjaxEntity($id, $param, $DIModels) {
		//Delega la private functions delegata dalla richiesta sulla entity
		$response = $this->$id($param, $DIModels);

		return $response;
	}
}