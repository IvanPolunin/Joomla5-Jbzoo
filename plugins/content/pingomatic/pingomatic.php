<?php
/**
 * @author Joomla! Extensions Store
 * @package JMAP::plugins::content
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\Event;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\SiteRouter;
use Joomla\Registry\Registry;
use JExtstore\Component\JMap\Administrator\Framework\Language\Multilang as JMapMultilang;
use JExtstore\Component\JMap\Administrator\Framework\Http as JMapHttp;
use JExtstore\Component\JMap\Administrator\Framework\Http\Transport\Curl as JMapHttpTransportCurl;
use JExtstore\Component\JMap\Administrator\Framework\Http\Transport\Socket as JMapHttpTransportSocket;
use JExtstore\Component\JMap\Administrator\Framework\Pinger\Weblog as JMapPingerWeblog;

/**
 * Observer class notified on events <<testable_behavior>>
 *
 * @author Joomla! Extensions Store
 * @package JMAP::plugins::content
 * @since 3.0
 */
class PlgContentPingomatic extends CMSPlugin implements SubscriberInterface {
	/**
	 * @access private
	 * @var boolean
	 */
	private $isPluginStopped;
	
	/**
	 * Plugin execution context
	 *
	 * @access private
	 * @var array
	 */
	private $context;
	
	/**
	 * Plugin Joomla execution context
	 *
	 * @access private
	 * @var string
	 */
	private $jcontext;
	
	/**
	 * Curl adapter reference
	 *
	 * @access private
	 * @var Object
	 */
	private $curlAdapter;
	
	/**
	 * Pinger class for webblog services such as Pingomatic
	 *
	 * @access private
	 * @var Object
	 */
	private $pingerInstance;
	
	/**
	 * Component config params
	 *
	 * @access private
	 * @var Object
	 */
	private $cParams;
	
	/**
	 * Adapters mapping based on context and route helper
	 *
	 * @access private
	 * @var array
	 */
	private $adaptersMapping;
	
	/**
	 * Single article routed link
	 *
	 * @access private
	 * @var string
	 */
	private $singleArticleRouted;
	
	/**
	 * Application reference
	 *
	 * @access protected
	 * @var Object
	 */
	protected $appInstance;
	
	/**
	 * Database connector
	 *
	 * @access protected
	 * @var Object
	 */
	protected $dbInstance;
	
	/**
	 * Load the CURL library needed from JMap Framework
	 *
	 * @access private
	 * @return boolean
	 */
	private function loadCurlLib() {
		// Check lib availability and load it
		if (file_exists ( JPATH_ROOT . '/administrator/components/com_jmap/Framework/Http/Http.php' )) {
			include_once (JPATH_ROOT . '/administrator/components/com_jmap/Framework/Http/Http.php');
			include_once (JPATH_ROOT . '/administrator/components/com_jmap/Framework/Http/Response.php');
			include_once (JPATH_ROOT . '/administrator/components/com_jmap/Framework/Http/Transport.php');
			include_once (JPATH_ROOT . '/administrator/components/com_jmap/Framework/Http/Transport/Curl.php');
			include_once (JPATH_ROOT . '/administrator/components/com_jmap/Framework/Http/Transport/Socket.php');
			
			// Instantiate dependency
			$this->curlAdapter = new JMapHttp ( new JMapHttpTransportCurl (), $this->cParams );
			
			return true;
		}
		
		return false;
	}
	
	/**
	 * Load the Pinger lib to ping weblog services
	 *
	 * @access private
	 * @return boolean
	 */
	private function loadPingerLib() {
		// Check lib availability and load it
		if (file_exists ( JPATH_ROOT . '/administrator/components/com_jmap/Framework/Pinger/Weblog.php' )) {
			include_once (JPATH_ROOT . '/administrator/components/com_jmap/Framework/Pinger/Weblog.php');

			// Instantiate dependency
			$this->pingerInstance = new JMapPingerWeblog();

			return true;
		}
	
		return false;
	}
	
	/**
	 * Send auto ping for this article URL available in the ping table using the curl adapter
	 *
	 * @access private
	 * @return boolean
	 */
	private function autoSendPing($title, $url, $rssurl, $services) {
		// Load safely the CURL JMap lib without autoloader
		if ($this->loadCurlLib ()) {
			// Array of POST vars
			$post = array ();
			$post ['title'] = $title;
			$post ['blogurl'] = $url;
			$post ['rssurl'] = $rssurl;
			$post = array_merge ( $post, ( array ) $services );
			
			// Post HTTP request to Pingomatic
			$httpResponse = $this->curlAdapter->post ( 'https://pingomatic.com/ping/', $post, array (), 5, 'JSitemap Professional Pinger' );
			
			// Check if HTTP status code is OK
			if ($httpResponse->code != 200) {
				throw new \RuntimeException ( Text::_ ( 'COM_JMAP_AUTOPING_ERROR_HTTP_STATUSCODE' ) );
			}
		}
		
		return true;
	}
	
	/**
	 * New router Joomla management
	 */
	private function findItemidNewRouter($link, $siteRouter) {
		// 1° STEP: build the route using the new router
		$articleMenuRoutedUriObject = $siteRouter->build ( $link );
	
		// Add compatibility support for no rewritten links
		$path = $articleMenuRoutedUriObject->getPath();
		$path = str_replace(array('/index.php', '/index.php/'), '/', $path);
		$articleMenuRoutedUriObject->setPath($path);
		
		$path = $articleMenuRoutedUriObject->getPath();
		$path = '/administrator' . $path;
		$path = str_replace(array('/index.php', '/index.php/'), '/', $path);
		$articleMenuRoutedUriObject->setPath($path);
		$originalBackendLanguageTag = $this->appInstance->getLanguage()->getTag();
		
		// 2° STEP: parse back the URL now finally including the routed Itemid
		Factory::getContainer()->get(\Joomla\CMS\Application\SiteApplication::class)->loadLanguage();
		// Avoid parse uri redirects when saving an article
		if ($this->appInstance->get('force_ssl') >= 1) {
			$articleMenuRoutedUriObject->setScheme('https');
		}
		$articleMenuParsedUriArray = $siteRouter->parse ($articleMenuRoutedUriObject);
		
		$jLang = Factory::getContainer()->get(\Joomla\CMS\Language\LanguageFactoryInterface::class)->createLanguage($originalBackendLanguageTag, false);
		Factory::$language = $jLang;
		$jLang->load('com_jmap', JPATH_ADMINISTRATOR . '/components/com_jmap', 'en-GB', true, true);
		if($originalBackendLanguageTag != 'en-GB') {
			$jLang->load('com_jmap', JPATH_ADMINISTRATOR, $originalBackendLanguageTag, true, false);
			$jLang->load('com_jmap', JPATH_ADMINISTRATOR . '/components/com_jmap', $originalBackendLanguageTag, true, false);
		}
		
		if(isset($articleMenuParsedUriArray['Itemid'])) {
			$link .= '&Itemid=' . $articleMenuParsedUriArray['Itemid'];
		}
	
		return $link;
	}
	
	/**
	 * Route save single article to the corresponding SEF link
	 *
	 * @access private
	 * @return string
	 */
	private function routeArticleToSefMenu($articleID, $catID, $language, $article) {
		// Try to route the article to a single article menu item view
		$helperRouteClass = $this->context ['class'];
		$classMethod = $this->context ['method'];
		$siteRouter = Factory::getContainer()->has('SiteRouter') ? Factory::getContainer()->get('SiteRouter'): SiteRouter::getInstance('site');
		
		// Patch for K2, ensure to always evaluate the article language, override the Factory language instance
		if($this->jcontext == 'com_k2.item' && $language != '*' && JMapMultilang::isEnabled ()) {
			$originalLanguage = $this->appInstance->getLanguage();
			$lang = Factory::getContainer()->get(\Joomla\CMS\Language\LanguageFactoryInterface::class)->createLanguage($language, $language);
			Factory::$language = $lang;
		}
		
		// Route helper native by component, com_content, com_k2
		if (! isset ( $this->context ['routing'] )) {
			$articleHelperRoute = $helperRouteClass::$classMethod ( $articleID, $catID, $language );
		} else {
			// Route helper universal JSitemap, com_zoo
			$articleHelperRoute = $helperRouteClass::$classMethod ( $article->option, $article->view, $article->id, $article->catid, null );
			
			// Check if the Zoo item has been routed to the view frontpage and if the linked app matches the correct one. Apps can be multiple.
			if ($articleHelperRoute) {
				$query = "SELECT " .
						 $this->dbInstance->quoteName('link') . "," .
						 $this->dbInstance->quoteName('params') .
						 "\n FROM " . $this->dbInstance->quoteName('#__menu') .
						 "\n WHERE " . $this->dbInstance->quoteName('id') . " = " . $this->dbInstance->quote($articleHelperRoute);
				$menuCurrentObject = $this->dbInstance->setQuery($query)->loadObject();
				if($menuCurrentObject->link == 'index.php?option=com_zoo&view=frontpage&layout=frontpage') {
					// Check if the current application ID of the item matches the menu item id that has been routed
					$currentAppId = $this->appInstance->getInput()->get('changeapp');
					$decodedParams = json_decode($menuCurrentObject->params);
					$menuAppId = $decodedParams->application;
					if($currentAppId != $menuAppId) {
						$query = "SELECT " .
								 $this->dbInstance->quoteName('id') . "," .
								 $this->dbInstance->quoteName('params') .
								 "\n FROM " . $this->dbInstance->quoteName('#__menu') .
								 "\n WHERE " . $this->dbInstance->quoteName('link') . " = " . $this->dbInstance->quote('index.php?option=com_zoo&view=frontpage&layout=frontpage');
						$menusZooFrontpage = $this->dbInstance->setQuery($query)->loadObjectList();
						foreach ($menusZooFrontpage as $menuZooFrontpage) {
							$thisMenuParams = json_decode($menuZooFrontpage->params);
							$thisMenuAppId = $thisMenuParams->application;
							if($thisMenuAppId == $currentAppId) {
								$articleHelperRoute = $menuZooFrontpage->id;
								break;
							}
						}
					}
				}
						
				$articleHelperRoute = '?Itemid=' . $articleHelperRoute;
			}
		}
		
		// Extract Itemid from the helper routed URL
		$extractedItemid = preg_match ( '/Itemid=\d+/i', $articleHelperRoute, $result );
		
		// Joomla new router
		if(stripos($articleHelperRoute, 'com_content') && !$extractedItemid) {
			$articleRouteWithItemid = $this->findItemidNewRouter ($articleHelperRoute, $siteRouter);
			$extractedItemid = preg_match ( '/Itemid=\d+/i', $articleRouteWithItemid, $result );
		}
		
		if (isset ( $result [0] )) {
			// Patch for K2, ensure to always evaluate the article language, override the Factory language instance
			if($this->jcontext == 'com_k2.item' && $language != '*' && JMapMultilang::isEnabled ()) {
				$result [0] .= '&lang='. $language;
				$articleHelperRoute .= '&lang='. $language;
			}
			
			// Get uri instance avoidng subdomains already included in the routing chunks
			$uriInstance = Uri::getInstance();
			$resourceLiveSite = rtrim($uriInstance->getScheme() . '://' . $uriInstance->getHost(), '/');

			$extractedItemid = $result [0];
			$articleMenuRouted = $siteRouter->build ( '?' . $extractedItemid )->toString ();
			
			// Store a single article routed URL so that i can be used at a later stage for the autoping feature only
			if($this->cParams->get('default_autoping_single_article', 1)) {
				if(isset($this->context['routing']) && $this->context['routing'] == 'jmap') {
					$this->singleArticleRouted = $siteRouter->build ( sprintf($this->context ['rawlink'], $articleID, $extractedItemid ) )->toString ();
				} else {
					$this->singleArticleRouted = $siteRouter->build ( $articleHelperRoute )->toString ();
				}
				
				// Integration override with sh404sef if enabled for single article link
				if (defined('SH404SEF_IS_RUNNING') && class_exists('Sh404sefHelperGeneral')) {
					if($this->jcontext == 'com_zoo.item') {
						$articleHelperRoute = sprintf($this->context ['rawlink'], $articleID, $extractedItemid );
					}
					
					// Sh404Sef processing routing
					$this->singleArticleRouted = \Sh404sefHelperGeneral::getSefFromNonSef($articleHelperRoute . '&' . $extractedItemid, true, false);
					
					// Prevent to append alias at the later stage
					if($this->jcontext == 'com_k2.item') {
						$this->sh404sefUrl = true;
					}
				}
			}
			
			// Patch for K2, restore the original Factory language instance
			if($this->jcontext == 'com_k2.item' && $language != '*' && JMapMultilang::isEnabled ()) {
				Factory::$language = $originalLanguage;
			}
			
			// Check if multilanguage is enabled
			if (JMapMultilang::isEnabled ()) {
				$defaultLanguage = ComponentHelper::getParams('com_languages')->get('site');
				if ($language != '*') {
					// New language manager instance
					$languageManager = JMapMultilang::getInstance ( $language );
				} else {
					// Get the default language tag
					// New language manager instance
					$languageManager = JMapMultilang::getInstance ( $defaultLanguage );
				}
				
				// Extract the language tag
				$selectedLanguage = $languageManager->getTag();
				$languageFilterPlugin = PluginHelper::getPlugin('system', 'languagefilter');
				$languageFilterPluginParams = new Registry($languageFilterPlugin->params);
				if($defaultLanguage == $selectedLanguage && $languageFilterPluginParams->get('remove_default_prefix', 0)) {
					$articleMenuRouted = str_replace ( '/administrator', '', $articleMenuRouted );
					if($this->singleArticleRouted) {
						$this->singleArticleRouted = str_replace ( '/administrator', '', $this->singleArticleRouted );
					}
				} else {
					$localeTag = $languageManager->getLocale ();
					$sefTag = $localeTag [4];
					$articleMenuRouted = str_replace ( '/administrator', '/' . $sefTag, $articleMenuRouted );
					if($this->singleArticleRouted) {
						$this->singleArticleRouted = str_replace ( '/administrator', '/' . $sefTag, $this->singleArticleRouted );
					}
				}
			} else {
				$articleMenuRouted = str_replace ( '/administrator', '', $articleMenuRouted );
			}
			$articleMenuRouted = preg_match('/http/i', $articleMenuRouted) ? $articleMenuRouted : $resourceLiveSite . '/' . ltrim($articleMenuRouted, '/');
			if($this->singleArticleRouted) {
				$this->singleArticleRouted = preg_match('/http/i', $this->singleArticleRouted) ? $this->singleArticleRouted : $resourceLiveSite . '/' . ltrim($this->singleArticleRouted, '/');
			}
			return $articleMenuRouted;
		} else {
			// Patch for K2, restore the original Factory language instance
			if($this->jcontext == 'com_k2.item' && $language != '*' && JMapMultilang::isEnabled ()) {
				Factory::$language = $originalLanguage;
			}
			
			// Check if routing is valid otherwise throw exception
			throw new \RuntimeException ( Text::_ ( 'COM_JMAP_AUTOPING_ERROR_NOSEFROUTE_FOUND' ) );
		}
	}
	
	/**
	 * Method to be called everytime an article in backend is saved,
	 * it's responsible to check and find if the SEF link of the article has been
	 * added to the Pingomatic table, and if found submit the ping form through CURL http adapter
	 *
	 * @subparam string $context The context of the content passed to the plugin (added in 1.6)
	 * @subparam object $article A Table Content object
	 * @subparam boolean $isNew If the content is just about to be created
	 *        	
	 * @return boolean true if function not enabled, is in front-end or is new. Else true or false depending on success of save function.
	 */
	public function pingContent(Event $event) {
		// subparams: $context, $article, $isNew
		$arrayPost = [];
		$arguments = $event->getArguments();
		$context = isset($arguments[0]) ? $event->getArgument(0) : $event->getArgument('context');
		$article = isset($arguments[1]) ? $event->getArgument(1) : $event->getArgument('subject');
		$isNew = isset($arguments[2]) ? $event->getArgument(2) : $event->getArgument('isNew');
		$arrayPost = isset($arguments[3]) ? $event->getArgument(3) : $event->getArgument('data');
		
		// Avoid operations if plugin is executed in frontend
		if (! $this->cParams->get ( 'default_autoping', 0 ) && ! $this->cParams->get ( 'autoping', 0 ) && ! $this->cParams->get ( 'enable_google_indexing_api', 0 )) {
			return;
		}
		
		// Avoid operation if the plugin is stopped
		if($this->isPluginStopped) {
			return;
		}
		
		// Avoid pinging if the article is unpublished
		$now = new DateTime('now', new DateTimeZone('UTC'));
		$articlePublishUp = new DateTime($article->publish_up);
		$nowFormatted = $now->format('Y-m-d H:i:s');
		$articlePublishUpFormatted = $articlePublishUp->format('Y-m-d H:i:s');
		
		// Check also if a workflow transition is set
		if(isset($arrayPost['transition']) && $arrayPost['transition']) {
			$query = method_exists ( $this->dbInstance, 'createQuery' ) ? $this->dbInstance->createQuery () : $this->dbInstance->getQuery ( true );
			$query->select ( $this->dbInstance->quoteName('options') );
			$query->from ( $this->dbInstance->quoteName ( '#__workflow_transitions' ) );
			$query->where ( $this->dbInstance->quoteName ( 'id' ) . '=' . $this->dbInstance->quote ( $arrayPost['transition'] ) );
			$transitionOptions = $this->dbInstance->setQuery ( $query )->loadObject ();
			if($transitionOptions) {
				$transitionOptions = json_decode($transitionOptions->options);
				$article->state = $transitionOptions->publishing;
			}
		}
		if($context == 'com_content.article' && ($article->state != 1 || $articlePublishUpFormatted > $nowFormatted)) {
			return;
		}
		if($context == 'com_k2.item' && ($article->published != 1 || $articlePublishUpFormatted > $nowFormatted)) {
			return;
		}
		if($context == 'com_zoo.item' && ($this->appInstance->getInput()->get('state') != 1 || $articlePublishUpFormatted > $nowFormatted)) {
			return;
		}
		
		// Ensure to process only native Joomla articles
		if (array_key_exists ( $context, $this->adaptersMapping )) {
			// Store the Joomla context
			$this->jcontext = $context;
			
			// Extract the correct route helper
			$routeHelper = $this->adaptersMapping [$context] ['file'];
			// Include needed files for the correct multilanguage routing from backend to frontend of the save articles
			if (file_exists ( $routeHelper )) {
				include_once ($routeHelper);
				
				// Store the context for static class method call
				$this->context = $this->adaptersMapping [$context];
			}
			
			// Start HTTP submission process, manage users exceptions if debug is enabled
			try {
				// Try attempt to resolve the article to a single menu or container category SEF link
				$hasArticleMenuRoute = $this->routeArticleToSefMenu ( $article->id, $article->catid, $article->language, $article );
				
				// If article has been resolved, fetch pings URLs from jmap_pingomatic table and do lookup
				if ($hasArticleMenuRoute) {
					// Check if the auto Pingomatic ping based on records is enabled
					if($this->cParams->get ( 'autoping', 0 )) {
						$query = method_exists ( $this->dbInstance, 'createQuery' ) ? $this->dbInstance->createQuery () : $this->dbInstance->getQuery ( true );
						$query->select ( '*' );
						$query->from ( $this->dbInstance->quoteName ( '#__jmap_pingomatic' ) );
						$query->where ( $this->dbInstance->quoteName ( 'blogurl' ) . '=' . $this->dbInstance->quote ( $hasArticleMenuRoute ) );
						
						// Is there a found pinged link for this article scope?
						$foundPingUrl = $this->dbInstance->setQuery ( $query )->loadObject ();
						if ($foundPingUrl) {
							// Retrieve ping record info and submit form using CURL adapter, else do nothing
							$titleToPing = $foundPingUrl->title;
							$urlToPing = $foundPingUrl->blogurl;
							$rssUrlToPing = $foundPingUrl->rssurl;
							$servicesToPing = json_decode ( $foundPingUrl->services );
							
							// If ping is OK update the pinging status and datetime in the Pingomatic table
							if ($this->autoSendPing ( $titleToPing, $urlToPing, $rssUrlToPing, $servicesToPing )) {
								$query = method_exists ( $this->dbInstance, 'createQuery' ) ? $this->dbInstance->createQuery () : $this->dbInstance->getQuery ( true );
								$query->update ( $this->dbInstance->quoteName ( '#__jmap_pingomatic' ) );
								$query->set ( $this->dbInstance->quoteName ( 'lastping' ) . ' = ' . $this->dbInstance->quote ( date ( 'Y-m-d H:i:s' ) ) );
								$query->where ( $this->dbInstance->quoteName ( 'id' ) . '=' . ( int ) $foundPingUrl->id );
								$this->dbInstance->setQuery ( $query )->execute ();
								
								// Everything complete fine, ping sent and updated!
								if ($this->cParams->get ( 'enable_debug', 0 )) {
									$this->appInstance->enqueueMessage ( Text::_ ( 'COM_JMAP_AUTOPING_COMPLETED_SUCCESFULLY' ), 'notice' );
								}
							}
						} else {
							// Display post message after save only if debug is enabled
							if ($this->cParams->get ( 'enable_debug', 0 )) {
								$this->appInstance->enqueueMessage ( Text::_ ( 'COM_JMAP_AUTOPING_ERROR_NOPING_CONTENT_FOUND' ), 'notice' );
							}
						}
					}
					
					// Check if the default Pingomatic/Weblogs ping is enabled
					if($this->cParams->get ( 'default_autoping', 0 )) {
						// Always submit autoping using XMLRPC web services
						if($this->loadPingerLib()) {
							// Get a single article routed URL override
							if($this->cParams->get('default_autoping_single_article', 1)) {
								$hasArticleMenuRoute = $this->singleArticleRouted;
							}
							
							// Normalize language URL if needed, remove untraslated query string
							$hasArticleMenuRoute = preg_replace('/\?(.)*$/i', '', $hasArticleMenuRoute);
							
							// K2 management
							if($this->cParams->get('default_autoping_single_article', 1)) {
								if($context == 'com_k2.item' && !isset($this->sh404sefUrl)) {
									$hasArticleMenuRoute .= '-' . $article->alias;
									// Check if the SEF suffix is enabled and correct the URL
									if($this->appInstance->get ( 'sef_suffix', 1 )) {
										$hasArticleMenuRoute = str_replace('.html', '', $hasArticleMenuRoute) . '.html';
									}
								}
							}
							
							// Get debug state
							$debugEnabled = $this->cParams->get ( 'enable_debug', 0 );
							$pingomaticPinged = $this->pingerInstance->ping_ping_o_matic($article->title, $hasArticleMenuRoute);
							if($debugEnabled && $pingomaticPinged) {
								$this->appInstance->enqueueMessage ( Text::_( 'COM_JMAP_AUTOPING_DEFAULT_AUTOPING_SENT_PINGOMATIC' ), 'notice' );
							}

							$googlePinged = $this->pingerInstance->ping_google($article->title, $hasArticleMenuRoute);
							// Got a 403 Forbidden error for the first request, place a new request to have success
							if(!$googlePinged) {
								$googlePinged = $this->pingerInstance->ping_google($article->title, $hasArticleMenuRoute);
							}
							if($debugEnabled && $googlePinged) {
								$this->appInstance->enqueueMessage ( Text::_( 'COM_JMAP_AUTOPING_DEFAULT_AUTOPING_SENT_GOOGLE' ), 'notice' );
							}

							$weblogsPinged = $this->pingerInstance->ping_weblogs_com($article->title, $hasArticleMenuRoute);
							if($debugEnabled && $weblogsPinged) {
								$this->appInstance->enqueueMessage ( Text::_( 'COM_JMAP_AUTOPING_DEFAULT_AUTOPING_SENT_WEBLOGS' ), 'notice' );
							}
							
							$blogsPinged = $this->pingerInstance->ping_blo_gs($article->title, $hasArticleMenuRoute);
							if($debugEnabled && $blogsPinged) {
								$this->appInstance->enqueueMessage ( Text::_( 'COM_JMAP_AUTOPING_DEFAULT_AUTOPING_SENT_BLOGS' ), 'notice' );
							}
							
							if ($this->loadCurlLib ()) {
								$joomlaConfig = $this->appInstance->getConfig();
								$httpTransport = new JMapHttpTransportCurl ();
								$connectionAdapter = new JMapHttp ( $httpTransport, $this->cParams );
								$baiduPinged = $connectionAdapter->post ( 'https://chineseseoshifu.com/tools/submit-to-baidu.php', array('yourname'=>$article->title, 'email'=>$joomlaConfig->get('mailfrom'), 'url'=>$hasArticleMenuRoute) );
								
								if($debugEnabled && $baiduPinged->code == 200) {
									$this->appInstance->enqueueMessage ( Text::_( 'COM_JMAP_AUTOPING_DEFAULT_AUTOPING_SENT_BAIDU' ), 'notice' );
								}
								
								$indexNowBing = $connectionAdapter->get('https://www.bing.com/indexnow?url=' . $hasArticleMenuRoute . '&key=' . $this->cParams->get('indexnow_apikey', '28bcb027f9b443719ceac7cd30556c3c'));
								if($debugEnabled && $indexNowBing->code == 200) {
									$this->appInstance->enqueueMessage ( Text::_( 'COM_JMAP_AUTOPING_DEFAULT_AUTOPING_SENT_INDEXNOW_BING' ), 'notice' );
								}
								
								$indexNowYandex = $connectionAdapter->get('https://yandex.com/indexnow?url=' . $hasArticleMenuRoute . '&key=' . $this->cParams->get('indexnow_apikey', '28bcb027f9b443719ceac7cd30556c3c'));
								if($debugEnabled && $indexNowYandex->code == 202) {
									$this->appInstance->enqueueMessage ( Text::_( 'COM_JMAP_AUTOPING_DEFAULT_AUTOPING_SENT_INDEXNOW_YANDEX' ), 'notice' );
								}
							}
							
							if($debugEnabled) {
								$this->appInstance->enqueueMessage ( $hasArticleMenuRoute, 'notice' );
							}
						}
					}
					
					// Notify Google if the Indexing API integration is active and the login is available
					if($this->cParams->get ( 'enable_google_indexing_api', 0 )) {
						// Get debug state
						$debugEnabled = $this->cParams->get ( 'enable_debug', 0 );
						
						// Register autoloader prefix
						// Auto loader setup
						require_once JPATH_ADMINISTRATOR . '/components/com_jmap/Framework/Loader.php';
						$namespace = 'JExtstore\\Component\\JMap';
						JExtstore\Component\JMap\Administrator\Framework\Loader::setup ();
						JExtstore\Component\JMap\Administrator\Framework\Loader::registerNamespacePsr4 ( $namespace . '\Site', JPATH_ROOT . '/components/com_jmap' );
						JExtstore\Component\JMap\Administrator\Framework\Loader::registerNamespacePsr4 ( $namespace . '\Administrator', JPATH_ROOT . '/administrator/components/com_jmap' );
						
						// Composer autoloader
						require_once JPATH_ADMINISTRATOR. '/components/com_jmap/Framework/composer/autoload_real.php';
						\ComposerAutoloaderInitcb4c0ac1dedbbba2f0b42e9cdf4d93d7::getLoader();
						
						$extensionMVCFactory = $this->appInstance->bootComponent('com_jmap')->getMVCFactory();
						$googleModel = $extensionMVCFactory->createModel('Google', 'Administrator');
						
						$apiResponse = $googleModel->indexingAPIAuthUpdate($hasArticleMenuRoute);
						
						if($debugEnabled && is_object($apiResponse)) {
							$this->appInstance->enqueueMessage ( Text::sprintf( 'COM_JMAP_GOOGLE_INDEXING_API_SUCCESS', $hasArticleMenuRoute), 'notice' );
						}
					}
				}
			} catch ( \Exception $e ) {
				// Display post message after save only if debug is enabled
				if ($this->cParams->get ( 'enable_debug', 0 )) {
					$this->appInstance->enqueueMessage ( $e->getMessage (), 'notice' );
				}
			}
		}
	}
	
	/**
	 * Returns an array of events this subscriber will listen to.
	 *
	 * @return  array
	 *
	 * @since   4.0.0
	 */
	public static function getSubscribedEvents(): array {
		return [
				'onContentAfterSave' => 'pingContent'
		];
	}
	
	/**
	 * Override registers Listeners to the Dispatcher
	 * It allows to legacy stop a plugin execution based on the return value of its constructor
	 *
	 * @override
	 * @return  void
	 */
	public function registerListeners(?DispatcherInterface $dispatcher = null) {
		// Check if the plugin has not been stopped by the constructor
		if(!$this->isPluginStopped) {
			parent::registerListeners($dispatcher);
		}
	}
	
	/**
	 * Class Constructor 
	 * @access	protected
	 * @param	object	$subject The object to observe
	 * @param 	array   $config  An array that holds the plugin configuration
	 * @since	1.0
	 */
	public function __construct(& $subject, $config = array()) {
		parent::__construct ( $subject, $config );
		
		// Init application
		$this->appInstance = Factory::getApplication();
		
		// Init database
		$this->dbInstance = Factory::getContainer()->get('DatabaseDriver');
		
		// Load component config
		$this->cParams = ComponentHelper::getParams ( 'com_jmap' );
		
		// Avoid operations if plugin is not executed in backend
		if (!$this->appInstance->isClient ('administrator')) {
			$this->isPluginStopped = true;
			return;
		}
		
		// Avoid operation if not supported extension is detected
		if(!in_array($this->appInstance->getInput()->get('option'), array('com_content', 'com_k2', 'com_zoo'))) {
			$this->isPluginStopped = true;
			return;
		}
		
		
		if (file_exists ( JPATH_ROOT . '/administrator/components/com_jmap/Framework/Language/Multilang.php' )) {
			include_once (JPATH_ROOT . '/administrator/components/com_jmap/Framework/Language/Multilang.php');
		}
		
		$this->adaptersMapping = array (
				'com_content.article' => array (
						'file' => JPATH_ROOT . '/components/com_content/src/Helper/RouteHelper.php',
						'class' => '\\Joomla\\Component\\Content\\Site\\Helper\\RouteHelper',
						'method' => 'getArticleRoute' 
				),
				'com_k2.item' => array (
						'file' => JPATH_ROOT . '/components/com_k2/helpers/route.php',
						'class' => 'K2HelperRoute',
						'method' => 'getItemRoute' 
				),
				'com_zoo.item' => array (
						'routing' => 'jmap',
						'rawlink' => 'index.php?option=com_zoo&view=item&task=item&item_id=%s&%s',
						'file' => JPATH_ROOT . '/administrator/components/com_jmap/Framework/Route/Helper.php',
						'class' => '\JExtstore\Component\JMap\Administrator\Framework\Route\Helper',
						'method' => 'getItemRoute' 
				) 
		);
		
		// Manage partial language translations
		$jLang = Factory::getApplication()->getLanguage ();
		$jLang->load ( 'com_jmap', JPATH_ROOT . '/administrator/components/com_jmap', 'en-GB', true, true );
		if ($jLang->getTag () != 'en-GB') {
			$jLang->load ( 'com_jmap', JPATH_SITE, null, true, false );
			$jLang->load ( 'com_jmap', JPATH_SITE . '/administrator/components/com_jmap', null, true, false );
		}
	}
}