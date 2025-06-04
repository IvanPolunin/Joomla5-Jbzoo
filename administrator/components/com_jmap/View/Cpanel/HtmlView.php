<?php
namespace JExtstore\Component\JMap\Administrator\View\Cpanel;
/**
 * @package JMAP::CPANEL::administrator::components::com_jmap
 * @subpackage views
 * @subpackage cpanel
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\SiteRouter;
use Joomla\Filter\OutputFilter;
use JExtstore\Component\JMap\Administrator\Framework\Helpers\Toolbars as ToolbarHelper;
use JExtstore\Component\JMap\Administrator\Framework\View as JMapView;
use JExtstore\Component\JMap\Administrator\Framework\Language\Multilang as JMapMultilang;

/**
 * CPanel view
 *
 * @package JMAP::CPANEL::administrator::components::com_jmap
 * @subpackage views
 * @subpackage cpanel
 * @since 1.0
 */
class HtmlView extends JMapView {
	// Template view variables
	protected $icons;
	protected $livesite;
	protected $livesitesef;
	protected $componentParams;
	protected $infodata;
	protected $lists;
	protected $indexnowApikey;
	protected $updatesData;
	protected $currentVersion;
	protected $siteRouter;
	protected $showSefLinks;
	protected $joomlaSefLinks;
	protected $siteItemid;
	protected $robotsVersion;
	protected $record;
	
	/**
	 * Render iconset for cpanel
	 *
	 * @param $link string
	 * @param $image string
	 * @access private
	 * @return string
	 */
	private function getIcon($link, $image, $text, $target = '', $title = null, $class = null) {
		$app = Factory::getApplication ();
		$lang = $app->getLanguage ();
		?>
		<div style="float:<?php echo ($lang->isRTL()) ? 'right' : 'left'; ?>;">
			<div class="icon">
				<a <?php echo $title . $class;?> <?php echo $target;?> href="<?php echo OutputFilter::ampReplace($link); ?>" role="button">
					<img src="components/com_jmap/images/<?php echo $image;?>" />
					<span><?php echo $text; ?></span>
				</a>
			</div>
		</div>
		<?php
	}
	
	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addDisplayToolbar() {
		ToolbarHelper::title( Text::_('COM_JMAP_CPANEL_TOOLBAR' ), 'jmap' );
		ToolbarHelper::custom('cpanel.display', 'home', 'home', 'COM_JMAP_CPANEL', false);
	}
	
	/**
	 * Control panel display
	 *        	
	 * @access public
	 * @param string $tpl
	 * @return void
	 */
	public function display($tpl = null) {
		$doc = $this->document;
		$componentParams = $this->getModel()->getState('cparams');
		$base = Uri::root();
		$this->loadJQuery($doc);
		$this->loadBootstrap($doc);
		$doc->getWebAssetManager()->registerAndUseStyle ( 'jmap.cpanel', 'administrator/components/com_jmap/css/cpanel.css');
		$doc->getWebAssetManager()->registerAndUseStyle ( 'jmap.jquery.fancybox', 'administrator/components/com_jmap/css/jquery.fancybox.css');
		$doc->getWebAssetManager()->registerAndUseScript ('jmap.chart', 'administrator/components/com_jmap/js/chart.js', [], [], ['jquery'] );
		$doc->getWebAssetManager()->registerAndUseScript ('jmap.cpanel', 'administrator/components/com_jmap/js/cpanel.js', [], [], ['jquery', 'jmap.boostrap-interface', 'jmap.chart'] );
		$doc->getWebAssetManager()->registerAndUseScript ('jmap.analyzer', 'administrator/components/com_jmap/js/analyzer.js', [], [], ['jquery'] );
		$doc->getWebAssetManager()->registerAndUseScript ('jmap.metainfo', 'administrator/components/com_jmap/js/metainfo.js', [], [], ['jquery'] );
		$doc->getWebAssetManager()->registerAndUseScript ('jmap.seospider', 'administrator/components/com_jmap/js/seospider.js', [], [], ['jquery'] );
		$ampSitemapEnabled = $componentParams->get('amp_sitemap_enabled', 0) && $componentParams->get('amp_suffix', null);
		
		if($componentParams->get('seostats_enabled', 1)) {
			$doc->getWebAssetManager()->registerAndUseScript ('jmap.seostats', 'administrator/components/com_jmap/js/seostats.js', [], [], ['jquery'] );
		}
		
		if($componentParams->get('enable_precaching', 0)) {
			// Check if multilanguage is enabled and the remove default prefix is active
			$pluginLangFilter = PluginHelper::getPlugin('system', 'languagefilter');
			$removeDefaultPrefix = @json_decode($pluginLangFilter->params)->remove_default_prefix;
			$doc->getWebAssetManager()->addInlineScript("var jmap_removedefaultprefix=" . (int)$removeDefaultPrefix . ";" .
														"var jmap_sh404sefmultilang=" . (int)$componentParams->get('sh404sef_multilanguage', 0) . ";" .
														"var jmap_multilanguageRawurls=" . (int)$componentParams->get('precaching_multilanguage_rawurls', 0) . ";" .
														"var jmap_ampsitemapenabled=" . (int)$componentParams->get('amp_sitemap_enabled', 0) . ";");
			$doc->getWebAssetManager()->registerAndUseScript ('jmap.xmlprecaching', 'administrator/components/com_jmap/js/xmlprecaching.js', [], [], ['jquery'] );
		}
		
		// Evaluate nonce csp feature
		$appNonce = $this->app->get('csp_nonce', null);
		$nonce = $appNonce ? ' nonce="' . $appNonce . '"' : '';
		$doc->addCustomTag ('<script type="text/javascript"' . $nonce . ' src="' . Uri::root ( true ) . '/administrator/components/com_jmap/js/jquery.fancybox.pack.js' . '"></script>');
		
		if($componentParams->get('geositemap_enabled', 0) && $componentParams->get('geositemap_address', null)) {
			$doc->getWebAssetManager()->addInlineScript("var initMap=function(){return true};var jmap_geositemapAddress='" . addslashes($componentParams->get('geositemap_address', '')) . "';");
			$doc->getWebAssetManager()->registerAndUseScript ('jmap.mapsapi', 'https://maps.googleapis.com/maps/api/js?key=AIzaSyDNlp3wN1Al_ksW92rmb5Y96RQGn68tKb8&callback=initMap', [], ['defer'=>true]);
			$doc->getWebAssetManager()->registerAndUseScript ('jmap.gmap', 'administrator/components/com_jmap/js/gmap.js', [], ['defer'=>true], ['jquery', 'jmap.mapsapi'] );
		}
		
		// Inject js translations
		$translations = array (	'COM_JMAP_ROBOTSPROGRESSTITLE',
							  	'COM_JMAP_ROBOTSPROGRESSSUBTITLE',
							  	'COM_JMAP_ROBOTSPROGRESSSUBTITLESUCCESS',
								'COM_JMAP_ROBOTSPROGRESSSUBTITLEERROR',
								'COM_JMAP_PROGRESSMODELSUBTITLE',
								'COM_JMAP_PRECACHING_TITLE',
								'COM_JMAP_START_PRECACHING_PROCESS',
								'COM_JMAP_PRECACHING_NO_DATASOURCES_FOUND',
								'COM_JMAP_PRECACHING_PROCESS_RUNNING',
								'COM_JMAP_PRECACHING_PROCESS_COMPLETED',
								'COM_JMAP_PRECACHING_REPORT_DATASOURCE',
								'COM_JMAP_PRECACHING_REPORT_DATASOURCE_TYPE',
								'COM_JMAP_PRECACHING_REPORT_LINKS',
								'COM_JMAP_PRECACHING_DATA_SOURCE_COMPLETED',
								'COM_JMAP_PRECACHING_DATASOURCES_RETRIEVED',
								'COM_JMAP_PRECACHING_PROCESS_FINALIZING',
								'COM_JMAP_PRECACHING_INTERRUPT',
								'COM_JMAP_PRECACHING_CACHED',
								'COM_JMAP_PRECACHING_NOT_CACHED',
								'COM_JMAP_PRECACHING_CLEARING',
								'COM_JMAP_PRECACHING_CLEAR_CACHE',
								'COM_JMAP_PUBLISHED_DATA_SOURCE_CHART',
								'COM_JMAP_TOTAL_DATA_SOURCE_CHART',
								'COM_JMAP_DATASETS_CHART',
								'COM_JMAP_MENU_DATA_SOURCE_CHART',
								'COM_JMAP_USER_DATA_SOURCE_CHART',
								'COM_JMAP_ANALYZER_TITLE',
								'COM_JMAP_ANALYZER_PROCESS_RUNNING',
								'COM_JMAP_ANALYZER_STARTED_SITEMAP_GENERATION',
								'COM_JMAP_ANALYZER_ERROR_STORING_FILE',
								'COM_JMAP_ANALYZER_GENERATION_COMPLETE',
								'COM_JMAP_METAINFO_TITLE',
								'COM_JMAP_METAINFO_PROCESS_RUNNING',
								'COM_JMAP_METAINFO_STARTED_SITEMAP_GENERATION',
								'COM_JMAP_METAINFO_ERROR_STORING_FILE',
								'COM_JMAP_METAINFO_GENERATION_COMPLETE',
								'COM_JMAP_SEOSTATS_LOADING',
								'COM_JMAP_STATSCROP_GRAPH_TODAY',
								'COM_JMAP_WEBSITE_SCREEN',
								'COM_JMAP_SEMRUSH_GRAPH',
								'COM_JMAP_NULL_RESPONSEDATA',
								'COM_JMAP_ERROR_HTTP',
								'COM_JMAP_CLICKTOUPDATE',
								'COM_JMAP_EXPIREON',
								'COM_JMAP_UPDATEPROGRESSTITLE',
								'COM_JMAP_DOWNLOADING_UPDATE_SUBTITLE',
								'COM_JMAP_INSTALLING_UPDATE_SUBTITLE',
								'COM_JMAP_COMPLETED_UPDATE_SUBTITLE',
								'COM_JMAP_PINGING_SITEMAP_TOBAIDU',
								'COM_JMAP_PINGING_SITEMAP_TOBAIDU_PLEASEWAIT',
								'COM_JMAP_PINGING_SITEMAP_TOBAIDU_COMPLETE',
								'COM_JMAP_PINGING_SITEMAP_TOBING',
								'COM_JMAP_PINGING_SITEMAP_TOBING_PLEASEWAIT',
								'COM_JMAP_PINGING_SITEMAP_TOBING_COMPLETE',
								'COM_JMAP_PINGING_SITEMAP_TOGOOGLE',
								'COM_JMAP_PINGING_SITEMAP_TOGOOGLE_PLEASEWAIT',
								'COM_JMAP_PINGING_SITEMAP_TOGOOGLE_COMPLETE',
								'COM_JMAP_SEOSPIDER_TITLE',
								'COM_JMAP_SEOSPIDER_PROCESS_RUNNING',
								'COM_JMAP_SEOSPIDER_STARTED_SITEMAP_GENERATION',
								'COM_JMAP_SEOSPIDER_ERROR_STORING_FILE',
								'COM_JMAP_SEOSPIDER_GENERATION_COMPLETE',
								'COM_JMAP_SEOSPIDER_PAGELOAD_FAST',
								'COM_JMAP_SEOSPIDER_PAGELOAD_AVERAGE',
								'COM_JMAP_SEOSPIDER_PAGELOAD_SLOW',
								'COM_JMAP_CRONJOB_GENERATED_SITEMAP_FILE',
								'COM_JMAP_WRITE_XML_WRITE',
								'COM_JMAP_WRITE_XML_WRITING',
								'COM_JMAP_WRITE_XML_SITEMAP_WRITTEN',
								'COM_JMAP_WRITE_XML_SITEMAP_URL',
								'COM_JMAP_PING_SITEMAP_CRONJOB',
								'COM_JMAP_ROBOTS_SITEMAP_ENTRY_CRONJOB',
								'COM_JMAP_PING_GOOGLE',
								'COM_JMAP_PING_BING',
								'COM_JMAP_PING_YANDEX',
								'COM_JMAP_PING_BAIDU',
								'COM_JMAP_COMPETITOR_STATS',
								'COM_JMAP_COMPETITOR_GOOGLE_PAGES',
								'COM_JMAP_COMPETITOR_GOOGLE_BACKLINKS',
								'COM_JMAP_COMPETITOR_GOOGLE_RELATED',
								'COM_JMAP_COMPETITOR_BING_PAGES',
								'COM_JMAP_COMPETITOR_BING_BACKLINKS',
								'COM_JMAP_COPIED_LINK'
		);
		$this->injectJsTranslations($translations, $doc);
		
		// Check for custom link domain
		$customDomain = trim($componentParams->get('custom_sitemap_domain', ''));
		$livesite = $customDomain ? rtrim($customDomain, '/') : substr_replace(Uri::root(), "", -1, 1);
		
		if($customDomain) {
			$customHttpPort = trim($componentParams->get('custom_http_port', ''));
			$getPort = $customHttpPort ? ':' . $customHttpPort : null;
			if($getPort) {
				$livesite = rtrim($livesite . $getPort, '/');
			}
			
			$adminRoute = \JMapRoute::_('index.php');
			$pathSubdomain = explode('/administrator', $adminRoute);
			if(!empty($pathSubdomain[0])) {
				$livesite = rtrim($livesite . $pathSubdomain[0], '/');
			}
		}
		
		$user = $this->app->getIdentity();
		$seoStatsCustomLink = $componentParams->get('seostats_custom_link', null);
		
		// Store predefined site language
		$defaultLanguageCode = JMapMultilang::getDefaultSiteLanguage();
		
		$lists = $this->get('Lists');
		$infoData = $this->get('Data');
		$doc->getWebAssetManager()->addInlineScript('var jmapChartData = ' . json_encode($infoData));
		$doc->getWebAssetManager()->addInlineScript("var jmap_baseURI='$base';" .
													"var jmap_linksRandom=" . $componentParams->get('sitemap_links_random', 0) . ";" .
													"var jmap_forceFormat=" . $componentParams->get('sitemap_links_forceformat', 0) . ";" .
													"var jmap_validationAnalysis=" . $componentParams->get('linksanalyzer_validation_analysis', 2) . ";" .
													"var jmap_splittingStatus=" . $componentParams->get('split_sitemap', 0) . ";" .
													"var jmap_seostats_service='" . $componentParams->get('seostats_service', 'statscrop') . "';" .
													"var jmap_seostats_targeturl='" . ($seoStatsCustomLink ? $seoStatsCustomLink : $base) . "';" .
													"var jmap_indexnow_apikey='" . $componentParams->get('indexnow_apikey', '28bcb027f9b443719ceac7cd30556c3c') . "';" .
													"var jmap_default_language_code='" . $defaultLanguageCode . "';" .
													"var jmap_livesite='" . $livesite . "';");
		
		// Assign SEF mode
		$this->siteRouter = Factory::getContainer()->has('SiteRouter') ? Factory::getContainer()->get('SiteRouter'): SiteRouter::getInstance('site');
		$this->showSefLinks = $componentParams->get('sitemap_links_sef', false);
		$this->joomlaSefLinks = $this->app->getConfig()->get('sef', true);
		$this->siteItemid = null;
		if($this->showSefLinks && $this->joomlaSefLinks) {
			$siteItemid = trim($componentParams->get('site_itemid', ''));
			if($siteItemid && is_numeric($siteItemid)) {
				$menuItem = Factory::getContainer()->get(\Joomla\CMS\Menu\MenuFactoryInterface::class)->createMenu('site')->getItem((int)$siteItemid);
				if(isset($menuItem->alias)) {
					$menuAlias = $menuItem->alias;
					$doc->getWebAssetManager()->addInlineScript("var jmap_sef_alias_links='$menuAlias';");
					$this->siteItemid = '&Itemid=' . (int)$siteItemid;
				}
			}
		}
		
		// Buffer delle icons
		ob_start ();
		$this->getIcon ( 'index.php?option=com_jmap&task=sources.display', 'icon-48-data.png', Text::_('COM_JMAP_SITEMAP_SOURCES' ), '', 'title="' . Text::_('COM_JMAP_SITEMAP_SOURCES' ) . '"');
		$this->getIcon ( 'index.php?option=com_jmap&task=wizard.display', 'icon-48-wizard.png', Text::_('COM_JMAP_NEW_WIZARD_DATASOURCE' ), '', 'title="' . Text::_('COM_JMAP_NEW_WIZARD_DATASOURCE' ) . '"');
		$this->getIcon ( $livesite . '/index.php?option=com_jmap&view=sitemap', 'icon-48-html_sitemap.png', Text::_('COM_JMAP_SHOW_HTML_MAP' ), 'target="_blank"', 'title="' . Text::_('COM_JMAP_SHOW_HTML_MAP' ) . '"', 'data-role="torefresh"' );
		$this->getIcon ( '#xmlsitemap', 'icon-48-xml_sitemap.png', Text::_('COM_JMAP_SHOW_XML_MAP' ), '', 'title="' . Text::_('COM_JMAP_SHOW_XML_MAP' ) . '"', 'class="fancybox"' );
		$this->getIcon ( '#xmlsitemap_xslt', 'icon-48-xsl_sitemap.png', Text::_('COM_JMAP_SHOW_XML_MAP_XSLT' ), '', 'title="' . Text::_('COM_JMAP_SHOW_XML_MAP_XSLT' ) . '"', 'class="fancybox"' );
		$this->getIcon ( '#xmlsitemap_export', 'icon-48-xml_export.png', Text::_('COM_JMAP_EXPORT_XML_SITEMAP' ), '', 'title="' . Text::_('COM_JMAP_EXPORT_XML_SITEMAP' ) . '"', 'class="fancybox"' );
		$this->getIcon ( substr_replace(Uri::root(), "", -1, 1) . '/index.php?option=com_jmap&task=sitemap.exportxml&format=xml', 'icon-48-analyze.png', Text::_('COM_JMAP_ANALYZE_MAP' ), '', 'title="' . Text::_('COM_JMAP_ANALYZE_MAP' ) . '"', 'class="jmap_analyzer"' );
		$this->getIcon ( substr_replace(Uri::root(), "", -1, 1) . '/index.php?option=com_jmap&task=sitemap.exportxml&format=xml', 'icon-48-seospider.png', Text::_('COM_JMAP_SEOSPIDER' ), '', 'title="' . Text::_('COM_JMAP_SEOSPIDER' ) . '"', 'class="jmap_seospider"' );
		$this->getIcon ( substr_replace(Uri::root(), "", -1, 1) . '/index.php?option=com_jmap&task=sitemap.exportxml&format=xml', 'icon-48-metainfo.png', Text::_('COM_JMAP_METAINFO' ), '', 'title="' . Text::_('COM_JMAP_METAINFO' ) . '"', 'class="jmap_metainfo"' );
		$this->getIcon ( 'index.php?option=com_jmap&task=indexing.display', 'icon-48-indexing.png', Text::_('COM_JMAP_SITEMAP_INDEXING' ), '', 'title="' . Text::_('COM_JMAP_SITEMAP_INDEXING' ) . '"');
		$this->getIcon ( 'index.php?option=com_jmap&task=datasets.display', 'icon-48-datasets.png', Text::_('COM_JMAP_SITEMAP_DATASETS' ), '', 'title="' . Text::_('COM_JMAP_SITEMAP_DATASETS' ) . '"');
		
		if($user->authorise('core.edit', 'com_jmap')) {
			$this->getIcon ( 'index.php?option=com_jmap&task=cpanel.editEntity', 'icon-48-robots.png', Text::_('COM_JMAP_ROBOTS_EDITOR' ), '', 'title="' . Text::_('COM_JMAP_ROBOTS_EDITOR' ) . '"', 'class="fancybox_iframe"' );
			$this->getIcon ( 'index.php?option=com_jmap&task=htaccess.editEntity', 'icon-48-htaccess.png', Text::_('COM_JMAP_HTACCESS_EDITOR' ), '', 'title="' . Text::_('COM_JMAP_HTACCESS_EDITOR' ) . '"', 'class="fancybox_iframe"' );
		}
		$this->getIcon ( 'index.php?option=com_jmap&task=pingomatic.display', 'icon-48-pingomatic.png', Text::_('COM_JMAP_PINGOMATIC_LINKS' ), '', 'title="' . Text::_('COM_JMAP_PINGOMATIC_LINKS' ) . '"');
		$this->getIcon ( 'index.php?option=com_jmap&task=aigenerator.display', 'icon-48-aigenerator.png', Text::_('COM_JMAP_AIGENERATOR' ), '', 'title="' . Text::_('COM_JMAP_AIGENERATOR' ) . '"');
		
		if($componentParams->get('enable_pattern_replacements', 'enabled') == 'enabled') {
			$this->getIcon ( 'index.php?option=com_jmap&task=patterns.display', 'icon-48-replacement.png', Text::_ ( 'COM_JMAP_PATTERNS_REPLACEMENT' ) );
		}
		
		$this->getIcon ( '#rssfeed', 'icon-48-rss-feed.png', Text::_('COM_JMAP_SHOW_RSS_FEED' ), '', 'title="' . Text::_('COM_JMAP_SHOW_RSS_FEED' ) . '"', 'class="fancybox rss"' );
		
		// Access check.
		if ($user->authorise('jmap.google', 'com_jmap')) {
			$analyticsService = $componentParams->get('analytics_service', 'google');
			$analyticsGoogleStats = $analyticsService == 'google' ? null : '&googlestats=' . $analyticsService . 'fetch';
			$stringText = 'COM_JMAP_' . strtoupper($analyticsService);
			$stringtextTitle = 'COM_JMAP_' . strtoupper($analyticsService) . '_ANALYTICS_TITLE';
			$this->getIcon ( 'index.php?option=com_jmap&task=google.display&googlestats=webmasters', 'icon-48-googlewebmasters.png', Text::_('COM_JMAP_GOOGLE_WEBMASTERS' ), '', 'title="' . Text::_('COM_JMAP_GOOGLE_WEBMASTERS_TITLE' ) . '"');
			$this->getIcon ( 'index.php?option=com_jmap&task=google.display' . $analyticsGoogleStats, 'icon-48-' . $analyticsService . '.png', Text::_($stringText), '', 'title="' . Text::_($stringtextTitle) . '"');
			$this->getIcon ( 'index.php?option=com_jmap&task=google.display&googlestats=pagespeedfetch', 'icon-48-googlepagespeed.png', Text::_('COM_JMAP_GOOGLE_PAGESPEED' ), '', 'title="' . Text::_('COM_JMAP_GOOGLE_PAGESPEED_TITLE' ) . '"');
		}
		
		// Access check.
		if ($user->authorise('core.admin', 'com_jmap')) {
			$this->getIcon ( 'index.php?option=com_jmap&task=config.display', 'icon-48-config.png', Text::_('COM_JMAP_CONFIG' ), '', 'title="' . Text::_('COM_JMAP_CONFIG' ) . '"' );
		}
		
		$this->getIcon ( 'https://storejextensions.org/jsitemap_professional_documentation.html', 'icon-48-help.png', Text::_('COM_JMAP_HELPTITLE' ), '', 'title="' . Text::_('COM_JMAP_HELPTITLE' ) . '"' );
		
		echo '<div style="display:none" id="xmlsitemap">';
		$this->getIcon ( $livesite . '/index.php?option=com_jmap&view=sitemap&format=xml', 'icon-48-xml_sitemap_standard.png', Text::_('COM_JMAP_SHOW_XML_STANDARD_MAP' ), 'target="_blank"' );
		$this->getIcon ( $livesite . '/index.php?option=com_jmap&view=sitemap&format=images', 'icon-48-xml_sitemap_images.png', Text::_('COM_JMAP_SHOW_XML_IMAGES_MAP' ), 'target="_blank"' );
		$this->getIcon ( $livesite . '/index.php?option=com_jmap&view=sitemap&format=gnews', 'icon-48-xml_sitemap_gnews.png', Text::_('COM_JMAP_SHOW_XML_GNEWS_MAP' ), 'target="_blank"' );
		$this->getIcon ( $livesite . '/index.php?option=com_jmap&view=sitemap&format=mobile', 'icon-48-xml_sitemap_mobile.png', Text::_('COM_JMAP_SHOW_XML_MOBILE_MAP' ), 'target="_blank"' );
		$this->getIcon ( $livesite . '/index.php?option=com_jmap&view=sitemap&format=videos', 'icon-48-xml_sitemap_videos.png', Text::_('COM_JMAP_SHOW_XML_VIDEOS_MAP' ), 'target="_blank"' );
		if($ampSitemapEnabled) {
			$this->getIcon ( $livesite . '/index.php?option=com_jmap&view=sitemap&format=amp', 'icon-48-xml_sitemap_amp.png', Text::_('COM_JMAP_SHOW_XML_AMP_MAP' ), 'target="_blank"' );
		}
		$this->getIcon ( $livesite . '/index.php?option=com_jmap&view=geositemap&format=xml', 'icon-48-xml_sitemap_geoxml.png', Text::_('COM_JMAP_SHOW_XML_GEOSITEMAP_MAP' ), 'data-language="1" target="_blank"' );
		echo '</div>';
		
		echo '<div style="display:none" id="xmlsitemap_xslt">';
		$this->getIcon ( $livesite . '/index.php?option=com_jmap&view=sitemap&format=xml&xslt=1', 'icon-48-xml_sitemap_standard.png', Text::_('COM_JMAP_SHOW_XML_STANDARD_MAP' ), 'target="_blank"' );
		$this->getIcon ( $livesite . '/index.php?option=com_jmap&view=sitemap&format=images&xslt=1', 'icon-48-xml_sitemap_images.png', Text::_('COM_JMAP_SHOW_XML_IMAGES_MAP' ), 'target="_blank"' );
		$this->getIcon ( $livesite . '/index.php?option=com_jmap&view=sitemap&format=gnews&xslt=1', 'icon-48-xml_sitemap_gnews.png', Text::_('COM_JMAP_SHOW_XML_GNEWS_MAP' ), 'target="_blank"' );
		$this->getIcon ( $livesite . '/index.php?option=com_jmap&view=sitemap&format=mobile&xslt=1', 'icon-48-xml_sitemap_mobile.png', Text::_('COM_JMAP_SHOW_XML_MOBILE_MAP' ), 'target="_blank"' );
		$this->getIcon ( $livesite . '/index.php?option=com_jmap&view=sitemap&format=videos&xslt=1', 'icon-48-xml_sitemap_videos.png', Text::_('COM_JMAP_SHOW_XML_VIDEOS_MAP' ), 'target="_blank"' );
		$this->getIcon ( $livesite . '/index.php?option=com_jmap&view=sitemap&format=hreflang&xslt=1', 'icon-48-xml_sitemap_hreflang.png', Text::_('COM_JMAP_SHOW_XML_HREFLANG_MAP' ), 'data-language="1" target="_blank"' );
		if($ampSitemapEnabled) {
			$this->getIcon ( $livesite . '/index.php?option=com_jmap&view=sitemap&format=amp&xslt=1', 'icon-48-xml_sitemap_amp.png', Text::_('COM_JMAP_SHOW_XML_AMP_MAP' ), 'target="_blank"' );
		}
		echo '</div>';
		
		echo '<div style="display:none" id="xmlsitemap_export">';
			$this->getIcon ( $livesite . '/index.php?option=com_jmap&task=sitemap.exportxml&format=xml', 'icon-48-xml_sitemap_standard.png', Text::_('COM_JMAP_EXPORT_XML_STANDARD_MAP' ));
			$this->getIcon ( $livesite . '/index.php?option=com_jmap&task=sitemap.exportxml&format=images', 'icon-48-xml_sitemap_images.png', Text::_('COM_JMAP_EXPORT_XML_IMAGES_MAP' ));
			$this->getIcon ( $livesite . '/index.php?option=com_jmap&task=sitemap.exportxml&format=gnews', 'icon-48-xml_sitemap_gnews.png', Text::_('COM_JMAP_EXPORT_XML_GNEWS_MAP' ));
			$this->getIcon ( $livesite . '/index.php?option=com_jmap&task=sitemap.exportxml&format=mobile', 'icon-48-xml_sitemap_mobile.png', Text::_('COM_JMAP_EXPORT_XML_MOBILE_MAP' ));
			$this->getIcon ( $livesite . '/index.php?option=com_jmap&task=sitemap.exportxml&format=videos', 'icon-48-xml_sitemap_videos.png', Text::_('COM_JMAP_EXPORT_XML_VIDEOS_MAP' ));
			$this->getIcon ( $livesite . '/index.php?option=com_jmap&task=sitemap.exportxml&format=hreflang', 'icon-48-xml_sitemap_hreflang.png', Text::_('COM_JMAP_EXPORT_XML_HREFLANG_MAP' ), '', null, 'data-language="1"');
			if($ampSitemapEnabled) {
				$this->getIcon ( $livesite . '/index.php?option=com_jmap&task=sitemap.exportxml&format=amp', 'icon-48-xml_sitemap_amp.png', Text::_('COM_JMAP_EXPORT_XML_AMP_MAP' ));
			}
			
			echo '<div class="jmap_clearer"></div>';
			
			$this->getIcon ( $livesite . '/index.php?option=com_jmap&task=sitemap.exportxml&format=xml', 'icon-48-xml_sitemap_standard.png', Text::_('COM_JMAP_WRITE_XML_STANDARD_MAP' ), '', null, 'class="writerbutton"');
			$this->getIcon ( $livesite . '/index.php?option=com_jmap&task=sitemap.exportxml&format=images', 'icon-48-xml_sitemap_images.png', Text::_('COM_JMAP_WRITE_XML_IMAGES_MAP' ), '', null, 'class="writerbutton"');
			$this->getIcon ( $livesite . '/index.php?option=com_jmap&task=sitemap.exportxml&format=gnews', 'icon-48-xml_sitemap_gnews.png', Text::_('COM_JMAP_WRITE_XML_GNEWS_MAP' ), '', null, 'class="writerbutton"');
			$this->getIcon ( $livesite . '/index.php?option=com_jmap&task=sitemap.exportxml&format=mobile', 'icon-48-xml_sitemap_mobile.png', Text::_('COM_JMAP_WRITE_XML_MOBILE_MAP' ), '', null, 'class="writerbutton"');
			$this->getIcon ( $livesite . '/index.php?option=com_jmap&task=sitemap.exportxml&format=videos', 'icon-48-xml_sitemap_videos.png', Text::_('COM_JMAP_WRITE_XML_VIDEOS_MAP' ), '', null, 'class="writerbutton"');
			$this->getIcon ( $livesite . '/index.php?option=com_jmap&task=sitemap.exportxml&format=hreflang', 'icon-48-xml_sitemap_hreflang.png', Text::_('COM_JMAP_WRITE_XML_HREFLANG_MAP' ), '', null, 'data-language="1" class="writerbutton"');
			if($ampSitemapEnabled) {
				$this->getIcon ( $livesite . '/index.php?option=com_jmap&task=sitemap.exportxml&format=amp', 'icon-48-xml_sitemap_amp.png', Text::_('COM_JMAP_WRITE_XML_AMP_MAP' ), '', null, 'class="writerbutton"');
			}

			echo '<div class="xmlcontainer"><label class="badge bg-primary">' . Text::_('COM_JMAP_CRONJOB_XML_LINK') . '</label>';?>
					<input data-role="sitemap_links" class="sitemap_links hasClickPopover" type="text" value="<?php echo OutputFilter::ampReplace($livesite . '/index.php?option=com_jmap&task=sitemap.exportxml&format=xml&cronjobclient=1');?>" />
				 </div>
			<?php 
			echo '<div class="xmlcontainer"><label class="badge bg-primary">' . Text::_('COM_JMAP_CRONJOB_IMAGES_LINK') . '</label>';?>
					<input data-role="sitemap_links" class="sitemap_links hasClickPopover" type="text" value="<?php echo OutputFilter::ampReplace($livesite . '/index.php?option=com_jmap&task=sitemap.exportxml&format=images&cronjobclient=1');?>" />
				 </div>
			<?php 
			echo '<div class="xmlcontainer"><label class="badge bg-primary">' . Text::_('COM_JMAP_CRONJOB_GNEWS_LINK') . '</label>';?>
					<input data-role="sitemap_links" class="sitemap_links hasClickPopover" type="text" value="<?php echo OutputFilter::ampReplace($livesite . '/index.php?option=com_jmap&task=sitemap.exportxml&format=gnews&cronjobclient=1');?>" />
				 </div>
			<?php 
			echo '<div class="xmlcontainer"><label class="badge bg-primary">' . Text::_('COM_JMAP_CRONJOB_MOBILE_LINK') . '</label>';?>
					<input data-role="sitemap_links" class="sitemap_links hasClickPopover" type="text" value="<?php echo OutputFilter::ampReplace($livesite . '/index.php?option=com_jmap&task=sitemap.exportxml&format=mobile&cronjobclient=1');?>" />
				 </div>
			<?php 
			echo '<div class="xmlcontainer"><label class="badge bg-primary">' . Text::_('COM_JMAP_CRONJOB_VIDEOS_LINK') . '</label>';?>
					<input data-role="sitemap_links" class="sitemap_links hasClickPopover" type="text" value="<?php echo OutputFilter::ampReplace($livesite . '/index.php?option=com_jmap&task=sitemap.exportxml&format=videos&cronjobclient=1');?>" />
				 </div>
			<?php 
			echo '<div class="xmlcontainer"><label class="badge bg-primary">' . Text::_('COM_JMAP_CRONJOB_HREFLANG_LINK') . '</label>';?>
					<input data-role="sitemap_links" class="sitemap_links hasClickPopover" type="text"  data-language="1" value="<?php echo OutputFilter::ampReplace($livesite . '/index.php?option=com_jmap&task=sitemap.exportxml&format=hreflang&cronjobclient=1');?>" />
				 </div>
			<?php 
			if($ampSitemapEnabled):
				echo '<div class="xmlcontainer"><label class="badge bg-primary">' . Text::_('COM_JMAP_CRONJOB_AMP_LINK') . '</label>';?>
						<input data-role="sitemap_links" class="sitemap_links hasClickPopover" type="text" value="<?php echo OutputFilter::ampReplace($livesite . '/index.php?option=com_jmap&task=sitemap.exportxml&format=amp&cronjobclient=1');?>" />
					 </div>
			<?php endif; ?>
		<?php
		echo '</div>';
		
		echo '<div style="display:none" id="rssfeed">';
		$this->getIcon ( $livesite . '/index.php?option=com_jmap&view=sitemap&format=rss', 'icon-48-xml_sitemap.png', Text::_('COM_JMAP_SHOW_RSS_FEED' ), 'target="_blank"');
		$this->getIcon ( $livesite . '/index.php?option=com_jmap&view=sitemap&format=rss&xslt=1', 'icon-48-xsl_sitemap.png', Text::_('COM_JMAP_SHOW_RSS_FEED_FORMATTED' ), 'target="_blank"');
		$this->getIcon ( $livesite . '/index.php?option=com_jmap&task=sitemap.exportxml&format=rss', 'icon-48-xml_export.png', Text::_('COM_JMAP_EXPORT_RSS_FEED' ));
		echo '<label class="badge bg-primary">' . Text::_('COM_JMAP_RSS_FEED_LINK') . '</label>';
		if(!$this->showSefLinks || !$this->joomlaSefLinks):?>
		<input data-role="sitemap_links" class="sitemap_links" type="text" value="<?php echo OutputFilter::ampReplace($livesite . '/index.php?option=com_jmap&view=sitemap&format=rss');?>" />
		<?php else:?>
		<input data-role="sitemap_links_sef" class="sitemap_links" type="text" data-valuenosef="<?php echo OutputFilter::ampReplace($livesite . '/index.php?option=com_jmap&view=sitemap&format=rss');?>" value="<?php echo $livesite . preg_replace('/(\/[A-Za-z0-9_-~]*)*\/administrator\//i', '/', $this->siteRouter->build('index.php?option=com_jmap&view=sitemap&format=rss' . $this->siteItemid));?>"/>
		<?php endif;
		echo '</div>';

		$contents = ob_get_clean ();
		 
		// Assign reference variables
		$this->icons = $contents;
		$this->livesite = $livesite;
		$this->livesitesef = $livesite;
		$this->componentParams = $componentParams;
		$this->infodata = $infoData;
		$this->lists = $lists;
		$this->indexnowApikey = $componentParams->get('indexnow_apikey', '28bcb027f9b443719ceac7cd30556c3c');
		$this->updatesData = $this->getModel()->getUpdates($this->get('httpclient'));
		$this->currentVersion = strval(simplexml_load_file(JPATH_COMPONENT_ADMINISTRATOR . '/jmap.xml')->version);
		
		// Build livesite SEF
		if($this->showSefLinks && $this->joomlaSefLinks) {
			$uriInstance = Uri::getInstance();
			$customHttpPort = trim($componentParams->get('custom_http_port', ''));
			$getPort = $customHttpPort ? ':' . $customHttpPort : '';
		
			$customDomain = trim($componentParams->get('custom_sitemap_domain', ''));
			$getDomain = $customDomain ? rtrim($customDomain, '/') : rtrim($uriInstance->getScheme() . '://' . $uriInstance->getHost(), '/');
		
			$this->livesitesef = rtrim($getDomain . $getPort, '/');
		}
		
		// Aggiunta toolbar
		$this->addDisplayToolbar();
		
		// Output del template
		parent::display ();
	}
	
	/**
	 * Edit entity view
	 *
	 * @access public
	 * @param Object& $row the item to edit
	 * @return void
	 */
	public function editEntity(&$row) {
		// Load JS Client App dependencies
		$doc = $this->app->getDocument();
		$this->loadJQuery($doc);
		$this->loadBootstrap($doc);
		$translations = array (	'COM_JMAP_ROBOTS_REQUIRED',
								'COM_JMAP_ROBOTS_ENTRY_ADDED',
								'COM_JMAP_CLICKTOUPDATE', 
								'COM_JMAP_EXPIREON',
								'COM_JMAP_UPDATEPROGRESSTITLE',
								'COM_JMAP_DOWNLOADING_UPDATE_SUBTITLE',
								'COM_JMAP_INSTALLING_UPDATE_SUBTITLE',
								'COM_JMAP_PROGRESSMODELSUBTITLE');
		$this->injectJsTranslations($translations, $doc);

		// Load specific JS App
		$doc->getWebAssetManager()->registerAndUseStyle ( 'jmap.cpanel', 'administrator/components/com_jmap/css/cpanel.css');
		$doc->getWebAssetManager()->registerAndUseScript ('jmap.cpanel', 'administrator/components/com_jmap/js/cpanel.js', [], [], ['jquery', 'jmap.boostrap-interface'] );
		$doc->getWebAssetManager()->addInlineScript("var jmap_linksRandom=0;" .
													"var jmap_forceFormat=0;");

		$this->option = $this->option;
		$this->robotsVersion = $this->getModel ()->getState ( 'robots_version' );
		$this->record = $row;
	
		parent::display ( 'edit' );
	}
		
	/**
	 * Rendering for installer APP that runs on JSitemap installation iframe
	 * @access public
	 * @return void
	 */
	public function showInstallerApp() {
		$doc = $this->app->getDocument();
		$this->loadJQuery($doc);
		$this->loadBootstrap($doc);
		$doc->getWebAssetManager()->registerAndUseStyle ( 'jmap.cpanel', 'administrator/components/com_jmap/css/cpanel.css');
		$doc->getWebAssetManager()->registerAndUseScript ('jmap.installer', 'administrator/components/com_jmap/js/installer.js', [], [], ['jquery'] );
		$doc->getWebAssetManager()->addInlineStyle('body{background:#dbe4f0 !important}');
	
		// Set layout
		$this->setLayout('default');
	
		// Format data
		parent::display ('installer');
	}
}