<?php
namespace JExtstore\Component\JMap\Administrator\View\Metainfo;
/**
 * @package JMAP::METAINFO::administrator::components::com_jmap
 * @subpackage views
 * @subpackage metainfo
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\Field\MediaField;
use JExtstore\Component\JMap\Administrator\Framework\Helpers\Toolbars as ToolbarHelper;
use JExtstore\Component\JMap\Administrator\Framework\View as JMapView;

/**
 * @package JMAP::METAINFO::administrator::components::com_jmap
 * @subpackage views
 * @subpackage metainfo
 * @since 3.2
 */
class HtmlView extends JMapView {
	// Template view variables
	protected $pagination;
	protected $searchpageword;
	protected $exactsearchpage;
	protected $needhttpsmigration;
	protected $lists;
	protected $orders;
	protected $items;
	protected $mediaField;
	protected $mediaFieldReflection;
	protected $sitemapLanguage;
	
	/**
	 * Add the page title and toolbar.
	 */
	protected function addDisplayToolbar() {
		ToolbarHelper::title( Text::_( 'COM_JMAP_SITEMAP_METAINFO' ), 'jmap' );
		
		if ($this->user->authorise('core.edit', 'com_jmap')) {
			$toolbar = Toolbar::getInstance();
			$dropdown = $toolbar->dropdownButton('importexport-group')
								->text('COM_JMAP_EXPORT_META_BTNS')
								->toggleSplit(false)
								->icon('icon-ellipsis-h')
								->buttonClass('btn btn-action')
								->listCheck(false);
			$childBar = $dropdown->getChildToolbar();
			$childBar->standardButton('download', 'COM_JMAP_EXPORT_META', 'metainfo.exportEntities')->listCheck(false);
			$childBar->standardButton('upload', 'COM_JMAP_IMPORT_META', 'metainfo.importEntities')->listCheck(false);
		}
		
		if ($this->user->authorise('core.delete', 'com_jmap') && $this->user->authorise('core.edit', 'com_jmap')) {
			ToolbarHelper::custom('metainfo.deleteEntity', 'delete', 'delete', 'COM_JMAP_DELETE_ALL_META', false);
		}
		
		if ($this->user->authorise('core.create', 'com_jmap') && $this->user->authorise('core.create', 'com_jmap')) {
			ToolbarHelper::custom('metainfo.saveAll', 'save', 'save', 'COM_JMAP_SAVEALL_META', false);
			ToolbarHelper::custom('metainfo.autoPopulate', 'database', 'database', 'COM_JMAP_AUTOPOPULATE_META', false);
			
			
			$toolbar = Toolbar::getInstance();
			$dropdown = $toolbar->dropdownButton('status-group')
								->text('COM_JMAP_MIGRATE_BTNS')
								->toggleSplit(false)
								->icon('icon-ellipsis-h')
								->buttonClass('btn btn-action')
								->listCheck(false);
			$childBar = $dropdown->getChildToolbar();
			
			$childBar->standardButton('refresh', 'COM_JMAP_MIGRATE_META', 'metainfo.migrateEntities')->listCheck(false);
			if($this->needhttpsmigration) {
				$childBar->standardButton('refresh', 'COM_JMAP_MIGRATE_HTTPS_META', 'metainfo.httpsMigrate')->listCheck(false);
			}
		}

		ToolbarHelper::custom('cpanel.display', 'home', 'home', 'COM_JMAP_CPANEL', false);
	}
	
	/**
	 * Creates a dropdown box for selecting how many records to show per page with override
	 *
	 * @return  string  The HTML for the limit # input box.
	 */
	protected function getLimitBox() {
		$limits = array();
		$limit = $this->getModel ()->getState ( 'limit' );
	
		// Make the option list.
		for ($i = 5; $i <= 30; $i += 5)
		{
			$limits[] = HTMLHelper::_('select.option', "$i");
		}
	
		$limits[] = HTMLHelper::_('select.option', '50', Text::_('J50'));
		$limits[] = HTMLHelper::_('select.option', '100', Text::_('J100'));
		$limits[] = HTMLHelper::_('select.option', '200', Text::_('J200'));
		$limits[] = HTMLHelper::_('select.option', '500', Text::_('J500'));
		$limits[] = HTMLHelper::_('select.option', '1000', '1000');
		$limits[] = HTMLHelper::_('select.option', '2000', '2000');
		$limits[] = HTMLHelper::_('select.option', '5000', '5000');
		$limits[] = HTMLHelper::_('select.option', '10000', '10000');
		$limits[] = HTMLHelper::_('select.option', '20000', '20000');
		$limits[] = HTMLHelper::_('select.option', '30000', '30000');
		$limits[] = HTMLHelper::_('select.option', '50000', '50000');
		$limits[] = HTMLHelper::_('select.option', '0', Text::_('JALL'));
	
		$selected = $limit == 0 ? 0 : $limit;
	
		// Build the select list.
		$html = HTMLHelper::_(
				'select.genericlist',
				$limits,
				'limit',
				'class="form-select" size="1" onchange="Joomla.submitform();"',
				'value',
				'text',
				$selected
		);
	
		return $html;
	}
	
	/**
	 * Default display listEntities
	 *        	
	 * @access public
	 * @param string $tpl
	 * @return void
	 */
	public function display($tpl = 'list') {
		// Get main records
		$rows = $this->get ( 'Data' );
		$lists = $this->get ( 'Filters' );
		$total = $this->get ( 'Total' );
		
		$doc = $this->app->getDocument();
		$this->loadJQuery($doc);
		$this->loadBootstrap($doc);
		$doc->getWebAssetManager()->registerAndUseScript ('jmap.webfontloader', 'administrator/components/com_jmap/js/webfontloader.js', [], [], ['jquery'] );
		$doc->getWebAssetManager()->registerAndUseScript ('jmap.filesources', 'administrator/components/com_jmap/js/filesources.js', [], [], ['jquery'] );
		$doc->getWebAssetManager()->registerAndUseScript ('jmap.migratemeta', 'administrator/components/com_jmap/js/migratemeta.js', [], [], ['jquery'] );
		$doc->getWebAssetManager()->registerAndUseScript ('jmap.metainfo', 'administrator/components/com_jmap/js/metainfo.js', [], [], ['jquery', 'jmap.webfontloader'] );
		$doc->getWebAssetManager()->registerAndUseStyle ( 'jmap.metainfo', 'administrator/components/com_jmap/css/metainfo.css');
		
		$globalJConfig = $this->app->getConfig();
		$safeJsSitename = str_ireplace(PHP_EOL, '', addcslashes($globalJConfig->get('sitename'), "'"));
		$safeJsSitename = trim(preg_replace("/([\r\n]+)/", '', $safeJsSitename));
		$doc->getWebAssetManager()->addInlineScript("var jmap_baseURI='" . Uri::root() . "';" .
													"var jmap_crawlerDelay=" . $this->getModel()->getComponentParams()->get('seospider_crawler_delay', 0) . ";" .
													"var jmap_metainfoAutopopulateSocialimageSelector='" . addcslashes(trim($this->getModel()->getComponentParams()->get('metainfo_autopopulate_socialimage_selector', '')), "'") . "';" .
													"var jmap_metainfoAutoGenerateMetatitle=" . $this->getModel()->getComponentParams()->get('metainfo_auto_generate_metatitle', 0) . ";" .
													"var jmap_metainfoAutoGenerateMetatitleCssSelector='" . addcslashes(trim($this->getModel()->getComponentParams()->get('metainfo_auto_generate_metatitle_css_selector', 'h1')), "'") . "';" .
													"var jmap_metainfoAutoGenerateMetatitleMaxLength=" . $this->getModel()->getComponentParams()->get('metainfo_auto_generate_metatitle_max_length', 55) . ";" .
													"var jmap_metainfoAutoGenerateMetadescription=" . $this->getModel()->getComponentParams()->get('metainfo_auto_generate_metadescription', 0) . ";" .
													"var jmap_metainfoAutoGenerateMetadescriptionCssSelector='" . addcslashes(trim($this->getModel()->getComponentParams()->get('metainfo_auto_generate_metadescription_css_selector', 'div[itemprop=articleBody],div.item-page')), "'") . "';" .
													"var jmap_metainfoAutoGenerateMetadescriptionMaxLength=" . $this->getModel()->getComponentParams()->get('metainfo_auto_generate_metadescription_max_length', 155) . ";" .
													"var jmap_chatgptGeneration=" . $this->getModel()->getComponentParams()->get('metainfo_enable_chatgpt_generation', 0) . ";" .
													"var jmap_siteName='" . $safeJsSitename . "';" .
													"var jmap_siteNamePageTitles=" . $globalJConfig->get('sitename_pagetitles', 0) . ";");
		
		// Inject js translations
		$translations = array (
				'COM_JMAP_METAINFO_TITLE',
				'COM_JMAP_METAINFO_PROCESS_RUNNING',
				'COM_JMAP_METAINFO_STARTED_SITEMAP_GENERATION',
				'COM_JMAP_METAINFO_ERROR_STORING_FILE',
				'COM_JMAP_METAINFO_GENERATION_COMPLETE',
				'COM_JMAP_METAINFO_ANALYZING_LINKS',
				'COM_JMAP_METAINFO_ERROR_STORING_DATA',
				'COM_JMAP_METAINFO_SET_ATLEAST_ONE',
				'COM_JMAP_METAINFO_SAVED',
				'COM_JMAP_ALL_METAINFO_SAVED',
				'COM_JMAP_DELETE_ALL_META_DESC',
				'COM_JMAP_CHARACTERS',
				'COM_JMAP_PIXEL_DESKTOP',
				'COM_JMAP_PIXEL_MOBILE',
				'COM_JMAP_REQUIRED',
				'COM_JMAP_PICKFILE',
				'COM_JMAP_STARTIMPORT',
				'COM_JMAP_CANCELIMPORT',
				'COM_JMAP_OPEN_FB_DEBUGGER',
				'COM_JMAP_MIGRATE_META_PREVIOUS_DOMAIN',
				'COM_JMAP_MIGRATE_META_NEW_DOMAIN',
				'COM_JMAP_MIGRATE_META_CONFIRM',
				'COM_JMAP_MIGRATE_META_CANCEL',
				'COM_JMAP_INVALID_URL',
				'COM_JMAP_AIGENERATOR_METAINFO_GENERATED'
		);
		$this->injectJsTranslations($translations, $doc);
		$doc->getWebAssetManager()->addInlineScript("
						Joomla.submitbutton = function(pressbutton) {
							Joomla.submitform( pressbutton );
							if (pressbutton == 'metainfo.exportEntities') {
								jQuery('#adminForm input[name=task]').val('metainfo.display');
							}
							return true;
						};
					");
						
		$orders = array ();
		$orders ['order'] = $this->getModel ()->getState ( 'order' );
		$orders ['order_Dir'] = $this->getModel ()->getState ( 'order_dir' );
		// Pagination view object model state populated
		$pagination = new Pagination ( $total, $this->getModel ()->getState ( 'limitstart' ), $this->getModel ()->getState ( 'limit' ) );
		
		$this->user = $this->app->getIdentity ();
		$this->pagination = $pagination;
		$this->searchpageword = $this->getModel ()->getState ( 'searchpageword', '' );
		$this->exactsearchpage = $this->getModel ()->getState ( 'exactsearchpage', null ) ? 'checked' : '';
		$this->needhttpsmigration = $this->getModel ()->getState ( 'needhttpsmigration', null );
		$this->sitemapLanguage = $this->getModel ()->getState ( 'sitemaplang', '' );
		$this->lists = $lists;
		$this->orders = $orders;
		$this->items = $rows;
		
		// Manage different metainfo media buttons
		$jForm = new Form('jmap_metainfo');
		$jceInstalled = ComponentHelper::isInstalled('com_jce') ;
		$jceEnabled = PluginHelper::isEnabled('editors', 'jce');
		$jceMediaManagerEnabled = ComponentHelper::getParams('com_jce')->get('replace_media_manager', 1);
		$jceFileExists = file_exists(JPATH_PLUGINS . '/fields/mediajce/fields/mediajce.php');
		if($jceInstalled && $jceEnabled && $jceMediaManagerEnabled && $jceFileExists) {
			require_once JPATH_PLUGINS . '/fields/mediajce/fields/mediajce.php';
			if(class_exists('\JFormFieldMediaJce')) {
				$this->mediaField = new \JFormFieldMediaJce();
			} else {
				$this->mediaField = new MediaField();
			}
		} else {
			$this->mediaField = new MediaField();
		} 
		$this->mediaField->setForm($jForm);
		$element = new \SimpleXMLElement('<field/>');
		$element->addAttribute('class', 'mediaimagefield');
		$element->addAttribute('default', '');
		$this->mediaField->setup($element, '');
		
		// Joomla 5.1+
		if(property_exists($this->mediaField, 'layoutData')) {
			$this->mediaFieldReflection = new \ReflectionProperty($this->mediaField, 'layoutData');
			$this->mediaFieldReflection->setAccessible(true);
		}
		
		// Aggiunta toolbar
		$this->addDisplayToolbar();
		
		parent::display ( $tpl );
	}
}