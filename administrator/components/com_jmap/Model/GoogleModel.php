<?php
namespace JExtstore\Component\JMap\Administrator\Model;
/**
 *
 * @package JMAP::GOOGLE::administrator::components::com_jmap
 * @subpackage models
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\String\StringHelper;
use JExtstore\Component\JMap\Administrator\Framework\Model as JMapModel;
use JExtstore\Component\JMap\Administrator\Framework\Http;
use JExtstore\Component\JMap\Administrator\Framework\Http\Transport\Socket;
use JExtstore\Component\JMap\Administrator\Framework\Http\Transport\Curl;
use JExtstore\Component\JMap\Administrator\Framework\Http\Response;
use JExtstore\Component\JMap\Administrator\Framework\Exception as JMapException;
use JExtstore\Component\JMap\Administrator\Framework\Language\Multilang;

/**
 * Google model responsibilities for access Google Analytics and Webmasters Tools API
 *
 * @package JMAP::SOURCES::administrator::components::com_jmap
 * @subpackage models
 * @since 3.1
 */
interface IModelGoogle {
	/**
	 * Main get data method
	 *
	 * @access public
	 * @return mixed Returns a data string if success or boolean if exceptions are trigged
	 */
	public function getDataAnalytics();
	
	/**
	 * Main get data method
	 *
	 * @access public
	 * @return mixed Returns a data string if success or boolean if exceptions are trigged
	 */
	public function getDataReporting();
	
	/**
	 * Main get data method
	 *
	 * @access public
	 * @return mixed Returns a data string if success or boolean if exceptions are trigged
	 */
	public function getDataData();
	
	/**
	 * Get data method for webmasters tools stats
	 *
	 * @access public
	 * @return mixed Returns a data string if success or boolean if exceptions are trigged
	 */
	public function getDataWebmasters();

	/**
	 * Get data method for free Statscrop stats by scraping even manipulating them for anonymization
	 *
	 * @access public
	 * @return mixed string
	 */
	public function getDataStatscrop();
	
	/**
	 * Get data method for free HypeStat stats by scraping even manipulating them for anonymization
	 *
	 * @access public
	 * @return mixed string
	 */
	public function getDataHypeStat();
	
	/**
	 * Get data method for free SearchMetrics stats by scraping even manipulating them for anonymization
	 * Additionally this method must use caching to prevent captcha locking
	 *
	 * @access public
	 * @return mixed string
	 */
	public function getDataSearchMetrics();
	
	/**
	 * Get data method for Google PageSpeed API without the need of OAuth authentication, the APIKEY is enough
	 *
	 * @access public
	 * @return mixed array
	 * @throws RuntimeException
	 */
	public function getDataPageSpeed();
	
	/**
	 * Return the google token
	 *
	 * @access public
	 * @return string
	 */
	public function getToken();
	
	/**
	 * Submit a sitemap link using the GWT API
	 *
	 * @access public
	 * @param string $sitemapUri
	 * @return boolean
	 */
	public function submitSitemap($sitemapUri);
	
	/**
	 * Delete a sitemap link using the GWT API
	 *
	 * @access public
	 * @param string $sitemapUri
	 * @return boolean
	 */
	public function deleteSitemap($sitemapUri);
}

/**
 * Sources model concrete implementation <<testable_behavior>>
 *
 * @package JMAP::GOOGLE::administrator::components::com_jmap
 * @subpackage models
 * @since 3.1
 */
class GoogleModel extends JMapModel implements IModelGoogle {
	/**
	 * Google_Client object
	 *
	 * @access private
	 * @var Google_Client
	 */
	private $client;
	
	/**
	 * Current profile found for Google Analytics
	 *
	 * @access private
	 * @var string
	 */
	private $currentProfile;
	
	/**
	 * Track the API connection mode, built in JSitemap Google App or user own
	 *
	 * @access private
	 * @var string
	 */
	private $hasOwnCredentials;
	
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
	 * Get the title string based on the gaquery selection
	 *
	 * @access private
	 * @param string $gaperiod
	 * @return string
	 */
	private function getTitle($gaquery) {
		switch ($gaquery) {
			case 'totalUsers':
			case 'users' :
				$title = Text::_ ( 'COM_JMAP_GOOGLE_VISITORS' );
				break;

			case 'screenPageViews':
			case 'pageviews' :
				$title = Text::_ ( 'COM_JMAP_GOOGLE_PAGE_VIEWS' );
				break;
				
			case 'engagementRate':
				$title = Text::_ ( 'COM_JMAP_GOOGLE_METRIC_ENGAGEMENTRATE' );
				break;

			case 'sessionsPerUser':
				$title = Text::_ ( 'COM_JMAP_GOOGLE_METRIC_SESSIONSPERUSER' );
				break;
				
			case 'bounceRate' :
				$title = Text::_ ( 'COM_JMAP_GOOGLE_BOUNCE_RATE' );
				break;
				
			case 'organicSearches' :
				$title = Text::_ ( 'COM_JMAP_GOOGLE_ORGANIC_SEARCHES' );
				break;
				
			default :
				$title = Text::_ ( 'COM_JMAP_GOOGLE_VISITS' );
		}
		
		return $title;
	}
	
	/**
	 * Get the period array based on the gaperiod selection
	 *
	 * @access private
	 * @param string $gaperiod
	 * @param boolean $isDataApi
	 * @return array
	 */
	private function getPeriod($gaperiod, $isDataApi = false) {
		$periodArray = array();
		
		switch ($gaperiod) {
			case 'today' :
				if($isDataApi) {
					$periodArray = array("from" => date ( 'Y-m-d', time () - 24 * 60 * 60 ),
					"to" => date ( 'Y-m-d' ),
					"showevery" => 1);
				} else {
					$periodArray = array("from" => date ( 'Y-m-d' ),
					"to" => date ( 'Y-m-d' ),
					"showevery" => 2);
				}
				break;
				
			case 'yesterday' :
				if($isDataApi) {
					$periodArray = array("from" => date ( 'Y-m-d', time () - 48 * 60 * 60 ),
					"to" => date ( 'Y-m-d', time () - 24 * 60 * 60 ),
					"showevery" => 1);
				} else {
					$periodArray = array("from" => date ( 'Y-m-d', time () - 24 * 60 * 60 ),
					"to" => date ( 'Y-m-d', time () - 24 * 60 * 60 ),
					"showevery" => 2);
				}
				break;
				
			case 'last7days' :
				$periodArray = array("from" => date ( 'Y-m-d', time () - 7 * 24 * 60 * 60 ),
				"to" => date ( 'Y-m-d' ),
				"showevery" => 1);
				break;
				
			case 'last14days' :
				$periodArray = array("from" => date ( 'Y-m-d', time () - 14 * 24 * 60 * 60 ),
				"to" => date ( 'Y-m-d' ),
				"showevery" => 2);
				break;
				
			case 'last3months' :
				$periodArray = array("from" => date ( 'Y-m-d', time () - 90 * 24 * 60 * 60 ),
				"to" => date ( 'Y-m-d' ),
				"showevery" => 7);
				break;
				
			case 'last6months' :
				$periodArray = array("from" => date ( 'Y-m-d', time () - 180 * 24 * 60 * 60 ),
				"to" => date ( 'Y-m-d' ),
				"showevery" => 15);
				break;
				
			case 'last12months' :
				$periodArray = array("from" => date ( 'Y-m-d', time () - 365 * 24 * 60 * 60 ),
				"to" => date ( 'Y-m-d' ),
				"showevery" => 30);
				break;
				
			case 'last30days' :
			default :
				$periodArray = array("from" => date ( 'Y-m-d', time () - 30 * 24 * 60 * 60 ),
				"to" => date ( 'Y-m-d' ),
				"showevery" => 3);
				break;
		}
		
		return $periodArray;
	}
	
	/**
	 * Purify and normalize domain protocol
	 *
	 * @access private
	 * @return string
	 */
	private function purifyDomain($domain) {
		if($this->getComponentParams ()->get('ga_domain_match_protocol', 0)) {
			return $domain;
		}
		return str_replace ( array (
				"https://",
				"http://",
				" "
		), "", rtrim ( $domain, "/" ) );
	}
	
	/**
	 * Purify and normalize domain uri for webmasters tools stats
	 *
	 * @access private
	 * @return string
	 */
	private function purifyWebmastersDomain($domain) {
		return str_replace ( array (
				" "
		), "", rtrim ( $domain, "/" ) );
	}
	
	/**
	 * Manage the authentication form and action
	 *
	 * @param Object $params
	 * @access private
	 * @return mixed A string when auth is needed, null if performing an auth
	 */
	private function authentication($params) {
		$this->client = new \Google_Client ();
		$this->client->setAccessType ( 'offline' );
		$this->client->setScopes ( array( 'https://www.googleapis.com/auth/analytics.readonly', 'https://www.googleapis.com/auth/webmasters' ));
		$this->client->setApplicationName ( 'JSitemap Professional' );
		$this->client->setRedirectUri ( 'https://storejextensions.org/oauthauthentication.html' );
		$this->client->setPrompt("consent");
	
		$this->hasOwnCredentials = false;
		if ($params->get ( 'ga_api_key' ) and $params->get ( 'ga_client_id' ) and $params->get ( 'ga_client_secret' )) {
			$this->client->setClientId ( $params->get ( 'ga_client_id' ) );
			$this->client->setClientSecret ( $params->get ( 'ga_client_secret' ) );
			$this->client->setDeveloperKey ( $params->get ( 'ga_api_key' ) ); // API key
			$this->hasOwnCredentials = true;
		} else {
			$this->client->setClientId ( '1229958023-ihvto9hv6ehqt759kg10ejfi6saaus82.apps.googleusercontent.com' );
			$this->client->setClientSecret ( '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' );
			$this->client->setDeveloperKey ( 'AIzaSyBOXBjtrtYPTQmpupLwY5AhKmazQqVQPzw' );
		}
	
		try {
			if ($this->getToken ()) { // extract token from session and configure client
				$token = $this->getToken ();
				$this->client->setAccessToken ( $token );
			}
		} catch ( \Exception $e ) {
			$jmapException = new JMapException ( $e->getMessage (), 'error' );
			$this->app->enqueueMessage ( $jmapException->getMessage (), $jmapException->getExceptionLevel () );
			return '<a class="btn btn-primary" href="index.php?option=com_jmap&amp;task=google.deleteEntity">' . Text::_ ( 'COM_JMAP_GOOGLE_LOGOUT' ) . '</a>';
		}

		if (! $result = $this->client->getAccessToken ()) { // auth call to google
			$authUrl = $this->client->createAuthUrl ();
			// Trying to authenticate?
			if (!$this->app->getInput()->get('ga_dash_authorize')) {
				$JoomlaCMSLanguageText = '\Joomla\CMS\Language\Text';
				$htmlSnippet = <<<HTML
									<div class="google_login">
										<div class="card w-50 p-3">
											<div class="card-block">
												<span class="badge bg-primary">
													{$JoomlaCMSLanguageText::_ ( 'COM_JMAP_GOOGLE_STEP1_CODE_DESC' )}
												</span>
						  						<a class="btn btn-success btn-sm hasPopover google" role="button" data-bs-content="{$JoomlaCMSLanguageText::_ ( 'COM_JMAP_GOOGLE_CODE_INSTUCTIONS' )}" href="$authUrl" target="_blank">
						  							{$JoomlaCMSLanguageText::_ ( 'COM_JMAP_GOOGLE_CODE' )}
						  						</a>
					  						</div>
				  						</div>
				
				  						<div class="card w-50 p-3">
				  							<div class="card-block">
					  							<span class="badge bg-primary">
					  								{$JoomlaCMSLanguageText::_ ( 'COM_JMAP_GOOGLE_STEP2_ACCESS_CODE_INSERT' )}
					  							</span>
					  							<input type="text" name="ga_dash_code" value="" size="61">
				  							</div>
				  						</div>
				
				  						<div class="card w-50 p-3">
				  							<div class="card-block">
					  							<span class="badge bg-primary">
					  								{$JoomlaCMSLanguageText::_ ( 'COM_JMAP_GOOGLE_STEP3_AUTHENTICATE' )}
					  							</span>
												<input type="submit" class="btn btn-success btn-sm waiter" role="button" name="ga_dash_authorize" value="{$JoomlaCMSLanguageText::_ ( 'COM_JMAP_GOOGLE_AUTHENTICATE' )}"/>
											</div>
										</div>
									</div>
				
				HTML;
					return $htmlSnippet;
				} else {
				// Yes! This is an authentication attempt let's try it
				try {
					$this->client->authenticate ( $this->app->getInput()->getString('ga_dash_code'));
  				} catch ( JMapException $e ) {
					$this->app->enqueueMessage ( $e->getMessage (), $e->getExceptionLevel () );
					return '<a class="btn btn-primary" href="index.php?option=com_jmap&amp;task=google.display">' . Text::_ ( 'COM_JMAP_GOBACK' ) . '</a>';
				} catch ( \Exception $e ) {
					$jmapException = new JMapException ( $e->getMessage (), 'error' );
					$this->app->enqueueMessage ( $jmapException->getMessage (), $jmapException->getExceptionLevel () );
					return '<a class="btn btn-primary" href="index.php?option=com_jmap&amp;task=google.display">' . Text::_ ( 'COM_JMAP_GOBACK' ) . '</a>';
				}

  				// Store the Google token in the DB for further login and authentication
				$this->storeToken ( $this->client->getAccessToken () );

				return null;
			}
		}
	}

	/**
	 * Store the Google token
	 *
	 * @access private
	 * @return boolean
	 */
	private function storeToken($token) {
		// Ensure that a token is present
		if(!$token) {
			return false;
		}
		
		// Ensure that only 1 record per each client exists in the database
		if($this->getToken()) {
			$this->deleteToken();
		}
		
		$clientID = (int)$this->app->getClientId();
		try {
			$dbToken = is_array($token) ? json_encode($token) : $token;
			$query = "INSERT IGNORE INTO #__jmap_google (id, token) VALUES ($clientID," .  $this->dbInstance->quote($dbToken) . ")";
			$this->dbInstance->setQuery ( $query );
			$result = $this->dbInstance->execute ();
			
			// Store logged in status in session
			$session = $this->app->getSession();
			$session->set('jmap_ga_authenticate', true);
		} catch ( JMapException $e ) {
			$this->app->enqueueMessage ( $e->getMessage (), $e->getExceptionLevel () );
			$result = false;
		} catch ( \Exception $e ) {
			$jmapException = new JMapException ( $e->getMessage (), 'error' );
			$this->app->enqueueMessage ( $jmapException->getMessage (), $jmapException->getExceptionLevel () );
			$result = false;
		}
		
		return $result;
	}
	
	/**
	 * Delete the Google token
	 *
	 * @access private
	 * @return boolean
	 */
	private function deleteToken() {
		$clientID = (int)$this->app->getClientId();
		try {
			$query = "DELETE FROM #__jmap_google WHERE id = " . $clientID;
			$this->dbInstance->setQuery ( $query )->execute();
			
			// Store logged in status in session
			$session = $this->app->getSession();
			$session->remove('jmap_ga_authenticate');
		} catch ( JMapException $e ) {
			$this->app->enqueueMessage ( $e->getMessage (), $e->getExceptionLevel () );
			return false;
		} catch ( \Exception $e ) {
			$jmapException = new JMapException ( $e->getMessage (), 'error' );
			$this->app->enqueueMessage ( $jmapException->getMessage (), $jmapException->getExceptionLevel () );
			return false;
		}
		
		return true;
	}
	
	/**
	 * Get visits by country
	 *
	 * @access private
	 * @return array
	 */
	private function getAnalyticsVisitsByCountry($service, $projectId, $from, $to, $params) {
		$metrics = 'ga:sessions';
		$dimensions = 'ga:country';
		try {
			$data = $service->data_ga->get ( 'ga:' . $projectId, $from, $to, $metrics, array (
					'dimensions' => $dimensions
			) );
		} catch ( \Exception $e ) {
			if($params->get('enable_debug', 0)) {
				$this->app->enqueueMessage($e->getMessage(), 'error');
			}
			return false;
		}
		if (! $data ['rows']) {
			return false;
		}
		
		$ga_dash_data = "";
		for($i = 0; $i < $data ['totalResults']; $i ++) {
			$ga_dash_data .= "['" . addslashes ( $data ['rows'] [$i] [0] ) . "'," . $data ['rows'] [$i] [1] . "],";
		}
		
		return $ga_dash_data;
	}
	
	/**
	 * Get Traffic Sources
	 *
	 * @access private
	 * @return array
	 */
	private function getAnalyticsTrafficSources($service, $projectId, $from, $to, $params) {
		$metrics = 'ga:sessions';
		$dimensions = 'ga:medium';
		try {
			$data = $service->data_ga->get ( 'ga:' . $projectId, $from, $to, $metrics, array (
					'dimensions' => $dimensions
			) );
		} catch ( \Exception $e ) {
			if($params->get('enable_debug', 0)) {
				$this->app->enqueueMessage($e->getMessage(), 'error');
			}
			return false;
		}
		if (! $data ['rows']) {
			return false;
		}
		
		$ga_dash_data = "";
		for($i = 0; $i < $data ['totalResults']; $i ++) {
			$ga_dash_data .= "['" . addslashes ( str_replace ( "(none)", "direct", $data ['rows'] [$i] [0] ) ) . "'," . $data ['rows'] [$i] [1] . "],";
		}
		
		return $ga_dash_data;
	}
	
	/**
	 * Get New vs. Returning
	 *
	 * @access private
	 * @return array
	 */
	private function getAnalyticsNewReturnVisitors($service, $projectId, $from, $to, $params) {
		$metrics = 'ga:sessions';
		$dimensions = 'ga:userType';
		try {
			$data = $service->data_ga->get ( 'ga:' . $projectId, $from, $to, $metrics, array (
					'dimensions' => $dimensions
			) );
		} catch ( \Exception $e ) {
			if($params->get('enable_debug', 0)) {
				$this->app->enqueueMessage($e->getMessage(), 'error');
			}
			return false;
		}
		
		if (! $data ['rows']) {
			return false;
		}
		
		$ga_dash_data = "";
		for($i = 0; $i < $data ['totalResults']; $i ++) {
			$ga_dash_data .= "['" . addslashes ( $data ['rows'] [$i] [0] ) . "'," . $data ['rows'] [$i] [1] . "],";
		}
		
		return $ga_dash_data;
	}
	
	/**
	 * Get Top Pages
	 *
	 * @access private
	 * @return array
	 */
	private function getAnalyticsTopPages($service, $projectId, $from, $to, $params) {
		$metrics = 'ga:pageviews';
		$dimensions = 'ga:pageTitle';
		try {
			$data = $service->data_ga->get ( 'ga:' . $projectId, $from, $to, $metrics, array (
					'dimensions' => $dimensions,
					'sort' => '-ga:pageviews',
					'max-results' => $params->get('ga_num_results', 24),
					'filters' => 'ga:pagePath!=/'
			) );
		} catch ( \Exception $e ) {
			if($params->get('enable_debug', 0)) {
				$this->app->enqueueMessage($e->getMessage(), 'error');
			}
			return false;
		}
		if (! $data ['rows']) {
			return false;
		}
		
		$ga_dash_data = "";
		$i = 0;
		while ( isset ( $data ['rows'] [$i] [0] ) ) {
			$ga_dash_data .= "['" . addslashes ( $data ['rows'] [$i] [0] ) . "'," . $data ['rows'] [$i] [1] . "],";
			$i ++;
		}
		
		return $ga_dash_data;
	}
	
	/**
	 * Get Top referrers
	 *
	 * @access private
	 * @return array
	 */
	private function getAnalyticsTopReferrers($service, $projectId, $from, $to, $params) {
		$metrics = 'ga:sessions';
		$dimensions = 'ga:source,ga:medium';
		try {
			$data = $service->data_ga->get ( 'ga:' . $projectId, $from, $to, $metrics, array (
					'dimensions' => $dimensions,
					'sort' => '-ga:sessions',
					'max-results' => $params->get('ga_num_results', 24),
					'filters' => 'ga:medium==referral'
			) );
		} catch ( \Exception $e ) {
			if($params->get('enable_debug', 0)) {
				$this->app->enqueueMessage($e->getMessage(), 'error');
			}
			return false;
		}
		if (! $data ['rows']) {
			return false;
		}
		
		$ga_dash_data = "";
		$i = 0;
		while ( isset ( $data ['rows'] [$i] [0] ) ) {
			$ga_dash_data .= "['" . addslashes ( $data ['rows'] [$i] [0] ) . "'," . $data ['rows'] [$i] [2] . "],";
			$i ++;
		}
		
		return $ga_dash_data;
	}
	
	/**
	 * Get Top searches
	 *
	 * @access private
	 * @return array
	 */
	private function getAnalyticsTopSearches($service, $projectId, $from, $to, $params) {
		$metrics = 'ga:sessions';
		$dimensions = 'ga:keyword';
		try {
			$data = $service->data_ga->get ( 'ga:' . $projectId, $from, $to, $metrics, array (
					'dimensions' => $dimensions,
					'sort' => '-ga:sessions',
					'max-results' => $params->get('ga_num_results', 24)
			) );
		} catch ( \Exception $e ) {
			if($params->get('enable_debug', 0)) {
				$this->app->enqueueMessage($e->getMessage(), 'error');
			}
			return false;
		}
		if (! $data ['rows']) {
			return false;
		}
		
		$ga_dash_data = "";
		$i = 0;
		while ( isset ( $data ['rows'] [$i] [0] ) ) {
			$ga_dash_data .= "['" . addslashes ( $data ['rows'] [$i] [0] ) . "'," . $data ['rows'] [$i] [1] . "],";
			$i ++;
		}
		
		return $ga_dash_data;
	}
	
	/**
	 * Get Top systems
	 *
	 * @access private
	 * @return array
	 */
	private function getAnalyticsTopSystems($service, $projectId, $from, $to, $params) {
		$metrics = 'ga:sessions';
		$dimensions = 'ga:browser,ga:operatingSystem';
		try {
			$data = $service->data_ga->get ( 'ga:' . $projectId, $from, $to, $metrics, array (
					'dimensions' => $dimensions,
					'sort' => '-ga:sessions',
					'max-results' => $params->get('ga_num_results', 24)
			) );
		} catch ( \Exception $e ) {
			if($params->get('enable_debug', 0)) {
				$this->app->enqueueMessage($e->getMessage(), 'error');
			}
			return false;
		}
		if (! $data ['rows']) {
			return false;
		}
		
		$ga_dash_data = "";
		$i = 0;
		while ( isset ( $data ['rows'] [$i] [0] ) ) {
			$ga_dash_data .= "['" . addslashes ( $data ['rows'] [$i] [0] ) . "','" . addslashes ($data ['rows'] [$i] [1]) . "'," . $data ['rows'] [$i] [2] . "],";
			$i ++;
		}
		
		return $ga_dash_data;
	}

	/**
	 * Get sessions visits
	 *
	 * @access private
	 * @param Google_Service_AnalyticsReporting $serviceReporting
	 * @param Google_Service_AnalyticsReporting_ReportRequest $request
	 * @param string $dimension
	 *
	 * @return string
	 */
	private function getReportingSessionMetric($serviceReporting, $request, $dimension) {
		$sessions = new \Google_Service_AnalyticsReporting_Metric();
		$sessions->setExpression("ga:sessions");
		$sessions->setAlias('sessions');
		
		$dimensionsObject = new \Google_Service_AnalyticsReporting_Dimension();
		$dimensionsObject->setName($dimension);
		$dimensionsArray = array($dimensionsObject);
		
		$request->setMetrics(array($sessions));
		$request->setDimensions($dimensionsArray);
		
		try {
			// Execute the ReportRequest Get for the main graph data
			$getRequest = new \Google_Service_AnalyticsReporting_GetReportsRequest();
			$getRequest->setReportRequests( array( $request) );
			$response = $serviceReporting->reports->batchGet( $getRequest );
			$rows = $response->getReports()[0]->getData()->getRows();
		} catch ( \Exception $e ) {
			if($this->getComponentParams()->get('enable_debug', 0)) {
				$this->app->enqueueMessage($e->getMessage(), 'error');
			}
			return false;
		}
		if (!$rows) {
			return false;
		}
		
		$gadash_data = "";
		for($rowIndex = 0; $rowIndex < count($rows); $rowIndex ++) {
			$row = $rows[ $rowIndex ];
			$rowMetrics = $row->getMetrics();
			$rowDimensions = $row->getDimensions();
			$rowValues = $rowMetrics[0]->getValues();
			$gadash_data .= "['" . $rowDimensions [0] . "'," . round ( $rowValues [0], 2 ) . "],";
		}
		
		return $gadash_data;
	}
	
	/**
	 * Get Top referrers
	 *
	 * @access private
	 * @param Google_Service_AnalyticsReporting $serviceReporting
	 * @param Google_Service_AnalyticsReporting_ReportRequest $request
	 *
	 * @return string
	 */
	private function getReportingTopReferrers($serviceReporting, $request) {
		$params = $this->getComponentParams ();
		
		$sessions = new \Google_Service_AnalyticsReporting_Metric();
		$sessions->setExpression("ga:sessions");
		$sessions->setAlias('sessions');
		
		$dimensionsSource = new \Google_Service_AnalyticsReporting_Dimension();
		$dimensionsSource->setName('ga:source');
		$dimensionsMedium = new \Google_Service_AnalyticsReporting_Dimension();
		$dimensionsMedium->setName('ga:medium');
		$dimensionsArray = array($dimensionsSource, $dimensionsMedium);
		
		$request->setMetrics(array($sessions));
		$request->setDimensions($dimensionsArray);
		
		// Add criteria, max results
		$request->setPageSize($params->get('ga_num_results', 24));
		
		// Add criteria, sort ordering
		$ordering = new \Google_Service_AnalyticsReporting_OrderBy();
		$ordering->setSortOrder('DESCENDING');
		$ordering->setFieldName("ga:sessions");
		$request->setOrderBys(array($ordering));
		
		// Add criteria, filters
		// Create the DimensionFilter.
		$dimensionFilter = new \Google_Service_AnalyticsReporting_DimensionFilter();
		$dimensionFilter->setDimensionName('ga:medium');
		$dimensionFilter->setOperator('EXACT');
		$dimensionFilter->setExpressions(array('referral'));
		// Create the DimensionFilterClauses
		$dimensionFilterClause = new \Google_Service_AnalyticsReporting_DimensionFilterClause();
		$dimensionFilterClause->setFilters(array($dimensionFilter));
		$request->setDimensionFilterClauses(array($dimensionFilterClause));
		
		try {
			// Execute the ReportRequest Get for the main graph data
			$getRequest = new \Google_Service_AnalyticsReporting_GetReportsRequest();
			$getRequest->setReportRequests( array( $request) );
			$response = $serviceReporting->reports->batchGet( $getRequest );
			$rows = $response->getReports()[0]->getData()->getRows();
		} catch ( \Exception $e ) {
			if($params->get('enable_debug', 0)) {
				$this->app->enqueueMessage($e->getMessage(), 'error');
			}
			return false;
		}
		if (!$rows) {
			return false;
		}
		
		$gadash_data = "";
		for($rowIndex = 0; $rowIndex < count($rows); $rowIndex ++) {
			$row = $rows[ $rowIndex ];
			$rowMetrics = $row->getMetrics();
			$rowDimensions = $row->getDimensions();
			$rowValues = $rowMetrics[0]->getValues();
			$gadash_data .= "['" . addslashes ( $rowDimensions [0] ) . "'," . $rowValues[0] . "],";
		}
		
		return $gadash_data;
	}
	
	/**
	 * Get Top searches
	 *
	 * @access private
	 * @return array
	 */
	private function getReportingTopSearches($serviceReporting, $request) {
		$params = $this->getComponentParams ();
		
		$sessions = new \Google_Service_AnalyticsReporting_Metric();
		$sessions->setExpression("ga:sessions");
		$sessions->setAlias('sessions');
		
		$dimensionsKeyword = new \Google_Service_AnalyticsReporting_Dimension();
		$dimensionsKeyword->setName('ga:keyword');
		$dimensionsArray = array($dimensionsKeyword);
		
		$request->setMetrics(array($sessions));
		$request->setDimensions($dimensionsArray);
		
		// Add criteria, max results
		$request->setPageSize($params->get('ga_num_results', 24));
		
		// Add criteria, sort ordering
		$ordering = new \Google_Service_AnalyticsReporting_OrderBy();
		$ordering->setSortOrder('DESCENDING');
		$ordering->setFieldName("ga:sessions");
		$request->setOrderBys(array($ordering));
		
		// Add criteria, filters
		// Create the DimensionFilter.
		$dimensionFilter = new \Google_Service_AnalyticsReporting_DimensionFilter();
		$dimensionFilter->setDimensionName('ga:keyword');
		$dimensionFilter->setNot(true);
		$dimensionFilter->setOperator('IN_LIST');
		$dimensionFilter->setExpressions(array('(all keys)'));
		// Create the DimensionFilterClauses
		$dimensionFilterClause = new \Google_Service_AnalyticsReporting_DimensionFilterClause();
		$dimensionFilterClause->setFilters(array($dimensionFilter));
		$request->setDimensionFilterClauses(array($dimensionFilterClause));
		
		try {
			// Execute the ReportRequest Get for the main graph data
			$getRequest = new \Google_Service_AnalyticsReporting_GetReportsRequest();
			$getRequest->setReportRequests( array( $request) );
			$response = $serviceReporting->reports->batchGet( $getRequest );
			$rows = $response->getReports()[0]->getData()->getRows();
		} catch ( \Exception $e ) {
			if($params->get('enable_debug', 0)) {
				$this->app->enqueueMessage($e->getMessage(), 'error');
			}
			return false;
		}
		if (!$rows) {
			return false;
		}
		
		$gadash_data = "";
		for($rowIndex = 0; $rowIndex < count($rows); $rowIndex ++) {
			$row = $rows[ $rowIndex ];
			$rowMetrics = $row->getMetrics();
			$rowDimensions = $row->getDimensions();
			$rowValues = $rowMetrics[0]->getValues();
			$gadash_data .= "['" . addslashes ( $rowDimensions [0] ) . "'," . $rowValues[0] . "],";
		}
		
		return $gadash_data;
	}
	
	/**
	 * Get Top Pages
	 *
	 * @access private
	 * @return array
	 */
	private function getReportingTopPages($serviceReporting, $request) {
		$params = $this->getComponentParams ();
		
		$pageViews = new \Google_Service_AnalyticsReporting_Metric();
		$pageViews->setExpression("ga:pageviews");
		$pageViews->setAlias('pageviews');
		
		$dimensionsPagetitle = new \Google_Service_AnalyticsReporting_Dimension();
		$dimensionsPagetitle->setName('ga:pageTitle');
		$dimensionsArray = array($dimensionsPagetitle);
		
		$request->setMetrics(array($pageViews));
		$request->setDimensions($dimensionsArray);
		
		// Add criteria, max results
		$request->setPageSize($params->get('ga_num_results', 24));
		
		// Add criteria, sort ordering
		$ordering = new \Google_Service_AnalyticsReporting_OrderBy();
		$ordering->setSortOrder('DESCENDING');
		$ordering->setFieldName("ga:pageviews");
		$request->setOrderBys(array($ordering));
		
		try {
			// Execute the ReportRequest Get for the main graph data
			$getRequest = new \Google_Service_AnalyticsReporting_GetReportsRequest();
			$getRequest->setReportRequests( array( $request) );
			$response = $serviceReporting->reports->batchGet( $getRequest );
			$rows = $response->getReports()[0]->getData()->getRows();
		} catch ( \Exception $e ) {
			if($params->get('enable_debug', 0)) {
				$this->app->enqueueMessage($e->getMessage(), 'error');
			}
			return false;
		}
		if (!$rows) {
			return false;
		}
		
		$gadash_data = "";
		for($rowIndex = 0; $rowIndex < count($rows); $rowIndex ++) {
			$row = $rows[ $rowIndex ];
			$rowMetrics = $row->getMetrics();
			$rowDimensions = $row->getDimensions();
			$rowValues = $rowMetrics[0]->getValues();
			$gadash_data .= "['" . addslashes ( $rowDimensions [0] ) . "'," . $rowValues[0] . "],";
		}
		
		return $gadash_data;
	}
	
	/**
	 * Get Top systems
	 *
	 * @access private
	 * @return array
	 */
	private function getReportingTopSystems($serviceReporting, $request) {
		$params = $this->getComponentParams ();
		
		$pageViews = new \Google_Service_AnalyticsReporting_Metric();
		$pageViews->setExpression("ga:sessions");
		$pageViews->setAlias('sessions');
		
		$dimensionsBrowser = new \Google_Service_AnalyticsReporting_Dimension();
		$dimensionsBrowser->setName('ga:browser');
		$dimensionsOS = new \Google_Service_AnalyticsReporting_Dimension();
		$dimensionsOS->setName('ga:operatingSystem');
		$dimensionsOSVersion = new \Google_Service_AnalyticsReporting_Dimension();
		$dimensionsOSVersion->setName('ga:operatingSystemVersion');
		$dimensionsArray = array($dimensionsBrowser, $dimensionsOS, $dimensionsOSVersion);
		
		$request->setMetrics(array($pageViews));
		$request->setDimensions($dimensionsArray);
		
		// Add criteria, max results
		$request->setPageSize($params->get('ga_num_results', 24));
		
		// Add criteria, sort ordering
		$ordering = new \Google_Service_AnalyticsReporting_OrderBy();
		$ordering->setSortOrder('DESCENDING');
		$ordering->setFieldName("ga:sessions");
		$request->setOrderBys(array($ordering));
		
		try {
			// Execute the ReportRequest Get for the main graph data
			$getRequest = new \Google_Service_AnalyticsReporting_GetReportsRequest();
			$getRequest->setReportRequests( array( $request) );
			$response = $serviceReporting->reports->batchGet( $getRequest );
			$rows = $response->getReports()[0]->getData()->getRows();
		} catch ( \Exception $e ) {
			if($params->get('enable_debug', 0)) {
				$this->app->enqueueMessage($e->getMessage(), 'error');
			}
			return false;
		}
		if (!$rows) {
			return false;
		}
		
		$gadash_data = "";
		for($rowIndex = 0; $rowIndex < count($rows); $rowIndex ++) {
			$row = $rows[ $rowIndex ];
			$rowMetrics = $row->getMetrics();
			$rowDimensions = $row->getDimensions();
			$rowValues = $rowMetrics[0]->getValues();
			$gadash_data .= "['" . addslashes ( $rowDimensions [0] ) . "','" . addslashes ( $rowDimensions [1] ) . " " . addslashes ( $rowDimensions [2] ) . "'," . $rowValues[0] . "],";
		}
		
		return $gadash_data;
	}
	
	/**
	 * Get sessions visits by dimension
	 *
	 * @access private
	 * @param Google_Service_AnalyticsReporting $serviceReporting
	 * @param Google_Service_AnalyticsReporting_ReportRequest $request
	 * @param string $dimension
	 *
	 * @return string
	 */
	private function getDataSessionMetric($service, $dimension, $dateRanges, $propertyID) {
		// Setup the textual data stats
		$request = new \Google_Service_AnalyticsData_RunReportRequest();
		
		// Set entity property ID
		$request->property = $propertyID;
		
		$sessions = new \Google_Service_AnalyticsData_Metric();
		$sessions->setName('sessions');
		
		$dimensionsObject = new \Google_Service_AnalyticsData_Dimension();
		$dimensionsObject->setName($dimension);
		$dimensionsArray = array($dimensionsObject);
		
		$request->setDateRanges($dateRanges);
		$request->setMetrics(array($sessions));
		$request->setDimensions($dimensionsArray);
		
		$gadash_data = "";
		try {
			// Execute the ReportRequest Get for the textual stats
			$response = $service->properties->RunReport($request->property, $request);
			$rows = $response->getRows();
			
			if(!empty($rows)) {
				for($rowIndex = 0; $rowIndex < count($rows); $rowIndex ++) {
					$row = $rows[ $rowIndex ];
					$rowValues = $row->getMetricValues();
					$rowDimensionsValues = $row->getDimensionValues();
					$gadash_data .= "['" . $rowDimensionsValues [0]->value . "'," . round ( $rowValues [0]->value, 2 ) . "],";
				}
			}
			
		} catch ( \Exception $e ) {
			if($this->getComponentParams()->get('enable_debug', 0)) {
				$this->app->enqueueMessage($e->getMessage(), 'error');
			}
			return false;
		}
		
		if (! $gadash_data) {
			return false;
		}
		
		return $gadash_data;
	}
	
	/**
	 * Get metric by dimension
	 *
	 * @access private
	 * @param Google_Service_AnalyticsReporting $serviceReporting
	 * @param Google_Service_AnalyticsReporting_ReportRequest $request
	 * @param string $dimension
	 *
	 * @return string
	 */
	private function getDataSessionDimension($service, $dimension, $metric, $dateRanges, $propertyID) {
		// Setup the textual data stats
		$request = new \Google_Service_AnalyticsData_RunReportRequest();
		
		// Set entity property ID
		$request->property = $propertyID;
		
		$sessions = new \Google_Service_AnalyticsData_Metric();
		$sessions->setName($metric);
		
		$dimensionsObject = new \Google_Service_AnalyticsData_Dimension();
		$dimensionsObject->setName($dimension);
		$dimensionsArray = array($dimensionsObject);
		
		$request->setDateRanges($dateRanges);
		$request->setMetrics(array($sessions));
		$request->setDimensions($dimensionsArray);
		
		$gadash_data = "";
		try {
			// Execute the ReportRequest Get for the textual stats
			$response = $service->properties->RunReport($request->property, $request);
			$rows = $response->getRows();
			
			if(!empty($rows)) {
				for($rowIndex = 0; $rowIndex < count($rows); $rowIndex ++) {
					$row = $rows[ $rowIndex ];
					$rowValues = $row->getMetricValues();
					$rowDimensionsValues = $row->getDimensionValues();
					$gadash_data .= "['" . $rowDimensionsValues [0]->value . "'," . round ( $rowValues [0]->value, 2 ) . "],";
				}
			}
			
		} catch ( \Exception $e ) {
			if($this->getComponentParams()->get('enable_debug', 0)) {
				$this->app->enqueueMessage($e->getMessage(), 'error');
			}
			return false;
		}
		
		if (! $gadash_data) {
			return false;
		}
		
		return trim($gadash_data, ',');
	}
	
	/**
	 * Get Top Pages
	 *
	 * @access private
	 * @return array
	 */
	private function getDataTopEntities($service, $dimension, $metric, $dateRanges, $propertyID) {
		$params = $this->getComponentParams ();
		
		$request = new \Google_Service_AnalyticsData_RunReportRequest();
		
		// Set entity property ID
		$request->property = $propertyID;
		
		$pageViews = new \Google_Service_AnalyticsData_Metric();
		$pageViews->setName($metric);
		
		if(is_array($dimension)) {
			$dimensionsArray = array();
			foreach ($dimension as $singleDimension) {
				$dimensionsObject = new \Google_Service_AnalyticsData_Dimension();
				$dimensionsObject->setName($singleDimension);
				$dimensionsArray[] = $dimensionsObject;
			}
		} else {
			$dimensionsObject = new \Google_Service_AnalyticsData_Dimension();
			$dimensionsObject->setName($dimension);
			$dimensionsArray = array($dimensionsObject);
		}
		
		$request->setDateRanges($dateRanges);
		$request->setMetrics(array($pageViews));
		$request->setDimensions($dimensionsArray);
		
		// Add criteria, max results
		$request->setLimit($params->get('ga_num_results', 24));
		
		// Add criteria, sort ordering
		$ordering = new \Google_Service_AnalyticsData_OrderBy();
		$orderingMetric = new \Google_Service_AnalyticsData_MetricOrderBy();
		$orderingMetric->setMetricName($metric);
		$ordering->setMetric($orderingMetric);
		$ordering->setDesc(true);
		$request->setOrderBys($ordering);
		
		$gadash_data = "";
		try {
			// Execute the ReportRequest Get for the textual stats
			$response = $service->properties->RunReport($request->property, $request);
			$rows = $response->getRows();
			
			if(!empty($rows)) {
				for($rowIndex = 0; $rowIndex < count($rows); $rowIndex ++) {
					$row = $rows[ $rowIndex ];
					$rowValues = $row->getMetricValues();
					$rowDimensionsValues = $row->getDimensionValues();
					if(!empty($rowDimensionsValues)) {
						$gadash_data .= "[";
						foreach ($rowDimensionsValues as $rowDimensionsValue) {
							$gadash_data .= "'" . addslashes($rowDimensionsValue->value) . "',";
						}
						$gadash_data .= round ( $rowValues [0]->value, 2 ) . "],";
					}
				}
			}
			
		} catch ( \Exception $e ) {
			if($params->get('enable_debug', 0)) {
				$this->app->enqueueMessage($e->getMessage(), 'error');
			}
			return false;
		}
		
		if (! $gadash_data) {
			return false;
		}
		
		return $gadash_data;
	}

	/**
	 * Return Google profile identifier object
	 *
	 * @access public
	 * @param string
	 * @return array
	 */
	private function getSitesProfiles($service, $client, $params) {
		try {
			$profile_switch = "";
			$profiles = $service->management_profiles->listManagementProfiles ( '~all', '~all' );
		} catch ( \Exception $e ) {
			return $e;
		}

		$debugBuffer = null;
		$items = $profiles->getItems ();
		if (count ( $items ) != 0) {
			foreach ( $items as &$profile ) {
				$profileid = $profile->getId ();
				$this->currentProfile = $profile;
				$currentProfileUrl = $profile->getWebsiteUrl ();
				if($params->get('enable_debug', 0)) {
					$debugBuffer .= '<li>' . $currentProfileUrl . '</li>';
				}
				if($params->get('gajs_code_use_analytics', 0)) {
					$webPropertyId = $profile->getWebPropertyId();
					if($webPropertyId == trim($params->get('gajs_code', ''))) {
						return $profileid;
					}
				}
				if ($this->purifyDomain ( $currentProfileUrl ) == $this->purifyDomain ( $params->get ( 'ga_domain', Uri::root () ) )) {
					return $profileid;
				}
			}
			// Fallback on the latest added domain to Google Analytics if no match found, with domain dumping if debug is enabled
			if($params->get('enable_debug', 0)) {
				echo Text::sprintf('COM_JMAP_GOOGLE_ANALYTICS_DEBUGINFO', $debugBuffer);
			}
			return $profileid;
		}
	}
	
	/**
	 * Get interface first step code
	 *
	 * @access private
	 * @param string title
	 * @param string gadash_data
	 * @param int $showevery
	 *
	 * @return string
	 */
	private function getFirstStepCode($title, $gadash_data, $showevery) {
		// Evaluate nonce csp feature
		$appNonce = $this->app->get('csp_nonce', null);
		$nonce = $appNonce ? ' nonce="' . $appNonce . '"' : '';
		$code = '<script type="text/javascript"' . $nonce . ' src="https://www.gstatic.com/charts/loader.js"></script>
		<script type="text/javascript"' . $nonce . ' src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC1xyDpvjdNwCcKmZ3StzX2oH8PfRKW_aI&callback=initJMapGoogleMap" defer></script>
      	<script type="text/javascript"' . $nonce . '>
			function initJMapGoogleMap(){};
			google.charts.load("current", {"packages": ["corechart","geochart","table"], "callback": ga_dash_callback});

			function ga_dash_callback(){
				ga_dash_drawstats();
				if(typeof ga_dash_drawmap == "function"){
					ga_dash_drawmap();
				}
				if(typeof ga_dash_drawpgd == "function"){
					ga_dash_drawpgd();
				}
				if(typeof ga_dash_drawrd == "function"){
					ga_dash_drawrd();
				}
				if(typeof ga_dash_drawsd == "function"){
					ga_dash_drawsd();
				}
				if(typeof ga_dash_drawsys == "function"){
					ga_dash_drawsys();
				}
				if(typeof ga_dash_drawtraffic == "function"){
					ga_dash_drawtraffic();
				}

				setTimeout(function() {
					window.dispatchEvent(new Event("resize"));
				}, 500);
			}

			function ga_dash_drawstats() {
		        var data = google.visualization.arrayToDataTable([' . "
					['" . Text::_ ( 'COM_JMAP_GOOGLE_DATE' ) . "', '" . $title . "']," . $gadash_data . "
		        ]);
							
		        var options = {
					legend: {position: 'none'},
					" . "colors:['#3366CC','#2B56AD']," . "
					pointSize: 3,
					title: '" . $title . "',
					chartArea: {width: '95%'},
					hAxis: { title: '" . Text::_ ( 'COM_JMAP_GOOGLE_DATE' ) . "',  titleTextStyle: {color: 'black'}, showTextEvery: " . $showevery . "},
					vAxis: { textPosition: 'none', minValue: 0}
				};
							
				var chart = new google.visualization.AreaChart(document.getElementById('gadash_div'));
				chart.draw(data, options);
			}";
		
		return $code;
	}
	
	/**
	 * Get interface visits by country code
	 *
	 * @access private
	 * @param string $getVisitsByCountry
	 *
	 * @return string
	 */
	private function getVisitsByCountryCode($getVisitsByCountry) {
		$code = '
		function ga_dash_drawmap() {
			var data = google.visualization.arrayToDataTable([' . "
			  ['Country', 'Visits']," . $getVisitsByCountry . "
			]);
			  		
			var options = {
				colors: ['white', '" . "blue" . "']
			};
						
			var chart = new google.visualization.GeoChart(document.getElementById('ga_dash_mapdata'));
			chart.draw(data, options);
		}";
		
		return $code;
	}
	
	/**
	 * Get interface traffic code
	 *
	 * @access private
	 * @param string $getTrafficSources
	 * @param string $getNewReturnVisitors
	 *
	 * @return string
	 */
	private function getTrafficCode($getTrafficSources, $getNewReturnVisitors) {
		$code = '
		function ga_dash_drawtraffic() {
			var data = google.visualization.arrayToDataTable([' . "
			  ['" . Text::_('COM_JMAP_GOOGLE_SOURCE') . "', '" . Text::_('COM_JMAP_GOOGLE_VISITS') . "']," . $getTrafficSources . '
			]);
			  		
			var datanvr = google.visualization.arrayToDataTable([' . "
			  ['" . Text::_('COM_JMAP_TYPE') . "', '" . Text::_('COM_JMAP_GOOGLE_VISITS') . "']," . $getNewReturnVisitors . "
			]);
			  		
			var chart = new google.visualization.PieChart(document.getElementById('ga_dash_trafficdata'));
			chart.draw(data, {
					tooltip: {showColorCode: true},
					is3D: true,
					height: 400,
					tooltipText: 'percentage',
					legend: {position: 'right', textStyle: {color: 'blue', fontSize: 16}},
					title: '" . Text::_('COM_JMAP_GOOGLE_TRAFFIC_SOURCES') . "'
			});
							
			var gadash = new google.visualization.PieChart(document.getElementById('ga_dash_nvrdata'));
			gadash.draw(datanvr,  {
					tooltip: {showColorCode: true},
					is3D: true,
					height: 400,
					tooltipText: 'percentage',
					legend: {position: 'right', textStyle: {color: 'blue', fontSize: 16}},
					title: '" . Text::_('COM_JMAP_GOOGLE_NEW_RETURNING') . "'
			});

			setTimeout(function(){
				if(document.querySelector('#ga_dash_trafficdata svg ~ div')) {
					document.querySelector('#ga_dash_trafficdata svg ~ div').classList.add('table', 'table-striped');
					document.querySelector('#ga_dash_nvrdata svg ~ div').classList.add('table', 'table-striped');
				}
			}, 500);
		}";
		
		return $code;
	}
	
	/**
	 * Get interface pages code
	 *
	 * @access private
	 * @param string $getTopPages
	 *
	 * @return string
	 */
	private function getTopPagesCode($getTopPages) {
		$code = '
		function ga_dash_drawpgd() {
			var data = google.visualization.arrayToDataTable([' . "
			  ['" . Text::_('COM_JMAP_GOOGLE_TOPPAGES') . "', '" . Text::_('COM_JMAP_GOOGLE_VISITS') . "']," . $getTopPages . "
			]);
			  		
			var options = {
					page: 'enable',
					pageSize: 6,
					width: '100%'
			};
			  		
			var chart = new google.visualization.Table(document.getElementById('ga_dash_pgddata'));
			chart.draw(data, options);
		}";
		return $code;
	}
	
	/**
	 * Get interface referrers code
	 *
	 * @access private
	 * @param string $getTopReferrers
	 *
	 * @return string
	 */
	private function getTopReferrers($getTopReferrers) {
		$code = '
		function ga_dash_drawrd() {
			var datar = google.visualization.arrayToDataTable([' . "
			  ['" . Text::_('COM_JMAP_GOOGLE_TOPREFERRERS') . "', '" . Text::_('COM_JMAP_GOOGLE_VISITS') . "']," . $getTopReferrers . "
			]);
			  		
			var options = {
					page: 'enable',
					pageSize: 6,
					width: '100%'
			};
			  		
			var chart = new google.visualization.Table(document.getElementById('ga_dash_rdata'));
			chart.draw(datar, options);
		}";
		return $code;
	}
	
	/**
	 * Get interface searches code
	 *
	 * @access private
	 * @param string $getTopSearches
	 *
	 * @return string
	 */
	private function getTopSearches($getTopSearches) {
		$code = '
		function ga_dash_drawsd() {
			var datas = google.visualization.arrayToDataTable([' . "
			  ['" . Text::_('COM_JMAP_GOOGLE_TOPSEARCHES') . "', '" . Text::_('COM_JMAP_GOOGLE_VISITS') . "']," . $getTopSearches . "
			]);
			  		
			var options = {
					page: 'enable',
					pageSize: 6,
					width: '100%'
			};
			  		
			var chart = new google.visualization.Table(document.getElementById('ga_dash_sdata'));
			chart.draw(datas, options);
		}";
		return $code;
	}
	
	/**
	 * Get interface searches code
	 *
	 * @access private
	 * @param string $getTopSearches
	 *
	 * @return string
	 */
	private function getTopSystems($getTopSystems) {
		$code = '
		function ga_dash_drawsys() {
			var datas = google.visualization.arrayToDataTable([' . "
			  ['" . Text::_('COM_JMAP_GOOGLE_TOPBROWSER') . "', '" . Text::_('COM_JMAP_GOOGLE_TOPSYSTEM') . "', '" . Text::_('COM_JMAP_GOOGLE_VISITS') . "']," . $getTopSystems . "
			]);
			  		
			var options = {
					page: 'enable',
					pageSize: 6,
					width: '100%'
			};
			  		
			var chart = new google.visualization.Table(document.getElementById('ga_dash_sysdata'));
			chart.draw(datas, options);
		}";
		return $code;
	}
	
	/**
	 * Get interface resize function
	 *
	 * @access private
	 * @param string $getTopSearches
	 *
	 * @return string
	 */
	private function getWindowResize() {
		$code = "window.addEventListener('resize', function(){
			if(typeof ga_dash_drawstats == 'function'){
				ga_dash_drawstats();
			}
			if(typeof ga_dash_drawmap == 'function'){
				ga_dash_drawmap();
			}
			if(typeof ga_dash_drawpgd == 'function'){
				ga_dash_drawpgd();
			}
			if(typeof ga_dash_drawrd == 'function'){
				ga_dash_drawrd();
			}
			if(typeof ga_dash_drawsd == 'function'){
				ga_dash_drawsd();
			}
			if(typeof ga_dash_drawsys == 'function'){
				ga_dash_drawsys();
			}
			if(typeof ga_dash_drawtraffic == 'function'){
				ga_dash_drawtraffic();
			}
		});

		if(document.querySelector('#menu-collapse')) {
			document.querySelector('#menu-collapse').addEventListener('click', function(){
				setTimeout(function() {
					window.dispatchEvent(new Event('resize'));
				}, 500);
			});
		}
		</script>";

		return $code;
	}
	
	/**
	 * Get interface period buttons
	 *
	 * @access private
	 * @param string $gaperiod
	 *
	 * @return string
	 */
	private function getPeriodButtons($gaperiod) {
		$code = '<div class="btn-toolbar">
					<div class="btn-wrapper"><button class="btn btn-info' . ($gaperiod == "today" ? ' active' : '') . '" role="button" onclick="document.getElementById(\'gaperiod\').value=\'today\'">' . Text::_ ( 'COM_JMAP_GOOGLE_TODAY' ) . '</button></div>
					<div class="btn-wrapper"><button class="btn btn-info' . ($gaperiod == "yesterday" ? ' active' : '') . '" role="button" onclick="document.getElementById(\'gaperiod\').value=\'yesterday\'">' . Text::_ ( 'COM_JMAP_GOOGLE_YESTERDAY' ) . '</button></div>
					<div class="btn-wrapper"><button class="btn btn-info' . ($gaperiod == "last7days" ? ' active' : '') . '" role="button" onclick="document.getElementById(\'gaperiod\').value=\'last7days\'">' . Text::_ ( 'COM_JMAP_GOOGLE_LAST7DAYS' ) . '</button></div>
					<div class="btn-wrapper"><button class="btn btn-info' . ($gaperiod == "last14days" ? ' active' : '') . '" role="button" onclick="document.getElementById(\'gaperiod\').value=\'last14days\'">' . Text::_ ( 'COM_JMAP_GOOGLE_LAST14DAYS' ) . '</button></div>
					<div class="btn-wrapper"><button class="btn btn-info' . ($gaperiod == "last30days" ? ' active' : '') . '" role="button" onclick="document.getElementById(\'gaperiod\').value=\'last30days\'">' . Text::_ ( 'COM_JMAP_GOOGLE_LAST30DAYS' ) . '</button></div>
					<div class="btn-wrapper"><button class="btn btn-info' . ($gaperiod == "last3months" ? ' active' : '') . '" role="button" onclick="document.getElementById(\'gaperiod\').value=\'last3months\'">' . Text::_ ( 'COM_JMAP_GOOGLE_LAST3MONTHS' ) . '</button></div>
					<div class="btn-wrapper"><button class="btn btn-info' . ($gaperiod == "last6months" ? ' active' : '') . '" role="button" onclick="document.getElementById(\'gaperiod\').value=\'last6months\'">' . Text::_ ( 'COM_JMAP_GOOGLE_LAST6MONTHS' ) . '</button></div>
					<div class="btn-wrapper"><button class="btn btn-info' . ($gaperiod == "last12months" ? ' active' : '') . '" role="button" onclick="document.getElementById(\'gaperiod\').value=\'last12months\'">' . Text::_ ( 'COM_JMAP_GOOGLE_LAST12MONTHS' ) . '</button></div>
				</div>';
		
		return $code;
	}
	
	/**
	 * Get interface metric buttons
	 *
	 * @access private
	 * @param string $gaperiod
	 *
	 * @return string
	 */
	private function getMetricButtons($gaquery, $metric1, $metric2, $metric3, $metric4, $metric5) {
		$code = '<div class="btn-toolbar">
					<div class="btn-wrapper"><button class="btn btn-info' . ($gaquery == $metric1 ? ' active' : '') . '" role="button" onclick="document.getElementById(\'gaquery\').value=\'' . $metric1 . '\'">' . Text::_ ( 'COM_JMAP_GOOGLE_METRIC_VISITORS' ) . '</button></div>
					<div class="btn-wrapper"><button class="btn btn-info' . ($gaquery == $metric2 ? ' active' : '') . '" role="button" onclick="document.getElementById(\'gaquery\').value=\'' . $metric2 . '\'">' . Text::_ ( 'COM_JMAP_GOOGLE_METRIC_PAGEVIEWS' ) . '</button></div>
					<div class="btn-wrapper"><button class="btn btn-info' . ($gaquery == $metric3 ? ' active' : '') . '" role="button" onclick="document.getElementById(\'gaquery\').value=\'' . $metric3 . '\'">' . ($metric3 == 'bounceRate' ? Text::_ ( 'COM_JMAP_GOOGLE_METRIC_BOUNCERATE' ) : Text::_ ( 'COM_JMAP_GOOGLE_METRIC_ENGAGEMENTRATE' )) . '</button></div>
					<div class="btn-wrapper"><button class="btn btn-info' . ($gaquery == $metric4 ? ' active' : '') . '" role="button" onclick="document.getElementById(\'gaquery\').value=\'' . $metric4 . '\'">' . ($metric4 == 'organicSearches' ? Text::_ ( 'COM_JMAP_GOOGLE_METRIC_ORGANICSEARCHES' ) : Text::_ ( 'COM_JMAP_GOOGLE_METRIC_SESSIONSPERUSER' )) . '</button></div>
					<div class="btn-wrapper"><button class="btn btn-info' . ($gaquery == $metric5 ? ' active' : '') . '" role="button" onclick="document.getElementById(\'gaquery\').value=\'' . $metric5 . '\'">' . Text::_ ( 'COM_JMAP_GOOGLE_METRIC_VISITS' ) . '</button></div>
				</div>';
		
		return $code;
	}
	
	/**
	 * Get interface for main multireports
	 *
	 * @access private
	 * @param string $gaperiod
	 *
	 * @return string
	 */
	private function getMultiReports($text1, $text2, $text3, $text4, $text5, $text6, $customIcon = 'fa-search') {
		$JoomlaCMSLanguageText = '\Joomla\CMS\Language\Text';
		$multiReports = <<<MULTIREPORTS
									<div class="card card-info card-group-google" id="jmap_googlegeo_accordion">
										<div class="card-header accordion-toggle accordion_lightblue" data-bs-toggle="collapse" data-bs-target="#jmap_googlestats_geo">
											<h4><span class="fas fa-image" aria-hidden="true"></span> {$JoomlaCMSLanguageText::_ ($text1)}</h4>
										</div>
										<div id="jmap_googlestats_geo" class="card-body card-block accordion-chart">
											<div id="ga_dash_mapdata"></div>
										</div>
									</div>
									
									<div class="card card-info card-group-google" id="jmap_googletraffic_accordion">
										<div class="card-header accordion-toggle accordion_lightblue" data-bs-toggle="collapse" data-bs-target="#jmap_googlestats_traffic">
											<h4><span class="fas fa-sort" aria-hidden="true"></span> {$JoomlaCMSLanguageText::_ ($text2)}</h4>
										</div>
										<div id="jmap_googlestats_traffic" class="card-body card-block accordion-chart">
											<div id="ga_dash_trafficdata"></div><div id="ga_dash_nvrdata"></div>
										</div>
									</div>
									
									<div class="card card-info card-group-google" id="jmap_googlereferrer_accordion">
										<div class="card-header accordion-toggle accordion_lightblue" data-bs-toggle="collapse" data-bs-target="#jmap_googlestats_referrers">
											<h4><span class="fas fa-caret-square-down" aria-hidden="true"></span> {$JoomlaCMSLanguageText::_ ($text3)}</h4>
										</div>
										<div id="jmap_googlestats_referrers" class="card-body card-block accordion-chart">
											<div id="ga_dash_rdata"></div>
										</div>
									</div>
									
									<div class="card card-info card-group-google" id="jmap_googlesystems_accordion">
										<div class="card-header accordion-toggle accordion_lightblue" data-bs-toggle="collapse" data-bs-target="#jmap_googlestats_systems">
											<h4><span class="fas fa-desktop" aria-hidden="true"></span> {$JoomlaCMSLanguageText::_($text5)}</h4>
										</div>
										<div id="jmap_googlestats_systems" class="card-body card-block accordion-chart">
											<div id="ga_dash_sysdata"></div>
										</div>
									</div>
			
									<div class="card card-info card-group-google" id="jmap_googlepages_accordion">
										<div class="card-header accordion-toggle accordion_lightblue" data-bs-toggle="collapse" data-bs-target="#jmap_googlestats_pages">
											<h4><span class="fas fa-file" aria-hidden="true"></span> {$JoomlaCMSLanguageText::_ ($text6)}</h4>
										</div>
										<div id="jmap_googlestats_pages" class="card-body card-block accordion-chart">
											<div id="ga_dash_pgddata"></div>
										</div>
									</div>
		
									<div class="card card-info card-group-google" id="jmap_googlesearches_accordion">
										<div class="card-header accordion-toggle accordion_lightblue" data-bs-toggle="collapse" data-bs-target="#jmap_googlestats_searches">
											<h4><span class="fas {$customIcon}" aria-hidden="true"></span> {$JoomlaCMSLanguageText::_ ($text4)}</h4>
										</div>
										<div id="jmap_googlestats_searches" class="card-body card-block accordion-chart">
											<div id="ga_dash_sdata"></div>
										</div>
									</div>
		MULTIREPORTS;
		return $multiReports;
	}
	
	/**
	 * Main get data method
	 *
	 * @access public
	 * @return mixed Returns a data string if success or boolean if exceptions are trigged
	 */
	public function getDataAnalytics() {
		$params = $this->getComponentParams ();
		
		// Perform the authentication management before going on
		$authenticationData = $this->authentication ( $params );
		if($authenticationData) {
			return $authenticationData;
		}
		
		// New Service instance for the API, Google_Service_Analytics
		$service = new \Google_Service_Analytics ( $this->client );
		
		$projectId = $this->getSitesProfiles ( $service, $this->client, $params );
		
		if ( $projectId instanceof \Exception ) {
			$this->deleteToken();
			$this->app->enqueueMessage ( $projectId->getMessage (), 'warning' );
			return '<a class="btn btn-primary" href="index.php?option=com_jmap&amp;task=google.display">' . Text::_ ( 'COM_JMAP_GOBACK' ) . '</a>';
		}
		
		if ($this->app->getInput()->get('gaquery')) {
			$gaquery = $this->app->getInput()->get('gaquery');
		} else {
			$gaquery = "sessions";
		}
		
		// Evaluate if the metric exists in this API, there could be an API change with incompatible metric so fallback in this case
		if(!in_array($gaquery, array('users', 'pageviews', 'bounceRate', 'organicSearches', 'sessions'))) {
			$gaquery = "sessions";
		}
		
		if ($this->app->getInput()->get('gaperiod')) {
			$gaperiod = $this->app->getInput()->get('gaperiod');
		} else {
			$gaperiod = "last30days";
		}
		
		extract($this->getPeriod ( $gaperiod ));
		$title = $this->getTitle ( $gaquery );
		
		$metrics = 'ga:' . $gaquery;
		$dimensions = 'ga:year,ga:month,ga:day';
		
		if ($gaperiod == "today" or $gaperiod == "yesterday") {
			$dimensions = 'ga:hour';
		} else {
			$dimensions = 'ga:year,ga:month,ga:day';
		}
		
		try {
			$data = $service->data_ga->get ( 'ga:' . $projectId, $from, $to, $metrics, array (
					'dimensions' => $dimensions
			) );
		} catch ( \Exception $e ) {
			$this->app->enqueueMessage ( $e->getMessage (), 'error' );
			return null;
		}
		$gadash_data = "";
		for($i = 0; $i < $data ['totalResults']; $i ++) {
			if ($gaperiod == "today" or $gaperiod == "yesterday") {
				$gadash_data .= "['" . $data ['rows'] [$i] [0] . ":00'," . round ( $data ['rows'] [$i] [1], 2 ) . "],";
			} else {
				$gadash_data .= "['" . $data ['rows'] [$i] [0] . "-" . $data ['rows'] [$i] [1] . "-" . $data ['rows'] [$i] [2] . "'," . round ( $data ['rows'] [$i] [3], 2 ) . "],";
			}
		}
		// Avoid errors in the drawing phase of the visits map
		if(!$gadash_data) {
			$gadash_data = "['" . date('Y-m-d') . "',0]";
		}
		
		$metrics = 'ga:sessions,ga:users,ga:pageviews,ga:bounceRate,ga:organicSearches,ga:sessionDuration';
		$dimensions = 'ga:year';
		try {
			$data = $service->data_ga->get ( 'ga:' . $projectId, $from, $to, $metrics, array (
					'dimensions' => $dimensions
			) );
			
			// Normalize the Google Analytics data rows if multiple ones are found, merge into a single one
			if(!empty($data['rows']) && count($data['rows']) > 1) {
				$newDataArray = array();
				$newDataArray[0][1] = 0;
				$newDataArray[0][2] = 0;
				$newDataArray[0][3] = 0;
				$newDataArray[0][4] = 0;
				$newDataArray[0][5] = 0;
				$numElems = count($data['rows']);
				for($indexRow = 0; $indexRow <= ($numElems - 1); $indexRow++) {
					$newDataArray[0][1] += $data['rows'][$indexRow][1];
					$newDataArray[0][2] += $data['rows'][$indexRow][2];
					$newDataArray[0][3] += $data['rows'][$indexRow][3];
					$newDataArray[0][4] += $data['rows'][$indexRow][4];
					$newDataArray[0][5] += $data['rows'][$indexRow][5];
				}
				// Normalize bounce rate media
				$newDataArray[0][4] = $newDataArray[0][4] / $numElems;
				// Set single normalized row
				$data->setRows($newDataArray);
			}
			// If no data, e.g. an empty property, initialize rows to zero to avoid warnings
			if(empty($data['rows'])) {
				$newDataArray = array();
				$newDataArray[0][1] = 0;
				$newDataArray[0][2] = 0;
				$newDataArray[0][3] = 0;
				$newDataArray[0][4] = 0;
				$newDataArray[0][5] = 0;
				$data->setRows($newDataArray);
			}
		} catch ( \Exception $e ) {
			$this->app->enqueueMessage ( $e->getMessage (), 'error' );
			return null;
		}
		
		$code = $this->getFirstStepCode ( $title, $gadash_data, $showevery );
		
		$getVisitsByCountry = $this->getAnalyticsVisitsByCountry ( $service, $projectId, $from, $to, $params );
		if ($getVisitsByCountry) {
			$code .= $this->getVisitsByCountryCode ( $getVisitsByCountry );
		}
		
		$getTrafficSources = $this->getAnalyticsTrafficSources ( $service, $projectId, $from, $to, $params );
		$getNewReturnVisitors = $this->getAnalyticsNewReturnVisitors ( $service, $projectId, $from, $to, $params );
		if ($getTrafficSources && $getNewReturnVisitors) {
			$code .= $this->getTrafficCode ( $getTrafficSources, $getNewReturnVisitors );
		}
		
		$getTopPages = $this->getAnalyticsTopPages ( $service, $projectId, $from, $to, $params );
		if ($getTopPages) {
			$code .= $this->getTopPagesCode ( $getTopPages );
		}
		
		$getTopReferrers = $this->getAnalyticsTopReferrers ( $service, $projectId, $from, $to, $params );
		if ($getTopReferrers) {
			$code .= $this->getTopReferrers ( $getTopReferrers );
		}
		
		$getTopSystems = $this->getAnalyticsTopSystems ( $service, $projectId, $from, $to, $params );
		if ($getTopSystems) {
			$code .= $this->getTopSystems ( $getTopSystems );
		}
		
		$getTopSearches = $this->getAnalyticsTopSearches ( $service, $projectId, $from, $to, $params );
		if ($getTopSearches) {
			$code .= $this->getTopSearches ( $getTopSearches );
		}
		
		$code .= $this->getWindowResize() .
		($this->currentProfile->getWebsiteUrl() ? "<span class='badge bg-primary mb-2 badge-gaproperty'>" . $this->currentProfile->getWebsiteUrl() . "</span>" : null) .
		($this->hasOwnCredentials ? null : "<span data-bs-content='" . Text::_('COM_JMAP_GOOGLE_APP_NOTSET_DESC') . "' class='badge bg-warning hasPopover google pull-right'>" . Text::_('COM_JMAP_GOOGLE_APP_NOTSET') . "</span>") .
		'<div id="ga-dash">' .
		$this->getPeriodButtons($gaperiod) .
		'<div class="card card-info card-group-google" id="jmap_googlegraph_accordion">
			<div class="card-header accordion-toggle accordion_lightblue" data-bs-toggle="collapse" data-bs-target="#jmap_googlestats_graph">
				<h4><span class="fas fa-chart-area" aria-hidden="true"></span> ' . Text::_ ('COM_JMAP_GOOGLE_STATS' ) . '</h4>
			</div>
			<div id="jmap_googlestats_graph" class="card-body card-block accordion-chart">'.
			$this->getMetricButtons($gaquery, 'users', 'pageviews', 'bounceRate', 'organicSearches', 'sessions') .
			'<div id="gadash_div" style="height:350px;"></div>
				<table class="gatable" cellpadding="4" width="100%" align="center">
					<tr>
						<td width="33%"><span class="badge bg-secondary">' . Text::_ ( 'COM_JMAP_GOOGLE_VISITS' ) . ':</span>
						<a href="javascript:void(0);" class="gatable">' . $data ['rows'] [0] [1] . '</td>
						<td width="33%"><span class="badge bg-secondary">' . Text::_ ( 'COM_JMAP_GOOGLE_VISITORS' ) . ':</span>
						<a href="javascript:void(0);" class="gatable">' . $data ['rows'] [0] [2] . '</a></td>
						<td width="33%"><span class="badge bg-secondary">' . Text::_ ( 'COM_JMAP_GOOGLE_PAGE_VIEWS' ) . ':</span>
						<a href="javascript:void(0);" class="gatable">' . $data ['rows'] [0] [3] . '</a></td>
					</tr>
					<tr>
						<td width="33%"><span class="badge bg-secondary">' . Text::_ ( 'COM_JMAP_GOOGLE_BOUNCE_RATE' ) . ':</span>
						<a href="javascript:void(0);" class="gatable">' . round ( $data ['rows'] [0] [4], 2 ) . '%</a></td>
						<td width="33%"><span class="badge bg-secondary">' . Text::_ ( 'COM_JMAP_GOOGLE_ORGANIC_SEARCHES' ) . ':</span>
						<a href="javascript:void(0);" class="gatable">' . $data ['rows'] [0] [5] . '</a></td>
						<td width="33%"><span class="badge bg-secondary">' . Text::_ ( 'COM_JMAP_GOOGLE_PAGES_VISIT' ) . ':</span>
						<a href="javascript:void(0);" class="gatable">' . (($data ['rows'] [0] [1]) ? round ( $data ['rows'] [0] [3] / $data ['rows'] [0] [1], 2 ) : '0') . '</a></td>
					</tr>
				</table>
			</div>
		</div>';

		$multiReports = $this->getMultiReports ('COM_JMAP_GOOGLE_MAP', 'COM_JMAP_GOOGLE_TRAFFIC', 'COM_JMAP_GOOGLE_REFERRERS', 'COM_JMAP_GOOGLE_SEARCHES', 'COM_JMAP_GOOGLE_SYSTEMS', 'COM_JMAP_GOOGLE_PAGES');
		$code .= $multiReports;
		$code .= '</div>';
		
		return $code;
	}
	
	/**
	 * Main get data method
	 *
	 * @access public
	 * @return mixed Returns a data string if success or boolean if exceptions are trigged
	 */
	public function getDataReporting() {
		$params = $this->getComponentParams ();
		
		// Perform the authentication management before going on
		$authenticationData = $this->authentication ( $params );
		if($authenticationData) {
			return $authenticationData;
		}
		
		// New Service instance for the API, Google_Service_Analytics
		$service = new \Google_Service_Analytics ( $this->client );
		$serviceReporting = new \Google_Service_AnalyticsReporting( $this->client );
		
		$projectId = $this->getSitesProfiles ( $service, $this->client, $params );
		
		if ( $projectId instanceof \Exception ) {
			$this->deleteToken();
			$this->app->enqueueMessage ( $projectId->getMessage (), 'warning' );
			return '<a class="btn btn-primary" href="index.php?option=com_jmap&amp;task=google.display">' . Text::_ ( 'COM_JMAP_GOBACK' ) . '</a>';
		}
		
		if ($this->app->getInput()->get('gaquery')) {
			$gaquery = $this->app->getInput()->get('gaquery');
		} else {
			$gaquery = "sessions";
		}
		
		// Evaluate if the metric exists in this API, there could be an API change with incompatible metric so fallback in this case
		if(!in_array($gaquery, array('users', 'pageviews', 'bounceRate', 'organicSearches', 'sessions'))) {
			$gaquery = "sessions";
		}
		
		if ($this->app->getInput()->get('gaperiod')) {
			$gaperiod = $this->app->getInput()->get('gaperiod');
		} else {
			$gaperiod = "last30days";
		}
		
		extract($this->getPeriod ( $gaperiod ));
		$title = $this->getTitle ( $gaquery );
		
		// Create the DateRange object
		$dateRange = new \Google_Service_AnalyticsReporting_DateRange();
		$dateRange->setStartDate($from);
		$dateRange->setEndDate($to);
		
		// Create the Metric object
		$sessions = new \Google_Service_AnalyticsReporting_Metric();
		$sessions->setExpression("ga:" . $gaquery);
		$sessions->setAlias($gaquery);
		
		//Create the Dimensions object.
		$dimensionsArray = array();
		if ($gaperiod == "today" or $gaperiod == "yesterday") {
			$dimensionsHour = new \Google_Service_AnalyticsReporting_Dimension();
			$dimensionsHour->setName("ga:hour");
			$dimensionsArray = array($dimensionsHour);
		} else {
			$dimensionsYear = new \Google_Service_AnalyticsReporting_Dimension();
			$dimensionsYear->setName("ga:year");
			$dimensionsMonth = new \Google_Service_AnalyticsReporting_Dimension();
			$dimensionsMonth->setName("ga:month");
			$dimensionsDay = new \Google_Service_AnalyticsReporting_Dimension();
			$dimensionsDay->setName("ga:day");
			$dimensionsArray = array($dimensionsYear, $dimensionsMonth, $dimensionsDay);
		}
		
		// Create the ReportRequest object.
		$request = new \Google_Service_AnalyticsReporting_ReportRequest();
		$request->setViewId($projectId);
		$request->setDateRanges($dateRange);
		$request->setMetrics(array($sessions));
		$request->setDimensions($dimensionsArray);
		
		try {
			// Execute the ReportRequest Get for the main graph data
			$getRequest = new \Google_Service_AnalyticsReporting_GetReportsRequest();
			$getRequest->setReportRequests( array( $request) );
			$response = $serviceReporting->reports->batchGet( $getRequest );
			$rows = $response->getReports()[0]->getData()->getRows();
		} catch ( \Exception $e ) {
			$this->app->enqueueMessage ( $e->getMessage (), 'error' );
			return null;
		}
		
		if(!empty($rows)) {
			$gadash_data = null;
			for($rowIndex = 0; $rowIndex < count($rows); $rowIndex ++) {
				$row = $rows[ $rowIndex ];
				$rowMetrics = $row->getMetrics();
				$rowDimensions = $row->getDimensions();
				$rowValues = $rowMetrics[0]->getValues();
				if ($gaperiod == "today" or $gaperiod == "yesterday") {
					$gadash_data .= "['" . $rowDimensions [0] . ":00'," . round ( $rowValues [0], 2 ) . "],";
				} else {
					$gadash_data .= "['" . $rowDimensions [0] . "-" . $rowDimensions [1] . "-" . $rowDimensions [2] . "'," . round ( $rowValues [0], 2 ) . "],";
				}
			}
		} else {
			$gadash_data = "[0,0]";
		}
		
		// Setup the textual data stats
		$sessions = new \Google_Service_AnalyticsReporting_Metric();
		$sessions->setExpression("ga:sessions");
		$sessions->setAlias('sessions');
		
		$users = new \Google_Service_AnalyticsReporting_Metric();
		$users->setExpression("ga:users");
		$users->setAlias('users');
		
		$pageViews = new \Google_Service_AnalyticsReporting_Metric();
		$pageViews->setExpression("ga:pageviews");
		$pageViews->setAlias('pageviews');
		
		$bounceRate = new \Google_Service_AnalyticsReporting_Metric();
		$bounceRate->setExpression("ga:bounceRate");
		$bounceRate->setAlias('bounceRate');
		
		$organicSearches = new \Google_Service_AnalyticsReporting_Metric();
		$organicSearches->setExpression("ga:organicSearches");
		$organicSearches->setAlias('organicSearches');
		
		$sessionDuration = new \Google_Service_AnalyticsReporting_Metric();
		$sessionDuration->setExpression("ga:sessionDuration");
		$sessionDuration->setAlias('sessionDuration');
		
		$dimensionYear = new \Google_Service_AnalyticsReporting_Dimension();
		$dimensionYear->setName("ga:year");
		$dimensionYear = array($dimensionYear);
		
		$request->setMetrics(array($sessions, $users, $pageViews, $bounceRate, $organicSearches, $sessionDuration));
		$request->setDimensions($dimensionYear);
		
		// Execute the ReportRequest Get for the textual stats
		$getRequest = new \Google_Service_AnalyticsReporting_GetReportsRequest();
		$getRequest->setReportRequests( array( $request) );
		$response = $serviceReporting->reports->batchGet($getRequest);
		$rows = $response->getReports()[0]->getData()->getRows();
		
		$textualData = array(null,null,null,null,null,null);
		if(!empty($rows)) {
			$row = $rows[0];
			$rowMetrics = $row->getMetrics();
			$textualData = $rowMetrics[0]->getValues();
		}
		
		$code = $this->getFirstStepCode ( $title, $gadash_data, $showevery );
		
		$getVisitsByCountry = $this->getReportingSessionMetric ( $serviceReporting, $request, 'ga:country' );
		if ($getVisitsByCountry) {
			$code .= $this->getVisitsByCountryCode ( $getVisitsByCountry );
		}
		
		$getTrafficSources = $this->getReportingSessionMetric ( $serviceReporting, $request, 'ga:medium' );
		$getNewReturnVisitors = $this->getReportingSessionMetric ( $serviceReporting, $request, 'ga:userType' );
		if ($getTrafficSources && $getNewReturnVisitors) {
			$getTrafficSources = StringHelper::str_ireplace('(none)', 'direct', $getTrafficSources);
			$code .= $this->getTrafficCode ( $getTrafficSources, $getNewReturnVisitors );
		}
		
		$getTopPages = $this->getReportingTopPages ( $serviceReporting, $request );
		if ($getTopPages) {
			$code .= $this->getTopPagesCode ( $getTopPages );
		}
		
		$getTopReferrers = $this->getReportingTopReferrers ( $serviceReporting, $request );
		if ($getTopReferrers) {
			$code .= $this->getTopReferrers ( $getTopReferrers );
		}
		
		$getTopSystems = $this->getReportingTopSystems ( $serviceReporting, $request );
		if ($getTopSystems) {
			$code .= $this->getTopSystems ( $getTopSystems );
		}
		
		$getTopSearches = $this->getReportingTopSearches ( $serviceReporting, $request );
		if ($getTopSearches) {
			$code .= $this->getTopSearches ( $getTopSearches );
		}
		
		$code .= $this->getWindowResize() .
		($this->currentProfile->getWebsiteUrl() ? "<span class='badge bg-primary mb-2 badge-gaproperty'>" . $this->currentProfile->getWebsiteUrl() . "</span>" : null) .
		($this->hasOwnCredentials ? null : "<span data-bs-content='" . Text::_('COM_JMAP_GOOGLE_APP_NOTSET_DESC') . "' class='badge bg-warning hasPopover google pull-right'>" . Text::_('COM_JMAP_GOOGLE_APP_NOTSET') . "</span>") .
		'<div id="ga-dash">' .
		$this->getPeriodButtons($gaperiod) .
		'<div class="card card-info card-group-google" id="jmap_googlegraph_accordion">
			<div class="card-header accordion-toggle accordion_lightblue" data-bs-toggle="collapse" data-bs-target="#jmap_googlestats_graph">
				<h4><span class="fas fa-chart-area" aria-hidden="true"></span> ' . Text::_ ('COM_JMAP_GOOGLE_STATS' ) . '</h4>
			</div>
			<div id="jmap_googlestats_graph" class="card-body card-block accordion-chart" >' .
			$this->getMetricButtons($gaquery, 'users', 'pageviews', 'bounceRate', 'organicSearches', 'sessions') .
			'<div id="gadash_div" style="height:350px;"></div>
				<table class="gatable" cellpadding="4" width="100%" align="center">
					<tr>
						<td width="33%"><span class="badge bg-secondary">' . Text::_ ( 'COM_JMAP_GOOGLE_VISITS' ) . ':</span>
						<a href="javascript:void(0);" class="gatable">' . $textualData[0] . '</td>
						<td width="33%"><span class="badge bg-secondary">' . Text::_ ( 'COM_JMAP_GOOGLE_VISITORS' ) . ':</span>
						<a href="javascript:void(0);" class="gatable">' . $textualData[1] . '</a></td>
						<td width="33%"><span class="badge bg-secondary">' . Text::_ ( 'COM_JMAP_GOOGLE_PAGE_VIEWS' ) . ':</span>
						<a href="javascript:void(0);" class="gatable">' . $textualData[2] . '</a></td>
					</tr>
					<tr>
						<td width="33%"><span class="badge bg-secondary">' . Text::_ ( 'COM_JMAP_GOOGLE_BOUNCE_RATE' ) . ':</span>
						<a href="javascript:void(0);" class="gatable">' . round ( $textualData[3], 2 ) . '%</a></td>
						<td width="33%"><span class="badge bg-secondary">' . Text::_ ( 'COM_JMAP_GOOGLE_ORGANIC_SEARCHES' ) . ':</span>
						<a href="javascript:void(0);" class="gatable">' . $textualData[4] . '</a></td>
						<td width="33%"><span class="badge bg-secondary">' . Text::_ ( 'COM_JMAP_GOOGLE_PAGES_VISIT' ) . ':</span>
						<a href="javascript:void(0);" class="gatable">' . (($textualData[0]) ? round ( $textualData[2] / $textualData[0], 2 ) : '0') . '</a></td>
					</tr>
				</table>
			</div>
		</div>';

		$multiReports = $this->getMultiReports ('COM_JMAP_GOOGLE_MAP', 'COM_JMAP_GOOGLE_TRAFFIC', 'COM_JMAP_GOOGLE_REFERRERS', 'COM_JMAP_GOOGLE_SEARCHES', 'COM_JMAP_GOOGLE_SYSTEMS', 'COM_JMAP_GOOGLE_PAGES');
		$code .= $multiReports;
		$code .= '</div>';
		
		return $code;
	}
	
	/**
	 * Main get data method
	 *
	 * @access public
	 * @return mixed Returns a data string if success or boolean if exceptions are trigged
	 */
	public function getDataData() {
		$params = $this->getComponentParams ();
		
		// Perform the authentication management before going on
		$authenticationData = $this->authentication ( $params );
		if($authenticationData) {
			return $authenticationData;
		}
		
		// Using a default constructor instructs the client to use the credentials
		// specified in GOOGLE_APPLICATION_CREDENTIALS environment variable.
		$service = new \Google_Service_AnalyticsData($this->client);
		
		if ($this->app->getInput()->get('gaquery')) {
			$gaquery = $this->app->getInput()->get('gaquery');
		} else {
			$gaquery = "sessions";
		}
		
		// Evaluate if the metric exists in this API, there could be an API change with incompatible metric so fallback in this case
		if(!in_array($gaquery, array('totalUsers', 'screenPageViews', 'engagementRate', 'sessionsPerUser', 'sessions'))) {
			$gaquery = "sessions";
		}
		
		if ($this->app->getInput()->get('gaperiod')) {
			$gaperiod = $this->app->getInput()->get('gaperiod');
		} else {
			$gaperiod = "last30days";
		}
		
		extract($this->getPeriod ( $gaperiod, true ));
		$title = $this->getTitle ( $gaquery );
		
		// Create the DateRange object
		$dateRanges = new \Google_Service_AnalyticsData_DateRange();
		$dateRanges->setStartDate($from);
		$dateRanges->setEndDate($to);
		
		// Create the Metric object
		$metrics = new \Google_Service_AnalyticsData_Metric();
		$metrics->setName($gaquery);
		
		//Create the Dimensions object.
		$dimensionsArray = array();
		// Set dimensions
		$dimensionsYear = new \Google_Service_AnalyticsData_Dimension();
		$dimensionsYear->setName("year");
		$dimensionsMonth = new \Google_Service_AnalyticsData_Dimension();
		$dimensionsMonth->setName("month");
		$dimensionsDay = new \Google_Service_AnalyticsData_Dimension();
		$dimensionsDay->setName("day");
		$dimensionsArray = array($dimensionsYear, $dimensionsMonth, $dimensionsDay);
		
		// Create the ReportRequest object.
		$request = new \Google_Service_AnalyticsData_RunReportRequest();
		
		// Add criteria, sort ordering
		$orderingYear = new \Google_Service_AnalyticsData_OrderBy();
		$orderingDimensionYear = new \Google_Service_AnalyticsData_DimensionOrderBy();
		$orderingDimensionYear->setDimensionName("year");
		$orderingYear->setDimension($orderingDimensionYear);
		$orderingYear->setDesc(false);
		
		$orderingMonth = new \Google_Service_AnalyticsData_OrderBy();
		$orderingDimensionMonth = new \Google_Service_AnalyticsData_DimensionOrderBy();
		$orderingDimensionMonth->setDimensionName("month");
		$orderingMonth->setDimension($orderingDimensionMonth);
		$orderingMonth->setDesc(false);
		
		$orderingDay = new \Google_Service_AnalyticsData_OrderBy();
		$orderingDimensionDay = new \Google_Service_AnalyticsData_DimensionOrderBy();
		$orderingDimensionDay->setDimensionName("day");
		$orderingDay->setDimension($orderingDimensionDay);
		$orderingDay->setDesc(false);
		
		$request->setOrderBys(array($orderingYear, $orderingMonth, $orderingDay));
		
		// Set entity property ID
		$propertyID = $params->get('ga_property_id');
		if(!$propertyID) {
			$this->app->enqueueMessage ( Text::_('COM_JMAP_GOOGLE_MISSING_PROPERTY_ID'), 'error' );
			return null;
		}
		
		$request->property = 'properties/' .  $propertyID;
		
		$request->setDateRanges($dateRanges);
		$request->setMetrics($metrics);
		$request->setDimensions($dimensionsArray);
		
		// Execute the ReportRequest Get for the main graph data
		try {
			$response = $service->properties->RunReport($request->property, $request);
			$rows = $response->getRows();
		} catch ( \Exception $e ) {
			$this->app->enqueueMessage ( $e->getMessage (), 'error' );
			return null;
		}
		
		if(!empty($rows)) {
			$gadash_data = null;
			for($rowIndex = 0; $rowIndex < count($rows); $rowIndex ++) {
				$row = $rows[ $rowIndex ];
				$rowValues = $row->getMetricValues();
				$rowDimensionsValues = $row->getDimensionValues();
				
				$gadash_data .= "['" . $rowDimensionsValues [0]->value . "-" . $rowDimensionsValues [1]->value . "-" . $rowDimensionsValues [2]->value . "'," . round ( $rowValues [0]->value, 2 ) . "],";
			}
		} else {
			$gadash_data = "[0,0]";
		}
		
		// Setup the textual data stats
		$request = new \Google_Service_AnalyticsData_RunReportRequest();
		$sessions = new \Google_Service_AnalyticsData_Metric();
		$sessions->setName('sessions');
		
		$users = new \Google_Service_AnalyticsData_Metric();
		$users->setName('totalUsers');
		
		$pageViews = new \Google_Service_AnalyticsData_Metric();
		$pageViews->setName('screenPageViews');
		
		$engagementRate = new \Google_Service_AnalyticsData_Metric();
		$engagementRate->setName('engagementRate');
		
		$sessionsPerUser = new \Google_Service_AnalyticsData_Metric();
		$sessionsPerUser->setName('sessionsPerUser');
		
		$sessionDuration = new \Google_Service_AnalyticsData_Metric();
		$sessionDuration->setName('userEngagementDuration');
		
		$dimensionYear = new \Google_Service_AnalyticsData_Dimension();
		$dimensionYear->setName("year");
		$dimensionsArray = array($dimensionYear);
		
		$request->setDateRanges($dateRanges);
		$request->setMetrics(array($sessions, $users, $pageViews, $engagementRate, $sessionsPerUser, $sessionDuration));
		$request->setDimensions($dimensionsArray);
		
		// Set entity property ID
		$request->property = 'properties/' .  $propertyID;
		
		// Execute the ReportRequest Get for the textual stats
		$response = $service->properties->RunReport($request->property, $request);
		$rows = $response->getRows();
		
		$textualData = array(null,null,null,null,null,null);
		if(!empty($rows)) {
			$row = $rows[0];
			$textualData = $row->getMetricValues();
		}
		
		$code = $this->getFirstStepCode ( $title, $gadash_data, $showevery );
		
		$getVisitsByCountry = $this->getDataSessionMetric ( $service, 'country', $dateRanges, $request->property );
		if ($getVisitsByCountry) {
			$code .= $this->getVisitsByCountryCode ( $getVisitsByCountry );
		}
		
		$getTrafficSources = $this->getDataSessionMetric ( $service, 'sessionDefaultChannelGrouping', $dateRanges, $request->property );
		$getNewVisitors = str_ireplace(array('[', ']'), '', explode(',', $this->getDataSessionDimension ( $service, 'year', 'newUsers', $dateRanges, $request->property )));
		$getNewVisitors[0] = Text::_('COM_JMAP_GOOGLE_NEW_VISITORS');
		$getTotalVisitors = str_ireplace(array('[', ']'), '', explode(',', $this->getDataSessionDimension ( $service, 'year', 'totalUsers', $dateRanges, $request->property )));
		$getTotalVisitors[0] = Text::_('COM_JMAP_GOOGLE_RETURNING_VISITORS');
		$getTotalVisitors[1] = isset($getNewVisitors[1]) ? abs($getTotalVisitors[1] - $getNewVisitors[1]) : (isset($getTotalVisitors[1]) ? $getTotalVisitors[1] : 0);
		$newVisitorsDashData = "['" . $getNewVisitors[0] . "'," . (isset($getNewVisitors[1]) ? round ( $getNewVisitors[1], 2 ) : 0) . "],";
		$returningVisitorsDashData = "['" . $getTotalVisitors[0] . "'," . round ( $getTotalVisitors[1], 2 ) . "]";
		$getNewReturnVisitors = $newVisitorsDashData . $returningVisitorsDashData;
		
		if ($getTrafficSources && $getNewReturnVisitors) {
			$code .= $this->getTrafficCode ( $getTrafficSources, $getNewReturnVisitors );
		}
		
		$getTopPages = $this->getDataTopEntities ( $service, 'pageTitle', 'screenPageViews', $dateRanges, $request->property );
		if ($getTopPages) {
			$code .= $this->getTopPagesCode ( $getTopPages );
		}
		
		$getTopReferrers = $this->getDataTopEntities ( $service, 'sessionSource', 'screenPageViews', $dateRanges, $request->property );
		if ($getTopReferrers) {
			$code .= $this->getTopReferrers ( $getTopReferrers );
		}
		
		$getTopSystems = $this->getDataTopEntities ( $service, array('browser', 'operatingSystemWithVersion'), 'sessions', $dateRanges, $request->property );
		if ($getTopSystems) {
			$code .= $this->getTopSystems ( $getTopSystems );
		}
		
		$getTopSearches = $this->getDataTopEntities ( $service, 'country', 'screenPageViews', $dateRanges, $request->property );
		if ($getTopSearches) {
			$code .= $this->getTopSearches ( $getTopSearches );
		}
		
		$code .= $this->getWindowResize() .
		("<span class='badge bg-primary mb-2 badge-gaproperty'>" . Text::sprintf('COM_JMAP_PROPERTY_ID', '<span class="badge badge-gadash">' . $propertyID . '</span>') . "</span>") .
		($this->hasOwnCredentials ? null : "<span data-bs-content='" . Text::_('COM_JMAP_GOOGLE_APP_NOTSET_DESC') . "' class='badge bg-warning hasPopover google pull-right'>" . Text::_('COM_JMAP_GOOGLE_APP_NOTSET') . "</span>") .
		'<div id="ga-dash">' .
		$this->getPeriodButtons($gaperiod) .
		'<div class="card card-info card-group-google" id="jmap_googlegraph_accordion">
			<div class="card-header accordion-toggle accordion_lightblue" data-bs-toggle="collapse" data-bs-target="#jmap_googlestats_graph">
				<h4><span class="fas fa-chart-area" aria-hidden="true"></span> ' . Text::_ ('COM_JMAP_GOOGLE_STATS' ) . '</h4>
			</div>
			<div id="jmap_googlestats_graph" class="card-body card-block accordion-chart" >' .
			$this->getMetricButtons($gaquery, 'totalUsers', 'screenPageViews', 'engagementRate', 'sessionsPerUser', 'sessions') .
			'<div id="gadash_div" style="height:350px;"></div>
				<table class="gatable" cellpadding="4" width="100%" align="center">
					<tr>
						<td width="33%"><span class="badge bg-secondary">' . Text::_ ( 'COM_JMAP_GOOGLE_VISITS' ) . ':</span>
						<a href="javascript:void(0);" class="gatable">' . (isset($textualData[0]) ? $textualData[0]->value : '-') . '</td>
						<td width="33%"><span class="badge bg-secondary">' . Text::_ ( 'COM_JMAP_GOOGLE_VISITORS' ) . ':</span>
						<a href="javascript:void(0);" class="gatable">' . (isset($textualData[1]) ? $textualData[1]->value : '-') . '</a></td>
						<td width="33%"><span class="badge bg-secondary">' . Text::_ ( 'COM_JMAP_GOOGLE_PAGE_VIEWS' ) . ':</span>
						<a href="javascript:void(0);" class="gatable">' . (isset($textualData[2]) ? $textualData[2]->value : '-') . '</a></td>
					</tr>
					<tr>
						<td width="33%"><span class="badge bg-secondary">' . Text::_ ( 'COM_JMAP_GOOGLE_METRIC_ENGAGEMENTRATE' ) . ':</span>
						<a href="javascript:void(0);" class="gatable">' . (isset($textualData[3]) ? round ( $textualData[3]->value, 2 ) : '0') . '%</a></td>
						<td width="33%"><span class="badge bg-secondary">' . Text::_ ( 'COM_JMAP_GOOGLE_METRIC_SESSIONSPERUSER' ) . ':</span>
						<a href="javascript:void(0);" class="gatable">' . (isset($textualData[4]) ? round ( $textualData[4]->value, 2 ) : '-') . '</a></td>
						<td width="33%"><span class="badge bg-secondary">' . Text::_ ( 'COM_JMAP_GOOGLE_PAGES_VISIT' ) . ':</span>
						<a href="javascript:void(0);" class="gatable">' . ((isset($textualData[2]) && isset($textualData[0]) && $textualData[0]->value) ? round ( $textualData[2]->value / $textualData[0]->value, 2 ) : '0') . '</a></td>
					</tr>
				</table>
			</div>
		</div>';
				
		$multiReports = $this->getMultiReports ('COM_JMAP_GOOGLE_MAP', 'COM_JMAP_GOOGLE_TRAFFIC', 'COM_JMAP_GOOGLE_REFERRERS', 'COM_JMAP_GOOGLE_COUNTRIES', 'COM_JMAP_GOOGLE_SYSTEMS', 'COM_JMAP_GOOGLE_PAGES', 'fa-globe');
		$code .= $multiReports;
		$code .= '</div>';
		
		return $code;
	}
	
	/**
	 * Get data method for webmasters tools stats
	 *
	 * @access public
	 * @return mixed Returns a data string if success or boolean if exceptions are trigged
	 */
	public function getDataWebmasters() {
		$params = $this->getComponentParams ();
	
		// Perform the authentication management before going on
		$authenticationData = $this->authentication ( $params );
		if($authenticationData) {
			$this->state->set('loggedout', true);
			$authenticationData .= '<input type="hidden" name="googlestats" value="webmasters" />';
			return $authenticationData;
		}

		// Set the analyzed domain in the model state
		$webmastersStatsDomain = $this->purifyWebmastersDomain( $params->get ( 'wm_domain', Uri::root() )) ;
		$this->state->set('stats_domain', $webmastersStatsDomain);
		$this->state->set('has_own_credentials', $this->hasOwnCredentials);

		// New Service instance for the API, Google_Service_Webmasters
		$service = new \Google_Service_Webmasters ( $this->client );

		$results = array();

		try {
			// Fetch sitemaps stats
			$results['sitemaps'] = $service->sitemaps->listSitemaps($webmastersStatsDomain);

			// New query request post body object
			$postBody = new \Google_Service_Webmasters_SearchAnalyticsQueryRequest();
			$postBody->setStartDate($this->getState('fromPeriod'));
			$postBody->setEndDate($this->getState('toPeriod'));

			// Fetch data metric
			$postBody->setDimensions(array('query'));
			$results['results_query'] = $service->searchanalytics->query($webmastersStatsDomain, $postBody);

			// Fetch data metric
			$postBody->setDimensions(array('page'));
			$results['results_page'] = $service->searchanalytics->query($webmastersStatsDomain, $postBody);
			
			// Fetch data metric
			$postBody->setDimensions(array('device'));
			$results['results_device'] = $service->searchanalytics->query($webmastersStatsDomain, $postBody);
			
			// Fetch data metric
			$postBody->setDimensions(array('country'));
			$results['results_country'] = $service->searchanalytics->query($webmastersStatsDomain, $postBody);
			
			// Fetch data metric
			$postBody->setDimensions(array('date'));
			$results['results_date'] = $service->searchanalytics->query($webmastersStatsDomain, $postBody);
			
			// Fetch inspect page url if submitted through form
			if($this->getState('inspectlink')) {
				$inspectionUrl = $this->getState('inspectlink');
				$searchConsoleDomain = rtrim($webmastersStatsDomain, '/') . '/';
				$results['inspect'] = json_decode($service->sitemaps->inspect($inspectionUrl, $searchConsoleDomain)->getBody());
			}
		} catch ( \Google_Exception $e ) {
			$exceptionCode = $e->getCode();
			$jmapException = new JMapException ( $e->getMessage (), 'error' );
			if($exceptionCode == 403) {
				$this->app->enqueueMessage ( $jmapException->getMessage () . Text::_('COM_JMAP_GOOGLE_WEBMASTERS_INTEGRATION_ERROR'), $jmapException->getExceptionLevel () );
			} else {
				$this->app->enqueueMessage ( $jmapException->getMessage (), $jmapException->getExceptionLevel () );
			}
			$result = array();
		} catch ( \Exception $e ) {
			$jmapException = new JMapException ( $e->getMessage (), 'error' );
			$this->app->enqueueMessage ( $jmapException->getMessage (), $jmapException->getExceptionLevel () );
			$result = array();
		}

		return $results;
	}

	/**
	 * Get data method for free Statscrop stats by scraping even manipulating them for anonymization
	 *
	 * @access public
	 * @return mixed string
	 */
	public function getDataStatscrop() {
		$cParams = $this->getComponentParams ();
		
		// Build the purified domain to scrape using the host only
		$domain = $cParams->get ( 'ga_domain', Uri::root () );
		$hostDomain = $this->getHost ( $domain );
		$url = "https://www.statscrop.com/www/$hostDomain";
		
		try {
			// Fetch remote data to scrape
			$httpTransport = $cParams->get ( 'analytics_service_http_transport', 'curl' ) == 'socket' ? 'file_get_contents' : new Curl ();
			
			// CURL lib
			if (is_object ( $httpTransport )) {
				$connectionAdapter = new Http ( $httpTransport, $cParams );
				
				// Init headers
				$headers = array (
						'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
						'Accept-Encoding' => 'text/plain',
						'Accept-Language' => 'en,it;q=0.9,en-US;q=0.8,de;q=0.7,es;q=0.6,fr;q=0.5,ru;q=0.4,ja;q=0.3,el;q=0.2,sk;q=0.1,nl;q=0.1,ar;q=0.1,sv;q=0.1,da;q=0.1',
						'Cache-Control' => 'no-cache',
						'Connection' => 'keep-alive',
						'Host' => 'www.statscrop.com',
						'Pragma' => 'no-cache',
						'Upgrade-Insecure-Requests' => '1',
						'User-Agent' => 'Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.93 Safari/537.36'
				);
				
				$httpResponse = $connectionAdapter->get ( $url, $headers );
			} else {
				// file_get_contents case
				$opts = array (
						'http' => array (
								'method' => "GET",
								'header' => "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9\r\n" .
								"Accept-Encoding: text/plain\r\n" .
								"Accept-Language: en,it;q=0.9,en-US;q=0.8,de;q=0.7,es;q=0.6,fr;q=0.5,ru;q=0.4,ja;q=0.3,el;q=0.2,sk;q=0.1,nl;q=0.1,ar;q=0.1,sv;q=0.1,da;q=0.1\r\n" .
								"Cache-Control: no-cache\r\n" .
								"Connection: keep-alive\r\n" .
								"Host: www.statscrop.com\r\n" .
								"Pragma: no-cache\r\n" .
								"Upgrade-Insecure-Requests: 1\r\n" .
								"User-Agent: Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.93 Safari/537.36"
						)
				);
				$context = stream_context_create ( $opts );
				$response = @file_get_contents ( $url, false, $context );
				
				if ($response) {
					$httpResponse = new Response ();
					$httpResponse->code = 200;
					$httpResponse->body = $response;
				} else {
					throw new \RuntimeException ( Text::sprintf ( 'COM_JMAP_ERROR_RETRIEVING_STATS', 409 ) );
				}
			}
			
			// Check if HTTP status code is 200 OK
			if ($httpResponse->code != 200 || !$httpResponse->body) {
				throw new \RuntimeException ( Text::sprintf( 'COM_JMAP_ERROR_RETRIEVING_STATS', $httpResponse->code) );
			}
			
			// Process result, all assets must be canonicalized to point to the native website https://www.statscrop.com
			$httpResponse->body = preg_replace ( '/src="\//i', 'src="https://www.statscrop.com/', $httpResponse->body );
			$httpResponse->body = preg_replace ( '/href="\/([^\/])/i', 'href="https://statscrop.com/$1', $httpResponse->body );
			$httpResponse->body = preg_replace ( '/href="/i', 'target="_blank" href="', $httpResponse->body );
			
			// Process DOM html
			require_once JPATH_ROOT . '/plugins/system/jmap/simplehtmldom.php';
			$simpleHtmlDomInstance = new \JMapSimpleHtmlDom ();
			$simpleHtmlDomInstance->load ( $httpResponse->body );
			
			// Find and remove inline script tags
			foreach ( $simpleHtmlDomInstance->find ( 'script' ) as $element ) {
				// Check for inner mixpanel scripts and remove element if any
				$innerText = $element->innertext;
				if (stripos ( $innerText, 'has_Adsense' ) !== false) {
					$element->outertext = '';
				}
				if (stripos ( $innerText, 'adsbygoogle' ) !== false) {
					$element->outertext = '';
				}
				if (stripos ( $innerText, 'GoogleAnalyticsObject' ) !== false) {
					$element->outertext = '';
				}
			}
			
			// Remove the developer tools template title heading
			foreach ( $simpleHtmlDomInstance->find ( 'header.navbar, label.sidebar-toggle-label, #sidebar-www, #site-competitors, caption.more, footer' ) as $element ) {
				$element->outertext = '';
			}
			
			foreach ( $simpleHtmlDomInstance->find ( 'small.d-block' ) as $element ) {
				// Check for inner mixpanel scripts and remove element if any
				$innerText = $element->innertext;
				if (stripos ( $innerText, 'Advertisement' ) !== false) {
					$element->outertext = '';
				}
			}
			
			// Create a style element to have full control of the custom styling of inner contents
			$newElement = $simpleHtmlDomInstance->createElement ( 'style', 'main{flex: 0 0 100% !important;max-width: 100% !important}body.stuff-content{padding-top:0}div.back-top{display:none!important}' );
			$simpleHtmlDomInstance->getElementByTagName ( 'head' )->appendChild ( $newElement );
			
			// Save and return the manipulated DOM structure
			$httpResponse->body = $simpleHtmlDomInstance->save ();
		} catch ( \RuntimeException $e ) {
			return $e->getMessage ();
		} catch ( \Exception $e ) {
			return $e->getMessage ();
		}
		
		return $httpResponse->body;
	}
	
	/**
	 * Get data method for free HypeStat stats by scraping even manipulating them for anonymization
	 *
	 * @access public
	 * @return mixed string
	 */
	public function getDataHypeStat() {
		$cParams = $this->getComponentParams ();
	
		// Build the purified domain to scrape using the host only
		$domain = $cParams->get ( 'ga_domain', Uri::root () );
		$hostDomain = $this->getHost ( $domain );
		$url = "https://hypestat.com/info/$hostDomain";
	
		try {
			// Fetch remote data to scrape
			$httpTransport = $cParams->get('analytics_service_http_transport', 'curl') == 'socket' ? new Socket() : new Curl();
			$connectionAdapter = new Http( $httpTransport, $cParams );
			$httpResponse = $connectionAdapter->get ( $url );
				
			// Check if HTTP status code is 200 OK
			if ($httpResponse->code != 200 || !$httpResponse->body) {
				throw new \RuntimeException ( Text::sprintf( 'COM_JMAP_ERROR_RETRIEVING_STATS', $httpResponse->code) );
			}
			
			// Fix for wrong height of the graphs
			$httpResponse->body = preg_replace ( '/height="170"/i', '', $httpResponse->body );
			
			// Process DOM html
			require_once JPATH_ROOT . '/plugins/system/jmap/simplehtmldom.php';
			$simpleHtmlDomInstance = new \JMapSimpleHtmlDom ();
			$simpleHtmlDomInstance->load ( $httpResponse->body );
				
			// Find and remove inline script tags
			foreach ( $simpleHtmlDomInstance->find ( 'script' ) as $element ) {
				// Remove adsbygoogle.js script
				if (stripos ( $element->getAttribute ( 'src' ), 'adsbygoogle' ) !== false) {
					$element->outertext = '';
				}
				// Remove gtag script
				if (stripos ( $element->getAttribute ( 'src' ), 'gtag' ) !== false) {
					$element->outertext = '';
				}
				// Remove gtag script
				if (stripos ( $element->getAttribute ( 'src' ), 'cmp' ) !== false) {
					$element->outertext = '';
				}
				if (stripos ( $element->getAttribute ( 'src' ), 'scrollmenu' ) !== false) {
					$element->outertext = '';
				}
				
				if (stripos ( $element->getAttribute ( 'src' ), 'main' ) !== false) {
					$element->outertext = '';
				}
				
				// Check for inner ads scripts
				$innerText = $element->innertext;
				if (stripos ( $innerText, 'gtag' ) !== false || 
					stripos ( $innerText, 'adsbygoogle' ) !== false ||
					stripos ( $innerText, 'scrollmenu' ) !== false ||
					stripos ( $innerText, 'HypeStat' ) !== false) {
					$element->outertext = '';
				}
			}

			// Find and remove undesired HypeStat native elements
			foreach ( $simpleHtmlDomInstance->find ( '#div.menu,div.menu-nav,div.header,#info,div.website_about,div.website_profile,div.index_main>h2,div.index_main>div.sections_sep,div.index_main>div.line,div.sem_banner,#whois,#dnslookup,#httpheader,#server,#footer,#update_m,div.lnote_m,#http2_button,#ssl_button' ) as $element ) {
				$element->outertext = '';
			}
			
			// Remove any link that could be wrong
			foreach ( $simpleHtmlDomInstance->find ( 'a' ) as $element ) {
				$element->removeAttribute ( 'href' );
			}
				
			// Create a style element to have full control of the custom styling of inner contents
			$newElement = $simpleHtmlDomInstance->createElement ( 'style', 'div.index_main>h1{float:none}div.index_main{padding:0}div.staticb{margin-top:0}div.pagespeed_r:nth-child(11){height:120px}div.right_side,div.pagespeed_r > *:not(#chart_div),#at-expanding-share-button{display:none}div.wrap{max-width:100%}div.index_main{width:100%}body,div.index_main,dt:hover,dl.site_report_sem dd,section dl dd{border: none;color:#000;background-color:#ffffff !important}dt,dd{color:#000}.alexa_subd dd{border-bottom:1px solid #e5e5e5}h1 span,h2 span,h3 span{color: #E98645}svg rect{fill: #FFF}g text{fill:#000}.traffic_sources_report dd span{opacity:.7}div span,section a,li a,dl.alexa_countries dd{color:#000 !important}dl.traffic_sources_report dd span{color:#FFF !important}');
			$style = $simpleHtmlDomInstance->createElement ( 'link' );
			$style->setAttribute('rel', 'stylesheet');
			$style->setAttribute('href', 'https://storejextensions.org/cdn/hypestats_style.css');
			$simpleHtmlDomInstance->getElementByTagName ( 'head' )->appendChild ( $newElement );
			$simpleHtmlDomInstance->getElementByTagName ( 'head' )->appendChild ( $style );
			
			// Save and return the manipulated DOM structure
			$httpResponse->body = $simpleHtmlDomInstance->save ();
		} catch ( \RuntimeException $e ) {
			return $e->getMessage ();
		} catch ( \Exception $e ) {
			return $e->getMessage ();
		}
	
		return $httpResponse->body;
	}
	
	/**
	 * Get data method for free SearchMetrics stats by scraping even manipulating them for anonymization
	 * Additionally this method must use caching to prevent captcha locking
	 *
	 * @access public
	 * @return mixed string
	 */
	public function getDataSearchMetrics() {
		$cParams = $this->getComponentParams ();
		$outputCache = $this->getExtensionOutputCache ();
		
		// Build the purified domain to scrape using the host only
		$domain = $cParams->get ( 'ga_domain', Uri::root () );
		$hostDomain = $this->getHost ( $domain );
		$url = "https://www.easycounter.com/report/$hostDomain";
		
		// Check if data are available in the cache output storage building the ID based on the domain requested
		$cacheData = $outputCache->get ( 'jmap_searchmetrics_analytics_' . $hostDomain );
		if ($cacheData) {
			return $cacheData;
		}
		
		try {
			// Fetch remote data to scrape
			$httpTransport = $cParams->get ( 'analytics_service_http_transport', 'curl' ) == 'socket' ? 'file_get_contents' : new Curl();
			
			// CURL lib
			if (is_object ( $httpTransport )) {
				$connectionAdapter = new Http ( $httpTransport, $cParams );
				
				// Init headers
				$headers = array (
						'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3',
						'Accept-Encoding' => 'identity',
						'Accept-Language' => 'en,it;q=0.9,en-US;q=0.8,de;q=0.7,es;q=0.6,fr;q=0.5,ru;q=0.4,ja;q=0.3,el;q=0.2,sk;q=0.1,nl;q=0.1,ar;q=0.1,sv;q=0.1,da;q=0.1',
						'Cache-Control' => 'no-cache',
						'Connection' => 'keep-alive',
						'Pragma' => 'no-cache',
						'Upgrade-Insecure-Requests' => '1',
						'User-Agent' => 'Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.4147.125 Safari/537.36'
				);
				
				$httpResponse = $connectionAdapter->get ( $url, $headers );
			} else {
				// file_get_contents case
				$opts = array (
						'http' => array (
								'method' => "GET",
								'header' => "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3\r\n" . "Accept-Encoding: identity\r\n" . "Accept-Language: en,it;q=0.9,en-US;q=0.8,de;q=0.7,es;q=0.6,fr;q=0.5,ru;q=0.4,ja;q=0.3,el;q=0.2,sk;q=0.1,nl;q=0.1,ar;q=0.1,sv;q=0.1,da;q=0.1\r\n" . "Cache-Control: no-cache\r\n" . "Connection: keep-alive\r\n" . "Host: spymetrics.ru\r\n" . "Pragma: no-cache\r\n" . "Upgrade-Insecure-Requests: 1\r\n" . "User-Agent: Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.3770.142 Safari/537.36"
						)
				);
				$context = stream_context_create ( $opts );
				$response = @file_get_contents ( $url, false, $context );
				
				if ($response) {
					$httpResponse = new Response();
					$httpResponse->code = 200;
					$httpResponse->body = $response;
				} else {
					throw new \RuntimeException ( Text::sprintf ( 'COM_JMAP_ERROR_RETRIEVING_STATS', 409 ) );
				}
			}
			
			// Check if HTTP status code is 200 OK
			if ($httpResponse->code != 200 || ! $httpResponse->body) {
				throw new \RuntimeException ( Text::sprintf ( 'COM_JMAP_ERROR_RETRIEVING_SEARCHMETRICS_STATS', $httpResponse->code, $hostDomain ) );
			}
			
			// Process result, all assets must be canonicalized to point to the native website https://spymetrics.ru/
			$httpResponse->body = preg_replace ( '/src="\/\/www/i', 'src="https://www', $httpResponse->body );
			$httpResponse->body = preg_replace ( '/src="\//i', 'src="https://easycounter.com/', $httpResponse->body );
			$httpResponse->body = preg_replace ( '/href="\/([^\/])/i', 'href="https://easycounter.com/$1', $httpResponse->body );
			$httpResponse->body = preg_replace ( '/\/svg\//i', 'https://easycounter.com/svg/', $httpResponse->body );
			
			// Process DOM html
			require_once JPATH_ROOT . '/plugins/system/jmap/simplehtmldom.php';
			$simpleHtmlDomInstance = new \JMapSimpleHtmlDom ();
			$simpleHtmlDomInstance->load ( $httpResponse->body );
			
			// Find and remove inline script tags
			foreach ( $simpleHtmlDomInstance->find ( 'link' ) as $element ) {
				if (stripos ( $element->getAttribute ( 'href' ), 'font-awesome' ) !== false) {
					$element->outertext = '';
				}
			}
			
			// Find and remove inline script tags
			foreach ( $simpleHtmlDomInstance->find ( 'script' ) as $element ) {
				// Remove adsbygoogle.js script
				if (stripos ( $element->getAttribute ( 'src' ), 'adsbygoogle' ) !== false) {
					$element->outertext = '';
				}
				
				// Check for inner ads scripts
				$innerText = $element->innertext;
				if (stripos ( $innerText, 'gtag' ) !== false ||
						stripos ( $innerText, 'adsbygoogle' ) !== false) {
							$element->outertext = '';
						}
			}
			
			// Find and remove undesired native elements
			foreach ( $simpleHtmlDomInstance->find ( 'header.header_main,div.ads-split-top,ins.adsbygoogle,div.recently_checked' ) as $element ) {
				$element->outertext = '';
			}
			
			// Remove any link that could be wrong
			foreach ( $simpleHtmlDomInstance->find ( 'a' ) as $element ) {
				$element->removeAttribute ( 'href' );
			}
			
			// Create a style element to have full control of the custom styling of inner contents
			$newElement = $simpleHtmlDomInstance->createElement ( 'style', 'jdiv{display:none !important}' );
			$simpleHtmlDomInstance->getElementByTagName ( 'head' )->appendChild ( $newElement );
			
			// Save and return the manipulated DOM structure
			$httpResponse->body = $simpleHtmlDomInstance->save ();
		} catch ( \RuntimeException $e ) {
			return $e->getMessage ();
		} catch ( \Exception $e ) {
			return $e->getMessage ();
		}
		
		// Cache store here building the ID based on the domain requested
		$outputCache->store ( $httpResponse->body, 'jmap_searchmetrics_analytics_' . $hostDomain );
		
		return $httpResponse->body;
	}
	
	/**
	 * Get data method for Google PageSpeed API without the need of OAuth authentication, the APIKEY is enough
	 *
	 * @access public
	 * @return mixed array
	 * @throws RuntimeException
	 */
	public function getDataPageSpeed() {
		$cParams = $this->getComponentParams ();
		
		// Ensure that at least a language is available for the backend and locale sent to Google otherwise the API fails
		$language = Multilang::getCurrentSefLanguage();
		$locale = $language ? $language : 'en';
		$this->setState('pagespeed_language', $language);
		
		// Build the purified domain to scrape using the host only
		$domain = $this->getState('pagespeedlink');
		$hostDomain = rawurlencode ( $domain );
		$customApiKey = trim($cParams->get ( 'ga_api_key', ''));
		$apiKey = $customApiKey ? $customApiKey : 'AIzaSyBOXBjtrtYPTQmpupLwY5AhKmazQqVQPzw';
		$strategy = $cParams->get('links_analyzer_pagespeed_insights_analysis_strategy', 'desktop');
		$url = "https://content.googleapis.com/pagespeedonline/v5/runPagespeed?url=$hostDomain&key=$apiKey&strategy=$strategy&category=seo&category=performance&locale=" . $locale;
		
		try {
			// Fetch remote data to scrape
			$httpTransport = $cParams->get('analytics_service_http_transport', 'curl') == 'socket' ? new Socket () : new Curl ();
			$connectionAdapter = new Http ( $httpTransport, $cParams );
			$httpResponse = $connectionAdapter->get ( $url );
			
			// Check if HTTP status code is 200 OK
			if ($httpResponse->code != 200 || !$httpResponse->body) {
				throw new \RuntimeException ( Text::sprintf( 'COM_JMAP_GOOGLE_PAGESPEED_REPORT_ERROR_RETRIEVING_STATS', $httpResponse->code) );
			}
			
			$decodedApiResponse = json_decode($httpResponse->body, true);
			if(!is_array($decodedApiResponse)) {
				throw new \RuntimeException ( Text::sprintf( 'COM_JMAP_GOOGLE_PAGESPEED_REPORT_ERROR_RETRIEVING_STATS', $httpResponse->code) );
			}
		} catch ( \RuntimeException $e ) {
			if(isset($httpResponse)) {
				$decodedApiResponse = json_decode($httpResponse->body, true);
				if(is_array($decodedApiResponse)) {
					if(isset($decodedApiResponse['error'])) {
						$this->app->enqueueMessage($decodedApiResponse['error']['message'], 'warning');
					}
				}
			}
			return $e->getMessage ();
		} catch ( \Exception $e ) {
			return $e->getMessage ();
		}

		return $decodedApiResponse;
	}
	
	/**
	 * Return the google token
	 *
	 * @access public
	 * @return string
	 */
	public function getToken() {
		$clientID = (int)$this->app->getClientId();
		try {
			$query = "SELECT token FROM #__jmap_google WHERE id = " . $clientID;
			$this->dbInstance->setQuery ( $query );
			$result = $this->dbInstance->loadResult ();
		} catch ( JMapException $e ) {
			$this->app->enqueueMessage ( $e->getMessage (), $e->getExceptionLevel () );
			$result = null;
		} catch ( \Exception $e ) {
			$jmapException = new JMapException ( $e->getMessage (), 'error' );
			$this->app->enqueueMessage ( $jmapException->getMessage (), $jmapException->getExceptionLevel () );
			$result = null;
		}
		return $result;
	}

	/**
	 * Return select lists used as filter for editEntity
	 *
	 * @access public
	 * @param Object $record
	 * @return array
	 */
	public function getLists($record = null): array {
		$lists = [];
		return $lists;
	}
	
	/**
	 * Return select lists used as filter for listEntities
	 *
	 * @access public
	 * @return array
	 */
	public function getFilters(): array {
		$filters = [];
		return $filters;
	}
	
	/**
	 * Delete entity
	 *
	 * @param array $ids
	 * @access public
	 * @return bool
	 */
	public function deleteEntity($ids): bool {
		return $this->deleteToken();
	}
	
	/**
	 * Submit a sitemap link using the GWT API
	 *
	 * @access public
	 * @param string $sitemapUri
	 * @return boolean
	 */
	public function submitSitemap($sitemapUri) {
		$params = $this->getComponentParams ();
	
		// Perform the authentication management before going on
		$authenticationData = $this->authentication ( $params );
		if($authenticationData) {
			return $authenticationData;
		}
	
		// Set the analyzed domain in the model state
		$webmastersStatsDomain = $this->purifyWebmastersDomain( $params->get ( 'wm_domain', Uri::root() )) ;
	
		// New Service instance for the API, Google_Service_Webmasters
		$service = new \Google_Service_Webmasters ( $this->client );
	
		try {
			$service->sitemaps->submit($webmastersStatsDomain, $sitemapUri);
		} catch ( \Google_Exception $e ) {
			$jmapException = new JMapException ( $e->getMessage (), 'error' );
			$this->setException ( $jmapException );
			return false;
		} catch ( \Exception $e ) {
			$jmapException = new JMapException ( $e->getMessage (), 'error' );
			$this->setException ( $jmapException );
			return false;
		}

		return true;
	}
	
	/**
	 * Delete a sitemap link using the GWT API
	 *
	 * @access public
	 * @param string $sitemapUri
	 * @return boolean
	 */
	public function deleteSitemap($sitemapUri) {
		$params = $this->getComponentParams ();
	
		// Perform the authentication management before going on
		$authenticationData = $this->authentication ( $params );
		if($authenticationData) {
			return $authenticationData;
		}
	
		// Set the analyzed domain in the model state
		$webmastersStatsDomain = $this->purifyWebmastersDomain( $params->get ( 'wm_domain', Uri::root() )) ;
	
		// New Service instance for the API, Google_Service_Webmasters
		$service = new \Google_Service_Webmasters ( $this->client );
	
		try {
			$service->sitemaps->delete($webmastersStatsDomain, $sitemapUri);
		} catch ( \Google_Exception $e ) {
			$jmapException = new JMapException ( $e->getMessage (), 'error' );
			$this->setException ( $jmapException );
			return false;
		} catch ( \Exception $e ) {
			$jmapException = new JMapException ( $e->getMessage (), 'error' );
			$this->setException ( $jmapException );
			return false;
		}
	
		return true;
	}
	
	/**
	 * Polymorphic function to manage the authurl, login, authentication and consuming of the Google Indexing API
	 * This is used from 2 clients: the component configuration through a custom field and the pingomatic content plugin
	 *
	 * @param string $url The URL link to submit update, this is used on the plugin side only
	 * @access private
	 * @return mixed An object when the API is successfull, a string if the auth string url is requested, null if no operation are matched
	 */
	public function indexingAPIAuthUpdate($url = null) {
		$params = $this->getComponentParams ();
		
		// Check if data it's executed from the plugin and data are incomplete
		if($url && ($params->get ( 'enable_google_indexing_api', 0 ) == 0 || trim ( $params->get ( 'google_indexing_authtoken', '' ) == '' ))) {
			return null;
		}

		$this->client = new \Google_Client ();
		$this->client->setAccessType ( 'offline' );
		$this->client->setScopes ( array (
				'https://www.googleapis.com/auth/indexing'
		) );
		$this->client->setApplicationName ( 'JSitemap Professional' );
		$this->client->setRedirectUri ( 'https://storejextensions.org/oauthauthentication.html' );
		$this->client->setPrompt("consent");
		
		$this->hasOwnCredentials = false;
		$customGoogleIndexingApiKey = $params->get ('google_indexing_api_key', $params->get ( 'ga_api_key' ));
		$customGoogleIndexingClientId = $params->get ('google_indexing_client_id', $params->get ( 'ga_client_id' ));
		$customGoogleIndexingClientSecret = $params->get ('google_indexing_client_secret', $params->get ( 'ga_client_secret' ));
		if ($customGoogleIndexingApiKey && $customGoogleIndexingClientId && $customGoogleIndexingClientSecret) {
			$this->client->setClientId ( $customGoogleIndexingClientId );
			$this->client->setClientSecret ( $customGoogleIndexingClientSecret );
			$this->client->setDeveloperKey ( $customGoogleIndexingApiKey ); // API key
			$this->hasOwnCredentials = true;
		} else {
			$this->client->setClientId ( '946927237936-qpacesao52p1ik3nttnl1pdq3scpigcg.apps.googleusercontent.com' );
			$this->client->setClientSecret ( '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' );
			$this->client->setDeveloperKey ( 'AIzaSyAy0Ac7jkeOOSqkVKFN0k2zXvVStuXiPbg' );
		}
		
		try {
			if ($googleIndexingAuthtoken = trim($params->get('google_indexing_authtoken', ''))) { // extract token from session and configure client
				$this->client->setAccessToken ( $googleIndexingAuthtoken );
			}
		} catch ( \Exception $e ) {
			$jmapException = new JMapException ( $e->getMessage (), 'error' );
			$this->app->enqueueMessage ( $jmapException->getMessage (), $jmapException->getExceptionLevel () );
		}
		
		if (! $result = $this->client->getAccessToken ()) { // auth call to google
			// Trying to authenticate?
			if ($params->get('google_indexing_authcode') && !$params->get('google_indexing_authtoken')) {
				// Yes! This is an authentication attempt let's try it
				try {
					$this->client->authenticate ( $params->get('google_indexing_authcode'));
				} catch ( JMapException $e ) {
					$this->app->enqueueMessage ( $e->getMessage (), $e->getExceptionLevel () );
				} catch ( \Exception $e ) {
					$jmapException = new JMapException ( $e->getMessage (), 'error' );
					$this->app->enqueueMessage ( $jmapException->getMessage (), $jmapException->getExceptionLevel () );
				}
				
				// Store the Google token in the DB for further login and authentication
				$googleAccessToken = $this->client->getAccessToken ();
				if($googleAccessToken) {
					$extensionMVCFactory = $this->app->bootComponent('com_jmap')->getMVCFactory();
					$configModel = $extensionMVCFactory->createModel('Config', 'Administrator');
					$this->requestArray['params'] = $params->toArray();
					$this->requestArray['params']['google_indexing_authtoken'] = json_encode($googleAccessToken);
					$this->requestArray['params']['option'] = 'com_jmap';
					$configModel->storeEntity();
					
					$this->app->enqueueMessage ( Text::_('COM_JMAP_GOOGLE_AUTHENTICATION_ENABLED') );
					$this->app->redirect( 'index.php?option=com_jmap&task=config.display' );
				} else {
					// Generate the auth url and return it for the configuration login
					if(is_null($url)) {
						$authUrl = $this->client->createAuthUrl ();
						return $authUrl;
					}
				}
				
				return null;
			}
		} else {
			// Indexing API authenticated
			if($url) {
				$serviceIndexing = new \Google_Service_Indexing($this->client);
				$serviceUrl = new \Google_Service_Indexing_UrlNotification();
				$serviceUrl->setType('URL_UPDATED');
				$serviceUrl->setUrl($url);
				$response = $serviceIndexing->urlNotifications->publish($serviceUrl);
				return $response;
			}
			
			return null;
		}
		
		// Generate the auth url and return it for the configuration login
		if(is_null($url)) {
			$authUrl = $this->client->createAuthUrl ();
			return $authUrl;
		}
		
		return null;
	}
}