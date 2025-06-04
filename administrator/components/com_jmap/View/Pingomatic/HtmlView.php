<?php
namespace JExtstore\Component\JMap\Administrator\View\Pingomatic;
/**
 * @package JMAP::PINGOMATIC::administrator::components::com_jmap
 * @subpackage views
 * @subpackage pingomatic
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
 * @package JMAP::PINGOMATIC::administrator::components::com_jmap
 * @subpackage views
 * @subpackage pingomatic
 * @since 2.0
 */
class HtmlView extends JMapView {
	// Template view variables
	protected $pagination;
	protected $searchword;
	protected $orders;
	protected $items;
	protected $dates;
	protected $urischeme;
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
		$toolbarHelperTitle = $isNew ? 'COM_JMAP_PINGOMATIC_LINKS_NEW' : 'COM_JMAP_PINGOMATIC_LINKS_EDIT';
		
		ToolbarHelper::title( Text::_( $toolbarHelperTitle ), 'jmap' );
	
		if ($isNew)  {
			// For new records, check the create permission.
			if ($isNew && ($user->authorise('core.create', 'com_jmap'))) {
				ToolbarHelper::custom('pingomatic.sendEntity', 'broadcast', 'broadcast', 'COM_JMAP_SEND_PING', false);
				ToolbarHelper::apply( 'pingomatic.applyEntity', 'JAPPLY');
				ToolbarHelper::save( 'pingomatic.saveEntity', 'JSAVE');
				ToolBarHelper::save2new( 'pingomatic.saveEntity2New');
			}
		} else {
			// Can't save the record if it's checked out.
			if (!$checkedOut) {
				// Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
				if ($user->authorise('core.edit', 'com_jmap')) {
					ToolbarHelper::custom('pingomatic.sendEntity', 'broadcast', 'broadcast', 'COM_JMAP_SEND_PING', false);
					ToolbarHelper::apply( 'pingomatic.applyEntity', 'JAPPLY');
					ToolbarHelper::save( 'pingomatic.saveEntity', 'JSAVE');
					ToolBarHelper::save2new( 'pingomatic.saveEntity2New');
				}
			}
		}
			
		ToolbarHelper::custom('pingomatic.cancelEntity', 'cancel', 'cancel', 'JCANCEL', false);
	}
	
	
	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addDisplayToolbar() {
		$user = $this->app->getIdentity();
		ToolbarHelper::title( Text::_('COM_JMAP_PINGOMATIC' ), 'jmap' );
		// Access check.
		if ($user->authorise('core.create', 'com_jmap')) {
			ToolbarHelper::addNew('pingomatic.editEntity', 'COM_JMAP_NEW_LINK');
		}
	
		if ($user->authorise('core.edit', 'com_jmap')) {
			ToolbarHelper::editList('pingomatic.editEntity', 'COM_JMAP_EDIT_LINK');
		}
	
		if ($user->authorise('core.delete', 'com_jmap') && $user->authorise('core.edit', 'com_jmap')) {
			ToolbarHelper::deleteList('COM_JMAP_DELETE_LINK', 'pingomatic.deleteEntity');
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
		$this->loadJQueryUI($doc);
		$doc->getWebAssetManager()->addInlineScript("
						jQuery(function($) {
							$('input[data-role=calendar]').datepicker({
								dateFormat:'yy-mm-dd',
								firstDay: 1
							}).prev('span').on('click', function(){
								$(this).datepicker('show');
							});
							$('a.fancybox').fancybox();
						});
					");
		$doc->getWebAssetManager()->registerAndUseStyle ( 'jmap.jquery.fancybox', 'administrator/components/com_jmap/css/jquery.fancybox.css');
		
		$doc->getWebAssetManager()->addInlineStyle('@media (max-width: 640px) { body.admin.com_jmap { min-width: 640px; }}');
		
		// Evaluate nonce csp feature
		$appNonce = $this->app->get('csp_nonce', null);
		$nonce = $appNonce ? ' nonce="' . $appNonce . '"' : '';
		$doc->addCustomTag ('<script type="text/javascript"' . $nonce . ' src="' . Uri::root ( true ) . '/administrator/components/com_jmap/js/jquery.fancybox.pack.js' . '"></script>');
		
		$orders = array ();
		$orders ['order'] = $this->getModel ()->getState ( 'order' );
		$orders ['order_Dir'] = $this->getModel ()->getState ( 'order_dir' );
		// Pagination view object model state populated
		$pagination = new Pagination ( $total, $this->getModel ()->getState ( 'limitstart' ), $this->getModel ()->getState ( 'limit' ) );
		$dates = array('from'=>$this->getModel()->getState('fromPeriod'), 'to'=>$this->getModel()->getState('toPeriod'));
		
		$this->user = $this->app->getIdentity ();
		$this->pagination = $pagination;
		$this->searchword = $this->getModel ()->getState ( 'searchword' );
		$this->orders = $orders;
		$this->items = $rows;
		$this->option = $this->getModel ()->getState ( 'option' );
		$this->dates = $dates;
		
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
		$doc->getWebAssetManager()->registerAndUseStyle ( 'jmap.pingomatic', 'administrator/components/com_jmap/css/pingomatic.css');
		
		$doc->getWebAssetManager()->addInlineScript("var jmap_baseURI='$base';" .
													"var jmap_urischeme='$this->urischeme';");
		
		// Inject js translations
		$translations = array(	'COM_JMAP_SELECTFIELD',
							  	'COM_JMAP_SELECTONESERVICE',
						  		'COM_JMAP_PROGRESSPINGOMATICTITLE',
							  	'COM_JMAP_PROGRESSPINGOMATICSUBTITLE',
							  	'COM_JMAP_PROGRESSPINGOMATICSUBTITLE2SUCCESS',
							  	'COM_JMAP_PROGRESSPINGOMATICSUBTITLE2ERROR',
							  	'COM_JMAP_PROGRESSMODELTITLE',
								'COM_JMAP_PROGRESSMODELSUBTITLE',
								'COM_JMAP_PROGRESSMODELSUBTITLE2SUCCESS',
								'COM_JMAP_PROGRESSMODELSUBTITLE2ERROR',
								'COM_JMAP_LOADING');
		$this->injectJsTranslations($translations, $doc);
		
		// Load specific JS App
		$doc->getWebAssetManager()->registerAndUseScript ('jmap.jquery.form', 'administrator/components/com_jmap/js/jquery.form.min.js', [], [], ['jquery'] );
		$doc->getWebAssetManager()->registerAndUseScript ('jmap.pingomatic', 'administrator/components/com_jmap/js/pingomatic.js', [], [], ['jquery', 'jmap.jquery.form'] );
		
		$doc->getWebAssetManager()->addInlineScript("
						Joomla.submitbutton = function(pressbutton) {
							if(!jQuery.fn.validation) {
								jQuery.extend(jQuery.fn, jmapjQueryBackup.fn);
							}
				
							jQuery('#adminForm').validation();
							
							if (pressbutton == 'pingomatic.cancelEntity') {	
								jQuery('#adminForm').off();
								Joomla.submitform( pressbutton );
								return true;
							}
							
							if (pressbutton == 'pingomatic.sendEntity') {	
								// Start Pingomatic JS APP plugin
								jQuery('#adminForm').Pingomatic({});
								return false;
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
		$this->record = $row;
		$this->lists = $lists;
		
		// Aggiunta toolbar
		$this->addEditEntityToolbar();
		
		parent::display ( 'edit' );
	}
}