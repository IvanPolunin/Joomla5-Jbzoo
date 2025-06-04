<?php
namespace JExtstore\Component\JMap\Administrator\View\Aigenerator;
/**
 * @package JMAP::AIGENERATOR::administrator::components::com_jmap
 * @subpackage views
 * @subpackage aigenerator
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Pagination\Pagination;
use Joomla\Filter\OutputFilter;
use JExtstore\Component\JMap\Administrator\Framework\Language\Multilang;
use JExtstore\Component\JMap\Administrator\Framework\Helpers\Toolbars as ToolbarHelper; 
use JExtstore\Component\JMap\Administrator\Framework\View as JMapView;

/**
 * @package JMAP::AIGENERATOR::administrator::components::com_jmap
 * @subpackage views
 * @subpackage aigenerator
 * @since 2.0
 */
class HtmlView extends JMapView {
	// Template view variables
	protected $pagination;
	protected $searchword;
	protected $orders;
	protected $items;
	protected $urischeme;
	protected $record;
	protected $lists;
	protected $languagePluginEnabled;
	protected $defaultLanguageCode;
	
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
		$toolbarHelperTitle = $isNew ? 'COM_JMAP_AIGENERATOR_CONTENT_NEW' : 'COM_JMAP_AIGENERATOR_CONTENT_EDIT';
		
		ToolbarHelper::title( Text::_( $toolbarHelperTitle ), 'jmap' );
	
		if ($isNew)  {
			// For new records, check the create permission.
			if ($isNew && ($user->authorise('core.create', 'com_jmap'))) {
				ToolbarHelper::custom('aigenerator.generateEntity', 'cogs', 'cogs', 'COM_JMAP_AIGENERATOR_GENERATE_CONTENTS', false);
				ToolbarHelper::apply( 'aigenerator.applyEntity', 'JAPPLY');
				ToolbarHelper::save( 'aigenerator.saveEntity', 'JSAVE');
				ToolBarHelper::save2new( 'aigenerator.saveEntity2New');
			}
		} else {
			// Can't save the record if it's checked out.
			if (!$checkedOut) {
				// Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
				if ($user->authorise('core.edit', 'com_jmap')) {
					ToolbarHelper::custom('aigenerator.generateEntity', 'cogs', 'cogs', 'COM_JMAP_AIGENERATOR_GENERATE_CONTENTS', false);
					ToolbarHelper::apply( 'aigenerator.applyEntity', 'JAPPLY');
					ToolbarHelper::save( 'aigenerator.saveEntity', 'JSAVE');
					ToolBarHelper::save2new( 'aigenerator.saveEntity2New');
				}
			}
		}
			
		ToolbarHelper::custom('aigenerator.cancelEntity', 'cancel', 'cancel', 'JCANCEL', false);
	}
	
	
	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addDisplayToolbar() {
		$user = $this->app->getIdentity();
		ToolbarHelper::title( Text::_('COM_JMAP_AIGENERATOR_TITLE' ), 'jmap' );
		// Access check.
		if ($user->authorise('core.create', 'com_jmap')) {
			ToolbarHelper::addNew('aigenerator.editEntity', 'COM_JMAP_AIGENERATOR_NEW_CONTENT');
		}
	
		if ($user->authorise('core.edit', 'com_jmap')) {
			ToolbarHelper::editList('aigenerator.editEntity', 'COM_JMAP_AIGENERATOR_EDIT_CONTENT');
		}
	
		if ($user->authorise('core.delete', 'com_jmap') && $user->authorise('core.edit', 'com_jmap')) {
			ToolbarHelper::deleteList('COM_JMAP_AIGENERATOR_DELETE_CONTENT', 'aigenerator.deleteEntity');
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
		$this->loadJQueryUI($doc);
		$doc->getWebAssetManager()->addInlineStyle('@media (max-width: 640px) { body.admin.com_jmap { min-width: 640px; }}');
		
		$orders = array ();
		$orders ['order'] = $this->getModel ()->getState ( 'order' );
		$orders ['order_Dir'] = $this->getModel ()->getState ( 'order_dir' );
		// Pagination view object model state populated
		$pagination = new Pagination ( $total, $this->getModel ()->getState ( 'limitstart' ), $this->getModel ()->getState ( 'limit' ) );
		
		$this->user = $this->app->getIdentity ();
		$this->pagination = $pagination;
		$this->searchword = $this->getModel ()->getState ( 'searchword' );
		$this->orders = $orders;
		$this->items = $rows;
		$this->lists = $lists;
		$this->option = $this->getModel ()->getState ( 'option' );
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
		
		// Detect uri scheme
		$instance = Uri::getInstance();
		$this->urischeme = $instance->isSSL() ? 'https' : 'http';
		
		// Load JS Client App dependencies
		$doc = $this->app->getDocument();
		$base = Uri::root();
		$this->loadJQuery($doc);
		$this->loadBootstrap($doc);
		$this->loadValidation($doc);
		
		$doc->getWebAssetManager()->addInlineScript("var jmap_baseURI='$base';" .
													"var jmap_urischeme='$this->urischeme';");
		
		// Inject js translations
		$translations = array(	'COM_JMAP_PROGRESSAIGENERATORTITLE',
								'COM_JMAP_PROGRESSAIGENERATORSUBTITLE',
								'COM_JMAP_AIGENERATOR_COPIED_CONTENT',
								'COM_JMAP_PROGRESSAIGENERATOR_ARTICLE_CREATION_TITLE',
								'COM_JMAP_PROGRESSAIGENERATOR_MODULE_CREATION_TITLE',
								'COM_JMAP_PROGRESSAIGENERATOR_CONTENT_CREATION_SUBTITLE_ARTICLE',
								'COM_JMAP_PROGRESSAIGENERATOR_CONTENT_CREATION_SUBTITLE_MODULE',
								'COM_JMAP_PROGRESSAIGENERATOR_CONTENT_CREATION_SUBTITLE_ARTICLE_SUCCESS',
								'COM_JMAP_PROGRESSAIGENERATOR_CONTENT_CREATION_SUBTITLE_MODULE_SUCCESS',
								'COM_JMAP_PROGRESSAIGENERATOR_ARTICLE_CREATION_ERROR',
								'COM_JMAP_PROGRESSAIGENERATOR_MODULE_CREATION_ERROR',
								'COM_JMAP_PROGRESSAIGENERATOR_CONTENT_CREATION_EDIT_ARTICLE',
								'COM_JMAP_PROGRESSAIGENERATOR_CONTENT_CREATION_EDIT_MODULE');
		$this->injectJsTranslations($translations, $doc);
		
		// Load specific JS App
		$doc->getWebAssetManager()->registerAndUseScript ('jmap.aigenerator', 'administrator/components/com_jmap/js/aigenerator.js', [], [], ['jquery'] );
		
		$doc->getWebAssetManager()->addInlineScript("
						Joomla.submitbutton = function(pressbutton) {
							if(!jQuery.fn.validation) {
								jQuery.extend(jQuery.fn, jmapjQueryBackup.fn);
							}
				
							jQuery('#adminForm').validation();
							
							if (pressbutton == 'aigenerator.cancelEntity') {	
								jQuery('#adminForm').off();
								Joomla.submitform( pressbutton );
								return true;
							}
							
							if(jQuery('#adminForm').validate()) {
								if (pressbutton == 'aigenerator.generateEntity') {	
									// Start the progress bar
									JMapAIContentGenerator.openProgressContentGeneration();
								}
								Joomla.submitform( pressbutton );
								return true;
							}
							return false;
						};
					");
		
		// Get default site language
		$langParams = ComponentHelper::getParams('com_languages');
		// Setup predefined site language
		$this->defaultLanguageCode = $langParams->get('site');
		$this->defaultSefLanguageCode = Multilang::getCurrentSefLanguage();
		
		$lists = $this->getModel()->getLists($row);
		$this->option = $this->getModel ()->getState ( 'option' );
		$this->record = $row;
		$this->lists = $lists;
		
		// Aggiunta toolbar
		$this->addEditEntityToolbar();
		
		parent::display ( 'edit' );
	}
}