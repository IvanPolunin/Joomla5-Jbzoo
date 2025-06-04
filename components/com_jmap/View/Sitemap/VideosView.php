<?php
namespace JExtstore\Component\JMap\Site\View\Sitemap;
/**
 * @package JMAP::SITEMAP::components::com_jmap
 * @subpackage views
 * @subpackage sitemap
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Component\ComponentHelper;
use JExtstore\Component\JMap\Administrator\Framework\View as JMapView;
use JExtstore\Component\JMap\Administrator\Framework\Http;
use JExtstore\Component\JMap\Administrator\Framework\Language\Multilang as JMapLanguageMultilang;

/**
 * Main view class
 *
 * @package JMAP::SITEMAP::components::com_jmap
 * @subpackage views
 * @subpackage sitemap
 * @since 1.0
 */
class VideosView extends JMapView {
	// Template view variables
	protected $data;
	protected $cparams;
	protected $HTTPClient;
	protected $outputtedVideosBuffer;
	protected $application;
	protected $xslt;
	protected $videoApisEndpoints;
	protected $htmlResponseReference;
	protected $liveSite;
	protected $outputtedLinksBuffer;
	protected $liveSiteCrawler;
	protected $source;
	protected $sourceparams;
	protected $asCategoryTitleField;
	protected $videoID;
	protected $apiJsonResponse;
	protected $videoTitle;
	
	/**
	 * Display the XML sitemap
	 * @access public
	 * @return void
	 */
	function display($tpl = null) {
		$document = $this->document;
		$document->setMimeEncoding('application/xml');
		$session = $this->app->getSession();
		
		// Call by cache handler get no params, so recover from model state
		if(!$tpl) {
			$tpl = $this->getModel ()->getState ( 'documentformat' );
		}
				   
		$this->data = $this->get ( 'SitemapData' );
		$this->cparams = $this->getModel ()->getState ( 'cparams' );
		// Transport wrapper
		$this->HTTPClient = new Http(null, $this->cparams);
		// Reload $this->outputtedVideosBuffer from previous session if process_status === run, AKA an ongoing JS AJAX precaching is running
		$this->outputtedVideosBuffer = $this->app->getInput()->get('process_status', null) === 'run' ? $session->get('com_jmap.videos_buffer') : array();
		$this->application = Factory::getApplication();
		$this->xslt = $this->getModel()->getState('xslt');
		$apiKeys = array('AIzaSyAv8lPTYncjHwkhf3M3lv0BeOx9UltHX98', 'AIzaSyDDtuTvcxYwOonNQYVs2ZVSmQpTNLx_Hm4', 'AIzaSyAIqxXTI3ZcigANSZYx5MQ5vfCs_-fNVso', 'AIzaSyDPfrgYSLe0ABlbat-U54s5JQpFKvkEDpQ', 'AIzaSyBX3XTuiah3dXZ8CR4lo23GUIUtFEqH228', 'AIzaSyAiKyJSgLGzK5aLovJfRmHOw5hk8BM-V_8');
		$youtubeVideosApikey = $apiKeys[array_rand($apiKeys)];
		$this->videoApisEndpoints = array('youtube'=>'https://www.googleapis.com/youtube/v3/videos?id=%s&key=' . $youtubeVideosApikey . '&part=snippet,contentDetails',
										  'vimeo'=>'https://vimeo.com/api/oembed.json?url=https://vimeo.com/%s',
										  'dailymotion'=>'https://api.dailymotion.com/video/%s?fields=title,duration,description,thumbnail_360_url');
		$this->htmlResponseReference = null;
		
		$uriInstance = Uri::getInstance();
		$customHttpPort = trim($this->cparams->get('custom_http_port', ''));
		$getPort = $customHttpPort ? ':' . $customHttpPort : '';
		
		$customDomain = trim($this->cparams->get('custom_sitemap_domain', ''));
		$getDomain = $customDomain ? rtrim($customDomain, '/') : rtrim($uriInstance->getScheme() . '://' . $uriInstance->getHost(), '/');

		if($this->cparams->get('append_livesite', true)) {
			$this->liveSite = rtrim($getDomain . $getPort, '/');
		} else {
			$this->liveSite = null;
		}
		
		// Initialize output links buffer with exclusion for links
		$this->outputtedLinksBuffer = $this->getModel()->getExcludedLinks($this->liveSite);
		
		// Crawler live site management
		if($this->cparams->get('sh404sef_multilanguage', 0) && JMapLanguageMultilang::isEnabled()) {
			$lang = '/' . $this->app->getInput()->get('lang');
			// Check if sh404sef insert language code param is off, otherwise the result would be doubled language chunk in liveSiteCrawler
			$sh404SefParams = ComponentHelper::getParams('com_sh404sef');
			if($sh404SefParams->get('shInsertLanguageCode', 0) || !$sh404SefParams->get('Enabled', 1)) {
				$lang = null;
			}
			$this->liveSiteCrawler = rtrim($getDomain . $getPort . $lang, '/');
		} else {
			$this->liveSiteCrawler = rtrim($getDomain . $getPort, '/');
		}
		
		// Check if the live site crawler must be forced to the non https domain
		if($this->cparams->get('force_crawler_http', 0)) {
			$this->liveSiteCrawler = str_replace('https://', 'http://', $this->liveSiteCrawler);
		}
		
		// Add include path
		$this->addTemplatePath(JPATH_COMPONENT . '/tmpl/sitemap/videos');
		$this->setLayout('default');
		parent::display ($tpl);
		
		// Assign $this->outputtedVideosBuffer for next session if process_status == start/run
		if(in_array($this->app->getInput()->get('process_status', null), array('start', 'run'))) {
			$session->set('com_jmap.videos_buffer', $this->outputtedVideosBuffer);
		}
		// Delete $this->outputtedVideosBuffer session if process_status == end
		if($this->app->getInput()->get('process_status', null) === 'end') {
			$session->remove('com_jmap.videos_buffer');
		}
	}
}