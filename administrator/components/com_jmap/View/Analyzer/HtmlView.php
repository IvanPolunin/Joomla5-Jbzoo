<?php
namespace JExtstore\Component\JMap\Administrator\View\Analyzer;
/**
 * @package JMAP::ANALYZER::administrator::components::com_jmap
 * @subpackage views
 * @subpackage analyzer
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Pagination\Pagination;
use JExtstore\Component\JMap\Administrator\Framework\Helpers\Toolbars as ToolbarHelper;
use JExtstore\Component\JMap\Administrator\Framework\View as JMapView;

/**
 * @package JMAP::ANALYZER::administrator::components::com_jmap
 * @subpackage views
 * @subpackage analyzer
 * @since 2.3.3
 */
class HtmlView extends JMapView {
	// Template view variables
	protected $validationType;
	protected $pagination;
	protected $searchpageword;
	protected $exactsearchpage;
	protected $link_type;
	protected $cparams;
	protected $dataRole;
	protected $lists;
	protected $orders;
	protected $items;
	
	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addDisplayToolbar() {
		ToolbarHelper::title( Text::_( 'COM_JMAP_SITEMAP_ANALYZER' ), 'jmap' );
			
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
	public function display($tpl = null) {
		// Get main records
		$rows = $this->get ( 'Data' );
		$lists = $this->get ( 'Filters' );
		$total = $this->get ( 'Total' );
		$this->validationType = (int)($this->getModel()->getComponentParams()->get('linksanalyzer_validation_analysis', 2));
		
		$doc = $this->app->getDocument();
		$this->loadJQuery($doc);
		$this->loadBootstrap($doc);
		$doc->getWebAssetManager()->registerAndUseScript ('jmap.analyzer', 'administrator/components/com_jmap/js/analyzer.js', [], [], ['jquery'] );
		$doc->getWebAssetManager()->addInlineScript("var jmap_baseURI='" . Uri::root() . "';" .
													"var jmap_validationAnalysis=" . $this->validationType . ";");
		
		$doc->getWebAssetManager()->addInlineStyle('@media (max-width: 1800px) and (min-width: 767px){ body.admin.com_jmap { min-width: 1800px; }}' .
												   '@media (max-width: 640px) { body.admin.com_jmap { min-width: 640px; }}');
		
		// Inject js translations
		$translations = array (
				'COM_JMAP_ANALYZER_TITLE',
				'COM_JMAP_ANALYZER_PROCESS_RUNNING',
				'COM_JMAP_ANALYZER_STARTED_SITEMAP_GENERATION',
				'COM_JMAP_ANALYZER_ERROR_STORING_FILE',
				'COM_JMAP_ANALYZER_GENERATION_COMPLETE',
				'COM_JMAP_ANALYZER_ANALYZING_LINKS',
				'COM_JMAP_ANALYZER_INDEXED_LINK',
				'COM_JMAP_ANALYZER_NOAVAILABLE_LINK',
				'COM_JMAP_ANALYZER_NOINDEXED_LINK',
				'COM_JMAP_ANALYZER_LINKVALID',
				'COM_JMAP_ANALYZER_LINK_NOVALID',
				'COM_JMAP_ANALYZER_NOINFO',
				'COM_JMAP_ANALYZER_PAGESPEED_LOW',
				'COM_JMAP_ANALYZER_PAGESPEED_AVERAGE',
				'COM_JMAP_ANALYZER_PAGESPEED_HIGH',
				'COM_JMAP_ANALYZER_PAGESPEED_LCP',
				'COM_JMAP_ANALYZER_PAGESPEED_LCP_DESC',
				'COM_JMAP_ANALYZER_PAGESPEED_LCP_DESC_XTD',
				'COM_JMAP_ANALYZER_PAGESPEED_FID',
				'COM_JMAP_ANALYZER_PAGESPEED_FID_DESC',
				'COM_JMAP_ANALYZER_PAGESPEED_FID_DESC_XTD',
				'COM_JMAP_ANALYZER_PAGESPEED_CLS',
				'COM_JMAP_ANALYZER_PAGESPEED_CLS_DESC',
				'COM_JMAP_ANALYZER_PAGESPEED_CLS_DESC_XTD');
		$this->injectJsTranslations($translations, $doc);
						
		$orders = array ();
		$orders ['order'] = $this->getModel ()->getState ( 'order' );
		$orders ['order_Dir'] = $this->getModel ()->getState ( 'order_dir' );
		// Pagination view object model state populated
		$pagination = new Pagination ( $total, $this->getModel ()->getState ( 'limitstart' ), $this->getModel ()->getState ( 'limit' ) );
		
		$this->user = $this->app->getIdentity ();
		$this->pagination = $pagination;
		$this->searchpageword = $this->getModel ()->getState ( 'searchpageword', '' );
		$this->exactsearchpage = $this->getModel ()->getState ( 'exactsearchpage', null ) ? 'checked' : '';
		$this->link_type = $this->getModel ()->getState ('link_type', null);
		$this->cparams = $this->getModel()->getComponentParams();
		$this->dataRole = $this->cparams->get('linksanalyzer_indexing_analysis', 1) ? 'link' : 'neutral';
		$this->lists = $lists;
		$this->orders = $orders;
		$this->items = $rows;
		
		// Aggiunta toolbar
		$this->addDisplayToolbar();
		
		parent::display ( 'list' );
	}
}