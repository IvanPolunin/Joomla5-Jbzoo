<?php
namespace JExtstore\Component\JMap\Administrator\View\Sources;
/**
 * @package JMAP::SOURCES::administrator::components::com_jmap
 * @subpackage views
 * @subpackage sources
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use Joomla\CMS\Pagination\Pagination;
use Joomla\Filter\OutputFilter;
use JExtstore\Component\JMap\Administrator\Framework\Helpers\Toolbars as ToolbarHelper;
use JExtstore\Component\JMap\Administrator\Framework\View as JMapView;

/**
 * @package JMAP::SOURCES::administrator::components::com_jmap
 * @subpackage views
 * @subpackage sources
 * @since 1.0
 */
class HtmlView extends JMapView {
	// Template view variables
	protected $user;
	protected $pagination;
	protected $searchword;
	protected $lists;
	protected $record;
	protected $registrySqlQueryManaged;
	protected $orders;
	protected $items;
	protected $cParams;
	protected $hasManifest;
	protected $hasItemsCategorization;
	protected $isCategorySource;
	protected $hasRouteManifest;
	protected $hasCreatedDate;
	protected $supportedGNewsExtension;
	protected $supportedRSSExtension;
	protected $supportedHreflangExtension;
	protected $languagePluginEnabled;
	
	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addEditEntityToolbar() {
		$user		= $this->app->getIdentity();
		$userId		= $user->id;
		$isNew		= ($this->record->id == 0);
		$checkedOut	= !($this->record->checked_out == 0 || $this->record->checked_out == $userId);
		$toolbarHelperTitle = $isNew ? 'COM_JMAP_SITEMAP_DATA_NEW' : 'COM_JMAP_SITEMAP_DATA_EDIT';
	
		ToolbarHelper::title( Text::_( $toolbarHelperTitle ), 'jmap' );
	
		if ($isNew)  {
			// For new records, check the create permission.
			if ($isNew && ($user->authorise('core.create', 'com_jmap'))) {
				// Evaluate data source type
				if($this->record->type === 'plugin') {
					ToolbarHelper::apply( 'sources.importPlugins', 'COM_JMAP_IMPORT_PLUGIN');
				} else { // All other cases
					ToolbarHelper::apply( 'sources.applyEntity', 'JAPPLY');
					ToolbarHelper::save( 'sources.saveEntity', 'JSAVE');
				}
			}
		} else {
			// Can't save the record if it's checked out.
			if (!$checkedOut) {
				// Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
				if ($user->authorise('core.edit', 'com_jmap')) {
					ToolbarHelper::apply( 'sources.applyEntity', 'JAPPLY');
					ToolbarHelper::save( 'sources.saveEntity', 'JSAVE');
				}
			}
		}
			
		ToolbarHelper::custom('sources.cancelEntity', 'cancel', 'cancel', 'JCANCEL', false);
	}
	
	
	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addDisplayToolbar() {
		$user = $this->app->getIdentity();
		ToolbarHelper::title( Text::_( 'COM_JMAP_SITEMAP_DATA' ), 'jmap' );
		// Access check.
		if ($user->authorise('core.create', 'com_jmap')) {
			ToolbarHelper::addNew('wizard.display', 'COM_JMAP_NEW_SOURCE');
		}
	
		if ($user->authorise('core.edit', 'com_jmap')) {
			ToolbarHelper::editList('sources.editEntity', 'COM_JMAP_EDIT_SOURCE');
		}
	
		ToolbarHelper::custom( 'sources.copyEntity', 'copy', 'copy', 'COM_JMAP_DUPLICATE' );
	
		if ($user->authorise('core.delete', 'com_jmap') && $user->authorise('core.edit', 'com_jmap')) {
			ToolbarHelper::deleteList(Text::_('COM_JMAP_DELETE_SOURCE'), 'sources.deleteEntity');
		}
		
		if ($user->authorise('core.edit', 'com_jmap')) {
			ToolbarHelper::custom('sources.exportEntities', 'download', 'download', 'COM_JMAP_EXPORT_SOURCE', true);
			ToolbarHelper::custom('sources.importEntities', 'upload', 'upload', 'COM_JMAP_IMPORT_SOURCE', false);
		}

		ToolbarHelper::custom('cpanel.display', 'home', 'home', 'COM_JMAP_CPANEL', false);
	}
	
	/**
	 * Default display listEntities
	 *        	
	 * @access public
	 * @param string $tpl
	 * @return void
	 */
	public function display($tpl = null) {
		// Get main records
		$rows = $this->get ( 'Data' );
		$lists = $this->get ( 'Filters' );
		$total = $this->get ( 'Total' );
		
		$doc = $this->app->getDocument();
		$this->loadJQuery($doc);
		$this->loadBootstrap($doc);
		$doc->getWebAssetManager()->registerAndUseScript ('jmap.filesources', 'administrator/components/com_jmap/js/filesources.js', [], [], ['jquery'] );
		
		$doc->getWebAssetManager()->addInlineScript("var checkAll = function( checkbox, stub ) {
			if (!checkbox.form) return false;
			stub = stub ? stub : 'cb';
			var c = 0,
			    i, e, n;
			for ( i = 0, n = checkbox.form.elements.length; i < n; i++ ) {
				e = checkbox.form.elements[ i ];
				if ( e.type == checkbox.type && e.id.indexOf( stub ) === 0 && typeof(e.dataset.enabled) === 'undefined') {
					e.checked = checkbox.checked;
					c += e.checked ? 1 : 0;
				}
			}
			if (checkbox.form.boxchecked) {
			      checkbox.form.boxchecked.value = c;
			      checkbox.form.boxchecked.dispatchEvent(new CustomEvent('change', {
			        bubbles: true,
			        cancelable: true
			      }));
			    }
			return true;
		};
		Joomla['checkAll'] = checkAll;
		");
		$doc->getWebAssetManager()->addInlineScript("
						Joomla.submitbutton = function(pressbutton) {
							Joomla.submitform( pressbutton );
							if (pressbutton == 'sources.exportEntities') {
								jQuery('#adminForm input[name=task]').val('sources.display');
							}
							return true;
						};
					");
		
		// Inject js translations
		$translations = array(
				'COM_JMAP_REQUIRED',
				'COM_JMAP_PICKFILE',
				'COM_JMAP_STARTIMPORT',
				'COM_JMAP_CANCELIMPORT'
		);
		$this->injectJsTranslations($translations, $doc);
		
		$orders = array ();
		$orders ['order'] = $this->getModel ()->getState ( 'order' );
		$orders ['order_Dir'] = $this->getModel ()->getState ( 'order_dir' );
		// Pagination view object model state populated
		$pagination = new Pagination ( $total, $this->getModel ()->getState ( 'limitstart' ), $this->getModel ()->getState ( 'limit' ) );
		
		$this->user = $this->app->getIdentity ();
		$this->pagination = $pagination;
		$this->searchword = $this->getModel ()->getState ( 'searchword' );
		$this->lists = $lists;
		$this->orders = $orders;
		$this->items = $rows;
		$this->option = $this->getModel ()->getState ( 'option' );
		$this->cParams = $this->getModel ()->getComponentParams();
		$this->languagePluginEnabled = $this->getModel ()->getLanguagePluginEnabled();
		
		// Aggiunta toolbar
		$this->addDisplayToolbar();
		
		parent::display ( 'list' );
	}
	
	/**
	 * Edit entity view
	 *
	 * @access public
	 * @param Object& $row the item to edit
	 * @return void
	 */
	public function editEntity(&$row) {
		// Sanitize HTML Object2Form
		OutputFilter::objectHTMLSafe( $row );
		$arrayExclusion = array();
		for($wmo=1,$maxOperators=3;$wmo<=$maxOperators;$wmo++) {
			$arrayExclusion[] = 'where'.$wmo.'_operator_maintable';
			for($wmojt=1,$maxjtOperators=3;$wmojt<=$maxjtOperators;$wmojt++) {
				$arrayExclusion[] = 'where'.$wmojt.'_operator_jointable'.$wmo;
			}
		}
		OutputFilter::objectHTMLSafe( $row->sqlquery_managed, ENT_QUOTES, $arrayExclusion);
		
		// Load JS Client App dependencies
		$doc = $this->app->getDocument();
		$base = Uri::root();
		$this->loadJQuery($doc);
		$this->loadJQueryUI($doc);
		$this->loadBootstrap($doc);
		$this->loadValidation($doc);
		$doc->getWebAssetManager()->registerAndUseStyle ( 'jmap.sources', 'administrator/components/com_jmap/css/sources.css');
		
		$doc->getWebAssetManager()->addInlineScript("var jmap_baseURI='$base';");
		
		$doc->getWebAssetManager()->addInlineStyle('@media (max-width: 1024px) { body.admin.com_jmap { min-width: 1024px; }}');
		
		// Inject js translations
		$translations = array('COM_JMAP_SELECTFIELD',
							  'COM_JMAP_STORED_PRIORITY',
							  'COM_JMAP_ERROR_FOR_PRIORITY',
							  'COM_JMAP_DELETED_PRIORITY',
							  'COM_JMAP_PRIORITY_MAKE_SELECTIONS',
							  'COM_JMAP_VALIDATON_ERROR_NOPRIORITY',
							  'COM_JMAP_PRIORITY_CHOOSE_TO_DELETE',
							  'COM_JMAP_CATEGORIES_EXCLUSION',
							  'COM_JMAP_CATEGORIES_INCLUSION',
							  'COM_JMAP_CHOOSE_CATEGORIES_EXCLUSION',
							  'COM_JMAP_CHOOSE_CATEGORIES_EXCLUSION_DESC',
							  'COM_JMAP_CHOOSE_CATEGORIES_INCLUSION',
							  'COM_JMAP_CHOOSE_CATEGORIES_INCLUSION_DESC',
							  'COM_JMAP_ARTICLES_EXCLUSION',
							  'COM_JMAP_ARTICLES_INCLUSION',
							  'COM_JMAP_CHOOSE_ARTICLES_EXCLUSION',
							  'COM_JMAP_CHOOSE_ARTICLES_EXCLUSION_DESC',
							  'COM_JMAP_CHOOSE_ARTICLES_INCLUSION',
							  'COM_JMAP_CHOOSE_ARTICLES_INCLUSION_DESC',
							  'COM_JMAP_RAW_SOURCE_LINK',
							  'COM_JMAP_LINK_TITLE',
							  'COM_JMAP_LINK_HREF',
							  'COM_JMAP_SELECTED_LINK_RECORDS');
		$this->injectJsTranslations($translations, $doc);
		
		// Load specific JS App
		$doc->getWebAssetManager()->registerAndUseScript ('jmap.sources', 'administrator/components/com_jmap/js/sources.js', [], [], ['jquery', 'jmap.classnative'] );
		
		$doc->getWebAssetManager()->addInlineScript("
					Joomla.submitbutton = function(pressbutton) {
						if(!jQuery.fn.validation) {
							jQuery.extend(jQuery.fn, jmapjQueryBackup.fn);
						}
				
						jQuery('#adminForm').validation();
				
						if (pressbutton == 'sources.cancelEntity') {
							jQuery('#adminForm').off();
							Joomla.submitform( pressbutton );
							return true;
						}
		
						if(jQuery('#adminForm').validate()) {
							Joomla.submitform( pressbutton );
							return true;
						}
						return false;
					};
				");
		
		$lists = $this->getModel()->getLists($row);
		$this->hasManifest = $this->getModel()->getHasManifest($row);
		$this->hasItemsCategorization = $this->getModel()->getHasCategoryByTitle($row);
		$this->isCategorySource = $this->getModel()->getIsCategorySource($row);
		$this->hasRouteManifest = $this->getModel()->getHasRouteManifest($row);
		$this->hasCreatedDate = $this->getModel()->getHasCreatedDate($row);
		$this->option = $this->getModel ()->getState ( 'option' );
		$this->supportedGNewsExtension = $this->getModel()->getHasGNewsSupport($row);
		$this->supportedRSSExtension = $this->getModel()->getHasRSSSupport($row);
		$this->supportedHreflangExtension = $this->getModel()->getHasHreflangSupport($row);
		
		// Load the parameter form if the data source is of type plugin
		if($row->type == 'plugin') {
			$this->params_form = $this->getModel()->getFormFields($row);
		}
		$this->record = $row;
		$this->lists = $lists;
		
		// Registry sqlquery_managed object
		$this->registrySqlQueryManaged = new Registry();
		$this->registrySqlQueryManaged->loadObject($row->sqlquery_managed);
		
		// Aggiunta toolbar
		$this->addEditEntityToolbar();
		
		parent::display ( 'edit' );
	}
}