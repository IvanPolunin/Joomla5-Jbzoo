<?php
namespace JExtstore\Component\JMap\Site\View\Google;
/**
 * @package JMAP::GOOGLE::administrator::components::com_jmap
 * @subpackage views
 * @subpackage google
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\HTML\HTMLHelper;
use JExtstore\Component\JMap\Administrator\Framework\View as JMapView;

/**
 * @package JMAP::GOOGLE::administrator::components::com_jmap
 * @subpackage views
 * @subpackage google
 * @since 3.1
 */
class HtmlView extends JMapView {
	// Template view variables
	protected $menuTitle;
	protected $lists;
	protected $googleData;
	protected $isLoggedIn;
	protected $cparams;
	protected $params;
	
	/**
	 * Prepares the document
	 */
	protected function _prepareDocument() {
		$document = $this->app->getDocument();
		$menus = $this->app->getMenu();
		$title = null;
	
		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();
		if(is_null($menu)) {
			return;
		}
	
		$this->params = new Registry();
		$menuParams = $menu->getParams()->toString();
		$this->params->loadString($menuParams);
	
		$title = $this->params->get('page_title', Text::_('COM_JMAP_GLOBAL_STATS_REPORT'));
		
		$this->setDocumentTitle($title);
	
		if ($this->params->get('menu-meta_description')) {
			$document->setDescription($this->params->get('menu-meta_description'));
		}
	
		if ($this->params->get('robots')) {
			$document->setMetadata('robots', $this->params->get('robots'));
		}
	}
	
	/**
	 * Default display listEntities
	 *        	
	 * @access public
	 * @param string $tpl
	 * @return void
	 */
	public function display($tpl = null) {
		$this->menuTitle = null;
		$menu = $this->app->getMenu ();
		$activeMenu = $menu->getActive ();
		if (isset ( $activeMenu )) {
			$this->menuTitle = $activeMenu->title;
		}
		
		// Minimal script inclusion
		$this->document->getWebAssetManager()->addInlineScript("
			JMapSubmitform = function(task) {
				form = document.getElementById('adminForm');
				form.task.value = task;
				if (typeof form.onsubmit == 'function') {
					form.onsubmit();
				}
				if (typeof form.fireEvent == 'function') {
					form.fireEvent('submit');
				}
				form.submit();
			};");
		
		// Minimal styles inclusion
		$this->document->getWebAssetManager()->addInlineStyle("
			span.badge.badge-warning,span.badge.bg-warning{width:fit-content}
			*.jes #ga-dash div.btn-wrapper {display: inline-block;border: none}
			*.jes #ga-dash div.btn-wrapper button {margin: 2px;border-radius: 0}
			*.jes #ga-dash div.accordion-body.accordion-inner.collapse {min-height: 0}
			*.jes #ga-dash div.card-header{color: #3a87ad;border-color: #bce8f1;background-color: #d9edf7;padding: 4px 8px}*.jes #ga-dash div.card-block{height:auto!important;padding: 0}*.jes #ga-dash div.card-header h4{font-size: 20px;font-weight: normal}
			*.jes #ga-dash button.btn-default.active{background-color:#e2e2e2}
			*.jes #ga_dash_trafficdata{border-bottom:1px solid #bce8f1}
			*.jes #ga_dash_trafficdata svg ~ div,*.jes #ga_dash_nvrdata svg ~ div{display:none;position:absolute !important;right:10px !important;bottom:0 !important;left:auto !important;width:auto !important;height:auto !important;overflow:visible !important}
			*.jes #ga_dash_trafficdata svg ~ div.table-striped,*.jes #ga_dash_nvrdata svg ~ div.table-striped{display:block}
			*.jes #ga-dash th.google-visualization-table-th{text-align:left}
			*.jes #ga-dash th.google-visualization-table-th.google-visualization-table-type-number{text-align:right}
			*.jes #ga-dash .table th,*.jes #ga-dash .table td{padding: 8px;line-height: 18px;text-align: left;vertical-align: top;border-top: 1px solid #ddd}
			*.jes #ga-dash .table-striped tbody > tr:nth-child(odd) > td,*.jes #ga-dash .table-striped tbody > tr:nth-child(odd) > th{background-color: #f9f9f9}
			*.jes #ga-dash{margin-top:10px}
			*.jes #ga-dash>div.btn-toolbar{margin-bottom:10px}
			*.jes span.google.badge.pull-right{display:none}
			*.jes *.well{padding:19px;background-color: #f5f5f5;}
			*.jes #toolbar-download{float:none}
			*.jes span.bg-primary,*.jes span.badge-primary,*.jes span.bg-primary{font-weight: normal;background-color: #999;padding:2px 4px;color:#FFF;border-radius:3px}
			*.jes span.badge.badge-gadash{color: #428bca;background-color: #ffffff}
			*.jes button.btn.active,*.jes button.btn-default:hover{color: #FFF;background-color: #2F96B4}
			*.jes a.btn.btn-primary.google{border:1px solid #bbb;padding:4px 12px;border-radius:4px}
			*.jes input[type=submit],*.jes button.btn{cursor:pointer;padding:3px}
			*.jes span.pull-right{float: right !important}
			*.jes span.badge-warning,*.jes span.bg-warning{color: #212529;background-color: #f0ad4e}
			*.jes input[name=ga_dash_code]{min-width: 420px;font-size: 12px}
			*.jes *.bg-primary{background-color: #010156!important}
			*.jes div.card{background: #f5f5f5;border: 1px solid #CCC;padding: 1rem !important;margin: 10px 0;width: 100% !important}");
		
		// Get main records
		$lists = $this->get ( 'Lists' );

		$googleStatsState = $this->getModel()->getState('googlestats', 'analytics');
		if($googleStatsState == 'statscropfetch' || 
		   $googleStatsState == 'hypestatfetch'	|| 
		   $googleStatsState == 'searchmetricsfetch' ) {
			// Load resources, iframe script used for frontend module and template styling for the iframed contents
			$this->document->getWebAssetManager()->registerAndUseScript ('jmap.iframe', 'modules/mod_jmap/tmpl/iframe.js' );
			$this->document->getWebAssetManager()->addInlineStyle('#jmap-analytics-frame{width:100%;border:none}');
			
			// Setup the iframe container
			$onLoadIFrame = "jmapIFrameAutoHeight('jmap-analytics-frame')";
			
			// Initialize the multilanguage status
			$languageQueryStringParam = null;
			if($this->app->getLanguageFilter()) {
				$knownLangs = LanguageHelper::getLanguages();
				$defaultLanguageCode = $this->app->getLanguage()->getTag();
				foreach ($knownLangs as $knownLang) {
					if($knownLang->lang_code == $defaultLanguageCode) {
						$languageQueryStringParam = '&lang=' . $knownLang->sef;
						break;
					}
				}
			}
			
			$renderGoogleStatsState = str_ireplace('fetch', 'render', $googleStatsState);
			$googleData = '<iframe title="Analytics" id="jmap-analytics-frame" src="' . Uri::root (false) . 'index.php?option=com_jmap&task=google.display&googlestats=' . $renderGoogleStatsState . '&format=raw' . $languageQueryStringParam . '" onload="' . $onLoadIFrame . '"></iframe>';
			$tpl = 'framed';
		} else {
			$gaApi = $this->getModel()->getComponentParams()->get('analytics_api', 'data');
			
			switch($gaApi) {
				// Retrieve data using the Analitics API
				case 'analytics':
					$googleData = $this->get ( 'DataAnalytics' );
					break;
					
					// Retrieve data using the Reporting API
				case 'reporting':
					$googleData = $this->get ( 'DataReporting' );
					break;
					
					// Retrieve data using the DATA GA4 API
				case 'data':
					$googleData = $this->get ( 'DataData' );
					break;
			}
			
			HTMLHelper::_('bootstrap.loadCss');
			$this->document->getWebAssetManager()->addInlineScript("
				document.addEventListener('DOMContentLoaded', function(){
					[].slice.call(document.querySelectorAll('*.jes a.hasPopover.google')).map(function (popoverEl) {
						return new bootstrap.Popover(popoverEl,{
							template : '<div class=\"popover\"><div class=\"popover-arrow\"></div><h3 class=\"popover-header\"></h3><div class=\"popover-body\"></div></div>',
							trigger : 'hover',
							placement : 'top',
							html : true
						});
					});
				});
			");
		}
		
		$this->loadJQuery($this->document);
		$this->document->getWebAssetManager ()->useStyle ( 'fontawesome' ); // Required for headers icons
		
		$this->lists = $lists;
		$this->googleData = $googleData;
		$this->isLoggedIn = $this->getModel()->getToken();
		$this->option = $this->getModel ()->getState ( 'option' );
		$this->cparams = $this->getModel ()->getComponentParams ();
		
		// Aggiunta toolbar
		$this->_prepareDocument();
		
		parent::display ($tpl);
	}
}