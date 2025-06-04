<?php
namespace JExtstore\Component\JMap\Administrator\View\Config;
/**
 *
 * @package JMAP::CONFIG::administrator::components::com_jmap
 * @subpackage views
 * @subpackage config
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use JExtstore\Component\JMap\Administrator\Framework\Helpers\Toolbars as ToolbarHelper;
use JExtstore\Component\JMap\Administrator\Framework\View as JMapView;

/**
 * Config view
 *
 * @package JMAP::CONFIG::administrator::components::com_jmap
 * @subpackage views
 * @subpackage config
 * @since 1.0
 */
class HtmlView extends JMapView {
	// Template view variables
	protected $params_form;
	protected $params;
	
	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addDisplayToolbar() {
		$user = $this->app->getIdentity();
		ToolbarHelper::title( Text::_( 'COM_JMAP_JMAPCONFIG' ), 'jmap' );
		
		if ($user->authorise('core.edit', 'com_jmap')) {
			ToolbarHelper::save('config.saveentity', 'COM_JMAP_SAVECONFIG');
			ToolbarHelper::custom('config.exportConfig', 'download', 'download', 'COM_JMAP_EXPORT_CONFIG', false);
			ToolbarHelper::custom('config.importConfig', 'upload', 'upload', 'COM_JMAP_IMPORT_CONFIG', false);
		}
		
		ToolbarHelper::custom('cpanel.display', 'home', 'home', 'COM_JMAP_CPANEL', false);
	}
	
	/**
	 * Configuration panel rendering for component settings
	 *
	 * @access public
	 * @param string $tpl
	 * @return void
	 */
	public function display($tpl = null) {
		$doc = $this->app->getDocument();
		$this->loadJQuery($doc);
		$this->loadBootstrap($doc);
		$this->loadValidation($doc);
		$doc->getWebAssetManager()->registerAndUseStyle ( 'jmap.colorpicker', 'administrator/components/com_jmap/css/colorpicker.css');
		$doc->getWebAssetManager()->registerAndUseScript ('jmap.colorpicker', 'administrator/components/com_jmap/js/colorpicker.js', [], [], ['jquery'] );
		$doc->getWebAssetManager()->registerAndUseScript ('jmap.fileconfig', 'administrator/components/com_jmap/js/fileconfig.js', [], [], ['jquery'] );
		
		// Load specific JS App
		$doc->getWebAssetManager()->addInlineScript("
					Joomla.submitbutton = function(pressbutton) {
						if(!jQuery.fn.validation) {
							jQuery.extend(jQuery.fn, jmapjQueryBackup.fn);
						}
				
						jQuery('#adminForm').validation();
		
						if (pressbutton == 'cpanel.display') {
							jQuery('#adminForm').off();
							Joomla.submitform( pressbutton );
							return true;
						}
		
						if(jQuery('#adminForm').validate()) {
							Joomla.submitform( pressbutton );
				
							if (pressbutton == 'config.exportConfig') {
								jQuery('#adminForm input[name=task]').val('config.display');
							}
				
							// Clear SEO stats and fetch new fresh data
							if( window.sessionStorage !== null && jQuery('#params_seostats_custom_link').data('changed') == 1) {
								sessionStorage.removeItem('seostats');
								sessionStorage.removeItem('seostats_service');
								sessionStorage.removeItem('seostats_targeturl');
							}
							return true;
						}
						var parentId = jQuery('ul.errorlist').parents('div.tab-pane').attr('id');

						var nodeElement = document.querySelector('#tab_configuration a[data-element=' + parentId + ']');
						if(nodeElement) {
							var tabInstance = new bootstrap.Tab(nodeElement);
							tabInstance.show();
						}

						return false;
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
		
		$params = $this->get('Data');
		$form = $this->get('Form');
		
		// Bind the form to the data.
		if ($form && $params) {
			$form->bind($params);
		}
		
		$this->params_form = $form;
		$this->params = $params;
		
		// Aggiunta toolbar
		$this->addDisplayToolbar();
		
		// Output del template
		parent::display();
	}
	
	/**
	 * Configuration panel rendering for component settings
	 *
	 * @access public
	 * @param string $tpl
	 * @return void
	 */
	public function checkCrawler($tpl = null) {
		$doc = $this->app->getDocument();
		$this->loadJQuery($doc);
		$this->loadBootstrap($doc);
		
		$this->testResults = $this->get('CheckCrawler');
		
		// Output del template
		parent::display($tpl);
	}
}