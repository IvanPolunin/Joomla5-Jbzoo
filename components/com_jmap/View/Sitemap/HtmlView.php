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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Registry\Registry;
use JExtstore\Component\JMap\Administrator\Framework\View as JMapView;

/**
 * Main view class
 *
 * @package JMAP::SITEMAP::components::com_jmap
 * @subpackage views
 * @subpackage sitemap
 * @since 1.0
 */
class HtmlView extends JMapView {
	// Template view variables
	protected $menuname;
	protected $params;
	protected $cparams;
	protected $goJsSitemap;
	protected $scriptsLoading;
	protected $marginSide;
	protected $isRTL;
	protected $mergeAliasMenu;
	protected $mergeGenericMenuByClass;
	protected $data;
	protected $application;
	protected $liveSite;
	protected $source;
	protected $sourceparams;
	protected $asCategoryTitleField;
	
	/**
	 * Prepares the document
	 */
	protected function _prepareDocument() {
		$app = Factory::getApplication();
		$document = $app->getDocument();
		$menus = $app->getMenu();
		$title = null;
		
		// Exclude prepare document if it's an embed module execution
		if($this->getModel ()->getState('jmap_module')) {
			return;
		}
		
		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();
		
		$this->params = new Registry();
		if(!is_null($menu)) {
			$menuParams = $menu->getParams()->toString();
			$this->params->loadString($menuParams);
		}
		
		$title = $this->params->get('page_title', $this->cparams->get ( 'defaulttitle', 'Sitemap' ));
		
		$this->setDocumentTitle($title);
		
		if ($this->params->get('menu-meta_description')) {
			$document->setDescription($this->params->get('menu-meta_description'));
		}
		
		if ($this->params->get('robots')) {
			$document->setMetadata('robots', $this->params->get('robots'));
		}
	}
	
	/**
	 * Display the sitemap
	 * @access public
	 * @return void
	 */
	public function display($tpl = null) {
		// App alias
		$app = $this->app;
		$menu = $app->getMenu ();
		
		// Document alias
		$document = $this->document;
		
		$this->menuname = $menu->getActive ();
		$this->cparams = $this->getModel ()->getState ( 'cparams' );
		if (isset ( $this->menuname )) {
			$this->menuname = $this->menuname->title;
		}
		
		// Call by cache handler get no params, so recover from model state
		if(!$tpl) {
			$tpl = $this->getModel ()->getState ( 'documentformat' );
		}
		
		// Accordion della sitemap
		if($this->getModel ()->getState ( 'cparams' )->get('includejquery', 1)) {
			$document->getWebAssetManager()->useScript('jquery');
			$document->getWebAssetManager()->useScript('jquery-noconflict');
		}
		
		// If there is a pingiframe request ensure that the gojs template is not executed, eventually fallback to the default template
		if($app->getInput()->getInt('pingiframe', null) && $this->cparams->get('sitemap_html_template', '') == 'gojs') {
			$this->cparams->set('sitemap_html_template', '');
		}
		
		// Check if enabled the draggable mindmap sitemap
		$draggableSitemap = $this->cparams->get('draggable_sitemap', 0);
		$mindMapSitemap = $this->cparams->get('sitemap_html_template', '') == 'mindmap';
		$goJsSitemap = $this->goJsSitemap = $this->cparams->get('sitemap_html_template', '') == 'gojs' ? 1 : 0;
		if($draggableSitemap && $mindMapSitemap) {
			$this->loadJQueryUI($document);
		}
		
		// Add the original component script
		$this->scriptsLoading = $this->cparams->get('loadasyncscripts', 0) ? true : false;
		if($this->cparams->get('treeview_scripts', 1)) {
			$document->getWebAssetManager()->registerAndUseScript('jmap.jquery.treeview', 'components/com_jmap/js/jquery.treeview.js', [], ['defer'=>$this->scriptsLoading]);
		}
		
		// Manage sitemap layout
		if(!$this->cparams->get('show_sitemap_icons', 1)) {
			$document->getWebAssetManager()->addInlineStyle('span.folder{cursor:pointer}');
		} else {
			// Check if a template override is requested
			if(!$this->cparams->get('template_override', 0)) {
				$document->getWebAssetManager()->registerAndUseStyle ( 'jmap.jquery.treeview', 'components/com_jmap/js/jquery.treeview.css');
				
				if($sitemapTemplate = $this->cparams->get('sitemap_html_template', null)) {
					$document->getWebAssetManager()->registerAndUseStyle ( 'jmap.jquery.treeview-' . $sitemapTemplate, 'components/com_jmap/js/jquery.treeview-' . $sitemapTemplate . '.css', [], [], ['jmap.jquery.treeview']);
				}
			} else {
				$template = $this->app->getTemplate(true);
				$templateName = $template->template;
				if(file_exists(JPATH_ROOT . '/media/templates/site/' . $templateName . '/css/com_jmap/js/jquery.treeview.css')) {
					$document->getWebAssetManager()->registerAndUseStyle ( 'jmap.jquery.treeview', 'media/templates/site/' . $templateName . '/css/com_jmap/js/jquery.treeview.css');
				} elseif(file_exists(JPATH_ROOT . '/templates/' . $templateName . '/css/com_jmap/js/jquery.treeview.css')) {
					$document->getWebAssetManager()->registerAndUseStyle ( 'jmap.jquery.treeview', 'templates/' . $templateName . '/css/com_jmap/js/jquery.treeview.css');
				}
				
				if($sitemapTemplate = $this->cparams->get('sitemap_html_template', null)) {
					if(file_exists(JPATH_ROOT . '/media/templates/site/' . $templateName . '/css/com_jmap/js/jquery.treeview-' . $sitemapTemplate . '.css')) {
						$document->getWebAssetManager()->registerAndUseStyle ( 'jmap.jquery.treeview-' . $sitemapTemplate, 'media/templates/site/' . $templateName . '/css/com_jmap/js/jquery.treeview-' . $sitemapTemplate . '.css');
					} elseif(file_exists(JPATH_ROOT . '/templates/' . $templateName . '/css/com_jmap/js/jquery.treeview-' . $sitemapTemplate . '.css')) {
						$document->getWebAssetManager()->registerAndUseStyle ( 'jmap.jquery.treeview-' . $sitemapTemplate, 'templates/' . $templateName . '/css/com_jmap/js/jquery.treeview-' . $sitemapTemplate . '.css');
					}
				}
			}
		}
		
		// Indentation margin side
		$this->marginSide = 'margin-left:';
		
		// Detect if the language is RTL and if so load overrides
		$this->isRTL = $this->app->getLanguage()->isRTL();
		if($this->isRTL && !$mindMapSitemap && !$goJsSitemap) {
			if($this->cparams->get('show_sitemap_icons', 1)) {
				// Check if a template override is requested
				if(!$this->cparams->get('template_override', 0)) {
					$document->getWebAssetManager()->registerAndUseStyle ( 'jmap.jquery.treeview', 'components/com_jmap/js/rtl/jquery.treeview.css');
					
					if($sitemapTemplate = $this->cparams->get('sitemap_html_template', null)) {
						$document->getWebAssetManager()->registerAndUseStyle ( 'jmap.jquery.treeview-' . $sitemapTemplate, 'components/com_jmap/js/rtl/jquery.treeview-' . $sitemapTemplate . '.css', [], [], ['jmap.jquery.treeview']);
					}
				} else {
					$template = $this->app->getTemplate(true);
					$templateName = $template->template;
					if(file_exists(JPATH_ROOT . '/media/templates/site/' . $templateName . '/css/com_jmap/js/rtl/jquery.treeview.css')) {
						$document->getWebAssetManager()->registerAndUseStyle ( 'jmap.jquery.treeview', 'media/templates/site/' . $templateName . '/css/com_jmap/js/rtl/jquery.treeview.css');
					} elseif(file_exists(JPATH_ROOT . '/templates/' . $templateName . '/css/com_jmap/js/rtl/jquery.treeview.css')) {
						$document->getWebAssetManager()->registerAndUseStyle ( 'jmap.jquery.treeview', 'templates/' . $templateName . '/css/com_jmap/js/rtl/jquery.treeview.css');
					}
					
					if($sitemapTemplate = $this->cparams->get('sitemap_html_template', null)) {
						if(file_exists(JPATH_ROOT . '/media/templates/site/' . $templateName . '/css/com_jmap/js/rtl/jquery.treeview-' . $sitemapTemplate . '.css')) {
							$document->getWebAssetManager()->registerAndUseStyle ( 'jmap.jquery.treeview-' . $sitemapTemplate, 'media/templates/site/' . $templateName . '/css/com_jmap/js/rtl/jquery.treeview-' . $sitemapTemplate . '.css');
						} elseif(file_exists(JPATH_ROOT . '/templates/' . $templateName . '/css/com_jmap/js/rtl/jquery.treeview-' . $sitemapTemplate . '.css')) {
							$document->getWebAssetManager()->registerAndUseStyle ( 'jmap.jquery.treeview-' . $sitemapTemplate, 'templates/' . $templateName . '/css/com_jmap/js/rtl/jquery.treeview-' . $sitemapTemplate . '.css');
						}
					}
				}
			}
			// Indentation margin side for RTL
			$this->marginSide = 'margin-right:';
		}
		
		$this->mergeAliasMenu = $this->cparams->get('merge_alias_menu', 0);
		$this->mergeGenericMenuByClass = $this->cparams->get('merge_generic_menu_by_class', 0);
		
		// Inject JS domain vars
		if($this->cparams->get('treeview_scripts', 1)) {
			$document->getWebAssetManager()->addInlineScript("
						var jmapExpandAllTree = " . $this->getModel ()->getState ( 'cparams' )->get('show_expanded', 0) . ";
						var jmapExpandLocation = '" . $this->getModel ()->getState ( 'cparams' )->get('expand_location', 'location') . "';
						var jmapAnimated = " . $this->getModel ()->getState ( 'cparams' )->get('animated', 1) . ";
						var jmapAnimateSpeed = " . $this->getModel ()->getState ( 'cparams' )->get('animate_speed', 200) . ";
						var jmapDraggableSitemap = " . $draggableSitemap . ";
						var jmapGojsSitemap = " . $goJsSitemap . ";
						var jmapisRTLLanguage = " . (int)$this->isRTL . ";
						var jmapHideEmptyCats = " . $this->getModel ()->getState ( 'cparams' )->get('hide_empty_cats', 0) . ";
						var jmapLinkableCatsSources = {};
						var jmapMergeMenuTree = {};
						var jmapMergeAliasMenu = " . $this->mergeAliasMenu . ";
						var jmapMergeGenericMenuByClass = " . $this->mergeGenericMenuByClass . ";
						var jmapExpandFirstLevel = " . $this->getModel ()->getState ( 'cparams' )->get('expand_first_level', 0) . ";
						var jmapGojsAutoHeightCanvas = " . $this->getModel ()->getState ( 'cparams' )->get('auto_height_canvas', 1) . ";
						var jmapGojsAutoScaleCanvas = " . $this->getModel ()->getState ( 'cparams' )->get('auto_scale_canvas', 0) . ";
						var jmapGojsRootColor = '" . $this->getModel ()->getState ( 'cparams' )->get('root_color', '#9df2e9') . "';
						var jmapGojsChildColor = '" . $this->getModel ()->getState ( 'cparams' )->get('child_color', '#e0c8be') . "';
						var jmapGojsNodeColorText = '" . $this->getModel ()->getState ( 'cparams' )->get('node_color_text', '#333') . "';
						var jmapGojsTreeOrientation = '" . $this->getModel ()->getState ( 'cparams' )->get('tree_orientation', 'horizontal') . "';
						jQuery(function($){
							$('ul.jmap_filetree li a:empty').parent('li').css('display', 'none');
						});
					");
		}
		
		// Inject custom CSS for module layout
		if($this->getModel ()->getState('jmap_module')) {
			if($customCssStyles = trim($this->cparams->get('custom_css_styles', ''))) {
				$document->getWebAssetManager()->addInlineStyle($customCssStyles);
			}
		}
		
		$this->data = $this->get ( 'SitemapData' );
		
		// Application alias
		$this->application = $app;
		
		$uriInstance = Uri::getInstance();
		if($this->cparams->get('append_livesite', true)) {
			$customHttpPort = trim($this->cparams->get('custom_http_port', ''));
			$getPort = $customHttpPort ? ':' . $customHttpPort : '';
			
			$customDomain = trim($this->cparams->get('custom_sitemap_domain', ''));
			$getDomain = $customDomain ? rtrim($customDomain, '/') : rtrim($uriInstance->getScheme() . '://' . $uriInstance->getHost(), '/');

			$this->liveSite = rtrim($getDomain . $getPort, '/');
		} else {
			$this->liveSite = null;
		}
		
		// Add meta info
		$this->_prepareDocument();
		
		parent::display ( $tpl );
	}
}