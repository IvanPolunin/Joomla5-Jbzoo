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
use Joomla\Component\Content\Site\Helper\RouteHelper as ContentRouteHelper;

$catsave = null;
$close = '';
$showPageBreaks = $this->cparams->get ( 'show_pagebreaks', 1 );
$openTarget =  $this->sourceparams->get ( 'opentarget', $this->cparams->get ('opentarget') );
$linkableContentCats = $this->sourceparams->get ( 'linkable_content_cats', 0 );
$excludeAllArticles = $this->sourceparams->get ( 'exclude_all_articles', 0 );

// Check if mindmind template
$sitemapTemplate = $this->cparams->get('sitemap_html_template', '');
$isMindMap = $sitemapTemplate == 'mindmap' ? true : false;

$mergeMenuTreeLevels = $this->sourceparams->get ( 'merge_menu_tree_levels', 'toplevel' );
$this->document->getWebAssetManager()->addInlineScript('jmapMergeMenuTreeLevels = "' . $mergeMenuTreeLevels . '";');
if(($mergeMenuTreeMode = $this->sourceparams->get ( 'merge_menu_tree', null )) && $this->cparams->get('treeview_scripts', 1))  {
	$this->document->getWebAssetManager()->addInlineScript('jmapMergeMenuTree["com_content"] = "' . $mergeMenuTreeMode . '";');
}

// Get default menu - home and check if a single article is linked, if so skip to avoid duplicated content
$homeArticleID = false;
$defaultMenu = $this->application->getMenu()->getDefault($this->app->getLanguage()->getTag());
if(	isset($defaultMenu->query['option']) &&
	isset($defaultMenu->query['view']) &&
	$defaultMenu->query['option'] == 'com_content' &&
	$defaultMenu->query['view'] == 'article') {
	$homeArticleID = (int)$defaultMenu->query['id'];
}

if (count ( $this->source->data ) != 0) {
	$first = true;
	
	// If a containing folder is required
	$containingFolderTitle = $this->sourceparams->get('showtitle', 1) && trim($this->sourceparams->get('title', ''));
	if($containingFolderTitle) {
		echo '<ul data-hash="com_content.container" class="jmap_filetree" style="' . $this->marginSide . '0px"><li><span class="folder">' . Text::_($this->sourceparams->get('title')) . '</span>';
	}
	
	if($mergeMenuTreeLevels == 'topchildlevels' && !$containingFolderTitle) {
		echo '<div data-hash="com_content.container">';
	}
	
	foreach ( $this->source->data as $elm ) {
		// Article found as linked to home, skip and avoid duplicate link
		if((int)$elm->id === $homeArticleID) {
			continue;
		}
		
		// Set for empty category root nodes that should not be clickable
		$noExpandableNode = $elm->id ? '' : ' noexpandable';
		if($excludeAllArticles) {
			$noExpandableNode = ' noexpandable';
		}
		$category = ($isMindMap || !$linkableContentCats) ? $elm->category : '<a target="' . $openTarget . '" href="' . $this->liveSite . \JMapRoute::_ ( ContentRouteHelper::getCategoryRoute($elm->catid, $elm->language ) ) . '">' . $elm->category . '</a>';
		if($mergeMenuTreeLevels == 'toplevel' || $mergeMenuTreeLevels == 'topchildlevels') {
			$topLevelCategoryId = $elm->level > 1 ? @$topLevelCategoryId : $elm->catid;
		} else {
			$topLevelCategoryId = $elm->catid;
		}
		
		if ($elm->catid != $catsave && ! $first) {
			echo '</ul></li></ul>';
			echo '<ul data-hash="com_content.category.' . $topLevelCategoryId . '" data-hash-catid="com_content.category.' . $elm->catid . '" data-level="' . $elm->level . '" class="jmap_filetree" style="' . $this->marginSide . (15 * ($elm->level - 1)) . 'px"><li class="' . $noExpandableNode . '"><span class="folder">' . $category . '</span>';
			echo '<ul>';
			$catsave = $elm->catid;
		} else {
			if ($first) {
				echo '<ul data-hash="com_content.category.' . $topLevelCategoryId . '" class="jmap_filetree" style="' . $this->marginSide . (15 * ($elm->level - 1)) . 'px"><li class="' . $noExpandableNode . '"><span class="folder">' . $category . '</span>';
				echo '<ul>';
				$first = false;
				$catsave = $elm->catid;
			}
		}
		
		// The data source could generate only links to categories without sub articles
		if(!$excludeAllArticles) {
			$elm->slug = $elm->alias ? ($elm->id . ':' . $elm->alias) : $elm->id;
			$seolink = $this->liveSite . \JMapRoute::_ ( ContentRouteHelper::getArticleRoute ( $elm->slug, $elm->catslug, $elm->language ) );

			echo '<li>';
			
			if($elm->id) {
				echo '<a target="' . $openTarget . '" href="' . $seolink . '" >' . $elm->title . '</a>';
			}
			
			if(!empty($elm->expandible) && $showPageBreaks) {
				echo '<ul>';
				foreach ($elm->expandible as $index=>$subPageBreak) {
					$seolink = $this->liveSite . \JMapRoute::_ ( ContentRouteHelper::getArticleRoute ( $elm->slug, $elm->catslug, $elm->language ) . '&limitstart=' . ($index + 1));
					echo '<li>' . '<a target="' . $openTarget . '" href="' . $seolink . '" >' . $subPageBreak . '</a></li>';
				}
				echo '</ul>';
			}
			echo '</li>';
		}
	}
	
	echo '</ul></li></ul>';
	
	// If a containing folder is required
	if($containingFolderTitle) {
		echo '</li></ul>';
	}
	
	if($mergeMenuTreeLevels == 'topchildlevels' && !$containingFolderTitle) {
		echo '</div>';
	}
}