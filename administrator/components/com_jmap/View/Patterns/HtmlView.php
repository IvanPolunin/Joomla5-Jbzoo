<?php
namespace JExtstore\Component\JMap\Administrator\View\Patterns;
/**
 * @package JMAP::LINKS::administrator::components::com_jmap
 * @subpackage views
 * @subpackage links
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Pagination\Pagination;
use Joomla\Filter\OutputFilter;
use JExtstore\Component\JMap\Administrator\Framework\Helpers\Toolbars as ToolbarHelper;
use JExtstore\Component\JMap\Administrator\Framework\View as JMapView;

/**
 * @package JMAP::PATTERNS::administrator::components::com_jmap
 * @subpackage views
 * @subpackage links
 * * @since 1.8
 */
class HtmlView extends JMapView {
	// Template view variables
	protected $pagination;
	protected $searchword;
	protected $urlRewriting;
	protected $orders;
	protected $lists;
	protected $items;
	protected $isMultiLanguage;
	protected $urischeme;
	protected $componentParams;
	protected $record;
	
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
		$toolbarHelperTitle = $isNew ? 'COM_JMAP_PATTERNS_REPLACEMENT_NEW' : 'COM_JMAP_PATTERNS_REPLACEMENT_EDIT';
		
		ToolbarHelper::title( Text::_( $toolbarHelperTitle ), 'jredirects' );
		
		if ($isNew)  {
			// For new records, check the create permission.
			if ($isNew && ($user->authorise('core.create', 'com_jmap'))) {
				ToolbarHelper::apply( 'patterns.applyEntity', 'JAPPLY');
				ToolbarHelper::save( 'patterns.saveEntity', 'JSAVE');
				ToolBarHelper::save2new( 'patterns.saveEntity2New');
			}
		} else {
			// Can't save the record if it's checked out.
			if (!$checkedOut) {
				// Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
				if ($user->authorise('core.edit', 'com_jmap')) {
					ToolbarHelper::apply( 'patterns.applyEntity', 'JAPPLY');
					ToolbarHelper::save( 'patterns.saveEntity', 'JSAVE');
					ToolBarHelper::save2new( 'patterns.saveEntity2New');
				}
			}
		}
		
		ToolbarHelper::custom('patterns.cancelEntity', 'cancel', 'cancel', 'JCANCEL', false);
	}
	
	
	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addDisplayToolbar() {
		$user = $this->app->getIdentity();
		ToolbarHelper::title( Text::_('COM_JMAP_PATTERNS_REPLACEMENT_TITLE' ), 'jredirects' );
		
		// Access check.
		if ($user->authorise('core.create')) {
			ToolbarHelper::addNew('patterns.editEntity', 'COM_JMAP_NEW_PATTERN');
		}
		
		if ($user->authorise('core.edit')) {
			ToolbarHelper::editList('patterns.editentity', 'COM_JMAP_EDIT_PATTERN');
		}
		
		if ($user->authorise('core.delete') && $user->authorise('core.edit')) {
			ToolbarHelper::deleteList('COM_JMAP_DELETE_ENTITY', 'patterns.deleteentity');
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
		$total = $this->get ( 'Total' );
		$lists = $this->get('Filters');
		
		$doc = $this->app->getDocument();
		$this->loadJQuery($doc);
		$this->loadBootstrap($doc);
		
		$doc->getWebAssetManager()->addInlineStyle('@media (max-width: 1280px) and (min-width: 768px) { body.admin.com_jmap { min-width: 1280px; }}');
		
		$orders = array ();
		$orders ['order'] = $this->getModel ()->getState ( 'order' );
		$orders ['order_Dir'] = $this->getModel ()->getState ( 'order_dir' );
		// Pagination view object model state populated
		$pagination = new Pagination ( $total, $this->getModel ()->getState ( 'limitstart' ), $this->getModel ()->getState ( 'limit' ) );
		
		$this->user = $this->app->getIdentity ();
		$this->pagination = $pagination;
		$this->searchword = $this->getModel ()->getState ( 'searchword' );
		$this->option = $this->getModel ()->getState ( 'option' );
		$this->urlRewriting = $this->app->get('sef_rewrite', 0) ? '' : 'index.php/';
		$this->orders = $orders;
		$this->lists = $lists;
		$this->items = $rows;
		
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
		
		// Inject js translations
		/*$translations = array( '' );
		 $this->injectJsTranslations($translations, $doc);*/
		
		// Load specific JS App
		$doc->getWebAssetManager()->addInlineScript("
						Joomla.submitbutton = function(pressbutton) {
							if(!jQuery.fn.validation) {
								jQuery.extend(jQuery.fn, jredirectsjQueryBackup.fn);
							}
				
							jQuery('#adminForm').validation();
				
							if (pressbutton == 'patterns.cancelEntity') {
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
		$this->option = $this->getModel ()->getState ( 'option' );
		$this->componentParams = $this->getModel()->getComponentParams();
		$this->record = $row;
		$this->lists = $lists;
		
		// Aggiunta toolbar
		$this->addEditEntityToolbar();
		
		parent::display ( 'edit' );
	}
}