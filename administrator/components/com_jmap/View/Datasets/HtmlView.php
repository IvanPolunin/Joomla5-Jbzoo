<?php
namespace JExtstore\Component\JMap\Administrator\View\Datasets;
/**
 * @package JMAP::DATASETS::administrator::components::com_jmap
 * @subpackage views
 * @subpackage datasets
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
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
 * @package JMAP::DATASETS::administrator::components::com_jmap
 * @subpackage views
 * @subpackage datasets
 * @since 2.0
 */
class HtmlView extends JMapView {
	// Template view variables
	protected $pagination;
	protected $searchword;
	protected $orders;
	protected $items;
	protected $urischeme;
	protected $componentParams;
	protected $record;
	protected $lists;
	
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
		$toolbarHelperTitle = $isNew ? 'COM_JMAP_DATASETS_NEW' : 'COM_JMAP_DATASETS_EDIT';
		
		ToolbarHelper::title( Text::_( $toolbarHelperTitle ), 'jmap' );
	
		if ($isNew)  {
			// For new records, check the create permission.
			if ($isNew && ($user->authorise('core.create', 'com_jmap'))) {
				ToolbarHelper::apply( 'datasets.applyEntity', 'JAPPLY');
				ToolbarHelper::save( 'datasets.saveEntity', 'JSAVE');
				ToolbarHelper::save2new( 'datasets.saveEntity2New');
			}
		} else {
			// Can't save the record if it's checked out.
			if (!$checkedOut) {
				// Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
				if ($user->authorise('core.edit', 'com_jmap')) {
					ToolbarHelper::apply( 'datasets.applyEntity', 'JAPPLY');
					ToolbarHelper::save( 'datasets.saveEntity', 'JSAVE');
					ToolbarHelper::save2new( 'datasets.saveEntity2New');
				}
			}
		}
			
		ToolbarHelper::custom('datasets.cancelEntity', 'cancel', 'cancel', 'JCANCEL', false);
	}
	
	
	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addDisplayToolbar() {
		$user = $this->app->getIdentity();
		ToolbarHelper::title( Text::_('COM_JMAP_DATASETS' ), 'jmap' );
		// Access check.
		if ($user->authorise('core.create', 'com_jmap')) {
			ToolbarHelper::addNew('datasets.editEntity', 'COM_JMAP_NEW_DATASET');
		}
	
		if ($user->authorise('core.edit', 'com_jmap')) {
			ToolbarHelper::editList('datasets.editEntity', 'COM_JMAP_EDIT_DATASET');
		}
	
		if ($user->authorise('core.delete', 'com_jmap') && $user->authorise('core.edit', 'com_jmap')) {
			ToolbarHelper::deleteList('COM_JMAP_DELETE_LINK', 'datasets.deleteEntity');
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
		
		$doc = $this->app->getDocument();
		$this->loadJQuery($doc);
		$this->loadBootstrap($doc);
		$doc->getWebAssetManager()->registerAndUseStyle ( 'jmap.datasets', 'administrator/components/com_jmap/css/datasets.css');

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
		$this->option = $this->getModel ()->getState ( 'option' );
		
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
		$this->loadJQueryUI($doc);
		$this->loadBootstrap($doc);
		$this->loadValidation($doc);
		$doc->getWebAssetManager()->registerAndUseStyle ( 'jmap.datasets', 'administrator/components/com_jmap/css/datasets.css');
		$doc->getWebAssetManager()->addInlineScript("var jmap_baseURI='$base';" .
													"var jmap_urischeme='$this->urischeme';");
		
		// Inject js translations
		$translations = array( 'COM_JMAP_SELECTONESOURCE' );
		$this->injectJsTranslations($translations, $doc);
		
		// Load specific JS App
		$doc->getWebAssetManager()->registerAndUseScript ('jmap.jquery.form', 'administrator/components/com_jmap/js/jquery.form.min.js', [], [], ['jquery'] );
		$doc->getWebAssetManager()->registerAndUseScript ('jmap.datasets', 'administrator/components/com_jmap/js/datasets.js', [], [], ['jquery'] );
		
		$doc->getWebAssetManager()->addInlineScript("
						Joomla.submitbutton = function(pressbutton) {
							if(!jQuery.fn.validation) {
								jQuery.extend(jQuery.fn, jmapjQueryBackup.fn);
							}
				
							jQuery('#adminForm').validation();
							
							if (pressbutton == 'datasets.cancelEntity') {
								jQuery('#adminForm').off();
								Joomla.submitform( pressbutton );
								return true;
							}
				
							if(jQuery('#adminForm').validate() && JMapDatasets.validateSelectable()) {
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