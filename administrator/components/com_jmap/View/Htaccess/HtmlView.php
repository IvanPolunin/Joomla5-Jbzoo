<?php
namespace JExtstore\Component\JMap\Administrator\View\Htaccess;
/**
 * @package JMAP::HTACCESS::::administrator::components::com_jmap
 * @subpackage views
 * @subpackage htaccess
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Uri\Uri;
use JExtstore\Component\JMap\Administrator\Framework\View as JMapView;

/**
 * Htaccess editor view
 *
 * @package JMAP::HTACCESS::administrator::components::com_jmap
 * @subpackage views
 * @subpackage htaccess
 * @since 3.0
 */
class HtmlView extends JMapView {
	// Template view variables
	protected $htaccessVersion;
	protected $record;
	
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
		$translations = array (	'COM_JMAP_HTACCESS_PATH',
								'COM_JMAP_HTACCESS_OLD_PATH',
								'COM_JMAP_HTACCESS_DIRECTIVE_ADDED',
								'COM_JMAP_HTACCESS_REQUIRED' );
		$this->injectJsTranslations($translations, $doc);

		// Load specific JS App
		$doc->getWebAssetManager()->registerAndUseStyle ( 'jmap.htaccess', 'administrator/components/com_jmap/css/htaccess.css');
		$doc->getWebAssetManager()->registerAndUseScript ('jmap.htaccess', 'administrator/components/com_jmap/js/htaccess.js', [], [], ['jquery'] );

		$this->option = $this->option;
		$this->htaccessVersion = $this->getModel ()->getState ( 'htaccess_version' );
		$this->record = $row;
	
		parent::display ( 'edit' );
	}
}