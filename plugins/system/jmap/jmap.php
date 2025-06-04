<?php
/**
 * @author Joomla! Extensions Store
 * @package JMAP::plugins::system
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
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Version;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Router\SiteRouter;
use Joomla\CMS\Version as JVersion;
use Joomla\CMS\Error\AbstractRenderer;
use Joomla\CMS\Router\Exception\RouteNotFoundException;
use Joomla\Registry\Registry;
use Joomla\Filter\OutputFilter;
use Joomla\String\StringHelper;
use JExtstore\Component\JMap\Administrator\Framework\Loader as JMapLoader;
use JExtstore\Component\JMap\Administrator\Framework\Language\Multilang as JMapMultilang;
use function GuzzleHttp\Psr7\uri_for;

/**
 * Observer class notified on events
 *
 * @author Joomla! Extensions Store
 * @package JMAP::plugins::system
 * @since 2.1
 */
class PlgSystemJMap extends CMSPlugin implements SubscriberInterface {
	/**
	 * @access private
	 * @var boolean
	 */
	private $isPluginStopped;
	
	/**
	 * Joomla config object
	 *
	 * @access private
	 * @var Object
	 */
	private $joomlaConfig;
	
	/**
	 * JSitemap config object
	 *
	 * @access private
	 * @var Object
	 */
	private $jmapConfig;
	
	/**
	 * JMap calculate URI
	 *
	 * @access private
	 * @var String
	 */
	private $jmapUri;
	
	/**
	 * App reference
	 *
	 * @access protected
	 * @var Object
	 */
	protected $appInstance;
	
	/**
	 * DB reference
	 *
	 * @access protected
	 * @var Object
	 */
	protected $dbInstance;
	
	/**
	 * Get a document object based on the container
	 * 
	 * @param boolean $reset
	 *
	 * @return Document object
	 */
	private function getDocument($reset = false) {
		$container = Factory::getContainer();
		
		// Build language object
		$conf = $this->appInstance->getConfig ();
		$locale = $conf->get ( 'language' );
		$debug = $conf->get ( 'debug_lang' );
		$languageInstance = $container->get ( \Joomla\CMS\Language\LanguageFactoryInterface::class )->createLanguage ( $locale, $debug );
		
		$input = $this->appInstance->getInput();
		$type = $input->get ( 'format', 'html', 'cmd' );
		
		// Build document object
		$version = new Version ();
		$attributes = array (
				'charset' => 'utf-8',
				'lineend' => 'unix',
				'tab' => "\t",
				'language' => $languageInstance->getTag (),
				'direction' => $languageInstance->isRtl () ? 'rtl' : 'ltr',
				'mediaversion' => $version->getMediaVersion ()
		);
		
		$documentInstance = $container->get ( \Joomla\CMS\Document\FactoryInterface::class )->createDocument ( $type, $attributes );
		
		$reflection = new \ReflectionProperty($this->appInstance, 'document');
		$reflection->setAccessible(true);
		
		if($reset) {
			$reflection->setValue($this->appInstance, null);
		} else {
			$reflection->setValue($this->appInstance, $documentInstance);
		}
		
		return $documentInstance;
	}
	
	/**
	 * Process content plugins
	 * 
	 * @access private
	 * @param string $custom404Text
	 * @param Object &$cParams
	 * @return string
	 */
	private function processContentPlugins($custom404Text, &$cParams) {
		// Process only if html mode is enabled and process plugins param is enabled
		if($cParams->get('custom_404_page_mode', 'html') == 'html' && $cParams->get('custom_404_process_content_plugins', 0)) {
			PluginHelper::importPlugin('content', null, true, $this->appInstance->getDispatcher());
			
			$dummyParams = new Registry();
			$elm = new \stdClass();
			$elm->text = $custom404Text;
			
			try {
				// Simulate document creation in this phase
				$doc = $this->getDocument();
				
				// Evaluate if native ContentPrepare Event exists, Joomla >= 5
				if(class_exists('\\Joomla\\CMS\\Event\\Content\\ContentPrepareEvent')) {
					$eventObject = new \Joomla\CMS\Event\Content\ContentPrepareEvent ( 'onContentPrepare', [
							'context' => 'com_content.article',
							'subject' => &$elm,
							'params'  => &$dummyParams,
							'page'    => 0
					]);
				} else {
					// Generic Joomla\Event\Event, Joomla 4
					$eventObject = new Event ( 'onContentPrepare', [
							'com_content.article',
							&$elm,
							&$dummyParams,
							0
					]);
				}
				
				$this->appInstance->getDispatcher ()->dispatch ( 'onContentPrepare', $eventObject );
				
				// Reset document to avoid potential issues
				$this->getDocument(true);
				
				// Downgrade the error reporting to avoid the HTTP status code pollution
				$errorReporting = $this->appInstance->get('error_reporting');
				if ($errorReporting === "development" || $errorReporting === "maximum") {
					$this->appInstance->set('error_reporting', 'simple');
				}
			} catch (\Exception $e) {
				$message = $e->getMessage();
			}
			$custom404Text = $elm->text;
		}
		
		// Always return input text, processed or not
		return $custom404Text;
	}
	
	/**
	 * Detect mobile requests
	 *
	 * @access private
	 * @return boolean
	 */
	private function isBotRequest() {
		$crawlers = array (
				'Google' => 'Google',
				'MSN' => 'msnbot',
				'Rambler' => 'Rambler',
				'Yahoo' => 'Yahoo',
				'Yandex' => 'Yandex',
				'AbachoBOT' => 'AbachoBOT',
				'accoona' => 'Accoona',
				'AcoiRobot' => 'AcoiRobot',
				'ASPSeek' => 'ASPSeek',
				'CrocCrawler' => 'CrocCrawler',
				'Dumbot' => 'Dumbot',
				'FAST-WebCrawler' => 'FAST-WebCrawler',
				'GeonaBot' => 'GeonaBot',
				'Gigabot' => 'Gigabot',
				'Lycos spider' => 'Lycos',
				'MSRBOT' => 'MSRBOT',
				'Altavista robot' => 'Scooter',
				'AltaVista robot' => 'Altavista',
				'ID-Search Bot' => 'IDBot',
				'eStyle Bot' => 'eStyle',
				'Scrubby robot' => 'Scrubby',
				'Facebook' => 'facebookexternalhit' 
		);
		// to get crawlers string used in function uncomment it
		// global $crawlers
		if (isset ( $_SERVER ['HTTP_USER_AGENT'] )) {
			$currentUserAgent = $_SERVER ['HTTP_USER_AGENT'];
			// it is better to save it in string than use implode every time
			$crawlers_agents = '/' . implode ( "|", $crawlers ) . '/';
			if (preg_match ( $crawlers_agents, $currentUserAgent, $matches )) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Handle the addition of the Google Analytics tracking code
	 *
	 * @access private
	 * @param Object $app
	 * @param Object $doc
	 * @param string $location
	 * @return void
	 */
	private function addGoogleAnalyticsTrackingCode($app, $doc, $location = 'body') {
		// Get component params
		$injectGaJs = $this->jmapConfig->get ( 'inject_gajs', 0 );
		$gajsCode = trim ( $this->jmapConfig->get ( 'gajs_code', '' ) );
		$gajsVersion = trim ( $this->jmapConfig->get ( 'inject_gajs_version', 'gtag' ) );
		
		// Google Tag Manager
		$injectGtmJS = $this->jmapConfig->get ( 'inject_gtmjs', 0 );
		$injectGtmNoscript = $this->jmapConfig->get ( 'inject_gtm_noscript', 1 );
		$gtmJsCode = trim ( $this->jmapConfig->get ( 'gtmjs_code', '' ) );
		
		$anonymizeIp = '';
		$anonymizeGtagIp = '';
		if( $this->jmapConfig->get ( 'gajs_anonymize', 0) ) {
			$anonymizeIp = "ga('set', 'anonymizeIp', true);";
			$anonymizeGtagIp = ", { 'anonymize_ip': true }";
		}
		
		// Evaluate nonce csp feature
		$appNonce = $app->get('csp_nonce', null);
		$nonce = $appNonce ? ' nonce="' . $appNonce . '"' : '';
		
		// Google Analytics
		if ($gajsVersion == 'analytics') {
			$script = <<<JS
									<!-- Google Analytics -->
									<script$nonce>
									(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
									(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
									m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
									})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
								
									ga('create', '$gajsCode', 'auto');
									ga('send', 'pageview');
									$anonymizeIp
									</script>
									<!-- End Google Analytics -->
			JS;
		} elseif ($gajsVersion == 'gtag') {
			$script = <<<JS2
									<!-- Global Site Tag (gtag.js) - Google Analytics -->
									<script$nonce async src="https://www.googletagmanager.com/gtag/js?id=$gajsCode"></script>
									<script$nonce>
									  window.dataLayer = window.dataLayer || [];
									  function gtag(){dataLayer.push(arguments);}
									  gtag('js', new Date());
									  gtag('config', '$gajsCode' $anonymizeGtagIp);
									</script>
			JS2;
		}

		// Google Tag Manager
		if($injectGtmJS) {
			$scriptGtmHead = <<<GTAG1
									<!-- Google Tag Manager -->
									<script$nonce>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
									new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
									j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
									'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
									})(window,document,'script','dataLayer','$gtmJsCode');</script>
									<!-- End Google Tag Manager -->
			GTAG1;
			if($injectGtmNoscript) {
				$scriptGtmBody = <<<GTAG2
									<!-- Google Tag Manager (noscript) -->
									<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=$gtmJsCode"
									height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
									<!-- End Google Tag Manager (noscript) -->
			GTAG2;
			}
		}
		
		// Check if the tracking code must be injected, manipulate output response
		if ($injectGaJs && $gajsCode) {
			if ($location == 'body') {
				$body = $app->getBody ();

				// Replace buffered main view contents at the body end
				$body = preg_replace ( '/<\/body>/i', $script . '</body>', $body, 1 );

				// Set the new response contents
				$app->setBody ( $body );
			} elseif ($location == 'head') {
				if ($doc->getType () === 'html') {
					$doc->addCustomTag ( $script );
				}
			}
		}
		
		// Check if the Google Tag Manager code must be injected
		if($injectGtmJS && $gtmJsCode) {
			$body = $app->getBody ();
			
			// Replace buffered main view contents at the body end
			$body = preg_replace ( '/<head(.*)>/iU', '<head$1>' . $scriptGtmHead, $body, 1 );
			
			// Set the new JResponse contents
			$app->setBody ( $body );
			
			if($injectGtmNoscript) {
				$body = $app->getBody ();
				
				// Replace buffered main view contents at the body end
				$body = preg_replace ( '/<body(.*)>/iU', '<body$1>' . $scriptGtmBody, $body, 1 );
				
				// Set the new JResponse contents
				$app->setBody ( $body );
			}
		}
	}
	
	/**
	 * Handle the addition of the Matomo tracking code
	 *
	 * @access private
	 * @param Object $app
	 * @param Object $doc
	 * @return void
	 */
	private function addPiwikproTrackingCode($app, $doc) {
		// Get component params
		$injectPiwikproJs = $this->jmapConfig->get ( 'inject_piwikprojs', 0 );
		
		// Check if the tracking code must be injected, manipulate output Response
		if ($injectPiwikproJs) {
			$piwikproUrl = trim ( $this->jmapConfig->get ( 'piwikpro_url', '' ) );
			$idSite = trim ( $this->jmapConfig->get ( 'piwikpro_idsite', '' ) );
			
			// Evaluate nonce csp feature
			$appNonce = $app->get('csp_nonce', null);
			$nonce = $appNonce ? ' nonce="' . $appNonce . '"' : '';
			
			$script = <<<JS
					<!-- Piwik PRO Tag Manager Container -->
					<script type="text/javascript"$nonce>
					var _paq = _paq || [];
					_paq.push(["trackPageView"]);
					_paq.push(["enableLinkTracking"]);
					(function() {
					var u="$piwikproUrl";
					_paq.push(["setTrackerUrl", u+"ppms.php"]);
					_paq.push(["setSiteId", "$idSite"]);
					var d=document, g=d.createElement("script"), s=d.getElementsByTagName("script")[0];
					g.type="text/javascript"; g.async=true; g.defer=true; g.src=u+"ppms.js"; s.parentNode.insertBefore(g,s);
					})();
					</script>
					<!-- End Piwik PRO Tag Manager Container -->
			JS;
			
			if ($doc->getType () === 'html') {
				$doc->addCustomTag ( $script );
			}
		}
	}
	
	/**
	 * Handle the addition of the Matomo tracking code
	 *
	 * @access private
	 * @param Object $app
	 * @param Object $doc
	 * @return void
	 */
	private function addMatomoTrackingCode($app, $doc) {
		// Get component params
		$injectMatomoJs = $this->jmapConfig->get ( 'inject_matomojs', 0 );
		
		// Check if the tracking code must be injected, manipulate output Response
		if ($injectMatomoJs) {
			$matomoUrl = trim ( $this->jmapConfig->get ( 'matomo_url', '' ) );
			$idSite = trim ( (int)$this->jmapConfig->get ( 'matomo_idsite', '' ) );
			
			// Evaluate nonce csp feature
			$appNonce = $app->get('csp_nonce', null);
			$nonce = $appNonce ? ' nonce="' . $appNonce . '"' : '';
			
			$script = <<<JS
					<!-- Matomo -->
					<script type="text/javascript"$nonce>
					var _paq = window._paq = window._paq || [];
					_paq.push(['trackPageView']);
					_paq.push(['enableLinkTracking']);
					(function() {
					var u="//$matomoUrl/";
					_paq.push(['setTrackerUrl', u+'matomo.php']);
					_paq.push(['setSiteId', $idSite]);
					var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
					g.type='text/javascript'; g.async=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);
					})();
					</script>
					<!-- End Matomo Code -->
			JS;
		
			if ($doc->getType () === 'html') {
				$doc->addCustomTag ( $script );
			}
		}
	}
	
	/**
	 * Handle the addition of the FBPixel tracking code
	 *
	 * @access private
	 * @param Object $app
	 * @param Object $doc
	 * @return void
	 */
	private function addFBPixelTrackingCode($app, $doc) {
		// Get component params
		$injectFBPixelJs = $this->jmapConfig->get ( 'inject_fbpixel', 0 );
		
		// Check if the tracking code must be injected, manipulate output Response
		if ($injectFBPixelJs) {
			$fbPixelId = trim ( $this->jmapConfig->get ( 'fbpixel_id', '' ) );
			
			// Evaluate nonce csp feature
			$appNonce = $app->get('csp_nonce', null);
			$nonce = $appNonce ? ' nonce="' . $appNonce . '"' : '';
			
			$script = <<<JS
							<!-- Facebook Pixel Code -->
							<script$nonce>
							!function(f,b,e,v,n,t,s)
							{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
							n.callMethod.apply(n,arguments):n.queue.push(arguments)};
							if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
							n.queue=[];t=b.createElement(e);t.async=!0;
							t.src=v;s=b.getElementsByTagName(e)[0];
							s.parentNode.insertBefore(t,s)}(window, document,'script',
							'https://connect.facebook.net/en_US/fbevents.js');
							fbq('init', '$fbPixelId');
							fbq('track', 'PageView');
							</script>
							<noscript>
							<img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=$fbPixelId&ev=PageView&noscript=1"/>
							</noscript>
							<!-- End Facebook Pixel Code -->
			JS;
			
			if ($doc->getType () === 'html') {
				$doc->addCustomTag ( $script );
			}
		}
	}
	
	/**
	 * Performs the text replacement in the app body
	 *
	 * @access private
	 * @return void
	 */
	private function patternReplacements () {
		$matchedPatterns = false;
		
		// Manage the patterns redirect
		$patternReplacementsQuery = "SELECT" .
									"\n " . $this->dbInstance->quoteName('original_text') . "," .
									"\n " . $this->dbInstance->quoteName('target_text') . "," .
									"\n " . $this->dbInstance->quoteName('original_text_regex') . "," .
									"\n " . $this->dbInstance->quoteName('target_text_regex') .
									"\n FROM" .
									"\n " . $this->dbInstance->quoteName('#__jmap_text_replacements') .
									"\n WHERE" .
									"\n" . $this->dbInstance->quoteName('published') . " = 1";
		$patternReplacements = $this->dbInstance->setQuery ( $patternReplacementsQuery )->loadObjectList ();
		if (count ( $patternReplacements )) {
			// Get page body content
			$body = $this->appInstance->getBody ();
			
			foreach ($patternReplacements as $singlePattern) {
				// Is there a match with the current URL?
				if(preg_match('#' . $singlePattern->original_text_regex . '#iu', $body)) {
					$body = preg_replace('#' . $singlePattern->original_text_regex . '#iu', $singlePattern->target_text_regex, $body);
					$matchedPatterns = true;
				}
			}
			
			// Set the new Response contents
			if($matchedPatterns) {
				$this->appInstance->setBody ( $body );
			}
		}
	}
	
	/**
	 * Main dispatch method
	 *
	 * @param Event $event
	 * @access public
	 * @return boolean
	 */
	public function dispatchUtility(Event $event) {
		// Avoid operations if plugin is executed in backend
		if (!$this->appInstance->isClient('site')) {
			return;
		}
		
		// Check if the plugin has not been stopped by the constructor
		if($this->isPluginStopped) {
			$dispatcher = $this->getDispatcher();
			$dispatcher->removeSubscriber($this);
			return;
		}
		
		// Security safe 1 - If JMAP internal link force always the lang url param using the cookie workaround
		if( $this->appInstance->getInput()->get ( 'option' ) == 'com_jmap' && $this->jmapConfig->get('advanced_multilanguage', 0)) {
			$lang = $this->appInstance->getInput()->get('lang');
		
			$sefs = LanguageHelper::getLanguages('sef');
			$lang_codes = LanguageHelper::getLanguages('lang_code');
		
			if (isset($sefs[$lang])) {
				$lang_code = $sefs[$lang]->lang_code;
		
				// Create a cookie.
				$conf = $this->appInstance->getConfig();
				$cookie_domain 	= $conf->get('config.cookie_domain', '');
				$cookie_path 	= $conf->get('config.cookie_path', '/');
				setcookie(ApplicationHelper::getHash('language'), $lang_code, 86400, $cookie_path, $cookie_domain);
				$this->appInstance->getInput()->cookie->set(ApplicationHelper::getHash('language'), $lang_code);
		
				// Set the request var.
				$this->appInstance->getInput()->set('language', $lang_code);
				
				// Check if remove default prefix is active and the default language is not the current one
				$defaultSiteLanguage = ComponentHelper::getParams('com_languages')->get('site', 'en-GB');
				$pluginLangFilter = Joomla\CMS\Plugin\PluginHelper::getPlugin('system', 'languagefilter');
				$removeDefaultPrefix = @json_decode($pluginLangFilter->params)->remove_default_prefix;
				if($removeDefaultPrefix && $defaultSiteLanguage != $lang_code) {
					$uri = Uri::getInstance();
					$path = $uri->getPath();
					// Force the language SEF code in the path
					$path = str_replace('/index.php', '/' . $lang . '/index.php', $path);
					$uri->setPath($path);
				}
			}
		}
		
		// Detect if current request come from a bot user agent
		if ($this->isBotRequest () && $this->appInstance->getInput()->get ( 'option' ) == 'com_jmap') {
			$_SERVER ['REQUEST_METHOD'] = 'POST';
			
			// Set dummy nobot var
			$this->appInstance->getInput()->post->set ( 'nobotsef', true );
			$GLOBALS['_' . strtoupper('post')] ['nobotsef'] = true;
		}
	}
	
	/**
	 * Hook for the auto Pingomatic third party extensions that have not its own
	 * route helper and work with the universal JSitemap route helper framework
	 *
	 * @param Event $event
	 * @access public
	 * @return boolean
	 */
	public function thirdPartyRoutePinging(Event $event) {
		// Security safe 2 - If JMAP internal link revert back the query string 'lang' param to the sef lang code 'en' instead of the iso lang code 'en-GB'
		$lang = $this->appInstance->getInput()->get('lang');
		if($lang && $this->appInstance->getInput()->get ( 'option' ) == 'com_jmap' && $this->jmapConfig->get('advanced_multilanguage', 0) && strlen($lang) > 2) {
			$languageCode = $this->appInstance->getInput()->get('language');
			$lang_codes = LanguageHelper::getLanguages('lang_code');
			if(isset($lang_codes[$languageCode])) {
				$sefLang = $lang_codes[$languageCode]->sef;
				$this->appInstance->getInput()->set('lang', $sefLang);
			}
		}
		
		// Avoid below operations if the plugin is not executed in backend app
		if (!$this->appInstance->isClient('administrator')) {
			return;
		}
		
		// Redirect to the component configuration if the Joomla global configuration is requested instead
		$dispatchedComponent = $this->appInstance->getInput()->get ( 'option' );
		$dispatchedView = $this->appInstance->getInput()->get ( 'view' );
		$componentConfig = $this->appInstance->getInput()->get ( 'component' );
		if($dispatchedComponent == 'com_config' && $dispatchedView == 'component' && $componentConfig == 'com_jmap') {
			$this->appInstance->redirect(Route::_('index.php?option=com_jmap&task=config.display', false));
			return;
		}
		
		// Get component params
		if (! $this->jmapConfig->get ( 'default_autoping', 0 ) && ! $this->jmapConfig->get ( 'autoping', 0 )) {
			return;
		}
		
		// Retrieve more informations as much as possible from the current POST array
		$option = $this->appInstance->getInput()->get ( 'option' );
		$view = $this->appInstance->getInput()->get ( 'view' );
		$controller = $this->appInstance->getInput()->get ( 'controller' );
		$task = $this->appInstance->getInput()->get ( 'task' );
		$id = $this->appInstance->getInput()->getInt ( 'id' );
		$catid = $this->appInstance->getInput()->get ( 'cid', null, 'array' );
		$language = $this->appInstance->getInput()->get ( 'language' );
		$name = $this->appInstance->getInput()->getString ( 'name' );
		if (is_array ( $catid )) {
			$catid = $catid [0];
		}
		
		// Valid execution mapping
		$arrayExecution = array (
				'com_zoo' => array (
						'controller' => 'item',
						'task' => array (
								'apply',
								'save',
								'save2new',
								'save2copy' 
						) 
				) 
		);
		
		// Test against valid execution, discard all invalid extensions operations
		if (array_key_exists ( $option, $arrayExecution )) {
			$testIfExecute = $arrayExecution [$option];
			foreach ( $testIfExecute as $property => $value ) {
				$evaluated = $$property;
				
				if (is_array ( $value )) {
					if (! in_array ( $evaluated, $value )) {
						return;
					}
				} else {
					if ($evaluated != $value) {
						return;
					}
				}
			}
		} else {
			return;
		}
		
		// Valid execution success! Go on to route the request to the content plugin, mimic the native Joomla onContentAfterSave
		
		// Auto loader setup
		// Register autoloader prefix
		require_once JPATH_ADMINISTRATOR . '/components/com_jmap/Framework/Loader.php';
		JMapLoader::setup();
		JMapLoader::registerNamespacePsr4 ( 'JExtstore\\Component\\JMap\\Administrator', JPATH_ADMINISTRATOR . '/components/com_jmap' );
		
		Joomla\CMS\Plugin\PluginHelper::importPlugin ( 'content', 'pingomatic', true, $this->appInstance->getDispatcher() );
		
		// Simulate the jsitemap_category_id object for the JSitemap route helper
		$elm = new \stdClass ();
		$zooParams = $this->appInstance->getInput()->get ( 'params', null, 'array' );
		$zooDetails = $this->appInstance->getInput()->get ( 'details', null, 'array' );
		$elm->jsitemap_category_id = (int)$zooParams['primary_category'];
		
		// Simulate the $article Joomla object passed to the content observers
		$itemObject = new \stdClass ();
		$itemObject->id = $id;
		$itemObject->catid = $elm;
		$itemObject->option = $option;
		$itemObject->view = $view ? $view : $controller;
		$itemObject->language = $language;
		$itemObject->title = $name;
		
		// Setup the publish_up in UTC format
		$originalPublishUp = new DateTime($zooDetails['publish_up'], new DateTimeZone($this->joomlaConfig->get('offset')));
		$originalPublishUp->setTimezone(new DateTimeZone("UTC"));
		$itemObject->publish_up = $originalPublishUp->format("Y-m-d H:i:s");
		
		// Trigger the content plugin event
		$this->appInstance->getDispatcher ()->dispatch ( 'onContentAfterSave', new Event ( 'onContentAfterSave', [ 
				'com_zoo.item',
				$itemObject,
				false
		] ) );
		
	}

	/**
	 * Hook for the management injection of the custom meta tags informations
	 *
	 * @param Event $event
	 * @access public
	 * @return void
	 */
	public function addHeadMetainfo(Event $event) {
		$document = $this->appInstance->getDocument();

		// Avoid operations if plugin is executed in backend
		if (!$this->appInstance->isClient('site')) {
			return;
		}

		// Checkpoint for Google Analytics tracking code addition
		if(($this->jmapConfig->get('inject_gajs', 0) || $this->jmapConfig->get('inject_gtmjs', 0)) && $this->jmapConfig->get('inject_gajs_location', 'body') == 'head') {
			$this->addGoogleAnalyticsTrackingCode($this->appInstance, $document, 'head');
		}
		
		if($this->jmapConfig->get('inject_matomojs', 0)) {
			$this->addMatomoTrackingCode($this->appInstance, $document);
		}
		
		if($this->jmapConfig->get('inject_piwikprojs', 0)) {
			$this->addPiwikproTrackingCode($this->appInstance, $document);
		}
		
		if($this->jmapConfig->get('inject_fbpixel', 0)) {
			$this->addFBPixelTrackingCode($this->appInstance, $document);
		}
		
		// Check if the robots opt-in directive is required
		if($this->jmapConfig->get('optin_contents', 0)) {
			$robots = trim($this->jmapConfig->get('optin_contents_robots_directive', 'max-snippet:-1,max-image-preview:large,max-video-preview:-1'));
			if($robots) {
				$currentMetaData = $document->getMetaData('robots');
				if(StringHelper::strpos($currentMetaData, 'noindex') === false && StringHelper::strpos($currentMetaData, 'nosnippet') === false) {
					$document->setMetaData('robots', $robots);
				}
			}
		}
		
		// Get the current URI and check for an entry in the DB table
		if($this->jmapConfig->get('metainfo_urldecode', 1)) {
			$uri = urldecode(Uri::current());
		} else {
			// Preserve URLs character encoding if any
			$uri = Uri::current();
		}
		
		// Double check if there is a query string difference
		$uriInstanceString = (string)Uri::getInstance();
		if(StringHelper::strpos($uriInstanceString, '?') !== false && $uri != $uriInstanceString) {
			// Get the current URI and check for an entry in the DB table
			if($this->jmapConfig->get('metainfo_urldecode', 1)) {
				$uri = urldecode($uriInstanceString);
			} else {
				// Preserve URLs character encoding if any
				$uri = $uriInstanceString;
			}
		}

		// K2 Tags decoding as an optional processing
		if($this->jmapConfig->get('metainfo_urlencode_space', 0)) {
			$uri = StringHelper::str_ireplace(' ', '%20', $uri);
		}

		// Remove trailing slash for URL matching, this ensure home page match if the option to remove ending slash is enabled
		if($this->jmapConfig->get('metainfo_remove_trailing_slash', 0)) {
			$originalUri = $uri;
			$uri = rtrim( $uri, '/' );
		}
		
		// Add trailing slash for URL matching, this ensure home page match if the option to not remove ending slash is enabled
		if($this->jmapConfig->get('metainfo_add_trailing_slash', 0)) {
			$originalUri = $uri;
			$uri = rtrim( $uri, '/' ) . '/';
		}
		
		// Apply same metadata even to the corresponding AMP pages
		if($this->jmapConfig->get('amp_sitemap_enabled', 0)) {
			$ampSuffix = $this->jmapConfig->get('amp_suffix', 'amp');
			if(preg_match("/\.$ampSuffix\./i", $uri)) {
				$uri = preg_replace("/\.$ampSuffix\./i", '.', $uri, 1);
			}
			if(preg_match('/\/' . $ampSuffix . '$/i', $uri)) {
				$uri = preg_replace('/\/' . $ampSuffix . '$/i', '', $uri, 1);
			}
		}
		
		// Store for later stage processing
		$this->jmapUri = $uri;
		
		// Setup the query
		$query = "SELECT *" .
				 "\n FROM #__jmap_metainfo" .
				 "\n WHERE " . $this->dbInstance->quoteName('linkurl') . " = " . $this->dbInstance->quote($uri) .
				 "\n AND " . $this->dbInstance->quoteName('published') . " = 1";
		try {
			$metaInfoForThisUri = $this->dbInstance->setQuery($query)->loadObject();
		} catch(\Exception $e) {}

		// Yes! Found some metainfo set for this uri, let's inject them into the document
		if(isset($metaInfoForThisUri->id)) {
			$title = trim($metaInfoForThisUri->meta_title);
			$description = trim($metaInfoForThisUri->meta_desc);
			$image = trim($metaInfoForThisUri->meta_image);
			$robots = $metaInfoForThisUri->robots;
			$ogTagsInclude = $this->jmapConfig->get('metainfo_ogtags', 1);
			$twitterCardsTagsInclude = $this->jmapConfig->get('metainfo_twitter_card_enable', 0);

			// Title and og:graph title
			if($title) {
				// Append site name
				if ($this->appInstance->get('sitename_pagetitles', 0) == 2 && trim($this->appInstance->get('sitename'))) {
					$title = $title . ' - ' . trim($this->appInstance->get('sitename'));
				} elseif ($this->appInstance->get('sitename_pagetitles', 0) == 1 && trim($this->appInstance->get('sitename'))) { // Prepend site name
					$title = trim($this->appInstance->get('sitename')) . ' - ' . $title;
				}
				
				$document->setTitle($title);
				$document->setMetaData('title', $title);
				$document->setMetaData('metatitle', $title);
				if($ogTagsInclude) {
					$document->setMetaData('og:title', $title, 'property');
					$document->setMetaData('twitter:title', $title);
				}
			}

			// Description and og:graph meta description
			if($description) {
				$document->setDescription($description);
				if($ogTagsInclude) {
					$document->setMetaData('og:description', $description, 'property');
					$document->setMetaData('twitter:description', $description);
				}
			}

			// Set always social share uri if title/desc is specified
			if(($title || $description) && $ogTagsInclude) {
				if($this->jmapConfig->get('metainfo_remove_trailing_slash', 0)) {
					$document->setMetaData('og:url', $originalUri, 'property');
				} else {
					$document->setMetaData('og:url', $uri, 'property');
				}
				
				$document->setMetaData('og:type', 'article', 'property');
			}
			
			// Image for social share og:image and twitter:image
			if($image && $ogTagsInclude) {
				$nakedImageLink = StringHelper::substr($image, 0, StringHelper::strpos($image, '#'));
				$nakedImageLink = StringHelper::substr($nakedImageLink, 0, StringHelper::strpos($nakedImageLink, '?'));
				
				$imageLink = preg_match('/http/i', $image) ? $image : Uri::base() . ltrim($image, '/');
				
				// For J4 query string is needed to remove it
				$imageLink = StringHelper::substr($imageLink, 0, StringHelper::strpos($imageLink, '#'));
				
				$document->setMetaData('og:image', $imageLink, 'property');
				$document->setMetaData('twitter:image', $imageLink);
				
				if(preg_match('/^http/i', $nakedImageLink)) {
					$imagePath = JPATH_ROOT . '/' . ltrim(StringHelper::str_ireplace(Uri::root(), '', $nakedImageLink, '/'));
				} else {
					$imagePath = JPATH_ROOT . '/' . ltrim($nakedImageLink, '/');
				}
				
				// Get image width/height and force the native meta propery tags for immediate share crawling
				if(file_exists($imagePath)) {
					list($width, $height) = getimagesize($imagePath);
					$document->setMetaData('og:image:width', $width, 'property');
					$document->setMetaData('og:image:height', $height, 'property');
				}
			}

			// Robots directive
			if($robots) {
				$document->setMetaData('robots', $robots);
			}
			
			// Additional Twitter cards tags
			if($ogTagsInclude && $twitterCardsTagsInclude) {
				$document->setMetaData('twitter:card', 'summary');
				$twitterCardSite = trim($this->jmapConfig->get('metainfo_twitter_card_site', ''));
				if($twitterCardSite) {
					$document->setMetaData('twitter:site', $twitterCardSite);
				}
				$twitterCardCreator = trim($this->jmapConfig->get('metainfo_twitter_card_creator', ''));
				if($twitterCardCreator) {
					$document->setMetaData('twitter:creator', $twitterCardCreator);
				}
			}
		}
		
		// Check if the override canonical feature is enabled and if so go on and check a url matching for some custom canonical
		if($this->jmapConfig->get('seospider_override_canonical', 1)) {
			$query = "SELECT *" .
					 "\n FROM #__jmap_canonicals" .
					 "\n WHERE " . $this->dbInstance->quoteName('linkurl') . " = " . $this->dbInstance->quote($uri);
			try {
				$canonicalForThisUri = $this->dbInstance->setQuery($query)->loadObject();
			} catch(\Exception $e) {}
			
			// Yes! Found a canonical override set for this uri, let's replace them into the document
			if(isset($canonicalForThisUri->id)) {
				// Remove the current canonical tag from the document
				$header = $document->getHeadData();
				foreach($header['links'] as $key => $array) {
					if($array['relation'] == 'canonical') {
						unset($document->_links[$key]);
					}
				}
				
				// Add the new overridden canonical link
				$document->addHeadLink(htmlspecialchars($canonicalForThisUri->canonical), 'canonical', 'rel', array('data-jmap-canonical-override'=>1));
				
				// Override also the og:url metatag with the current canonical
				if(isset($title) && ($title || $description) && $ogTagsInclude) {
					$document->setMetaData('og:url', $canonicalForThisUri->canonical, 'property');
				}
			}
		}
		
		// Fix pagination links if detected adding a page number/results suffix to make them unique and not duplicated
		$isPagination = $this->appInstance->getInput()->get->get('start', null, 'int');
		$isPage = $this->appInstance->getInput()->get->get('page', null, 'int');
		if($isPagination || $isPage) {
			$jmapParams = ComponentHelper::getParams('com_jmap');

			// Fix pagination is enabled
			if($jmapParams->get('unique_pagination', 1)) {
				// Get dispatched component params with view overrides
				$contentParams = $this->appInstance->getParams();

				// Load JMap language translations
				$jLang = $this->appInstance->getLanguage ();
				$jLang->load ( 'com_jmap', JPATH_ROOT . '/components/com_jmap', 'en-GB', true, true );
				if ($jLang->getTag () != 'en-GB') {
					$jLang->load ( 'com_jmap', JPATH_SITE, null, true, false );
					$jLang->load ( 'com_jmap', JPATH_SITE . '/components/com_jmap', null, true, false );
				}

				// Check if pagination params are detected otherwise fallback
				$leadingNum = $contentParams->get('num_leading_articles', null);
				$introNum = $contentParams->get('num_intro_articles', null);
				if($leadingNum && $introNum) {
					$articlesPerPage = (int)($leadingNum + $introNum);
					$pageNum = ' - ' . Text::_('COM_JMAP_PAGE_NUMBER') . ((int)($isPagination / $articlesPerPage) + 1);
				} else {
					// Fallback for generic components staring from xxx
					if($isPage) {
						$pageNum = ' - ' . Text::_('COM_JMAP_PAGE_NUMBER') . (int)$isPage;
					} else {
						$pageNum = ' - ' . Text::_('COM_JMAP_RESULTS_FROM') . $isPagination;
					}
				}

				$currentTitle = $document->getTitle();
				$document->setTitle($currentTitle . $pageNum);
				$currentDescription = $document->getDescription();
				$document->setDescription($currentDescription . $pageNum);
			}
		}
		
		// Add script json+ld for Rich Snippet Searchbox ONLY to the website homepage
		if ($this->jmapConfig->get ( 'searchbox_enable', 0 )) {
			$loadedMenuItem = $this->appInstance->getMenu()->getActive();
			if(	$loadedMenuItem && $loadedMenuItem->home == 1 ) {
				$json = array ();
				$array = array ();
				$url = $this->jmapConfig->get ( 'searchbox_url', Uri::root () );
				$type = $this->jmapConfig->get ( 'searchbox_type', 'finder' );
				$custom = $this->jmapConfig->get ( 'searchbox_custom', '' );
				$uriInstance = Uri::getInstance();
				$getDomain = rtrim($uriInstance->getScheme() . '://' . $uriInstance->getHost(), '/');
				
				// Register autoloader prefix
				require_once JPATH_ADMINISTRATOR . '/components/com_jmap/Framework/Loader.php';
				JMapLoader::setup();
				JMapLoader::registerNamespacePsr4 ( 'JExtstore\\Component\\JMap\\Administrator', JPATH_ADMINISTRATOR . '/components/com_jmap' );
				$multiLanguageEnabled = JMapMultilang::isEnabled ();
				$currentSefLanguage = JMapMultilang::getCurrentSefLanguage () . '/';
					
				if ($type == 'finder') {
					$smartSearchComponentLink = Route::_ ( 'index.php?option=com_finder&q={search_term}', false );
					$smartSearchComponentLink = StringHelper::str_ireplace ( '/?', '?', $smartSearchComponentLink );
					if ($multiLanguageEnabled) {
						$smartSearchComponentLink = StringHelper::str_ireplace ( $currentSefLanguage, '', $smartSearchComponentLink );
					}
					$search = $getDomain . $smartSearchComponentLink;
				} else {
					$search = $custom;
				}
					
				$array ['@context'] = 'https://schema.org';
				$array ['@type'] = 'WebSite';
				$array ['url'] = $url;
				$array ['potentialAction'] ['@type'] = 'SearchAction';
				$array ['potentialAction'] ['target'] = $search;
				$array ['potentialAction'] ['query-input'] = 'required name=search_term';
					
				$document->getWebAssetManager()->addInlineScript ( json_encode ( $array ), [], ['type' => 'application/ld+json'] );
			}
		}
	}
	
	/**
	 * Hook for the management of the custom 404 page
	 *
	 * @param Event $event
	 * @subparam $errorClass
	 * @access public
	 * @return boolean
	 */
	public function onExceptionHandler404Page(Event $event) {
		static $custom404Handled = false;
		// subparams: $form, $data
		$arguments = $event->getArguments();
		$errorClass = $event->getArgument('subject');
		
		if($custom404Handled) {
			return false;
		}

		// Mark as handled for next execution cycles
		$custom404Handled = true;

		// Get component params and ensure that the custom 404 page is enabled
		$cParams = ComponentHelper::getParams('com_jmap');
		if(!$cParams->get('custom_404_page_status', 0)) {
			return false;
		}

		// 404 custom page managed as an override by the postProcessParseRule
		if($cParams->get('custom_404_page_override', 1)) {
			return false;
		}

		// Execute only in frontend
		if (!$this->appInstance->isClient('site')) {
			return false;
		}
		
		// Dispatched format, apply only to html document
		$documentFormat = $this->appInstance->getInput()->get ( 'format', null );
		if ($documentFormat && $documentFormat != 'html') {
			return false;
		}
		
		// Dispatched template file, ignores component tmpl
		if ($this->appInstance->getInput()->get ( 'tmpl', null ) === 'component') {
			return false;
		}
		
		if (!$errorClass instanceof RouteNotFoundException) {
			return false;
		}
	
		$documentRenderer = AbstractRenderer::getRenderer('html');
		$document = $documentRenderer->getDocument();
		
		// Evaluate the error code, 404 only is of our interest and ignore everything else
		// Generate and set a new custom error message based on custom text/html
		$custom404Text = $cParams->get('custom_404_page_text', null);

		// Process contents
		$custom404Text = $this->processContentPlugins($custom404Text, $cParams);

		// Check if a strip tags is required
		if($cParams->get('custom_404_page_mode', 'html') == 'text') {
			$custom404Text = strip_tags($custom404Text);
		}

		// Set the new Exception message supporting HTML and hoping that htmlspecialchars in not used by the error.php of the template
		try {
			$reflection = new \ReflectionProperty($errorClass, 'message');
			$reflection->setAccessible(true);
			$reflection->setValue($errorClass, $custom404Text);
		} catch(\Exception $e) {
			$error = $e->getMessage();
		}
	}

	/**
	 * Application event
	 *
	 * @param Event $event
	 * @access public
	 */
	public function refactorAppBody(Event $event) {
		// Framework reference
		$doc = $this->appInstance->getDocument ();
	
		// Check if the app can start
		if (!$this->appInstance->isClient('site')) {
			return false;
		}
	
		// Check if the app can start
		if ($doc->getType () !== 'html') {
			return false;
		}
	
		$option = $this->appInstance->getInput()->get('option', null);
		if ( $option == 'com_jmap' && $this->appInstance->getInput()->get('format') ) {
			return false;
		}
		
		// Check if the override headings feature is enabled and if so go on and check a url matching for some heading
		if($this->jmapConfig->get('seospider_override_headings', 1)) {
			// Search an headings override for this URL
			$query = "SELECT *" .
					 "\n FROM #__jmap_headings" .
					 "\n WHERE " . $this->dbInstance->quoteName('linkurl') . " = " . $this->dbInstance->quote($this->jmapUri);
			try {
				$headingsForThisUri = $this->dbInstance->setQuery($query)->loadObject();
			} catch(\Exception $e) {}
		
			// Yes! Found some headings override set for this uri, let's replace them into the document
			if(isset($headingsForThisUri->id)) {
				// Go on only if there is at least one valid heading override
				if($headingsForThisUri->h1 || $headingsForThisUri->h2 || $headingsForThisUri->h3) {
					// Include DOM parser class
					require_once (JPATH_ROOT . '/plugins/system/jmap/simplehtmldom.php');
		
					$simpleHtmlDomInstance = new \JMapSimpleHtmlDom();
					$simpleHtmlDomInstance->load( $this->appInstance->getBody () );
		
					// Find and replace the first encountered H1 tag
					if($headingsForThisUri->h1) {
						$domElementsH1 = $simpleHtmlDomInstance->find( 'h1' );
		
						// Replace the original H1 header with the overridden one
						if(isset($domElementsH1[0])) {
							$element = $domElementsH1[0];
							$nodeText = $element->text(true);
							$nodeText = $headingsForThisUri->h1;
							$element->innertext = $nodeText;
							$element->setAttribute('data-jmap-heading-override', 1);
						}
					}
		
					// Find and replace the first encountered H2 tag
					if($headingsForThisUri->h2) {
						$domElementsH2 = $simpleHtmlDomInstance->find( 'h2' );
							
						// Replace the original H2 header with the overridden one
						if(isset($domElementsH2[0])) {
							$element = $domElementsH2[0];
							$nodeText = $element->text(true);
							$nodeText = $headingsForThisUri->h2;
							$element->innertext = $nodeText;
							$element->setAttribute('data-jmap-heading-override', 1);
						}
					}
		
					// Find and replace the first encountered H3 tag
					if($headingsForThisUri->h3) {
						$domElementsH3 = $simpleHtmlDomInstance->find( 'h3' );
							
						// Replace the original H3 header with the overridden one
						if(isset($domElementsH3[0])) {
							$element = $domElementsH3[0];
							$nodeText = $element->text(true);
							$nodeText = $headingsForThisUri->h3;
							$element->innertext = $nodeText;
							$element->setAttribute('data-jmap-heading-override', 1);
						}
					}
		
					$body = $simpleHtmlDomInstance->save();
		
					// Final assignment
					$this->appInstance->setBody ( $body );
				}
			}
		}
	
		// Checkpoint for Google Analytics tracking code addition
		if(($this->jmapConfig->get('inject_gajs', 0) || $this->jmapConfig->get('inject_gtmjs', 0)) && $this->jmapConfig->get('inject_gajs_location', 'body') == 'body') {
			$this->addGoogleAnalyticsTrackingCode($this->appInstance, $doc, 'body');
		}
		
		if($this->jmapConfig->get('inject_matomojs', 0)) {
			$this->addMatomoTrackingCode($this->appInstance, $doc);
		}
		
		if($this->jmapConfig->get('inject_piwikprojs', 0)) {
			$this->addPiwikproTrackingCode($this->appInstance, $doc);
		}
		
		if($this->jmapConfig->get('inject_fbpixel', 0)) {
			$this->addFBPixelTrackingCode($this->appInstance, $doc);
		}
		
		if($this->jmapConfig->get('enable_pattern_replacements', 'enabled') == 'enabled') {
			$this->patternReplacements();
		}
	}
	
	/**
	 * Preprocess dummy to load language files
	 *
	 * @param Event $event
	 * @subparam Joomla\CMS\Form\Form $form
	 * @subparam object $data
	 * @access public
	 * @return boolean
	 */
	public function loadModulesLanguageFiles(Event $event) {
		// subparams: $form, $data
		$arguments = $event->getArguments();
		$form = isset($arguments[0]) ? $event->getArgument(0) : $event->getArgument('subject');
		$data = isset($arguments[1]) ? $event->getArgument(1) : $event->getArgument('data');
		
		// Manage partial language translations if editing modules jmap in backend
		if((($this->appInstance->getInput()->get('option') == 'com_modules' || $this->appInstance->getInput()->get('option') == 'com_advancedmodules') &&
			$this->appInstance->getInput()->get('view') == 'module' &&
			$this->appInstance->getInput()->get('layout') == 'edit' &&
			$this->appInstance->isClient ('administrator')) ||
			($this->appInstance->getInput()->get('option') == 'com_config' &&
			$this->appInstance->getInput()->get('view') == 'modules' &&
			$this->appInstance->getInput()->get('id') &&
			$this->appInstance->isClient ('site'))) {
			$jLang = $this->appInstance->getLanguage ();
			$jLang->load ( 'com_jmap', JPATH_ADMINISTRATOR . '/components/com_jmap', 'en-GB', true, true );
			if ($jLang->getTag () != 'en-GB') {
				$jLang->load ( 'com_jmap', JPATH_ADMINISTRATOR, null, true, false );
				$jLang->load ( 'com_jmap', JPATH_ADMINISTRATOR . '/components/com_jmap', null, true, false );
			}
		}
		
		// Check if the default merge data source feature is enabled
		$cParams = ComponentHelper::getParams('com_jmap');
		if(!$cParams->get('merge_generic_menu_by_class', 0)) {
			return true;
		}
		
		// Only works on JForms
		if (!($form instanceof \Joomla\CMS\Form\Form)) return true;
		
		// which belong to the following components
		$components_list = array(
				"com_menus.item"
		);
		
		$formName = $form->getName();
		if ($this->appInstance->isClient('site') || !in_array($formName, $components_list)) return true;
		
		if(!isset($data->type) || (isset($data->type) && $data->type != 'component')) return true;
		
		switch ($formName) {
			case 'com_menus.item':
				$form->load('<form>
								<fields name="params">
									<fieldset name="menu-options">
										<field name="jsitemap_default_datasource" type="radio" label="Default sitemap menu item" description="Choose this menu item, as the default one for the linked component, to merge data taken from a separate data source" layout="joomla.form.field.radio.switcher" default="0" filter="integer">
											<option value="0">JNO</option>
											<option value="1">JYES</option>
										</field>
									</fieldset>
								</fields>
							</form>');
				break;
		}
		
		return true;
	}
	
	/**
	 * Integration for components performing route helper directly in the main router such as Virtuemart
	 * The component router must be executed BEFORE the SiteRouter::buildSefRoute to allow the Itemid to be already found by the crouter
	 *
	 * @param &$router Router object
	 * @param &$uri Uri object
	 * @access public
	 * @return void
	 */
	public function preProcessBuildRule(&$router, &$uri) {
		$option = $this->appInstance->getInput()->get ( 'option' );
		$urlOption = $uri->getVar('option');
		if (!$this->jmapConfig->get('preprocess_route_helpers', 1) || !$this->appInstance->isClient ('site') || !array_key_exists($urlOption, $this->appInstance->get('jmap_croute_helpers_preprocess', []))) {
			return;
		}
		
		$originalUri = clone ($uri);
		$query = $originalUri->getQuery ( true );
		
		// Build the component route
		$component = preg_replace ( '/[^A-Z0-9_\.-]/i', '', $query ['option'] );
		$crouter = $router->getComponentRouter ( $component );
		$crouter->build ( $query );
		
		if (! empty ( $query ['Itemid'] ) && $query ['Itemid'] != $uri->getVar ( 'Itemid' )) {
			$uri->setVar ( 'Itemid', $query ['Itemid'] );
		}
	}
	
	/**
	 * Support for new routing throwing 404 exception in the parse function of the base router
	 *
	 * @param &$router Router object
	 * @param &$uri Uri object
	 * @access public
	 * @return boolean
	 */
	public function postProcessParseRule(&$router, &$uri) {
		// Dispatched format, apply only to html document
		$documentFormat = $this->appInstance->getInput()->get ( 'format', null );
		if ($documentFormat && $documentFormat != 'html') {
			return false;
		}
		
		// Dispatched template file, ignores component tmpl
		if ($this->appInstance->getInput()->get ( 'tmpl', null ) === 'component') {
			return false;
		}
		
		$siteRouter = Factory::getContainer()->has('SiteRouter') ? Factory::getContainer()->get('SiteRouter') : SiteRouter::getInstance ( 'site' ); 
		$option = $siteRouter->getVar('option') ? : $uri->getVar('option');
	
		// Check if all parts of the URL have been parsed.
		// Otherwise we have an invalid URL
		if ($option == 'com_content' && strlen($uri->getPath()) > 0) {
			// Get component params and ensure that the custom 404 page is enabled
			$cParams = ComponentHelper::getParams('com_jmap');
	
			// Generate and set a new custom error message based on custom text/html
			$custom404Text = $cParams->get('custom_404_page_text', null);
			
			// Process contents
			$custom404Text = $this->processContentPlugins($custom404Text, $cParams);
	
			// Check if a strip tags is required
			if($cParams->get('custom_404_page_mode', 'html') == 'text') {
				$custom404Text = strip_tags($custom404Text);
			}
			
			throw new \Exception($custom404Text, 404);
		}
	}
	
	/**
	 * Event to manipulate the menu item dashboard in backend
	 *
	 * @param Event $event
	 * @subparam   array  &$policy  The privacy policy status data, passed by reference, with keys "published" and "editLink"
	 *
	 * @return  void
	 */
	public function processMenuItemsDashboard(Event $event) {
		// subparams: $context, $items
		$arguments = $event->getArguments();
		$context = isset($arguments[0]) ? $event->getArgument(0) : $event->getArgument('context');
		$items = isset($arguments[1]) ? $event->getArgument(1) : $event->getArgument('subject');
		
		if(!empty($items) && $context == 'administrator.module.mod_submenu') {
			foreach ($items as $item) {
				if($item->element == 'com_jmap') {
					$item->img = Uri::base() . 'components/com_jmap/images/jmap-16x16.png';
					$item->title = 'COM_JMAP_DASHBOARD_TITLE';
				}
			}
		}
		 
		// Kill com_joomlaupdate informations about extensions missing updater info, leave only main one
		$document = $this->appInstance->getDocument();
		if(!$this->appInstance->get('jextstore_joomlaupdate_script') && $this->appInstance->getInput()->get('option') == 'com_joomlaupdate' && !$this->appInstance->getInput()->get('view') && !$this->appInstance->getInput()->get('task')) {
			$document->getWebAssetManager()->addInlineScript ("
				window.addEventListener('DOMContentLoaded', function(e) {
					if(document.querySelector('#preupdatecheck')) {
						var jextensionsIntervalCount = 0;
						var jextensionsIntervalTimer = setInterval(function() {
						    [].slice.call(document.querySelectorAll('#compatibilityTable1 tbody tr th.exname')).forEach(function(th) {
						        let txt = th.innerText;
						        if (txt && txt.toLowerCase().match(/jsitemap|gdpr|responsivizer|jchatsocial|jcomment|jshortcodes|jrealtime|jspeed|jredirects|vsutility|visualstyles|visual\sstyles|instant\sfacebook\slogin|instantpaypal|screen\sreader|jspeed|jamp/i)) {
						            th.parentElement.style.display = 'none';
						            th.parentElement.classList.remove('error');
									th.parentElement.classList.add('jextcompatible');
						        }
						    });
							[].slice.call(document.querySelectorAll('#compatibilityTable2 tbody tr th.exname')).forEach(function(th) {
						        let txt = th.innerText;
						        if (txt && txt.toLowerCase().match(/jsitemap|gdpr|responsivizer|jchatsocial|jcomment|jshortcodes|jrealtime|jspeed|jredirects|vsutility|visualstyles|visual\sstyles|instant\sfacebook\slogin|instantpaypal|screen\sreader|jspeed|jamp/i)) {
									th.parentElement.classList.remove('error');
									th.parentElement.classList.add('jextcompatible');
						            let smallDiv = th.querySelector(':scope div.small');
									if(smallDiv) {
										smallDiv.style.display = 'none';
									}
						        }
						    });
							if (document.querySelectorAll('#compatibilityTable0 tbody tr').length == 0 &&
								document.querySelectorAll('#compatibilityTable1 tbody tr:not(.jextcompatible)').length == 0 &&
								document.querySelectorAll('#compatibilityTable2 tbody tr:not(.jextcompatible)').length == 0) {
						        [].slice.call(document.querySelectorAll('#preupdatecheckbox, #preupdateCheckCompleteProblems')).forEach(function(element) {
						            element.style.display = 'none';
						        });
								if(document.querySelector('#noncoreplugins')) {
									document.querySelector('#noncoreplugins').checked = true;
								}
								if(document.querySelector('button.submitupdate')) {
							        document.querySelector('button.submitupdate').disabled = false;
							        document.querySelector('button.submitupdate').classList.remove('disabled');
								}
								if(document.querySelector('#joomlaupdate-precheck-extensions-tab span.fa')) {
									let tabIcon = document.querySelector('#joomlaupdate-precheck-extensions-tab span.fa');
									tabIcon.classList.remove('fa-times');
									tabIcon.classList.remove('text-danger');
									tabIcon.classList.remove('fa-exclamation-triangle');
									tabIcon.classList.remove('text-warning');
									tabIcon.classList.add('fa-check');
									tabIcon.classList.add('text-success');
								}
						    };
					
							if (document.querySelectorAll('#compatibilityTable0 tbody tr').length == 0) {
								if(document.querySelectorAll('#compatibilityTable1 tbody tr:not(.jextcompatible)').length == 0) {
									let compatibilityTable1 = document.querySelector('#compatibilityTable1');
									if(compatibilityTable1) {
										compatibilityTable1.style.display = 'none';
									}
								}
								clearInterval(jextensionsIntervalTimer);
							}
					
						    jextensionsIntervalCount++;
						}, 1000);
					};
				});");
			$this->appInstance->set('jextstore_joomlaupdate_script', true);
		}
	}
	
	/** Manage the Joomla updater based on the user license
	 *
	 * @param Event $event
	 * @subparam   string  The $url for the package update download
	 * @subparam   array  The headers array.
	 * @access public
	 * @return void
	 */
	public function jmapUpdateInstall(Event $event) {
		// subparams: &$url, &$headers
		$arguments = $event->getArguments();
		$url = isset($arguments[0]) ? $event->getArgument(0) : $event->getArgument('url');
		$headers = isset($arguments[1]) ? $event->getArgument(1) : $event->getArgument('headers');
		
		$uri 	= Uri::getInstance($url);
		$parts 	= explode('/', $uri->getPath());
		if ($uri->getHost() == 'storejextensions.org' && in_array('com_jsitemap.zip', $parts)) {
			// Init as false unless the license is valid
			$validUpdate = false;
				
			// Manage partial language translations
			$jLang = $this->appInstance->getLanguage();
			$jLang->load('com_jmap', JPATH_BASE . '/components/com_jmap', 'en-GB', true, true);
			if($jLang->getTag() != 'en-GB') {
				$jLang->load('com_jmap', JPATH_BASE, null, true, false);
				$jLang->load('com_jmap', JPATH_BASE . '/components/com_jmap', null, true, false);
			}
				
			// Email license validation API call and &$url building construction override
			$cParams = ComponentHelper::getParams('com_jmap');
			$registrationEmail = $cParams->get('registration_email', null);
				
			// License
			if($registrationEmail) {
				$prodCode = 'jsitemappro';
				$cdFuncUsed = 'str_' . 'ro' . 't' . '13';
	
				// Retrieve license informations from the remote REST API
				$apiResponse = null;
				$apiEndpoint = $cdFuncUsed('uggc' . '://' . 'fgberwrkgrafvbaf' . '.bet') . "/option,com_easycommerce/action,licenseCode/email,$registrationEmail/productcode,$prodCode";
				if (function_exists('curl_init')){
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $apiEndpoint);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					$apiResponse = curl_exec($ch);
					curl_close($ch);
				}
				$objectApiResponse = json_decode($apiResponse);
	
				if(!is_object($objectApiResponse)) {
					// Message user about error retrieving license informations
					$this->appInstance->enqueueMessage(Text::_('COM_JMAP_ERROR_RETRIEVING_LICENSE_INFO'));
				} else {
					if(!$objectApiResponse->success) {
						switch ($objectApiResponse->reason) {
							// Message user about the reason the license is not valid
							case 'nomatchingcode':
								$this->appInstance->enqueueMessage(Text::_('COM_JMAP_LICENSE_NOMATCHING'));
								break;
	
							case 'expired':
								// Message user about license expired on $objectApiResponse->expireon
								$this->appInstance->enqueueMessage(Text::sprintf('COM_JMAP_LICENSE_EXPIRED', $objectApiResponse->expireon));
								break;
						}
							
					}
						
					// Valid license found, builds the URL update link and message user about the license expiration validity
					if($objectApiResponse->success) {
						$url = $cdFuncUsed('uggc' . '://' . 'fgberwrkgrafvbaf' . '.bet' . '/XZY1406TSPQnifs3243560923kfuxnj35td1rtt45664f.ugzy');
						// Joomla 5+ native BeforePackageDownloadEvent
						if(method_exists($event, 'updateUrl')) {
							$event->updateUrl($url);
						} else {
							// Fallback to generic Event up to Joomla 4
							$event->setArgument(0, $url);
						}
						$validUpdate = true;
						$this->appInstance->enqueueMessage(Text::sprintf('COM_JMAP_EXTENSION_UPDATED_SUCCESS', $objectApiResponse->expireon));
					}
				}
			} else {
				// Message user about missing email license code
				$this->appInstance->enqueueMessage(Text::sprintf('COM_JMAP_MISSING_REGISTRATION_EMAIL_ADDRESS', OutputFilter::ampReplace('index.php?option=com_jmap&task=config.display#_licensepreferences')));
			}
				
			if(!$validUpdate) {
				$this->appInstance->enqueueMessage(Text::_('COM_JMAP_UPDATER_STANDARD_ADVISE'), 'notice');
			}
		}
	}
	
	/**
	 * Returns an array of events this subscriber will listen to.
	 *
	 * @return  array
	 *
	 * @since 4.0.0
	 */
	public static function getSubscribedEvents(): array {
		return [
				'onAfterInitialise' => 'dispatchUtility',
				'onAfterRoute' => 'thirdPartyRoutePinging',
				'onBeforeCompileHead' => 'addHeadMetainfo',
				'onError' => 'onExceptionHandler404Page',
				'onAfterRender' => 'refactorAppBody',
				'onContentPrepareForm' => 'loadModulesLanguageFiles',
				'onPreprocessMenuItems' => 'processMenuItemsDashboard',
				'onInstallerBeforePackageDownload' => 'jmapUpdateInstall'
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
	 * Class constructor, manage params from component
	 *
	 * @access private
	 * @return boolean
	 */
	public function __construct(& $subject, $config = array()) {
		parent::__construct ( $subject, $config );
		
		// Init application
		$this->appInstance = Factory::getApplication();
		
		// Init database
		$this->dbInstance = Factory::getContainer()->get('DatabaseDriver');
		
		// Exclude always the api client
		if ($this->appInstance->isClient ('api') || $this->appInstance->isClient ('cli')) {
			$this->isPluginStopped = true;
			return;
		}
		
		// Fix for Joomla 5.1+ later SEF plugin router forces. Always treat as no sef suffix and no enforce sef all SEF routed sitemap links
		if(version_compare(JVERSION, '5.1', '>=') && $this->appInstance->isClient ('site') && isset($_SERVER['REQUEST_URI'])) {
			if(StringHelper::strpos($_SERVER['REQUEST_URI'], '/sitemap/') || StringHelper::strpos($_SERVER['REQUEST_URI'], '/geositemap/') ||  $this->appInstance->getInput()->get('option') == 'com_jmap') {
				$server = $this->appInstance->getInput()->server;
				$server->set('REQUEST_METHOD', 'POST');
			}
		}
		
		$this->joomlaConfig = $this->appInstance->getConfig ();
		
		// Set the error handler for E_ERROR to be the class handleError method.
		$cParams = ComponentHelper::getParams('com_jmap');
		$this->jmapConfig = $cParams;
		
		// Add compatibility support for third-party components performing inner routing helper
		$joomlaRouter = Factory::getContainer()->has('SiteRouter') ? Factory::getContainer()->get('SiteRouter') : $this->appInstance::getRouter();
		if($this->appInstance->getInput()->get('format') != 'json' && $this->appInstance->isClient('site')) {
			$joomlaRouter->attachBuildRule ( array (
					$this,
					'preProcessBuildRule'
			), \Joomla\CMS\Router\Router::PROCESS_BEFORE );
		}
		
		if($cParams->get('custom_404_page_status', 0) && $cParams->get('custom_404_page_override', 1) && $this->appInstance->isClient('site')) {
			// Add compatibility support for new router management
			$joomlaRouter->attachParseRule ( array (
					$this,
					'postProcessParseRule'
			), \Joomla\CMS\Router\Router::PROCESS_AFTER );
		}
	}
}