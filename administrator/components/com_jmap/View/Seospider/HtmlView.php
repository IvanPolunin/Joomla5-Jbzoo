<?php
namespace JExtstore\Component\JMap\Administrator\View\Seospider;
/**
 * @package JMAP::SEOSPIDER::administrator::components::com_jmap
 * @subpackage views
 * @subpackage seospider
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
 * @package JMAP::SEOSPIDER::administrator::components::com_jmap
 * @subpackage views
 * @subpackage seospider
 * @since 3.8
 */
class HtmlView extends JMapView {
	// Template view variables
	protected $cparams;
	protected $pagination;
	protected $link_type;
	protected $searchpageword;
	protected $dataRole;
	protected $lists;
	protected $orders;
	protected $items;
	protected $limitValue;
	
	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addDisplayToolbar() {
		ToolbarHelper::title( Text::_( 'COM_JMAP_SITEMAP_SEOSPIDER' ), 'jmap' );

		// Check user permissions to edit record
		if ($this->user->authorise('core.edit', 'com_jmap')) {
			if($this->cparams->get('seospider_override_headings', 1) || $this->cparams->get('seospider_override_canonical', 1)) {
				ToolbarHelper::custom('seospider.exportEntities', 'download', 'download', 'COM_JMAP_EXPORT_HEADINGS', false);
				ToolbarHelper::custom('seospider.importEntities', 'upload', 'upload', 'COM_JMAP_IMPORT_HEADINGS', false);
			}
			ToolbarHelper::custom('seospider.exportXls', 'arrow-down-2', 'arrow-down-2', 'COM_JMAP_EXPORT_XLS', false);
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
		$this->cparams = $this->getModel()->getComponentParams();
		
		$doc = $this->app->getDocument();
		$this->loadJQuery($doc);
		$this->loadJQueryUI($doc);
		$this->loadBootstrap($doc);
		
		$doc->getWebAssetManager()->registerAndUseScript ('jmap.seospider', 'administrator/components/com_jmap/js/seospider.js', [], [], ['jquery'] );
		$doc->getWebAssetManager()->registerAndUseScript ('jmap.filesources', 'administrator/components/com_jmap/js/filesources.js', [], [], ['jquery'] );
		$doc->getWebAssetManager()->registerAndUseStyle ('jmap.seospider', 'administrator/components/com_jmap/css/seospider.css');
		
		$doc->getWebAssetManager()->addInlineScript("var jmap_baseURI='" . Uri::root() . "';" .
													"var jmap_crawlerDelay=" . $this->cparams->get('seospider_crawler_delay', 500) . ";" .
													"var jmap_overrideheadings=" . $this->cparams->get('seospider_override_headings', 1) . ";" .
													"var jmap_overrideheadingsHtml=" . $this->cparams->get('seospider_override_headings_html', 0) . ";" .
													"var jmap_overridecanonical=" . $this->cparams->get('seospider_override_canonical', 1) . ";");
		
		$doc->getWebAssetManager()->addInlineStyle('@media (max-width: 2048px) { body.admin.com_jmap { min-width: fit-content; }}');
		
		// Inject js translations
		$translations = array (
				'COM_JMAP_SEOSPIDER_TITLE',
				'COM_JMAP_SEOSPIDER_PROCESS_RUNNING',
				'COM_JMAP_SEOSPIDER_STARTED_SITEMAP_GENERATION',
				'COM_JMAP_SEOSPIDER_ERROR_STORING_FILE',
				'COM_JMAP_SEOSPIDER_GENERATION_COMPLETE',
				'COM_JMAP_SEOSPIDER_CRAWLING_LINKS',
				'COM_JMAP_SEOSPIDER_NOAVAILABLE_LINK',
				'COM_JMAP_SEOSPIDER_LINKVALID',
				'COM_JMAP_SEOSPIDER_LINK_NOVALID',
				'COM_JMAP_SEOSPIDER_NOINFO',
				'COM_JMAP_SEOSPIDER_TITLE_TOOSHORT',
				'COM_JMAP_SEOSPIDER_TITLE_TOOSHORT_DESC',
				'COM_JMAP_SEOSPIDER_TITLE_TOOLONG',
				'COM_JMAP_SEOSPIDER_TITLE_TOOLONG_DESC',
				'COM_JMAP_SEOSPIDER_TITLE_MISSING',
				'COM_JMAP_SEOSPIDER_TITLE_MISSING_DESC',
				'COM_JMAP_SEOSPIDER_DESCRIPTION_TOOSHORT',
				'COM_JMAP_SEOSPIDER_DESCRIPTION_TOOSHORT_DESC',
				'COM_JMAP_SEOSPIDER_DESCRIPTION_TOOLONG',
				'COM_JMAP_SEOSPIDER_DESCRIPTION_TOOLONG_DESC',
				'COM_JMAP_SEOSPIDER_DESCRIPTION_MISSING',
				'COM_JMAP_SEOSPIDER_DESCRIPTION_MISSING_DESC',
				'COM_JMAP_SEOSPIDER_DIALOG_DUPLICATES_TITLE',
				'COM_JMAP_SEOSPIDER_DIALOG_DUPLICATES_DESCRIPTION',
				'COM_JMAP_SEOSPIDER_NOINDEX',
				'COM_JMAP_SEOSPIDER_NOINDEX_DESC',
				'COM_JMAP_SEOSPIDER_NOFOLLOW',
				'COM_JMAP_SEOSPIDER_NOFOLLOW_DESC',
				'COM_JMAP_SEOSPIDER_HEADERS_MISSING',
				'COM_JMAP_SEOSPIDER_HEADERS_MISSING_DESC',
				'COM_JMAP_SEOSPIDER_OPEN_DETAILS',
				'COM_JMAP_SEOSPIDER_TITLE_DETAILS',
				'COM_JMAP_SEOSPIDER_DESCRIPTION_DETAILS',
				'COM_JMAP_SEOSPIDER_SELECTED_LINK_DETAILS',
				'COM_JMAP_SEOSPIDER_CONTENT_ANALYSIS_DIALOG_TITLE',
				'COM_JMAP_SEOSPIDER_CONTENT_ANALYSIS_LINK',
				'COM_JMAP_SEOSPIDER_CONTENT_ANALYSIS_FOCUS_KEYWORD',
				'COM_JMAP_SEOSPIDER_CONTENT_ANALYSIS_CHOOSE_KEYWORD',
				'COM_JMAP_SEOSPIDER_CONTENT_ANALYSIS_START',
				'COM_JMAP_SEOSPIDER_CONTENT_ANALYSIS_STARTED',
				'COM_JMAP_SEOSPIDER_CONTENT_ANALYSIS_RESULTS',
				'COM_JMAP_SEOSPIDER_CONTENT_ANALYSIS_ERROR',
				'COM_JMAP_SEOSPIDER_CONTENT_ANALYSIS_DIALOG_FOOTER',
				'COM_JMAP_SEOSPIDER_CONTENT_ANALYSIS_TITLE_KEYWORD',
				'COM_JMAP_SEOSPIDER_CONTENT_ANALYSIS_TITLE_NOKEYWORD',
				'COM_JMAP_SEOSPIDER_CONTENT_ANALYSIS_DESCRIPTION_KEYWORD',
				'COM_JMAP_SEOSPIDER_CONTENT_ANALYSIS_DESCRIPTION_NOKEYWORD',
				'COM_JMAP_SEOSPIDER_CONTENT_ANALYSIS_H1_KEYWORD',
				'COM_JMAP_SEOSPIDER_CONTENT_ANALYSIS_H2_H3_KEYWORD',
				'COM_JMAP_SEOSPIDER_CONTENT_ANALYSIS_HEADERS_NO_KEYWORD',
				'COM_JMAP_SEOSPIDER_CONTENT_ANALYSIS_INURL_KEYWORD',
				'COM_JMAP_SEOSPIDER_CONTENT_ANALYSIS_INURL_NOKEYWORD',
				'COM_JMAP_SEOSPIDER_CONTENT_ANALYSIS_REPS_KEYWORD',
				'COM_JMAP_SEOSPIDER_CONTENT_ANALYSIS_REPS_NOKEYWORD',
				'COM_JMAP_SEOSPIDER_CONTENT_ANALYSIS_ALTIMAGES_KEYWORD',
				'COM_JMAP_SEOSPIDER_CONTENT_ANALYSIS_ALTIMAGES_NOKEYWORD',
				'COM_JMAP_SEOSPIDER_HEADINGS_DIALOG_TITLE',
				'COM_JMAP_SEOSPIDER_HEADINGS_LINK',
				'COM_JMAP_SEOSPIDER_HEADINGS_ORIGINAL_HEADING',
				'COM_JMAP_SEOSPIDER_HEADINGS_OVERRIDE_HEADING',
				'COM_JMAP_SEOSPIDER_HEADINGS_SAVE',
				'COM_JMAP_SEOSPIDER_HEADINGS_DELETE',
				'COM_JMAP_SEOSPIDER_HEADINGS_SAVED_MESSAGE',
				'COM_JMAP_SEOSPIDER_HEADINGS_EDIT_OVERRIDE',
				'COM_JMAP_SEOSPIDER_CANONICAL_DIALOG_TITLE',
				'COM_JMAP_SEOSPIDER_CANONICAL_LINK',
				'COM_JMAP_SEOSPIDER_CANONICAL_ORIGINAL_HEADING',
				'COM_JMAP_SEOSPIDER_CANONICAL_OVERRIDE_HEADING',
				'COM_JMAP_SEOSPIDER_CANONICAL_SAVE',
				'COM_JMAP_SEOSPIDER_CANONICAL_DELETE',
				'COM_JMAP_SEOSPIDER_CANONICAL_SAVING_OVERRIDE',
				'COM_JMAP_SEOSPIDER_CANONICAL_DELETING_OVERRIDE',
				'COM_JMAP_SEOSPIDER_CANONICAL_SAVED_MESSAGE',
				'COM_JMAP_SEOSPIDER_CANONICAL_EDIT_OVERRIDE',
				'COM_JMAP_SEOSPIDER_CANONICAL_EDIT_OVERRIDE_ACTIVE',
				'COM_JMAP_CANONICAL_URL_REQUIRED',
				'COM_JMAP_ROBOTS_REQUIRED',
				'COM_JMAP_SEOSPIDER_HEADINGS_SAVING_OVERRIDE',
				'COM_JMAP_SEOSPIDER_HEADINGS_DELETING_OVERRIDE',
				'COM_JMAP_SEOSPIDER_HEADINGS_EDIT_OVERRIDE_ACTIVE',
				'COM_JMAP_SEOSPIDER_PAGELOAD_FAST',
				'COM_JMAP_SEOSPIDER_PAGELOAD_AVERAGE',
				'COM_JMAP_SEOSPIDER_PAGELOAD_SLOW',
				'COM_JMAP_EXPORT_XLS',
				'COM_JMAP_REQUIRED',
				'COM_JMAP_PICKFILE',
				'COM_JMAP_STARTIMPORT',
				'COM_JMAP_CANCELIMPORT'
		);
		$this->injectJsTranslations($translations, $doc);
		$doc->getWebAssetManager()->addInlineScript("
						Joomla.submitbutton = function(pressbutton) {
							Joomla.submitform( pressbutton );
							if (pressbutton == 'seospider.exportEntities') {
								jQuery('#adminForm input[name=task]').val('seospider.display');
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
		$this->link_type = $this->getModel ()->getState ('link_type', null);
		$this->searchpageword = $this->getModel ()->getState ('searchpageword', '');
		$this->dataRole = $this->cparams->get('linksanalyzer_indexing_analysis', 1) ? 'link' : 'neutral';
		$this->lists = $lists;
		$this->orders = $orders;
		$this->items = $rows;
		$this->limitValue = $this->getModel ()->getState ( 'limit' );
		
		// Aggiunta toolbar
		$this->addDisplayToolbar();
		
		parent::display ( $tpl );
	}
}