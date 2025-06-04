<?php
namespace JExtstore\Component\JMap\Administrator\View\Indexing;
/**
 * @package JMAP::INDEXING::administrator::components::com_jmap
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
use JExtstore\Component\JMap\Administrator\Framework\Helpers\Toolbars as ToolbarHelper;
use JExtstore\Component\JMap\Administrator\Framework\View as JMapView;
use JExtstore\Component\JMap\Administrator\Framework\Seostats\Services\Google\Search as ServicesGoogleSearch;

/**
 * @package JMAP::INDEXING::administrator::components::com_jmap
 * @subpackage views
 * @subpackage datasets
 * @since 3.3
 */
class HtmlView extends JMapView {
	// Template view variables
	protected $pagination;
	protected $searchword;
	protected $serpsearch;
	protected $rankedpagekeyword;
	protected $items;
	protected $lists;
	protected $totalPagesValue;
	
	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addDisplayToolbar() {
		$user = $this->app->getIdentity();
		ToolbarHelper::title( Text::_('COM_JMAP_INDEXING' ), 'jmap' );
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
		$rows = $this->get('Data');
		$lists = $this->get ( 'Filters' );
		
		$doc = $this->app->getDocument();
		$this->loadJQuery($doc);
		$this->loadBootstrap($doc);
		$doc->getWebAssetManager()->addInlineScript("var jmap_baseURI='" . Uri::root() . "';");
		$doc->getWebAssetManager()->registerAndUseScript ('jmap.supersuggest', 'administrator/components/com_jmap/js/supersuggest.js', [], [], ['jquery'] );
		$doc->getWebAssetManager()->registerAndUseScript ('jmap.indexing', 'administrator/components/com_jmap/js/indexing.js', [], [], ['jquery', 'jmap.supersuggest'] );
		$doc->getWebAssetManager()->registerAndUseStyle ( 'jmap.indexing', 'administrator/components/com_jmap/css/indexing.css');
		
		$doc->getWebAssetManager()->addInlineStyle('@media (max-width: 1024px) { body.admin.com_jmap { min-width: 1024px; }}');
		
		// Pagination view object model state populated
		$pagination = new Pagination ( $this->getModel ()->getState ( 'numpages', 10 ) * 10, $this->getModel ()->getState ( 'limitstart', 0 ), 10 );
		$this->pagination = $pagination;
		$this->searchword = $this->getModel ()->getState ( 'searchword' );
		$this->serpsearch = $this->getModel ()->getState ( 'serpsearch' );
		$this->rankedpagekeyword = $this->getModel ()->getState ( 'rankedpagekeyword', null );
		$this->items = $rows;
		$this->lists = $lists;
		
		// Store number of indexed links found
		$totalLinksHtml = ServicesGoogleSearch::$numberIndexedPages;
		if($totalLinksHtml && isset($totalLinksHtml[1])) {
			$explodedChunks = explode(' ', $totalLinksHtml[1]);
			if(ServicesGoogleSearch::$paginationNumber > 0) {
				$calculatedChunk = count($explodedChunks) - 4;
			} else {
				$calculatedChunk = 1;
			}
			if(is_numeric(str_replace(',', '.', $explodedChunks[$calculatedChunk]))) {
				$this->totalPagesValue = $explodedChunks[$calculatedChunk];
			}
		}
		
		// Aggiunta toolbar
		$this->addDisplayToolbar();
			
		parent::display ( 'list' );
	}
}