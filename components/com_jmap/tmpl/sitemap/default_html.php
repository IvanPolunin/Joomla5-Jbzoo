<?php
/** 
 * @package JMAP::SITEMAP::components::com_jmap
 * @subpackage views
 * @subpackage sitemap
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;

$classdiv = htmlspecialchars($this->cparams->get ( 'classdiv', 'sitemap' ), ENT_COMPAT, 'UTF-8');
$classdiv = htmlspecialchars($this->cparams->get ( 'pageclass_sfx', $classdiv), ENT_COMPAT, 'UTF-8');
$columnSitemap = $this->cparams->get('column_sitemap', 0);
$sitemapTemplate = $this->cparams->get('sitemap_html_template', '');
$isMindMap = $sitemapTemplate == 'mindmap' ? true : false;
$showTemplate = $this->cparams->get('show_sitemap_icons', 1);
$hideEmptyCats = $this->cparams->get('hide_empty_cats', 0);
$expandTreeState = array();

// Evaluate if tmpl is used for example for component from a custom HTML module IFrame
$isTmpl = $this->app->getInput()->get('tmpl', false);
echo '<div id="jmap_sitemap" class="' . $classdiv . '" data-template="' . $sitemapTemplate . '">';

// Inject custom CSS styles for mindmap template SCK
if($isMindMap && $showTemplate) {
	$subTemplateName = $this->_layout . '_mindmap.php';
	if (file_exists ( JPATH_SITE . '/components/com_jmap/tmpl/sitemap/' . $subTemplateName )) {
		echo $this->loadTemplate ( 'mindmap' );
	}
}

// title
$cshowtitle = $this->cparams->get ( 'show_title', 1 );
$headerlevel = $this->cparams->get ( 'headerlevel', $this->cparams->get ( 'headerlevel', 1 ) );

if ($cshowtitle && !$isTmpl) {
	$titleToUse = $this->cparams->get ( 'title_type', 'maintitle' );
	$defaultTitle = $this->cparams->get ( 'defaulttitle', null );
	if($defaultTitle) {
		$title = $defaultTitle;
	} else {
		$title = $this->cparams->get ( $titleToUse, null );
		if(!$title) {
			$title = $this->menuname;
		}
	}
	echo '<h' . $headerlevel . '>' . $title . '</h' . $headerlevel . '>';
} 

if (isset($this->params) && $this->params->get('show_page_heading', 1) && $this->params->get('page_heading', '')): ?>
	<div class="page-header">
		<?php echo '<h' . ($headerlevel) . '>' . $this->escape($this->params->get('page_heading')) . '</h' . ($headerlevel) . '>';?>
	</div>
<?php endif;

if($this->cparams->get('show_toggler', 0) && !$this->goJsSitemap):?>
	<div id="jmap_toggler"><?php echo Text::_('COM_JMAP_TOGGLER');?> 
		<span style="cursor:pointer" id="jmap_toggler_all"><?php echo Text::_('COM_JMAP_TOGGLER_ALL');?></span>
		<span style="cursor:pointer" id="jmap_toggler_none"><?php echo Text::_('COM_JMAP_TOGGLER_NONE');?></span>
	</div>
<?php endif;

$section_headerlevel = $categorie_headerlevel = $headerlevel + 2;
$title_headerlevel = $headerlevel + 3;

// Init multicolumns
$numColumn = $this->cparams->get('column_maxnum', 3);
$maxPerColumn = 1;
// Find informations for multicolumn data sources
$numDataSources = count($this->data);
$alwaysNewColumn = (bool)($numDataSources <= $numColumn);
if(!$alwaysNewColumn) {
	// Rest data sources for last 3rd column
	$rest = $numDataSources % $numColumn;
	// Integer part for 2 main column
	$integralNum = $numDataSources - $rest;
	// Max Data Sources assigned to single column, following %3 eg. n-2/n-1/n, 6|6|4, 6|6|5, 6|6|6, 7|7|5, 7|7|6, 7|7|7, etc 
	$maxPerColumn = ($integralNum / $numColumn) + ($rest ? 1 : 0);
}

// Init foreach cycle on data sources
$datasourceCounter = 0;
$currentColumn = 1;
foreach ( $this->data as $source ) {
	if($datasourceCounter === 0) {
		echo '<div class="jmapcolumn instance' . $currentColumn . '">';
		$currentColumn = 1;
	} elseif (($datasourceCounter % $maxPerColumn == 0 || $alwaysNewColumn) && !($isMindMap && $showTemplate)) {
		$currentColumn++;
		echo '</div>';
		echo '<div class="jmapcolumn instance' . $currentColumn . '">';
	}
	// Store source type to track changes
	$currentSourceType = $source->type;
	// Strategy pattern source type template visualization
	if ($source->type) {
		$this->source = $source;
		$this->sourceparams = $source->params;
		$this->asCategoryTitleField = $this->findAsCategoryTitleField($source);
		if($this->sourceparams->get('htmlinclude', 1)) {
			$subTemplateName = $this->_layout . '_html_' . $source->type . '.php';
			if (file_exists ( JPATH_SITE . '/components/com_jmap/tmpl/sitemap/' . $subTemplateName )) {
				echo $this->loadTemplate ( 'html_' . $source->type );
			}
		}
		
		// Assign expand tree status based on source type
		switch ($source->type) {
			case 'content':
				$expandTreeState['com_content'] = $this->sourceparams->get('show_content_expanded', '');
				break;

			case 'user':
				$hasCatsNamespace = (bool)(isset($this->asCategoryTitleField) && $this->asCategoryTitleField) ? '.items' : null;
				$expandTreeState[$this->source->chunks->option . $hasCatsNamespace] = $this->sourceparams->get('show_content_expanded', '');
				break;
		}
	}
	$datasourceCounter++;
}
echo '</div></div>';

// Inject column styles based on real columns injected - Discard always the mindmap layout
if($columnSitemap && !($isMindMap && $showTemplate)) {
	$percentage = (int)(100 / $currentColumn);
	$this->document->getWebAssetManager()->addInlineStyle('div.jmapcolumn{float:left;width: ' . $percentage . '%;}#jmap_sitemap{overflow:hidden;}');
	$this->document->getWebAssetManager()->addInlineStyle('@media (max-width:639px) {div.jmapcolumn {width:100%;float: none;}}');
}

// Hide empty cats if required
if($hideEmptyCats) {
	$this->document->getWebAssetManager()->addInlineStyle('li.noexpandable.last{display:none}');
}

// Manage independant content source tree expand
$this->document->getWebAssetManager()->addInlineScript("var jmapExpandContentTree = '" . json_encode($expandTreeState) . "';");

// Add the GOJS Tree Layout application
if($this->cparams->get('treeview_scripts', 1) && $sitemapTemplate == 'gojs') {
	$gojsHeight = str_ireplace('px', '', $this->cparams->get('height_canvas', '1000')) . 'px';
	$gojsWidth = $this->cparams->get('width_canvas', '100%');
	
	$this->document->getWebAssetManager()->registerAndUseScript ('jmap.gojs', 'https://storejextensions.org/cdn/go.js', [], ['defer'=>$this->scriptsLoading]);
	
	$this->document->getWebAssetManager()->addInlineStyle('#gojsjmaptreelayout{width:' . $gojsWidth . ';height:' . $gojsHeight . ';}');
	
	if($this->cparams->get('auto_height_canvas', 1)) {
		$this->document->getWebAssetManager()->addInlineStyle('#gojsjmaptreelayout > div{display:none}');
	}
	
	if($this->cparams->get('instructions_canvas', 1)) {
		echo '<div id="gojsjmaptreelayout_instructions" class="' . $classdiv . '">' . Text::_('COM_JMAP_GOJS_INSTRUCTIONS') . '</div>';
	}
	echo '<div id="gojsjmaptreelayout" class="' . $classdiv . '"></div>';
}